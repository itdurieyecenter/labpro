<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model Dashboard Eksekutif
 *
 * Seluruh angka di sini dihitung langsung dari data transaksi setiap kali
 * dashboard dibuka; tidak ada kolom ringkasan yang disimpan, sehingga angka
 * tidak pernah basi. Ambang batas dan target dibaca dari tabel `pengaturan`.
 */
class Dashboard_model extends CI_Model {

    protected $t_reg = 'pendaftaran';
    protected $t_det = 'pendaftaran_detail';

    public function __construct() {
        parent::__construct();
        $this->load->helper('ui_helper');
    }

    public function get_config() {
        return [
            'periode_default' => max(1, (int) get_setting('dashboard_periode_default_hari', 30)),
            'target_tat' => [
                'klinis'     => max(1, (int) get_setting('dashboard_target_tat_klinis_jam', 24)),
                'lingkungan' => max(1, (int) get_setting('dashboard_target_tat_lingkungan_jam', 120)),
                'mcu'        => max(1, (int) get_setting('dashboard_target_tat_mcu_jam', 48)),
                'lainnya'    => max(1, (int) get_setting('dashboard_target_tat_lainnya_jam', 48)),
            ],
            'top_limit'      => max(3, (int) get_setting('dashboard_top_pemeriksaan_limit', 10)),
            'tertunda_limit' => max(3, (int) get_setting('dashboard_order_tertunda_limit', 8)),
            'tren_bulan'     => max(3, (int) get_setting('dashboard_tren_pendapatan_bulan', 12)),
        ];
    }

    /**
     * Ekspresi SQL target TAT per kategori pemeriksaan, dalam satuan jam.
     * Nilai di-cast ke integer sehingga aman disisipkan ke query.
     */
    private function _sql_target_tat($alias = 'p') {
        $t = $this->get_config()['target_tat'];
        return sprintf(
            "CASE %s.kategori_pemeriksaan WHEN 'klinis' THEN %d WHEN 'lingkungan' THEN %d WHEN 'mcu' THEN %d ELSE %d END",
            $alias, (int) $t['klinis'], (int) $t['lingkungan'], (int) $t['mcu'], (int) $t['lainnya']
        );
    }

    private function _rentang($mulai, $selesai) {
        $mulai = $mulai ?: date('Y-m-d', strtotime('-' . ($this->get_config()['periode_default'] - 1) . ' days'));
        $selesai = $selesai ?: date('Y-m-d');
        if (strtotime($mulai) > strtotime($selesai)) {
            list($mulai, $selesai) = [$selesai, $mulai];
        }
        return [date('Y-m-d', strtotime($mulai)), date('Y-m-d', strtotime($selesai))];
    }

    // =====================================================================
    // KARTU RINGKASAN
    // =====================================================================

    public function kpi($mulai = null, $selesai = null) {
        list($mulai, $selesai) = $this->_rentang($mulai, $selesai);

        $kunjungan = $this->db->where('tanggal_pendaftaran >=', $mulai)
                              ->where('tanggal_pendaftaran <=', $selesai)
                              ->where('status_order !=', 'cancelled')
                              ->count_all_results($this->t_reg);

        $hariIni = $this->db->where('tanggal_pendaftaran', date('Y-m-d'))
                            ->where('status_order !=', 'cancelled')
                            ->count_all_results($this->t_reg);

        $kemarin = $this->db->where('tanggal_pendaftaran', date('Y-m-d', strtotime('-1 day')))
                            ->where('status_order !=', 'cancelled')
                            ->count_all_results($this->t_reg);

        $uang = $this->db->select("
                    COALESCE(SUM(CASE WHEN status_pembayaran = 'PAID' THEN total_biaya ELSE 0 END), 0) AS realisasi,
                    COALESCE(SUM(CASE WHEN status_pembayaran IN ('GUARANTEED', 'UNPAID') THEN total_biaya ELSE 0 END), 0) AS piutang,
                    COALESCE(SUM(CASE WHEN status_pembayaran != 'CANCELLED' THEN total_biaya ELSE 0 END), 0) AS tagihan,
                    COALESCE(SUM(CASE WHEN status_pembayaran != 'CANCELLED' THEN total_jasa_dokter + total_jasa_petugas + total_jasa_manajemen ELSE 0 END), 0) AS jasa,
                    COALESCE(SUM(CASE WHEN status_pembayaran != 'CANCELLED' THEN total_bhp + total_kso ELSE 0 END), 0) AS beban
                ", FALSE)
                ->where('tanggal_pendaftaran >=', $mulai)
                ->where('tanggal_pendaftaran <=', $selesai)
                ->where('status_order !=', 'cancelled')
                ->get($this->t_reg)
                ->row_array();

        $parameter = $this->db->from($this->t_det . ' d')
                              ->join($this->t_reg . ' p', 'p.id = d.pendaftaran_id', 'inner')
                              ->where('p.tanggal_pendaftaran >=', $mulai)
                              ->where('p.tanggal_pendaftaran <=', $selesai)
                              ->where('p.status_order !=', 'cancelled')
                              ->count_all_results();

        $aktif = $this->db->where_not_in('status_order', ['completed', 'cancelled'])
                          ->count_all_results($this->t_reg);

        $target = $this->_sql_target_tat('p');
        $tat = $this->db->select("
                    COUNT(*) AS selesai,
                    COALESCE(AVG(TIMESTAMPDIFF(MINUTE, p.created_at, p.waktu_hasil_selesai)), 0) AS rata_menit,
                    COALESCE(SUM(CASE WHEN TIMESTAMPDIFF(MINUTE, p.created_at, p.waktu_hasil_selesai) <= ({$target}) * 60 THEN 1 ELSE 0 END), 0) AS tepat
                ", FALSE)
                ->from($this->t_reg . ' p')
                ->where('p.tanggal_pendaftaran >=', $mulai)
                ->where('p.tanggal_pendaftaran <=', $selesai)
                ->where('p.waktu_hasil_selesai IS NOT NULL', null, FALSE)
                ->where('p.created_at IS NOT NULL', null, FALSE)
                ->get()
                ->row_array();

        $selesaiCount = (int) ($tat['selesai'] ?? 0);

        return [
            'periode'            => ['mulai' => $mulai, 'selesai' => $selesai],
            'kunjungan'          => $kunjungan,
            'kunjungan_hari_ini' => $hariIni,
            'kunjungan_kemarin'  => $kemarin,
            'delta_harian'       => $kemarin > 0 ? round((($hariIni - $kemarin) / $kemarin) * 100, 1) : null,
            'parameter_diuji'    => $parameter,
            'order_aktif'        => $aktif,
            'realisasi'          => (float) ($uang['realisasi'] ?? 0),
            'piutang'            => (float) ($uang['piutang'] ?? 0),
            'tagihan'            => (float) ($uang['tagihan'] ?? 0),
            'jasa'               => (float) ($uang['jasa'] ?? 0),
            'beban'              => (float) ($uang['beban'] ?? 0),
            'tat_selesai'        => $selesaiCount,
            'tat_rata_jam'       => round(((float) ($tat['rata_menit'] ?? 0)) / 60, 1),
            'tat_tepat'          => (int) ($tat['tepat'] ?? 0),
            'tat_ketepatan'      => $selesaiCount > 0 ? round(((int) $tat['tepat'] / $selesaiCount) * 100, 1) : null,
        ];
    }

    // =====================================================================
    // TREN KUNJUNGAN
    // =====================================================================

    /**
     * Jumlah kunjungan per tanggal, sudah diisi nol pada tanggal tanpa
     * transaksi supaya garis grafik tidak melompati hari kosong.
     */
    public function tren_kunjungan($mulai = null, $selesai = null) {
        list($mulai, $selesai) = $this->_rentang($mulai, $selesai);

        $rows = $this->db->select('tanggal_pendaftaran AS tanggal, COUNT(*) AS jumlah', FALSE)
                         ->where('tanggal_pendaftaran >=', $mulai)
                         ->where('tanggal_pendaftaran <=', $selesai)
                         ->where('status_order !=', 'cancelled')
                         ->group_by('tanggal_pendaftaran')
                         ->order_by('tanggal_pendaftaran', 'ASC')
                         ->get($this->t_reg)
                         ->result_array();

        $peta = [];
        foreach ($rows as $r) {
            $peta[$r['tanggal']] = (int) $r['jumlah'];
        }

        $hasil = [];
        $kursor = strtotime($mulai);
        $akhir = strtotime($selesai);
        $batas = 0;
        while ($kursor <= $akhir && $batas < 400) {
            $tgl = date('Y-m-d', $kursor);
            $hasil[] = ['tanggal' => $tgl, 'jumlah' => $peta[$tgl] ?? 0];
            $kursor = strtotime('+1 day', $kursor);
            $batas++;
        }

        return $hasil;
    }

    // =====================================================================
    // KOMPOSISI & UTILISASI
    // =====================================================================

    public function komposisi_kategori($mulai = null, $selesai = null) {
        list($mulai, $selesai) = $this->_rentang($mulai, $selesai);

        $rows = $this->db->select('kategori_pemeriksaan AS kategori, COUNT(*) AS jumlah', FALSE)
                         ->where('tanggal_pendaftaran >=', $mulai)
                         ->where('tanggal_pendaftaran <=', $selesai)
                         ->where('status_order !=', 'cancelled')
                         ->group_by('kategori_pemeriksaan')
                         ->get($this->t_reg)
                         ->result_array();

        $label = ['klinis' => 'Klinis', 'lingkungan' => 'Lingkungan', 'mcu' => 'MCU', 'lainnya' => 'Lainnya'];
        $peta = [];
        foreach ($rows as $r) {
            $peta[$r['kategori']] = (int) $r['jumlah'];
        }

        $hasil = [];
        foreach ($label as $kunci => $nama) {
            $hasil[] = ['kategori' => $kunci, 'nama' => $nama, 'jumlah' => $peta[$kunci] ?? 0];
        }

        return $hasil;
    }

    public function top_pemeriksaan($mulai = null, $selesai = null, $limit = null) {
        list($mulai, $selesai) = $this->_rentang($mulai, $selesai);
        $limit = $limit ?: $this->get_config()['top_limit'];

        return $this->db->select('d.kode_item, d.nama_item, d.kategori_lab, COUNT(*) AS jumlah, COALESCE(SUM(d.tarif), 0) AS nilai', FALSE)
                        ->from($this->t_det . ' d')
                        ->join($this->t_reg . ' p', 'p.id = d.pendaftaran_id', 'inner')
                        ->where('p.tanggal_pendaftaran >=', $mulai)
                        ->where('p.tanggal_pendaftaran <=', $selesai)
                        ->where('p.status_order !=', 'cancelled')
                        ->group_by('d.kode_item')
                        ->order_by('jumlah', 'DESC')
                        ->limit((int) $limit)
                        ->get()
                        ->result_array();
    }

    public function hasil_abnormal($mulai = null, $selesai = null) {
        list($mulai, $selesai) = $this->_rentang($mulai, $selesai);

        $rows = $this->db->select('d.hasil_flag AS flag, COUNT(*) AS jumlah', FALSE)
                         ->from($this->t_det . ' d')
                         ->join($this->t_reg . ' p', 'p.id = d.pendaftaran_id', 'inner')
                         ->where('p.tanggal_pendaftaran >=', $mulai)
                         ->where('p.tanggal_pendaftaran <=', $selesai)
                         ->where('d.hasil_flag IS NOT NULL', null, FALSE)
                         ->where('d.hasil_nilai IS NOT NULL', null, FALSE)
                         ->group_by('d.hasil_flag')
                         ->get()
                         ->result_array();

        $label = ['normal' => 'Normal', 'low' => 'Di Bawah Rujukan', 'high' => 'Di Atas Rujukan', 'critical' => 'Kritis'];
        $peta = [];
        foreach ($rows as $r) {
            $peta[$r['flag']] = (int) $r['jumlah'];
        }

        $hasil = [];
        foreach ($label as $kunci => $nama) {
            $hasil[] = ['flag' => $kunci, 'nama' => $nama, 'jumlah' => $peta[$kunci] ?? 0];
        }

        return $hasil;
    }

    // =====================================================================
    // TURN AROUND TIME
    // =====================================================================

    /**
     * Rata-rata lama tiap tahap dalam jam. Tahap yang belum pernah terisi
     * waktunya dikembalikan sebagai null, bukan nol, supaya tidak terbaca
     * sebagai "cepat sekali".
     */
    public function tat_per_tahap($mulai = null, $selesai = null) {
        list($mulai, $selesai) = $this->_rentang($mulai, $selesai);

        $row = $this->db->select("
                    COALESCE(AVG(CASE WHEN waktu_sampling IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, created_at, waktu_sampling) END), -1) AS registrasi_sampling,
                    COALESCE(AVG(CASE WHEN waktu_sampling IS NOT NULL AND waktu_hasil_selesai IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, waktu_sampling, waktu_hasil_selesai) END), -1) AS sampling_hasil,
                    COALESCE(AVG(CASE WHEN waktu_hasil_selesai IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, created_at, waktu_hasil_selesai) END), -1) AS total,
                    SUM(CASE WHEN waktu_sampling IS NULL THEN 1 ELSE 0 END) AS tanpa_sampling,
                    SUM(CASE WHEN waktu_hasil_selesai IS NULL THEN 1 ELSE 0 END) AS tanpa_hasil,
                    COUNT(*) AS total_order
                ", FALSE)
                ->where('tanggal_pendaftaran >=', $mulai)
                ->where('tanggal_pendaftaran <=', $selesai)
                ->where('status_order !=', 'cancelled')
                ->get($this->t_reg)
                ->row_array();

        $jam = function ($menit) {
            return ((float) $menit) < 0 ? null : round(((float) $menit) / 60, 1);
        };

        return [
            'registrasi_sampling' => $jam($row['registrasi_sampling'] ?? -1),
            'sampling_hasil'      => $jam($row['sampling_hasil'] ?? -1),
            'total'               => $jam($row['total'] ?? -1),
            'tanpa_sampling'      => (int) ($row['tanpa_sampling'] ?? 0),
            'tanpa_hasil'         => (int) ($row['tanpa_hasil'] ?? 0),
            'total_order'         => (int) ($row['total_order'] ?? 0),
        ];
    }

    public function tat_per_kategori($mulai = null, $selesai = null) {
        list($mulai, $selesai) = $this->_rentang($mulai, $selesai);
        $target = $this->_sql_target_tat('p');

        $rows = $this->db->select("
                    p.kategori_pemeriksaan AS kategori,
                    COUNT(*) AS selesai,
                    COALESCE(AVG(TIMESTAMPDIFF(MINUTE, p.created_at, p.waktu_hasil_selesai)), 0) AS rata_menit,
                    COALESCE(SUM(CASE WHEN TIMESTAMPDIFF(MINUTE, p.created_at, p.waktu_hasil_selesai) <= ({$target}) * 60 THEN 1 ELSE 0 END), 0) AS tepat,
                    MAX({$target}) AS target_jam
                ", FALSE)
                ->from($this->t_reg . ' p')
                ->where('p.tanggal_pendaftaran >=', $mulai)
                ->where('p.tanggal_pendaftaran <=', $selesai)
                ->where('p.waktu_hasil_selesai IS NOT NULL', null, FALSE)
                ->group_by('p.kategori_pemeriksaan')
                ->get()
                ->result_array();

        $label = ['klinis' => 'Klinis', 'lingkungan' => 'Lingkungan', 'mcu' => 'MCU', 'lainnya' => 'Lainnya'];
        $hasil = [];
        foreach ($rows as $r) {
            $jml = max(1, (int) $r['selesai']);
            $hasil[] = [
                'kategori'   => $r['kategori'],
                'nama'       => $label[$r['kategori']] ?? ucfirst((string) $r['kategori']),
                'selesai'    => (int) $r['selesai'],
                'rata_jam'   => round(((float) $r['rata_menit']) / 60, 1),
                'target_jam' => (int) $r['target_jam'],
                'tepat'      => (int) $r['tepat'],
                'ketepatan'  => round(((int) $r['tepat'] / $jml) * 100, 1),
            ];
        }

        return $hasil;
    }

    // =====================================================================
    // PENDAPATAN
    // =====================================================================

    public function tren_pendapatan($bulan = null) {
        $bulan = $bulan ?: $this->get_config()['tren_bulan'];
        $awal = date('Y-m-01', strtotime('-' . ((int) $bulan - 1) . ' months'));

        $rows = $this->db->select("
                    DATE_FORMAT(tanggal_pendaftaran, '%Y-%m') AS periode,
                    COALESCE(SUM(CASE WHEN status_pembayaran = 'PAID' THEN total_biaya ELSE 0 END), 0) AS realisasi,
                    COALESCE(SUM(CASE WHEN status_pembayaran IN ('GUARANTEED', 'UNPAID') THEN total_biaya ELSE 0 END), 0) AS piutang,
                    COUNT(*) AS kunjungan
                ", FALSE)
                ->where('tanggal_pendaftaran >=', $awal)
                ->where('status_order !=', 'cancelled')
                ->group_by('periode')
                ->order_by('periode', 'ASC')
                ->get($this->t_reg)
                ->result_array();

        $peta = [];
        foreach ($rows as $r) {
            $peta[$r['periode']] = $r;
        }

        $namaBulan = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $hasil = [];
        for ($i = (int) $bulan - 1; $i >= 0; $i--) {
            $stamp = strtotime("-{$i} months", strtotime(date('Y-m-01')));
            $periode = date('Y-m', $stamp);
            $baris = $peta[$periode] ?? null;
            $hasil[] = [
                'periode'    => $periode,
                'label'      => $namaBulan[(int) date('n', $stamp)] . ' ' . date('y', $stamp),
                'realisasi'  => (float) ($baris['realisasi'] ?? 0),
                'piutang'    => (float) ($baris['piutang'] ?? 0),
                'kunjungan'  => (int) ($baris['kunjungan'] ?? 0),
            ];
        }

        return $hasil;
    }

    public function pendapatan_per_tipe($mulai = null, $selesai = null) {
        list($mulai, $selesai) = $this->_rentang($mulai, $selesai);

        return $this->db->select("
                    kelompok_tarif,
                    COUNT(*) AS kunjungan,
                    COALESCE(SUM(total_biaya), 0) AS nilai,
                    COALESCE(SUM(diskon_nominal), 0) AS diskon
                ", FALSE)
                ->where('tanggal_pendaftaran >=', $mulai)
                ->where('tanggal_pendaftaran <=', $selesai)
                ->where('status_order !=', 'cancelled')
                ->group_by('kelompok_tarif')
                ->order_by('nilai', 'DESC')
                ->get($this->t_reg)
                ->result_array();
    }

    // =====================================================================
    // PANTAUAN OPERASIONAL
    // =====================================================================

    public function distribusi_status($mulai = null, $selesai = null) {
        list($mulai, $selesai) = $this->_rentang($mulai, $selesai);

        $rows = $this->db->select('status_order AS status, COUNT(*) AS jumlah', FALSE)
                         ->where('tanggal_pendaftaran >=', $mulai)
                         ->where('tanggal_pendaftaran <=', $selesai)
                         ->group_by('status_order')
                         ->get($this->t_reg)
                         ->result_array();

        $label = [
            'registered' => 'Terdaftar',
            'sampling'   => 'Pengambilan Sampel',
            'processing'  => 'Pengujian',
            'validasi'   => 'Menunggu Validasi',
            'completed'  => 'Selesai',
            'cancelled'  => 'Dibatalkan',
        ];
        $peta = [];
        foreach ($rows as $r) {
            $peta[$r['status']] = (int) $r['jumlah'];
        }

        $hasil = [];
        foreach ($label as $kunci => $nama) {
            $hasil[] = ['status' => $kunci, 'nama' => $nama, 'jumlah' => $peta[$kunci] ?? 0];
        }

        return $hasil;
    }

    /**
     * Order yang belum selesai, diurutkan dari yang paling lama menunggu.
     * Status melewati target TAT dihitung dari ambang pada tabel pengaturan.
     */
    public function order_tertunda($limit = null) {
        $limit = $limit ?: $this->get_config()['tertunda_limit'];
        $target = $this->_sql_target_tat('p');

        $rows = $this->db->select("
                    p.id, p.nomor_registrasi, p.nama_pasien, p.kategori_pemeriksaan, p.jenis_sampel,
                    p.status_order, p.tanggal_pendaftaran, p.created_at, p.waktu_sampling,
                    TIMESTAMPDIFF(MINUTE, p.created_at, NOW()) AS umur_menit,
                    ({$target}) AS target_jam
                ", FALSE)
                ->from($this->t_reg . ' p')
                ->where_not_in('p.status_order', ['completed', 'cancelled'])
                ->order_by('p.created_at', 'ASC')
                ->limit((int) $limit)
                ->get()
                ->result_array();

        foreach ($rows as &$r) {
            $umurJam = round(((float) $r['umur_menit']) / 60, 1);
            $targetJam = max(1, (int) $r['target_jam']);
            $r['umur_jam'] = $umurJam;
            $r['target_jam'] = $targetJam;
            $r['persen_target'] = round(($umurJam / $targetJam) * 100);

            if ($umurJam > $targetJam) {
                $r['status_tat'] = 'terlewat';
            } elseif ($umurJam >= $targetJam * 0.75) {
                $r['status_tat'] = 'mendekati';
            } else {
                $r['status_tat'] = 'aman';
            }
        }

        return $rows;
    }

    // =====================================================================
    // PERINGATAN LOGISTIK & ALAT
    // =====================================================================

    /**
     * Mengambil peringatan dari modul Gudang dan Aset lewat model masing-masing
     * supaya definisi "kritis" tetap satu sumber, tidak diduplikasi di sini.
     */
    public function peringatan() {
        $hasil = [
            'gudang' => null,
            'aset' => null,
            'stok_kritis' => [],
            'batch_kedaluwarsa' => [],
            'kalibrasi' => [],
        ];

        if ($this->db->table_exists('gudang_barang')) {
            $this->load->model('gudang_model');
            $hasil['gudang'] = $this->gudang_model->ringkasan();
            $hasil['stok_kritis'] = array_slice($this->gudang_model->get_stok_kritis(), 0, 6);
            $hasil['batch_kedaluwarsa'] = array_slice($this->gudang_model->get_batch_kedaluwarsa(), 0, 6);
        }

        if ($this->db->table_exists('aset_barang')) {
            $this->load->model('aset_model');
            $hasil['aset'] = $this->aset_model->ringkasan();
            $hasil['kalibrasi'] = array_slice($this->aset_model->get_kalibrasi_perhatian(), 0, 6);
        }

        return $hasil;
    }
}
