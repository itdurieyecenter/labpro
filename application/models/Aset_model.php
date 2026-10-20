<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model Aset & Inventaris Laboratorium (KIB-B Peralatan dan Mesin).
 *
 * Mengikuti tiga acuan:
 *   - Permendagri 108/2016 : kodefikasi BMD dan Nomor Urut Pendaftaran
 *   - Permendagri 47/2021  : penyusutan garis lurus tanpa nilai residu
 *   - ISO/IEC 17025        : instrumen ukur wajib terkalibrasi dan masih
 *                            berlaku; yang kedaluwarsa dinyatakan tidak laik
 *
 * Status kalibrasi dan jadwal pemeliharaan tidak disimpan sebagai kolom,
 * melainkan diturunkan dari riwayat terakhir. Dengan begitu nilainya mustahil
 * basi terhadap catatan yang sebenarnya.
 */
class Aset_model extends CI_Model {

    protected $table_ref = 'aset_referensi';
    protected $table_aset = 'aset_barang';
    protected $table_kalibrasi = 'aset_kalibrasi';
    protected $table_pemeliharaan = 'aset_pemeliharaan';
    protected $table_mutasi = 'aset_mutasi';
    protected $table_pegawai = 'pegawai';

    public function __construct() {
        parent::__construct();
    }

    // =====================================================================
    // KONFIGURASI & UTILITAS
    // =====================================================================

    public function get_config() {
        return [
            'alert_kalibrasi' => (int)get_setting('aset_alert_kalibrasi_hari', 90),
            'kritis_kalibrasi' => (int)get_setting('aset_alert_kalibrasi_kritis', 30),
            'alert_pemeliharaan' => (int)get_setting('aset_alert_pemeliharaan_hari', 30),
            'prefix_mutasi' => get_setting('aset_prefix_mutasi', 'MA'),
            'masa_manfaat_default' => (int)get_setting('aset_masa_manfaat_default', 8),
            'blokir_tak_laik' => (int)get_setting('aset_blokir_tak_laik', 1) === 1,
        ];
    }

    public function bulan_romawi($bulan) {
        $map = [1 => 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        return $map[(int)$bulan] ?? 'I';
    }

    public function get_referensi($kategori, $activeOnly = true) {
        if ($activeOnly) {
            $this->db->where('is_active', 1);
        }
        return $this->db->where('kategori', $kategori)
                        ->order_by('sort_order', 'ASC')
                        ->get($this->table_ref)
                        ->result_array();
    }

    public function generate_nomor_mutasi() {
        $cfg = $this->get_config();
        $tahun = date('Y');

        $last = $this->db->select('nomor_transaksi')
                         ->like('nomor_transaksi', $cfg['prefix_mutasi'] . '/', 'after')
                         ->like('nomor_transaksi', '/' . $tahun, 'before')
                         ->order_by('id', 'DESC')
                         ->limit(1)
                         ->get($this->table_mutasi)
                         ->row_array();

        $urut = 1;
        if ($last && preg_match('#/(\d+)/#', $last['nomor_transaksi'], $m)) {
            $urut = (int)$m[1] + 1;
        }

        return sprintf('%s/%04d/%s/%s', $cfg['prefix_mutasi'], $urut, $this->bulan_romawi(date('n')), $tahun);
    }

    /**
     * Nomor Urut Pendaftaran berikutnya untuk satu kode BMD.
     * Dua unit barang dengan kode BMD sama dibedakan oleh NUP-nya.
     */
    public function get_next_nup($kodeBmd) {
        $row = $this->db->select_max('nup')
                        ->where('kode_bmd', $kodeBmd)
                        ->get($this->table_aset)
                        ->row_array();
        return (int)($row['nup'] ?? 0) + 1;
    }

    public function susun_nomor_register($kodeBmd, $nup) {
        return $kodeBmd . '-' . str_pad((string)$nup, 4, '0', STR_PAD_LEFT);
    }

    // =====================================================================
    // PENYUSUTAN (PERMENDAGRI 47/2021, GARIS LURUS TANPA NILAI RESIDU)
    // =====================================================================

    public function hitung_penyusutan($aset) {
        $nilai = (float)($aset['nilai_perolehan'] ?? 0);
        $masaManfaat = (int)($aset['masa_manfaat_tahun'] ?? 0);
        $tahunPerolehan = (int)($aset['tahun_perolehan'] ?? 0);

        if ($nilai <= 0 || $masaManfaat <= 0 || $tahunPerolehan <= 0) {
            return [
                'per_tahun' => 0, 'umur_tahun' => 0, 'akumulasi' => 0,
                'nilai_buku' => $nilai, 'persen_susut' => 0, 'habis_manfaat' => false,
            ];
        }

        $perTahun = $nilai / $masaManfaat;
        $umur = max(0, (int)date('Y') - $tahunPerolehan);
        $umurEfektif = min($umur, $masaManfaat);

        $akumulasi = round($perTahun * $umurEfektif, 2);
        $nilaiBuku = max(0, round($nilai - $akumulasi, 2));

        return [
            'per_tahun' => round($perTahun, 2),
            'umur_tahun' => $umur,
            'umur_efektif' => $umurEfektif,
            'akumulasi' => $akumulasi,
            'nilai_buku' => $nilaiBuku,
            'persen_susut' => $nilai > 0 ? (int)round(($akumulasi / $nilai) * 100) : 0,
            'habis_manfaat' => $umur >= $masaManfaat,
        ];
    }

    // =====================================================================
    // STATUS KALIBRASI (ISO/IEC 17025)
    // =====================================================================

    public function get_kalibrasi_terakhir($asetId) {
        return $this->db->where('aset_id', (int)$asetId)
                        ->order_by('tanggal_kalibrasi', 'DESC')
                        ->order_by('id', 'DESC')
                        ->limit(1)
                        ->get($this->table_kalibrasi)
                        ->row_array();
    }

    /**
     * Nilai status kalibrasi satu aset berikut kelaikan pakainya.
     *
     * Instrumen yang tidak wajib kalibrasi selalu dianggap laik. Untuk yang
     * wajib, kalibrasi kedaluwarsa, hasil tidak laik, atau belum pernah
     * dikalibrasi sama-sama berarti alat tidak boleh dipakai menguji.
     */
    public function status_kalibrasi($aset, $cfg = null) {
        $cfg = $cfg ?: $this->get_config();

        if ((int)($aset['wajib_kalibrasi'] ?? 0) !== 1) {
            return [
                'status' => 'tidak_wajib', 'label' => 'Tidak Wajib Kalibrasi',
                'sisa_hari' => null, 'berlaku_sampai' => null, 'laik_pakai' => true,
            ];
        }

        $last = $this->get_kalibrasi_terakhir((int)$aset['id']);

        if (!$last) {
            return [
                'status' => 'belum_pernah', 'label' => 'Belum Pernah Dikalibrasi',
                'sisa_hari' => null, 'berlaku_sampai' => null, 'laik_pakai' => false,
            ];
        }

        if ($last['hasil'] === 'tidak_laik') {
            return [
                'status' => 'tidak_laik', 'label' => 'Hasil Kalibrasi Tidak Laik',
                'sisa_hari' => null, 'berlaku_sampai' => $last['masa_berlaku_sampai'],
                'laik_pakai' => false, 'sertifikat' => $last['nomor_sertifikat'],
            ];
        }

        $sisa = (int)floor((strtotime($last['masa_berlaku_sampai']) - strtotime(date('Y-m-d'))) / 86400);

        if ($sisa < 0) {
            $status = 'kedaluwarsa'; $label = 'Kalibrasi Kedaluwarsa'; $laik = false;
        } elseif ($sisa <= $cfg['kritis_kalibrasi']) {
            $status = 'kritis'; $label = 'Segera Kalibrasi Ulang'; $laik = true;
        } elseif ($sisa <= $cfg['alert_kalibrasi']) {
            $status = 'peringatan'; $label = 'Perlu Dijadwalkan'; $laik = true;
        } else {
            $status = 'laik'; $label = 'Terkalibrasi'; $laik = true;
        }

        return [
            'status' => $status,
            'label' => $label,
            'sisa_hari' => $sisa,
            'berlaku_sampai' => $last['masa_berlaku_sampai'],
            'laik_pakai' => $laik,
            'sertifikat' => $last['nomor_sertifikat'],
            'lembaga' => $last['lembaga_kalibrasi'],
            'hasil' => $last['hasil'],
        ];
    }

    // =====================================================================
    // JADWAL PEMELIHARAAN
    // =====================================================================

    public function status_pemeliharaan($aset, $cfg = null) {
        $cfg = $cfg ?: $this->get_config();

        if ((int)($aset['wajib_pemeliharaan'] ?? 0) !== 1) {
            return ['status' => 'tidak_wajib', 'label' => 'Tidak Dijadwalkan', 'jatuh_tempo' => null, 'sisa_hari' => null];
        }

        $interval = max(1, (int)($aset['interval_pemeliharaan_bulan'] ?? 6));

        $last = $this->db->where('aset_id', (int)$aset['id'])
                         ->where('status', 'selesai')
                         ->where('tanggal_realisasi IS NOT NULL', null, false)
                         ->order_by('tanggal_realisasi', 'DESC')
                         ->limit(1)
                         ->get($this->table_pemeliharaan)
                         ->row_array();

        // Bila belum pernah dipelihara, hitung dari tanggal perolehan.
        $acuan = $last['tanggal_realisasi'] ?? ($aset['tanggal_perolehan'] ?? null);
        if (empty($acuan)) {
            return ['status' => 'belum_terjadwal', 'label' => 'Belum Ada Acuan', 'jatuh_tempo' => null, 'sisa_hari' => null];
        }

        $jatuhTempo = date('Y-m-d', strtotime($acuan . ' +' . $interval . ' months'));
        $sisa = (int)floor((strtotime($jatuhTempo) - strtotime(date('Y-m-d'))) / 86400);

        if ($sisa < 0) {
            $status = 'terlewat'; $label = 'Terlewat';
        } elseif ($sisa <= $cfg['alert_pemeliharaan']) {
            $status = 'segera'; $label = 'Segera Jatuh Tempo';
        } else {
            $status = 'terjadwal'; $label = 'Terjadwal';
        }

        return [
            'status' => $status, 'label' => $label,
            'jatuh_tempo' => $jatuhTempo, 'sisa_hari' => $sisa,
            'terakhir' => $last['tanggal_realisasi'] ?? null,
        ];
    }

    // =====================================================================
    // MASTER ASET
    // =====================================================================

    /**
     * Lengkapi baris aset dengan seluruh nilai turunan.
     */
    public function lengkapi($aset, $cfg = null) {
        if (!$aset) return null;
        $cfg = $cfg ?: $this->get_config();

        $aset['penyusutan'] = $this->hitung_penyusutan($aset);
        $aset['kalibrasi'] = $this->status_kalibrasi($aset, $cfg);
        $aset['pemeliharaan'] = $this->status_pemeliharaan($aset, $cfg);

        // Alat dinyatakan tidak laik pakai bila kalibrasinya bermasalah atau
        // kondisinya rusak berat. Ini yang dipakai memblokir pemakaian.
        $aset['laik_pakai'] = $aset['kalibrasi']['laik_pakai']
                              && $aset['kondisi'] !== 'rusak_berat'
                              && $aset['status'] !== 'dihapuskan';

        return $aset;
    }

    public function get_by_id($id) {
        $row = $this->db->where('id', (int)$id)->get($this->table_aset)->row_array();
        if (!$row) return null;

        $row = $this->lengkapi($row);
        $row['riwayat_kalibrasi'] = $this->db->where('aset_id', (int)$id)
                                             ->order_by('tanggal_kalibrasi', 'DESC')
                                             ->get($this->table_kalibrasi)->result_array();
        $row['riwayat_pemeliharaan'] = $this->db->where('aset_id', (int)$id)
                                                ->order_by('COALESCE(tanggal_realisasi, tanggal_rencana)', 'DESC', FALSE)
                                                ->get($this->table_pemeliharaan)->result_array();
        $row['riwayat_mutasi'] = $this->db->where('aset_id', (int)$id)
                                          ->order_by('tanggal', 'DESC')->order_by('id', 'DESC')
                                          ->get($this->table_mutasi)->result_array();
        return $row;
    }

    public function get_datatable($params) {
        $draw = (int)($params['draw'] ?? 1);
        $start = (int)($params['start'] ?? 0);
        $length = (int)($params['length'] ?? 10);
        $search = trim($params['search']['value'] ?? '');

        $recordsTotal = $this->db->count_all_results($this->table_aset);

        $this->db->from($this->table_aset);
        $this->_filter($params, $search);
        $recordsFiltered = $this->db->count_all_results('', FALSE);

        $this->db->order_by('nama_aset', 'ASC');
        if ($length > 0) {
            $this->db->limit($length, $start);
        }

        $rows = $this->db->get()->result_array();
        $cfg = $this->get_config();

        foreach ($rows as &$r) {
            $r = $this->lengkapi($r, $cfg);
        }
        unset($r);

        // Penyaring status kalibrasi diterapkan setelah nilai turunan dihitung.
        if (!empty($params['status_kalibrasi']) && $params['status_kalibrasi'] !== 'all') {
            $rows = array_values(array_filter($rows, function ($r) use ($params) {
                return $r['kalibrasi']['status'] === $params['status_kalibrasi'];
            }));
        }

        return [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $rows,
        ];
    }

    private function _filter($params, $search) {
        if ($search !== '') {
            $this->db->group_start()
                     ->like('nama_aset', $search)
                     ->or_like('nomor_register', $search)
                     ->or_like('kode_bmd', $search)
                     ->or_like('nomor_seri', $search)
                     ->or_like('merk_tipe', $search)
                     ->group_end();
        }
        if (!empty($params['golongan']) && $params['golongan'] !== 'all') {
            $this->db->where('golongan', $params['golongan']);
        }
        if (!empty($params['lokasi_ruangan']) && $params['lokasi_ruangan'] !== 'all') {
            $this->db->where('lokasi_ruangan', $params['lokasi_ruangan']);
        }
        if (!empty($params['kondisi']) && $params['kondisi'] !== 'all') {
            $this->db->where('kondisi', $params['kondisi']);
        }
        if (!empty($params['status']) && $params['status'] !== 'all') {
            $this->db->where('status', $params['status']);
        }
    }

    public function save_aset($data, $id = null, $userId = null, $userNama = 'Petugas') {
        $kodeBmd = trim($data['kode_bmd']);
        if ($kodeBmd === '') {
            return ['status' => false, 'message' => 'Kode BMD wajib diisi sesuai kodefikasi Permendagri 108/2016.'];
        }

        $masaManfaat = (int)$data['masa_manfaat_tahun'];
        if ($masaManfaat <= 0) {
            $masaManfaat = $this->get_config()['masa_manfaat_default'];
        }

        $pegawai = null;
        if (!empty($data['penanggung_jawab_id'])) {
            $pegawai = $this->db->where('id', (int)$data['penanggung_jawab_id'])
                                ->get($this->table_pegawai)->row_array();
        }

        $payload = [
            'kode_bmd' => $kodeBmd,
            'golongan' => $data['golongan'],
            'nama_aset' => trim($data['nama_aset']),
            'merk_tipe' => $data['merk_tipe'] ?: null,
            'nomor_seri' => $data['nomor_seri'] ?: null,
            'ukuran_spesifikasi' => $data['ukuran_spesifikasi'] ?: null,
            'bahan' => $data['bahan'] ?: null,
            'tanggal_perolehan' => $data['tanggal_perolehan'] ?: null,
            'tahun_perolehan' => !empty($data['tanggal_perolehan']) ? (int)date('Y', strtotime($data['tanggal_perolehan'])) : null,
            'cara_perolehan' => $data['cara_perolehan'] ?: null,
            'nomor_dokumen_perolehan' => $data['nomor_dokumen_perolehan'] ?: null,
            'nilai_perolehan' => (float)$data['nilai_perolehan'],
            'masa_manfaat_tahun' => $masaManfaat,
            'lokasi_ruangan' => $data['lokasi_ruangan'] ?: null,
            'penanggung_jawab_id' => $pegawai ? (int)$pegawai['id'] : null,
            'penanggung_jawab_nama' => $pegawai ? $this->_nama_pegawai($pegawai) : null,
            'kondisi' => $data['kondisi'],
            'wajib_kalibrasi' => (int)$data['wajib_kalibrasi'],
            'interval_kalibrasi_bulan' => max(1, (int)$data['interval_kalibrasi_bulan']),
            'wajib_pemeliharaan' => (int)$data['wajib_pemeliharaan'],
            'interval_pemeliharaan_bulan' => max(1, (int)$data['interval_pemeliharaan_bulan']),
            'keterangan' => $data['keterangan'] ?: null,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($id) {
            $lama = $this->db->where('id', (int)$id)->get($this->table_aset)->row_array();
            if (!$lama) {
                return ['status' => false, 'message' => 'Aset tidak ditemukan.'];
            }

            $this->db->where('id', (int)$id)->update($this->table_aset, $payload);

            // Perubahan ruangan atau kondisi dicatat sebagai mutasi agar
            // rekam jejaknya tetap utuh.
            if ($lama['lokasi_ruangan'] !== $payload['lokasi_ruangan']) {
                $this->catat_mutasi((int)$id, 'pindah_ruangan', [
                    'tanggal' => date('Y-m-d'),
                    'dari_ruangan' => $lama['lokasi_ruangan'],
                    'ke_ruangan' => $payload['lokasi_ruangan'],
                    'keterangan' => 'Perubahan lokasi melalui penyuntingan data aset',
                ], $userId, $userNama);
            }
            if ($lama['kondisi'] !== $payload['kondisi']) {
                $this->catat_mutasi((int)$id, 'perubahan_kondisi', [
                    'tanggal' => date('Y-m-d'),
                    'dari_kondisi' => $lama['kondisi'],
                    'ke_kondisi' => $payload['kondisi'],
                    'keterangan' => 'Perubahan kondisi melalui penyuntingan data aset',
                ], $userId, $userNama);
            }

            return ['status' => true, 'id' => (int)$id, 'data' => $this->get_by_id((int)$id)];
        }

        $nup = !empty($data['nup']) ? (int)$data['nup'] : $this->get_next_nup($kodeBmd);

        $bentrok = $this->db->where('kode_bmd', $kodeBmd)->where('nup', $nup)
                            ->get($this->table_aset)->row_array();
        if ($bentrok) {
            return ['status' => false, 'message' => 'Kode BMD ' . $kodeBmd . ' dengan NUP ' . $nup . ' sudah terpakai oleh ' . $bentrok['nama_aset'] . '.'];
        }

        $payload['nup'] = $nup;
        $payload['nomor_register'] = $this->susun_nomor_register($kodeBmd, $nup);
        $payload['status'] = 'aktif';
        $payload['created_at'] = date('Y-m-d H:i:s');

        $this->db->insert($this->table_aset, $payload);
        $savedId = (int)$this->db->insert_id();

        $this->catat_mutasi($savedId, 'penempatan_awal', [
            'tanggal' => $payload['tanggal_perolehan'] ?: date('Y-m-d'),
            'ke_ruangan' => $payload['lokasi_ruangan'],
            'ke_kondisi' => $payload['kondisi'],
            'ke_status' => 'aktif',
            'nomor_dokumen' => $payload['nomor_dokumen_perolehan'],
            'keterangan' => 'Pencatatan awal aset ke dalam inventaris',
        ], $userId, $userNama);

        return ['status' => true, 'id' => $savedId, 'data' => $this->get_by_id($savedId)];
    }

    private function _nama_pegawai($p) {
        $nama = trim($p['nama_lengkap'] ?? '');
        $depan = trim($p['gelar_depan'] ?? '');
        $belakang = trim($p['gelar_belakang'] ?? '');
        $hasil = ($depan !== '' ? $depan . ' ' : '') . $nama;
        return $belakang !== '' ? $hasil . ', ' . $belakang : $hasil;
    }

    /**
     * Aset hanya boleh dihapus selama belum memiliki riwayat apa pun. Aset yang
     * sudah bermutasi cukup dihapuskan secara administratif lewat penghapusan BMD.
     */
    public function hapus_aset($id) {
        $aset = $this->db->where('id', (int)$id)->get($this->table_aset)->row_array();
        if (!$aset) {
            return ['status' => false, 'message' => 'Aset tidak ditemukan.'];
        }

        $jmlKal = $this->db->where('aset_id', (int)$id)->count_all_results($this->table_kalibrasi);
        $jmlPel = $this->db->where('aset_id', (int)$id)->count_all_results($this->table_pemeliharaan);
        $jmlMut = $this->db->where('aset_id', (int)$id)->where('tipe !=', 'penempatan_awal')
                           ->count_all_results($this->table_mutasi);

        if ($jmlKal + $jmlPel + $jmlMut > 0) {
            return [
                'status' => false,
                'message' => 'Aset ini sudah memiliki riwayat (' . $jmlKal . ' kalibrasi, ' . $jmlPel . ' pemeliharaan, ' . $jmlMut . ' mutasi) sehingga tidak dapat dihapus dari basis data. Gunakan menu Mutasi & Penghapusan agar rekam jejaknya tetap utuh.'
            ];
        }

        $this->db->where('id', (int)$id)->delete($this->table_aset);
        return ['status' => true, 'message' => 'Aset ' . $aset['nama_aset'] . ' (' . $aset['nomor_register'] . ') dihapus.'];
    }

    // =====================================================================
    // KALIBRASI
    // =====================================================================

    public function simpan_kalibrasi($data, $userNama = 'Petugas') {
        $aset = $this->db->where('id', (int)$data['aset_id'])->get($this->table_aset)->row_array();
        if (!$aset) {
            return ['status' => false, 'message' => 'Aset tidak ditemukan.'];
        }

        $tglKal = $data['tanggal_kalibrasi'];
        $berlaku = $data['masa_berlaku_sampai'];

        // Bila masa berlaku tidak diisi, turunkan dari interval kalibrasi aset.
        if (empty($berlaku)) {
            $berlaku = date('Y-m-d', strtotime($tglKal . ' +' . max(1, (int)$aset['interval_kalibrasi_bulan']) . ' months'));
        }

        if (strtotime($berlaku) <= strtotime($tglKal)) {
            return ['status' => false, 'message' => 'Masa berlaku kalibrasi harus lebih akhir dari tanggal kalibrasi.'];
        }

        $this->db->insert($this->table_kalibrasi, [
            'aset_id' => (int)$aset['id'],
            'tanggal_kalibrasi' => $tglKal,
            'masa_berlaku_sampai' => $berlaku,
            'lembaga_kalibrasi' => trim($data['lembaga_kalibrasi']),
            'nomor_sertifikat' => $data['nomor_sertifikat'] ?: null,
            'terakreditasi_kan' => (int)$data['terakreditasi_kan'],
            'hasil' => $data['hasil'],
            'nilai_koreksi' => $data['nilai_koreksi'] ?: null,
            'biaya' => (float)$data['biaya'],
            'catatan' => $data['catatan'] ?: null,
            'dicatat_oleh' => $userNama,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $pesan = 'Kalibrasi ' . $aset['nama_aset'] . ' tercatat, berlaku sampai ' . $berlaku . '.';
        if ($data['hasil'] === 'tidak_laik') {
            $pesan .= ' Hasil tidak laik, alat ditandai dilarang dipakai menguji.';
        }

        return ['status' => true, 'message' => $pesan, 'id' => (int)$this->db->insert_id()];
    }

    /**
     * Daftar aset yang kalibrasinya bermasalah atau akan segera habis.
     */
    public function get_kalibrasi_perhatian() {
        $cfg = $this->get_config();

        $rows = $this->db->where('wajib_kalibrasi', 1)
                         ->where('status !=', 'dihapuskan')
                         ->order_by('nama_aset', 'ASC')
                         ->get($this->table_aset)
                         ->result_array();

        $hasil = [];
        foreach ($rows as $r) {
            $r = $this->lengkapi($r, $cfg);
            if (in_array($r['kalibrasi']['status'], ['belum_pernah', 'tidak_laik', 'kedaluwarsa', 'kritis', 'peringatan'], true)) {
                $hasil[] = $r;
            }
        }

        $urutan = ['tidak_laik' => 0, 'kedaluwarsa' => 1, 'belum_pernah' => 2, 'kritis' => 3, 'peringatan' => 4];
        usort($hasil, function ($a, $b) use ($urutan) {
            return ($urutan[$a['kalibrasi']['status']] ?? 9) <=> ($urutan[$b['kalibrasi']['status']] ?? 9);
        });

        return $hasil;
    }

    // =====================================================================
    // PEMELIHARAAN
    // =====================================================================

    public function simpan_pemeliharaan($data, $id = null, $userNama = 'Petugas') {
        $aset = $this->db->where('id', (int)$data['aset_id'])->get($this->table_aset)->row_array();
        if (!$aset) {
            return ['status' => false, 'message' => 'Aset tidak ditemukan.'];
        }

        $status = $data['status'];
        $realisasi = $data['tanggal_realisasi'] ?: null;

        if ($status === 'selesai' && empty($realisasi)) {
            return ['status' => false, 'message' => 'Pemeliharaan berstatus selesai wajib mencantumkan tanggal realisasi.'];
        }

        $payload = [
            'aset_id' => (int)$aset['id'],
            'jenis' => $data['jenis'],
            'tanggal_rencana' => $data['tanggal_rencana'] ?: null,
            'tanggal_realisasi' => $realisasi,
            'pelaksana' => $data['pelaksana'],
            'nama_pelaksana' => $data['nama_pelaksana'] ?: null,
            'uraian_pekerjaan' => $data['uraian_pekerjaan'] ?: null,
            'suku_cadang' => $data['suku_cadang'] ?: null,
            'biaya' => (float)$data['biaya'],
            'hasil' => $data['hasil'] ?: null,
            'status' => $status,
            'catatan' => $data['catatan'] ?: null,
            'dicatat_oleh' => $userNama,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($id) {
            $this->db->where('id', (int)$id)->update($this->table_pemeliharaan, $payload);
            $savedId = (int)$id;
        } else {
            $payload['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert($this->table_pemeliharaan, $payload);
            $savedId = (int)$this->db->insert_id();
        }

        return ['status' => true, 'id' => $savedId, 'message' => 'Catatan pemeliharaan ' . $aset['nama_aset'] . ' tersimpan.'];
    }

    public function get_pemeliharaan_datatable($params) {
        $draw = (int)($params['draw'] ?? 1);
        $start = (int)($params['start'] ?? 0);
        $length = (int)($params['length'] ?? 10);
        $search = trim($params['search']['value'] ?? '');

        $recordsTotal = $this->db->count_all_results($this->table_pemeliharaan);

        $this->db->select('p.*, a.nomor_register, a.nama_aset, a.lokasi_ruangan');
        $this->db->from($this->table_pemeliharaan . ' p');
        $this->db->join($this->table_aset . ' a', 'a.id = p.aset_id', 'inner');

        if ($search !== '') {
            $this->db->group_start()
                     ->like('a.nama_aset', $search)
                     ->or_like('a.nomor_register', $search)
                     ->or_like('p.nama_pelaksana', $search)
                     ->group_end();
        }
        if (!empty($params['status']) && $params['status'] !== 'all') {
            $this->db->where('p.status', $params['status']);
        }
        if (!empty($params['jenis']) && $params['jenis'] !== 'all') {
            $this->db->where('p.jenis', $params['jenis']);
        }

        $recordsFiltered = $this->db->count_all_results('', FALSE);

        $this->db->order_by('COALESCE(p.tanggal_realisasi, p.tanggal_rencana)', 'DESC', FALSE);
        if ($length > 0) {
            $this->db->limit($length, $start);
        }

        return [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $this->db->get()->result_array(),
        ];
    }

    /**
     * Aset yang pemeliharaan berkalanya terlewat atau segera jatuh tempo.
     */
    public function get_pemeliharaan_jatuh_tempo() {
        $cfg = $this->get_config();

        $rows = $this->db->where('wajib_pemeliharaan', 1)
                         ->where('status !=', 'dihapuskan')
                         ->get($this->table_aset)
                         ->result_array();

        $hasil = [];
        foreach ($rows as $r) {
            $r = $this->lengkapi($r, $cfg);
            if (in_array($r['pemeliharaan']['status'], ['terlewat', 'segera'], true)) {
                $hasil[] = $r;
            }
        }

        usort($hasil, function ($a, $b) {
            return ($a['pemeliharaan']['sisa_hari'] ?? 9999) <=> ($b['pemeliharaan']['sisa_hari'] ?? 9999);
        });

        return $hasil;
    }

    // =====================================================================
    // MUTASI & PENGHAPUSAN
    // =====================================================================

    public function catat_mutasi($asetId, $tipe, $data, $userId = null, $userNama = 'Petugas') {
        $this->db->insert($this->table_mutasi, [
            'nomor_transaksi' => $this->generate_nomor_mutasi(),
            'aset_id' => (int)$asetId,
            'tipe' => $tipe,
            'tanggal' => $data['tanggal'] ?? date('Y-m-d'),
            'dari_ruangan' => $data['dari_ruangan'] ?? null,
            'ke_ruangan' => $data['ke_ruangan'] ?? null,
            'dari_kondisi' => $data['dari_kondisi'] ?? null,
            'ke_kondisi' => $data['ke_kondisi'] ?? null,
            'dari_status' => $data['dari_status'] ?? null,
            'ke_status' => $data['ke_status'] ?? null,
            'pihak_terkait' => $data['pihak_terkait'] ?? null,
            'nomor_dokumen' => $data['nomor_dokumen'] ?? null,
            'keterangan' => $data['keterangan'] ?? null,
            'user_id' => $userId,
            'user_nama' => $userNama,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return (int)$this->db->insert_id();
    }

    /**
     * Jalankan mutasi dan terapkan perubahannya pada master aset.
     */
    public function proses_mutasi($data, $userId = null, $userNama = 'Petugas') {
        $aset = $this->db->where('id', (int)$data['aset_id'])->get($this->table_aset)->row_array();
        if (!$aset) {
            return ['status' => false, 'message' => 'Aset tidak ditemukan.'];
        }
        if ($aset['status'] === 'dihapuskan') {
            return ['status' => false, 'message' => 'Aset ' . $aset['nomor_register'] . ' sudah dihapuskan dan tidak dapat dimutasi lagi.'];
        }

        $tipe = $data['tipe'];
        $perubahan = ['updated_at' => date('Y-m-d H:i:s')];
        $jejak = ['tanggal' => $data['tanggal'] ?: date('Y-m-d'),
                  'pihak_terkait' => $data['pihak_terkait'] ?: null,
                  'nomor_dokumen' => $data['nomor_dokumen'] ?: null,
                  'keterangan' => $data['keterangan'] ?: null];

        switch ($tipe) {
            case 'pindah_ruangan':
                if (empty($data['ke_ruangan'])) {
                    return ['status' => false, 'message' => 'Ruangan tujuan wajib dipilih.'];
                }
                if ($data['ke_ruangan'] === $aset['lokasi_ruangan']) {
                    return ['status' => false, 'message' => 'Ruangan tujuan sama dengan lokasi saat ini.'];
                }
                $jejak['dari_ruangan'] = $aset['lokasi_ruangan'];
                $jejak['ke_ruangan'] = $data['ke_ruangan'];
                $perubahan['lokasi_ruangan'] = $data['ke_ruangan'];
                break;

            case 'peminjaman':
                if (empty($data['pihak_terkait'])) {
                    return ['status' => false, 'message' => 'Nama peminjam wajib diisi.'];
                }
                $jejak['dari_status'] = $aset['status'];
                $jejak['ke_status'] = 'dipinjam';
                $perubahan['status'] = 'dipinjam';
                break;

            case 'pengembalian':
                if ($aset['status'] !== 'dipinjam' && $aset['status'] !== 'perbaikan') {
                    return ['status' => false, 'message' => 'Aset ini tidak sedang dipinjam maupun diperbaiki.'];
                }
                $jejak['dari_status'] = $aset['status'];
                $jejak['ke_status'] = 'aktif';
                $perubahan['status'] = 'aktif';
                break;

            case 'perbaikan':
                $jejak['dari_status'] = $aset['status'];
                $jejak['ke_status'] = 'perbaikan';
                $perubahan['status'] = 'perbaikan';
                break;

            case 'perubahan_kondisi':
                if (empty($data['ke_kondisi'])) {
                    return ['status' => false, 'message' => 'Kondisi baru wajib dipilih.'];
                }
                $jejak['dari_kondisi'] = $aset['kondisi'];
                $jejak['ke_kondisi'] = $data['ke_kondisi'];
                $perubahan['kondisi'] = $data['ke_kondisi'];
                break;

            case 'penghapusan':
                // Penghapusan BMD menuntut dasar hukum berupa surat keputusan.
                if (empty($data['nomor_dokumen'])) {
                    return ['status' => false, 'message' => 'Penghapusan BMD wajib mencantumkan nomor surat keputusan penghapusan sebagai dasar hukum.'];
                }
                $jejak['dari_status'] = $aset['status'];
                $jejak['ke_status'] = 'dihapuskan';
                $perubahan['status'] = 'dihapuskan';
                break;

            default:
                return ['status' => false, 'message' => 'Jenis mutasi tidak dikenali.'];
        }

        $this->db->trans_begin();

        $this->db->where('id', (int)$aset['id'])->update($this->table_aset, $perubahan);
        $this->catat_mutasi((int)$aset['id'], $tipe, $jejak, $userId, $userNama);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'Mutasi gagal disimpan, seluruh perubahan dibatalkan.'];
        }
        $this->db->trans_commit();

        $label = [
            'pindah_ruangan' => 'dipindahkan ke ' . ($data['ke_ruangan'] ?? '-'),
            'peminjaman' => 'dicatat dipinjam oleh ' . ($data['pihak_terkait'] ?? '-'),
            'pengembalian' => 'dikembalikan dan berstatus aktif',
            'perbaikan' => 'dikirim untuk perbaikan',
            'perubahan_kondisi' => 'diubah kondisinya menjadi ' . str_replace('_', ' ', $data['ke_kondisi'] ?? '-'),
            'penghapusan' => 'dihapuskan berdasarkan ' . ($data['nomor_dokumen'] ?? '-'),
        ];

        return ['status' => true, 'message' => 'Aset ' . $aset['nama_aset'] . ' ' . ($label[$tipe] ?? 'dimutasi') . '.'];
    }

    public function get_mutasi_datatable($params) {
        $draw = (int)($params['draw'] ?? 1);
        $start = (int)($params['start'] ?? 0);
        $length = (int)($params['length'] ?? 10);
        $search = trim($params['search']['value'] ?? '');

        $recordsTotal = $this->db->count_all_results($this->table_mutasi);

        $this->db->select('m.*, a.nomor_register, a.nama_aset');
        $this->db->from($this->table_mutasi . ' m');
        $this->db->join($this->table_aset . ' a', 'a.id = m.aset_id', 'inner');

        if ($search !== '') {
            $this->db->group_start()
                     ->like('a.nama_aset', $search)
                     ->or_like('a.nomor_register', $search)
                     ->or_like('m.nomor_transaksi', $search)
                     ->or_like('m.pihak_terkait', $search)
                     ->group_end();
        }
        if (!empty($params['tipe']) && $params['tipe'] !== 'all') {
            $this->db->where('m.tipe', $params['tipe']);
        }

        $recordsFiltered = $this->db->count_all_results('', FALSE);

        $this->db->order_by('m.tanggal', 'DESC')->order_by('m.id', 'DESC');
        if ($length > 0) {
            $this->db->limit($length, $start);
        }

        return [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $this->db->get()->result_array(),
        ];
    }

    // =====================================================================
    // RINGKASAN
    // =====================================================================

    public function ringkasan() {
        $cfg = $this->get_config();

        $rows = $this->db->get($this->table_aset)->result_array();

        $r = [
            'total' => 0, 'aktif' => 0, 'dipinjam' => 0, 'perbaikan' => 0, 'dihapuskan' => 0,
            'baik' => 0, 'rusak_ringan' => 0, 'rusak_berat' => 0,
            'wajib_kalibrasi' => 0, 'tidak_laik' => 0, 'kalibrasi_perhatian' => 0,
            'pemeliharaan_terlewat' => 0,
            'nilai_perolehan' => 0, 'nilai_buku' => 0, 'akumulasi_penyusutan' => 0,
            'habis_manfaat' => 0,
        ];

        foreach ($rows as $row) {
            $a = $this->lengkapi($row, $cfg);

            $r['total']++;
            $r[$a['status']] = ($r[$a['status']] ?? 0) + 1;
            $r[$a['kondisi']] = ($r[$a['kondisi']] ?? 0) + 1;

            $r['nilai_perolehan'] += (float)$a['nilai_perolehan'];
            $r['nilai_buku'] += $a['penyusutan']['nilai_buku'];
            $r['akumulasi_penyusutan'] += $a['penyusutan']['akumulasi'];
            if ($a['penyusutan']['habis_manfaat']) $r['habis_manfaat']++;

            if ((int)$a['wajib_kalibrasi'] === 1) {
                $r['wajib_kalibrasi']++;
                if (in_array($a['kalibrasi']['status'], ['kedaluwarsa', 'tidak_laik', 'belum_pernah'], true)) {
                    $r['tidak_laik']++;
                }
                if (in_array($a['kalibrasi']['status'], ['kritis', 'peringatan'], true)) {
                    $r['kalibrasi_perhatian']++;
                }
            }

            if ($a['pemeliharaan']['status'] === 'terlewat') {
                $r['pemeliharaan_terlewat']++;
            }
        }

        return $r;
    }

    public function get_aset_options() {
        return $this->db->select('id, nomor_register, nama_aset, lokasi_ruangan, kondisi, status, wajib_kalibrasi, interval_kalibrasi_bulan')
                        ->where('status !=', 'dihapuskan')
                        ->order_by('nama_aset', 'ASC')
                        ->get($this->table_aset)
                        ->result_array();
    }

    public function get_pegawai_options() {
        $rows = $this->db->select('id, nip, nama_lengkap, gelar_depan, gelar_belakang, nama_jabatan')
                         ->order_by('nama_lengkap', 'ASC')
                         ->get($this->table_pegawai)
                         ->result_array();

        foreach ($rows as &$p) {
            $p['nama_formatted'] = $this->_nama_pegawai($p);
        }
        return $rows;
    }
}
