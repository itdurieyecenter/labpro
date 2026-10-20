<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model Gudang & Persediaan Reagen / BHP.
 *
 * Stok tidak disimpan sebagai satu angka pada master barang, melainkan
 * dijumlahkan dari sisa tiap batch. Pendekatan ini membuat satu jenis barang
 * dapat memiliki beberapa nomor batch dengan masa kedaluwarsa berbeda, dan
 * setiap pengeluaran tetap dapat dirunut sampai ke batch asalnya.
 */
class Gudang_model extends CI_Model {

    protected $table_ref = 'gudang_referensi';
    protected $table_barang = 'gudang_barang';
    protected $table_batch = 'gudang_batch';
    protected $table_mutasi = 'gudang_mutasi';
    protected $table_opname = 'gudang_opname';
    protected $table_opname_detail = 'gudang_opname_detail';

    public function __construct() {
        parent::__construct();
    }

    // =====================================================================
    // KONFIGURASI & UTILITAS
    // =====================================================================

    public function get_config() {
        return [
            'metode_keluar' => strtoupper(get_setting('gudang_metode_keluar', 'FIFO')),
            'alert_kedaluwarsa' => (int)get_setting('gudang_alert_kedaluwarsa_hari', 90),
            'alert_kritis' => (int)get_setting('gudang_alert_kritis_hari', 30),
            'prefix_masuk' => get_setting('gudang_prefix_masuk', 'GM'),
            'prefix_keluar' => get_setting('gudang_prefix_keluar', 'GK'),
            'prefix_opname' => get_setting('gudang_prefix_opname', 'SO'),
            'izinkan_minus' => (int)get_setting('gudang_izinkan_stok_minus', 0) === 1,
        ];
    }

    public function bulan_romawi($bulan) {
        $map = [1 => 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        return $map[(int)$bulan] ?? 'I';
    }

    /**
     * Nomor transaksi berurut per tahun, misal GM/0007/X/2026.
     */
    public function generate_nomor($prefix, $tabel = null, $kolom = 'nomor_transaksi') {
        $tabel = $tabel ?: $this->table_mutasi;
        $tahun = date('Y');

        $last = $this->db->select($kolom)
                         ->like($kolom, $prefix . '/', 'after')
                         ->like($kolom, '/' . $tahun, 'before')
                         ->order_by('id', 'DESC')
                         ->limit(1)
                         ->get($tabel)
                         ->row_array();

        $urut = 1;
        if ($last && preg_match('#/(\d+)/#', $last[$kolom], $m)) {
            $urut = (int)$m[1] + 1;
        }

        return sprintf('%s/%04d/%s/%s', $prefix, $urut, $this->bulan_romawi(date('n')), $tahun);
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

    // =====================================================================
    // MASTER BARANG
    // =====================================================================

    /**
     * Stok terkini satu barang: jumlah sisa seluruh batch.
     */
    public function get_stok($barangId) {
        $row = $this->db->select('COALESCE(SUM(jumlah_sisa), 0) AS stok', FALSE)
                        ->where('barang_id', (int)$barangId)
                        ->get($this->table_batch)
                        ->row_array();
        return (float)($row['stok'] ?? 0);
    }

    public function get_batch_barang($barangId, $hanyaTersedia = false) {
        $cfg = $this->get_config();

        $this->db->where('barang_id', (int)$barangId);
        if ($hanyaTersedia) {
            $this->db->where('jumlah_sisa >', 0);
        }

        $rows = $this->db->order_by('tanggal_terima', 'ASC')->order_by('id', 'ASC')
                         ->get($this->table_batch)->result_array();

        foreach ($rows as &$b) {
            $b['status_kedaluwarsa'] = $this->_status_kedaluwarsa($b['tanggal_kedaluwarsa'], $cfg);
        }
        return $rows;
    }

    private function _status_kedaluwarsa($tanggal, $cfg = null) {
        $cfg = $cfg ?: $this->get_config();

        if (empty($tanggal)) {
            return ['status' => 'tanpa_tanggal', 'sisa_hari' => null];
        }

        $sisa = (int)floor((strtotime($tanggal) - strtotime(date('Y-m-d'))) / 86400);

        if ($sisa < 0) {
            $status = 'kedaluwarsa';
        } elseif ($sisa <= $cfg['alert_kritis']) {
            $status = 'kritis';
        } elseif ($sisa <= $cfg['alert_kedaluwarsa']) {
            $status = 'peringatan';
        } else {
            $status = 'aman';
        }

        return ['status' => $status, 'sisa_hari' => $sisa];
    }

    public function get_barang_by_id($id) {
        $row = $this->db->where('id', (int)$id)->get($this->table_barang)->row_array();
        if (!$row) return null;

        $row['stok'] = $this->get_stok((int)$id);
        $row['batch'] = $this->get_batch_barang((int)$id);
        $row['status_stok'] = $this->_status_stok($row);
        return $row;
    }

    private function _status_stok($barang) {
        $stok = (float)($barang['stok'] ?? 0);
        $min = (float)($barang['stok_minimum'] ?? 0);

        if ($stok <= 0) return 'habis';
        if ($min > 0 && $stok <= $min) return 'kritis';
        if ($min > 0 && $stok <= $min * 1.5) return 'menipis';
        return 'aman';
    }

    public function get_barang_datatable($params) {
        $draw = (int)($params['draw'] ?? 1);
        $start = (int)($params['start'] ?? 0);
        $length = (int)($params['length'] ?? 10);
        $search = trim($params['search']['value'] ?? '');

        $recordsTotal = $this->db->count_all_results($this->table_barang);

        $this->db->from($this->table_barang);
        $this->_filter_barang($params, $search);
        $recordsFiltered = $this->db->count_all_results('', FALSE);

        $this->db->order_by('nama_barang', 'ASC');
        if ($length > 0) {
            $this->db->limit($length, $start);
        }

        $rows = $this->db->get()->result_array();
        $cfg = $this->get_config();

        foreach ($rows as &$r) {
            $r['stok'] = $this->get_stok((int)$r['id']);
            $r['status_stok'] = $this->_status_stok($r);
            $r['jml_batch'] = (int)$this->db->where('barang_id', (int)$r['id'])
                                            ->where('jumlah_sisa >', 0)
                                            ->count_all_results($this->table_batch);

            // Batch terdekat kedaluwarsa yang masih bersisa.
            $terdekat = $this->db->select('nomor_batch, tanggal_kedaluwarsa, jumlah_sisa')
                                 ->where('barang_id', (int)$r['id'])
                                 ->where('jumlah_sisa >', 0)
                                 ->where('tanggal_kedaluwarsa IS NOT NULL', null, false)
                                 ->order_by('tanggal_kedaluwarsa', 'ASC')
                                 ->limit(1)
                                 ->get($this->table_batch)
                                 ->row_array();

            $r['batch_terdekat'] = $terdekat;
            $r['status_kedaluwarsa'] = $terdekat
                ? $this->_status_kedaluwarsa($terdekat['tanggal_kedaluwarsa'], $cfg)
                : ['status' => 'tanpa_tanggal', 'sisa_hari' => null];
        }

        // Filter status stok diterapkan setelah stok dihitung dari batch.
        if (!empty($params['status_stok']) && $params['status_stok'] !== 'all') {
            $rows = array_values(array_filter($rows, function ($r) use ($params) {
                return $r['status_stok'] === $params['status_stok'];
            }));
        }

        return [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $rows,
        ];
    }

    private function _filter_barang($params, $search) {
        if ($search !== '') {
            $this->db->group_start()
                     ->like('kode_barang', $search)
                     ->or_like('nama_barang', $search)
                     ->or_like('merk', $search)
                     ->group_end();
        }
        if (!empty($params['kategori']) && $params['kategori'] !== 'all') {
            $this->db->where('kategori', $params['kategori']);
        }
        if (isset($params['is_active']) && $params['is_active'] !== 'all' && $params['is_active'] !== '') {
            $this->db->where('is_active', (int)$params['is_active']);
        }
    }

    public function generate_kode_barang($kategori) {
        $ref = $this->db->where('kategori', 'kategori_barang')
                        ->where('nama', $kategori)
                        ->get($this->table_ref)
                        ->row_array();

        $prefix = $ref && !empty($ref['kode']) ? $ref['kode'] : 'BRG';

        $last = $this->db->select('kode_barang')
                         ->like('kode_barang', $prefix . '-', 'after')
                         ->order_by('id', 'DESC')
                         ->limit(1)
                         ->get($this->table_barang)
                         ->row_array();

        $urut = 1;
        if ($last && preg_match('/-(\d+)$/', $last['kode_barang'], $m)) {
            $urut = (int)$m[1] + 1;
        }

        return sprintf('%s-%04d', $prefix, $urut);
    }

    public function save_barang($data, $id = null) {
        $payload = [
            'nama_barang' => trim($data['nama_barang']),
            'kategori' => $data['kategori'],
            'satuan' => $data['satuan'],
            'merk' => $data['merk'] ?: null,
            'kemasan' => $data['kemasan'] ?: null,
            'suhu_penyimpanan' => $data['suhu_penyimpanan'] ?: null,
            'lokasi_simpan' => $data['lokasi_simpan'] ?: null,
            'stok_minimum' => (float)$data['stok_minimum'],
            'stok_maksimum' => (float)$data['stok_maksimum'],
            'wajib_batch' => (int)$data['wajib_batch'],
            'keterangan' => $data['keterangan'] ?: null,
            'is_active' => (int)$data['is_active'],
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($id) {
            $this->db->where('id', (int)$id)->update($this->table_barang, $payload);
            return (int)$id;
        }

        $payload['kode_barang'] = !empty($data['kode_barang'])
            ? trim($data['kode_barang'])
            : $this->generate_kode_barang($data['kategori']);
        $payload['created_at'] = date('Y-m-d H:i:s');

        $this->db->insert($this->table_barang, $payload);
        return (int)$this->db->insert_id();
    }

    /**
     * Barang hanya boleh dihapus selama belum pernah bermutasi, agar kartu
     * stok historis tidak kehilangan acuan.
     */
    public function hapus_barang($id) {
        $barang = $this->db->where('id', (int)$id)->get($this->table_barang)->row_array();
        if (!$barang) {
            return ['status' => false, 'message' => 'Barang tidak ditemukan.'];
        }

        $jmlMutasi = $this->db->where('barang_id', (int)$id)->count_all_results($this->table_mutasi);
        if ($jmlMutasi > 0) {
            return [
                'status' => false,
                'message' => 'Barang ini sudah memiliki ' . $jmlMutasi . ' riwayat mutasi sehingga tidak dapat dihapus. Nonaktifkan saja agar riwayat kartu stok tetap utuh.'
            ];
        }

        $this->db->where('id', (int)$id)->delete($this->table_barang);
        return ['status' => true, 'message' => 'Barang ' . $barang['nama_barang'] . ' dihapus.'];
    }

    // =====================================================================
    // PENERIMAAN BARANG
    // =====================================================================

    /**
     * Catat penerimaan: membentuk satu batch baru dan satu baris mutasi masuk.
     */
    public function terima_barang($data, $userId = null, $userNama = 'Petugas') {
        $barang = $this->db->where('id', (int)$data['barang_id'])->get($this->table_barang)->row_array();
        if (!$barang) {
            return ['status' => false, 'message' => 'Barang tidak ditemukan.'];
        }

        $jumlah = (float)$data['jumlah'];
        if ($jumlah <= 0) {
            return ['status' => false, 'message' => 'Jumlah penerimaan harus lebih besar dari nol.'];
        }

        if ((int)$barang['wajib_batch'] === 1) {
            if (empty($data['nomor_batch'])) {
                return ['status' => false, 'message' => $barang['nama_barang'] . ' wajib mencantumkan nomor batch.'];
            }
            if (empty($data['tanggal_kedaluwarsa'])) {
                return ['status' => false, 'message' => $barang['nama_barang'] . ' wajib mencantumkan tanggal kedaluwarsa.'];
            }
        }

        $tglTerima = $data['tanggal_terima'] ?: date('Y-m-d');

        if (!empty($data['tanggal_kedaluwarsa']) && strtotime($data['tanggal_kedaluwarsa']) < strtotime($tglTerima)) {
            return ['status' => false, 'message' => 'Tanggal kedaluwarsa tidak boleh lebih awal dari tanggal penerimaan.'];
        }

        $this->db->trans_begin();

        $this->db->insert($this->table_batch, [
            'barang_id' => (int)$barang['id'],
            'nomor_batch' => $data['nomor_batch'] ?: null,
            'tanggal_terima' => $tglTerima,
            'tanggal_kedaluwarsa' => $data['tanggal_kedaluwarsa'] ?: null,
            'jumlah_masuk' => $jumlah,
            'jumlah_sisa' => $jumlah,
            'harga_satuan' => (float)($data['harga_satuan'] ?? 0),
            'pemasok' => $data['pemasok'] ?: null,
            'nomor_dokumen' => $data['nomor_dokumen'] ?: null,
            'keterangan' => $data['keterangan'] ?: null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $batchId = (int)$this->db->insert_id();

        $cfg = $this->get_config();
        $nomor = $this->generate_nomor($cfg['prefix_masuk']);
        $saldo = $this->get_stok((int)$barang['id']);

        $this->db->insert($this->table_mutasi, [
            'nomor_transaksi' => $nomor,
            'barang_id' => (int)$barang['id'],
            'batch_id' => $batchId,
            'tipe' => 'masuk',
            'tanggal' => $tglTerima,
            'jumlah' => $jumlah,
            'saldo_sesudah' => $saldo,
            'harga_satuan' => (float)($data['harga_satuan'] ?? 0),
            'keterangan' => $data['keterangan'] ?: ('Penerimaan dari ' . ($data['pemasok'] ?: 'pemasok')),
            'user_id' => $userId,
            'user_nama' => $userNama,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if (!empty($data['harga_satuan'])) {
            $this->db->where('id', (int)$barang['id'])
                     ->update($this->table_barang, ['harga_satuan_terakhir' => (float)$data['harga_satuan']]);
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'Penerimaan gagal disimpan, seluruh perubahan dibatalkan.'];
        }

        $this->db->trans_commit();

        return [
            'status' => true,
            'message' => 'Penerimaan ' . $nomor . ' tercatat. Stok ' . $barang['nama_barang'] . ' kini ' . $this->_angka($saldo) . ' ' . $barang['satuan'] . '.',
            'nomor' => $nomor,
            'batch_id' => $batchId,
            'saldo' => $saldo,
        ];
    }

    private function _angka($n) {
        return rtrim(rtrim(number_format((float)$n, 2, ',', '.'), '0'), ',');
    }

    // =====================================================================
    // PENGELUARAN BARANG (FIFO / FEFO)
    // =====================================================================

    /**
     * Urutan batch yang akan digerus lebih dulu.
     *
     * FIFO mengutamakan batch yang paling dahulu diterima, sesuai KAK.
     * FEFO mengutamakan yang paling dahulu kedaluwarsa; batch tanpa tanggal
     * kedaluwarsa ditempatkan paling belakang agar tidak mendahului batch
     * yang punya tenggat.
     */
    public function get_antrian_batch($barangId) {
        $cfg = $this->get_config();

        $this->db->where('barang_id', (int)$barangId)
                 ->where('jumlah_sisa >', 0)
                 ->where('is_terkunci', 0);

        if ($cfg['metode_keluar'] === 'FEFO') {
            $this->db->order_by('tanggal_kedaluwarsa IS NULL', 'ASC', FALSE);
            $this->db->order_by('tanggal_kedaluwarsa', 'ASC');
        }

        $this->db->order_by('tanggal_terima', 'ASC');
        $this->db->order_by('id', 'ASC');

        return $this->db->get($this->table_batch)->result_array();
    }

    /**
     * Simulasi pengeluaran tanpa menyimpan, dipakai form untuk memperlihatkan
     * batch mana saja yang akan terpakai sebelum petugas menyimpan.
     */
    public function simulasi_keluar($barangId, $jumlah) {
        $jumlah = (float)$jumlah;
        $stok = $this->get_stok($barangId);
        $cfg = $this->get_config();

        $rencana = [];
        $sisaButuh = $jumlah;

        foreach ($this->get_antrian_batch($barangId) as $b) {
            if ($sisaButuh <= 0) break;

            $ambil = min($sisaButuh, (float)$b['jumlah_sisa']);
            $rencana[] = [
                'batch_id' => (int)$b['id'],
                'nomor_batch' => $b['nomor_batch'],
                'tanggal_kedaluwarsa' => $b['tanggal_kedaluwarsa'],
                'tersedia' => (float)$b['jumlah_sisa'],
                'diambil' => $ambil,
                'status_kedaluwarsa' => $this->_status_kedaluwarsa($b['tanggal_kedaluwarsa'], $cfg),
            ];
            $sisaButuh -= $ambil;
        }

        return [
            'metode' => $cfg['metode_keluar'],
            'stok_tersedia' => $stok,
            'diminta' => $jumlah,
            'cukup' => $sisaButuh <= 0,
            'kekurangan' => max(0, $sisaButuh),
            'rencana' => $rencana,
        ];
    }

    /**
     * Keluarkan barang dengan menggerus batch sesuai antrian FIFO/FEFO.
     * Satu baris mutasi dibuat untuk tiap batch yang tersentuh, sehingga
     * kartu stok memperlihatkan asal setiap satuan yang keluar.
     */
    public function keluarkan_barang($data, $userId = null, $userNama = 'Petugas', $tipe = 'keluar') {
        $barang = $this->db->where('id', (int)$data['barang_id'])->get($this->table_barang)->row_array();
        if (!$barang) {
            return ['status' => false, 'message' => 'Barang tidak ditemukan.'];
        }

        $jumlah = (float)$data['jumlah'];
        if ($jumlah <= 0) {
            return ['status' => false, 'message' => 'Jumlah pengeluaran harus lebih besar dari nol.'];
        }

        $cfg = $this->get_config();
        $simulasi = $this->simulasi_keluar((int)$barang['id'], $jumlah);

        if (!$simulasi['cukup'] && !$cfg['izinkan_minus']) {
            return [
                'status' => false,
                'message' => sprintf(
                    'Stok %s tidak mencukupi. Diminta %s %s, tersedia %s %s.',
                    $barang['nama_barang'], $this->_angka($jumlah), $barang['satuan'],
                    $this->_angka($simulasi['stok_tersedia']), $barang['satuan']
                ),
            ];
        }

        $tanggal = $data['tanggal'] ?: date('Y-m-d');
        $nomor = $this->generate_nomor($cfg['prefix_keluar']);
        $now = date('Y-m-d H:i:s');

        $this->db->trans_begin();

        $saldo = $simulasi['stok_tersedia'];

        foreach ($simulasi['rencana'] as $r) {
            $this->db->set('jumlah_sisa', 'jumlah_sisa - ' . (float)$r['diambil'], FALSE)
                     ->set('updated_at', $now)
                     ->where('id', $r['batch_id'])
                     ->update($this->table_batch);

            $saldo -= (float)$r['diambil'];

            $this->db->insert($this->table_mutasi, [
                'nomor_transaksi' => $nomor,
                'barang_id' => (int)$barang['id'],
                'batch_id' => $r['batch_id'],
                'tipe' => $tipe,
                'tanggal' => $tanggal,
                'jumlah' => (float)$r['diambil'],
                'saldo_sesudah' => $saldo,
                'harga_satuan' => (float)$barang['harga_satuan_terakhir'],
                'tujuan' => $data['tujuan'] ?: null,
                'keterangan' => $data['keterangan'] ?: null,
                'user_id' => $userId,
                'user_nama' => $userNama,
                'created_at' => $now,
            ]);
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'Pengeluaran gagal disimpan, seluruh perubahan dibatalkan.'];
        }

        $this->db->trans_commit();

        return [
            'status' => true,
            'message' => sprintf(
                'Pengeluaran %s tercatat dari %d batch. Sisa stok %s kini %s %s.',
                $nomor, count($simulasi['rencana']), $barang['nama_barang'],
                $this->_angka($saldo), $barang['satuan']
            ),
            'nomor' => $nomor,
            'saldo' => $saldo,
            'rencana' => $simulasi['rencana'],
        ];
    }

    // =====================================================================
    // KARTU STOK
    // =====================================================================

    public function get_kartu_stok($barangId, $mulai = null, $selesai = null) {
        $this->db->select('m.*, b.nomor_batch, b.tanggal_kedaluwarsa');
        $this->db->from($this->table_mutasi . ' m');
        $this->db->join($this->table_batch . ' b', 'b.id = m.batch_id', 'left');
        $this->db->where('m.barang_id', (int)$barangId);

        if ($mulai) $this->db->where('m.tanggal >=', $mulai);
        if ($selesai) $this->db->where('m.tanggal <=', $selesai);

        $this->db->order_by('m.tanggal', 'ASC')->order_by('m.id', 'ASC');

        return $this->db->get()->result_array();
    }

    public function ringkasan_kartu($barangId, $mulai = null, $selesai = null) {
        $rows = $this->get_kartu_stok($barangId, $mulai, $selesai);

        $masuk = 0;
        $keluar = 0;
        foreach ($rows as $r) {
            if (in_array($r['tipe'], ['masuk', 'opname_plus', 'retur'], true)) {
                $masuk += (float)$r['jumlah'];
            } else {
                $keluar += (float)$r['jumlah'];
            }
        }

        $saldoAkhir = $this->get_stok($barangId);

        return [
            'total_masuk' => $masuk,
            'total_keluar' => $keluar,
            'saldo_awal' => $saldoAkhir - $masuk + $keluar,
            'saldo_akhir' => $saldoAkhir,
            'jml_transaksi' => count($rows),
        ];
    }

    // =====================================================================
    // PERINGATAN STOK & KEDALUWARSA
    // =====================================================================

    public function get_stok_kritis() {
        $rows = $this->db->where('is_active', 1)->get($this->table_barang)->result_array();

        $hasil = [];
        foreach ($rows as $r) {
            $r['stok'] = $this->get_stok((int)$r['id']);
            $r['status_stok'] = $this->_status_stok($r);

            if (in_array($r['status_stok'], ['habis', 'kritis', 'menipis'], true)) {
                $r['kekurangan'] = max(0, (float)$r['stok_minimum'] - $r['stok']);
                $hasil[] = $r;
            }
        }

        $urutan = ['habis' => 0, 'kritis' => 1, 'menipis' => 2];
        usort($hasil, function ($a, $b) use ($urutan) {
            return ($urutan[$a['status_stok']] ?? 9) <=> ($urutan[$b['status_stok']] ?? 9);
        });

        return $hasil;
    }

    public function get_batch_kedaluwarsa() {
        $cfg = $this->get_config();
        $batas = date('Y-m-d', strtotime('+' . $cfg['alert_kedaluwarsa'] . ' days'));

        $rows = $this->db->select('b.*, g.kode_barang, g.nama_barang, g.satuan, g.kategori')
                         ->from($this->table_batch . ' b')
                         ->join($this->table_barang . ' g', 'g.id = b.barang_id', 'inner')
                         ->where('b.jumlah_sisa >', 0)
                         ->where('b.tanggal_kedaluwarsa IS NOT NULL', null, false)
                         ->where('b.tanggal_kedaluwarsa <=', $batas)
                         ->order_by('b.tanggal_kedaluwarsa', 'ASC')
                         ->get()
                         ->result_array();

        foreach ($rows as &$r) {
            $r['status_kedaluwarsa'] = $this->_status_kedaluwarsa($r['tanggal_kedaluwarsa'], $cfg);
        }

        return $rows;
    }

    public function ringkasan() {
        $cfg = $this->get_config();

        $totalBarang = $this->db->where('is_active', 1)->count_all_results($this->table_barang);
        $kritis = $this->get_stok_kritis();
        $kedaluwarsa = $this->get_batch_kedaluwarsa();

        $jmlKedaluwarsa = 0;
        $jmlKritisExp = 0;
        foreach ($kedaluwarsa as $k) {
            if ($k['status_kedaluwarsa']['status'] === 'kedaluwarsa') $jmlKedaluwarsa++;
            if ($k['status_kedaluwarsa']['status'] === 'kritis') $jmlKritisExp++;
        }

        $nilai = $this->db->select('COALESCE(SUM(jumlah_sisa * harga_satuan), 0) AS total', FALSE)
                          ->where('jumlah_sisa >', 0)
                          ->get($this->table_batch)
                          ->row_array();

        $mutasiBulanIni = $this->db->where('tanggal >=', date('Y-m-01'))
                                   ->where('tanggal <=', date('Y-m-t'))
                                   ->count_all_results($this->table_mutasi);

        return [
            'total_barang' => $totalBarang,
            'stok_habis' => count(array_filter($kritis, function ($k) { return $k['status_stok'] === 'habis'; })),
            'stok_kritis' => count(array_filter($kritis, function ($k) { return $k['status_stok'] === 'kritis'; })),
            'perlu_pesan' => count($kritis),
            'batch_kedaluwarsa' => $jmlKedaluwarsa,
            'batch_kritis_exp' => $jmlKritisExp,
            'batch_perlu_perhatian' => count($kedaluwarsa),
            'nilai_persediaan' => (float)($nilai['total'] ?? 0),
            'mutasi_bulan_ini' => $mutasiBulanIni,
            'metode_keluar' => $cfg['metode_keluar'],
        ];
    }

    public function get_mutasi_terakhir($limit = 10) {
        return $this->db->select('m.*, g.kode_barang, g.nama_barang, g.satuan')
                        ->from($this->table_mutasi . ' m')
                        ->join($this->table_barang . ' g', 'g.id = m.barang_id', 'inner')
                        ->order_by('m.id', 'DESC')
                        ->limit((int)$limit)
                        ->get()
                        ->result_array();
    }

    // =====================================================================
    // STOCK OPNAME
    // =====================================================================

    /**
     * Buka sesi opname baru, langsung terisi seluruh barang aktif beserta
     * stok sistem saat ini sebagai pembanding hitungan fisik.
     */
    public function buat_opname($data, $userId = null, $userNama = 'Petugas') {
        $berjalan = $this->db->where('status', 'draft')->get($this->table_opname)->row_array();
        if ($berjalan) {
            return [
                'status' => false,
                'message' => 'Masih ada sesi opname berstatus draft (' . $berjalan['nomor_opname'] . '). Selesaikan atau batalkan dahulu.'
            ];
        }

        $cfg = $this->get_config();
        $nomor = $this->generate_nomor($cfg['prefix_opname'], $this->table_opname, 'nomor_opname');

        $this->db->trans_begin();

        $this->db->insert($this->table_opname, [
            'nomor_opname' => $nomor,
            'tanggal' => $data['tanggal'] ?: date('Y-m-d'),
            'status' => 'draft',
            'petugas_id' => $userId,
            'petugas_nama' => $data['petugas_nama'] ?: $userNama,
            'catatan' => $data['catatan'] ?: null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $opnameId = (int)$this->db->insert_id();

        $barang = $this->db->where('is_active', 1)->order_by('nama_barang', 'ASC')
                           ->get($this->table_barang)->result_array();

        $batch = [];
        foreach ($barang as $b) {
            $batch[] = [
                'opname_id' => $opnameId,
                'barang_id' => (int)$b['id'],
                'kode_barang' => $b['kode_barang'],
                'nama_barang' => $b['nama_barang'],
                'satuan' => $b['satuan'],
                'stok_sistem' => $this->get_stok((int)$b['id']),
                'stok_fisik' => null,
                'selisih' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }

        if (!empty($batch)) {
            $this->db->insert_batch($this->table_opname_detail, $batch);
        }

        $this->db->where('id', $opnameId)->update($this->table_opname, ['total_item' => count($batch)]);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'Sesi opname gagal dibuat.'];
        }

        $this->db->trans_commit();

        return [
            'status' => true,
            'message' => 'Sesi opname ' . $nomor . ' dibuka dengan ' . count($batch) . ' item.',
            'id' => $opnameId,
            'nomor' => $nomor,
        ];
    }

    public function get_opname_by_id($id) {
        $row = $this->db->where('id', (int)$id)->get($this->table_opname)->row_array();
        if (!$row) return null;

        $row['detail'] = $this->db->where('opname_id', (int)$id)
                                  ->order_by('nama_barang', 'ASC')
                                  ->get($this->table_opname_detail)
                                  ->result_array();
        return $row;
    }

    public function get_opname_list($limit = 50) {
        return $this->db->order_by('id', 'DESC')->limit((int)$limit)
                        ->get($this->table_opname)->result_array();
    }

    public function simpan_hitungan($detailId, $stokFisik, $keterangan = null) {
        $detail = $this->db->where('id', (int)$detailId)->get($this->table_opname_detail)->row_array();
        if (!$detail) {
            return ['status' => false, 'message' => 'Baris opname tidak ditemukan.'];
        }

        $header = $this->db->where('id', (int)$detail['opname_id'])->get($this->table_opname)->row_array();
        if (!$header || $header['status'] !== 'draft') {
            return ['status' => false, 'message' => 'Sesi opname ini sudah ditutup.'];
        }

        // Stok sistem disegarkan agar selisih tetap sahih bila sempat ada
        // mutasi lain selama penghitungan fisik berlangsung.
        $stokSistem = $this->get_stok((int)$detail['barang_id']);
        $fisik = ($stokFisik === '' || $stokFisik === null) ? null : (float)$stokFisik;
        $selisih = $fisik === null ? 0 : $fisik - $stokSistem;

        $this->db->where('id', (int)$detailId)->update($this->table_opname_detail, [
            'stok_sistem' => $stokSistem,
            'stok_fisik' => $fisik,
            'selisih' => $selisih,
            'keterangan' => $keterangan,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return [
            'status' => true,
            'data' => ['stok_sistem' => $stokSistem, 'stok_fisik' => $fisik, 'selisih' => $selisih],
        ];
    }

    /**
     * Tutup sesi opname dan terapkan selisih sebagai mutasi penyesuaian.
     *
     * Selisih lebih ditambahkan ke batch terbaru yang masih bersisa, atau
     * membentuk batch penyesuaian bila barang sedang kosong. Selisih kurang
     * menggerus batch mengikuti antrian FIFO/FEFO seperti pengeluaran biasa.
     */
    public function selesaikan_opname($id, $userId = null, $userNama = 'Petugas') {
        $header = $this->db->where('id', (int)$id)->get($this->table_opname)->row_array();
        if (!$header) {
            return ['status' => false, 'message' => 'Sesi opname tidak ditemukan.'];
        }
        if ($header['status'] !== 'draft') {
            return ['status' => false, 'message' => 'Sesi opname ini sudah ditutup sebelumnya.'];
        }

        $detail = $this->db->where('opname_id', (int)$id)
                           ->where('stok_fisik IS NOT NULL', null, false)
                           ->get($this->table_opname_detail)
                           ->result_array();

        if (empty($detail)) {
            return ['status' => false, 'message' => 'Belum ada satu pun item yang dihitung fisik.'];
        }

        $now = date('Y-m-d H:i:s');
        $tanggal = $header['tanggal'];
        $totalLebih = 0;
        $totalKurang = 0;
        $jmlPenyesuaian = 0;

        $this->db->trans_begin();

        foreach ($detail as $d) {
            $stokSistem = $this->get_stok((int)$d['barang_id']);
            $selisih = (float)$d['stok_fisik'] - $stokSistem;

            if (abs($selisih) < 0.0001) {
                continue;
            }

            $jmlPenyesuaian++;

            if ($selisih > 0) {
                $totalLebih += $selisih;
                $this->_opname_tambah((int)$d['barang_id'], $selisih, $tanggal, $header['nomor_opname'], (int)$id, $userId, $userNama, $now);
            } else {
                $kurang = abs($selisih);
                $totalKurang += $kurang;
                $this->_opname_kurang((int)$d['barang_id'], $kurang, $tanggal, $header['nomor_opname'], (int)$id, $userId, $userNama, $now);
            }
        }

        $this->db->where('id', (int)$id)->update($this->table_opname, [
            'status' => 'selesai',
            'total_selisih_lebih' => $totalLebih,
            'total_selisih_kurang' => $totalKurang,
            'waktu_selesai' => $now,
            'updated_at' => $now,
        ]);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return ['status' => false, 'message' => 'Penyesuaian opname gagal diterapkan, seluruh perubahan dibatalkan.'];
        }

        $this->db->trans_commit();

        return [
            'status' => true,
            'message' => sprintf(
                'Opname %s ditutup. %d item disesuaikan (lebih %s, kurang %s).',
                $header['nomor_opname'], $jmlPenyesuaian, $this->_angka($totalLebih), $this->_angka($totalKurang)
            ),
        ];
    }

    private function _opname_tambah($barangId, $jumlah, $tanggal, $nomorOpname, $opnameId, $userId, $userNama, $now) {
        $batch = $this->db->where('barang_id', $barangId)
                          ->where('jumlah_sisa >', 0)
                          ->order_by('tanggal_terima', 'DESC')
                          ->limit(1)
                          ->get($this->table_batch)
                          ->row_array();

        if ($batch) {
            $batchId = (int)$batch['id'];
            $this->db->set('jumlah_sisa', 'jumlah_sisa + ' . (float)$jumlah, FALSE)
                     ->set('jumlah_masuk', 'jumlah_masuk + ' . (float)$jumlah, FALSE)
                     ->set('updated_at', $now)
                     ->where('id', $batchId)
                     ->update($this->table_batch);
        } else {
            // Barang sedang kosong: bentuk batch penyesuaian tanpa tanggal
            // kedaluwarsa agar petugas menyadari asalnya dari opname.
            $this->db->insert($this->table_batch, [
                'barang_id' => $barangId,
                'nomor_batch' => 'OPNAME-' . date('Ymd'),
                'tanggal_terima' => $tanggal,
                'tanggal_kedaluwarsa' => null,
                'jumlah_masuk' => $jumlah,
                'jumlah_sisa' => $jumlah,
                'keterangan' => 'Batch penyesuaian hasil ' . $nomorOpname,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $batchId = (int)$this->db->insert_id();
        }

        $this->db->insert($this->table_mutasi, [
            'nomor_transaksi' => $nomorOpname,
            'barang_id' => $barangId,
            'batch_id' => $batchId,
            'tipe' => 'opname_plus',
            'tanggal' => $tanggal,
            'jumlah' => $jumlah,
            'saldo_sesudah' => $this->get_stok($barangId),
            'referensi_tabel' => $this->table_opname,
            'referensi_id' => $opnameId,
            'keterangan' => 'Penyesuaian lebih hasil stock opname',
            'user_id' => $userId,
            'user_nama' => $userNama,
            'created_at' => $now,
        ]);
    }

    private function _opname_kurang($barangId, $jumlah, $tanggal, $nomorOpname, $opnameId, $userId, $userNama, $now) {
        $sisaButuh = (float)$jumlah;

        foreach ($this->get_antrian_batch($barangId) as $b) {
            if ($sisaButuh <= 0) break;

            $ambil = min($sisaButuh, (float)$b['jumlah_sisa']);

            $this->db->set('jumlah_sisa', 'jumlah_sisa - ' . $ambil, FALSE)
                     ->set('updated_at', $now)
                     ->where('id', (int)$b['id'])
                     ->update($this->table_batch);

            $this->db->insert($this->table_mutasi, [
                'nomor_transaksi' => $nomorOpname,
                'barang_id' => $barangId,
                'batch_id' => (int)$b['id'],
                'tipe' => 'opname_minus',
                'tanggal' => $tanggal,
                'jumlah' => $ambil,
                'saldo_sesudah' => $this->get_stok($barangId),
                'referensi_tabel' => $this->table_opname,
                'referensi_id' => $opnameId,
                'keterangan' => 'Penyesuaian kurang hasil stock opname',
                'user_id' => $userId,
                'user_nama' => $userNama,
                'created_at' => $now,
            ]);

            $sisaButuh -= $ambil;
        }
    }

    public function batalkan_opname($id) {
        $header = $this->db->where('id', (int)$id)->get($this->table_opname)->row_array();
        if (!$header) {
            return ['status' => false, 'message' => 'Sesi opname tidak ditemukan.'];
        }
        if ($header['status'] !== 'draft') {
            return ['status' => false, 'message' => 'Hanya sesi berstatus draft yang dapat dibatalkan.'];
        }

        $this->db->where('id', (int)$id)->update($this->table_opname, [
            'status' => 'dibatalkan',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return ['status' => true, 'message' => 'Sesi opname ' . $header['nomor_opname'] . ' dibatalkan tanpa penyesuaian stok.'];
    }
}
