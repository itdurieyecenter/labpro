<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Approval_model extends CI_Model {

    protected $table_rules = 'setting_approval_hasil';
    protected $table_tracking = 'pendaftaran_approval';
    protected $table_pegawai = 'pegawai';

    public function __construct() {
        parent::__construct();
    }

    /**
     * Format Nama Lengkap Pegawai beserta Gelar Depan & Belakang
     */
    public function format_nama_pegawai($p) {
        if (!$p) return '-';
        $nama = trim($p['nama_lengkap'] ?? '');
        $gelarDepan = trim($p['gelar_depan'] ?? '');
        $gelarBelakang = trim($p['gelar_belakang'] ?? '');

        $result = '';
        if (!empty($gelarDepan)) {
            $result .= $gelarDepan . ' ';
        }
        $result .= $nama;
        if (!empty($gelarBelakang)) {
            $result .= ', ' . $gelarBelakang;
        }
        return $result;
    }

    /**
     * Ambil Semua Konfigurasi Aturan Approval Berjenjang
     */
    public function get_rules($activeOnly = false) {
        $this->db->select('r.*, p.nip, p.nama_lengkap, p.gelar_depan, p.gelar_belakang, p.nama_jabatan as pegawai_jabatan, p.seksi_ruangan, p.foto, p.tanda_tangan as pegawai_tanda_tangan');
        $this->db->from($this->table_rules . ' r');
        $this->db->join($this->table_pegawai . ' p', 'p.id = r.pegawai_id', 'left');
        
        if ($activeOnly) {
            $this->db->where('r.is_active', 1);
        }

        $this->db->order_by('r.urutan', 'ASC');
        $this->db->order_by('r.id', 'ASC');
        $rows = $this->db->get()->result_array();

        foreach ($rows as &$r) {
            $r['nama_formatted'] = $this->format_nama_pegawai($r);
        }

        return $rows;
    }

    /**
     * Ambil 1 Rule Berdasarkan ID
     */
    public function get_rule_by_id($id) {
        $this->db->select('r.*, p.nip, p.nama_lengkap, p.gelar_depan, p.gelar_belakang, p.nama_jabatan as pegawai_jabatan, p.seksi_ruangan, p.tanda_tangan as pegawai_tanda_tangan');
        $this->db->from($this->table_rules . ' r');
        $this->db->join($this->table_pegawai . ' p', 'p.id = r.pegawai_id', 'left');
        $this->db->where('r.id', (int)$id);
        $row = $this->db->get()->row_array();
        if ($row) {
            $row['nama_formatted'] = $this->format_nama_pegawai($row);
        }
        return $row;
    }

    /**
     * Dapatkan Daftar Pegawai untuk Opsi Pilihan Select2
     */
    public function get_pegawai_options() {
        $rows = $this->db->select('id, nip, nama_lengkap, gelar_depan, gelar_belakang, nama_jabatan, seksi_ruangan, foto, tanda_tangan')
            ->order_by('nama_lengkap', 'ASC')
            ->get($this->table_pegawai)
            ->result_array();

        foreach ($rows as &$p) {
            $p['nama_formatted'] = $this->format_nama_pegawai($p);
        }

        return $rows;
    }

    /**
     * Dapatkan Nomor Urut Berikutnya
     */
    public function get_next_urutan() {
        $max = $this->db->select_max('urutan')->get($this->table_rules)->row_array();
        return (int)($max['urutan'] ?? 0) + 1;
    }

    /**
     * Simpan / Perbarui Aturan Approval
     */
    public function save_rule($data, $id = null) {
        $urutan = (int)$data['urutan'];
        $pegawaiId = (int)$data['pegawai_id'];

        $pegawai = $this->db->where('id', $pegawaiId)->get($this->table_pegawai)->row_array();
        if (!$pegawai) {
            return ['status' => false, 'message' => 'Pegawai yang dipilih tidak ditemukan dalam sistem.'];
        }

        $now = date('Y-m-d H:i:s');

        $this->db->trans_start();

        $payload = [
            'urutan' => $urutan,
            'pegawai_id' => $pegawaiId,
            'jabatan_approval' => trim($data['jabatan_approval'] ?? ($pegawai['nama_jabatan'] ?: 'Pejabat Verifikator')),
            'keterangan' => trim($data['keterangan'] ?? ''),
            'is_required' => isset($data['is_required']) ? (int)$data['is_required'] : 1,
            'is_active' => isset($data['is_active']) ? (int)$data['is_active'] : 1,
            'updated_at' => $now
        ];

        // Jika ada tanda tangan pegawai yang diperbarui
        if (!empty($data['tanda_tangan'])) {
            $this->db->where('id', $pegawaiId)->update($this->table_pegawai, [
                'tanda_tangan' => $data['tanda_tangan']
            ]);
        }

        if (!empty($id)) {
            // Cek jika urutan bentrok dengan ID lain, geser atau sesuaikan
            $conflict = $this->db->where('urutan', $urutan)->where('id !=', (int)$id)->get($this->table_rules)->row_array();
            if ($conflict) {
                // Geser yang bentrok ke urutan lama item ini
                $current = $this->db->where('id', (int)$id)->get($this->table_rules)->row_array();
                $oldUrutan = $current ? (int)$current['urutan'] : ($urutan + 1);
                $this->db->where('id', (int)$conflict['id'])->update($this->table_rules, ['urutan' => $oldUrutan]);
            }

            $this->db->where('id', (int)$id)->update($this->table_rules, $payload);
            $action = 'UPDATE';
            $ruleId = (int)$id;
        } else {
            // Insert baru: Jika nomor urut sudah dipakai, dorong urutan >= urutan baru naik 1
            $this->db->where('urutan >=', $urutan)->set('urutan', 'urutan + 1', FALSE)->update($this->table_rules);
            
            $payload['created_at'] = $now;
            $this->db->insert($this->table_rules, $payload);
            $ruleId = $this->db->insert_id();
            $action = 'INSERT';
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return ['status' => false, 'message' => 'Gagal menyimpan konfigurasi approval ke database.'];
        }

        return [
            'status' => true,
            'message' => 'Konfigurasi tingkat approval hasil lab berhasil disimpan.',
            'id' => $ruleId,
            'action' => $action
        ];
    }

    /**
     * Hapus Aturan Approval dan Normalkan Urutan Kembali
     */
    public function delete_rule($id) {
        $rule = $this->db->where('id', (int)$id)->get($this->table_rules)->row_array();
        if (!$rule) {
            return ['status' => false, 'message' => 'Data tidak ditemukan.'];
        }

        $this->db->trans_start();

        $this->db->where('id', (int)$id)->delete($this->table_rules);

        // Normalkan urutan yang lebih besar agar tidak ada gap nomor
        $this->db->where('urutan >', (int)$rule['urutan'])
                 ->set('urutan', 'urutan - 1', FALSE)
                 ->update($this->table_rules);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return ['status' => false, 'message' => 'Gagal menghapus aturan approval.'];
        }

        return ['status' => true, 'message' => 'Tingkat approval berhasil dihapus dan urutan dinormalkan.'];
    }

    /**
     * Tukar Posisi Urutan Naik (Up) atau Turun (Down)
     */
    public function move_order($id, $direction = 'up') {
        $current = $this->db->where('id', (int)$id)->get($this->table_rules)->row_array();
        if (!$current) {
            return ['status' => false, 'message' => 'Data tidak ditemukan.'];
        }

        $currentUrutan = (int)$current['urutan'];

        if ($direction === 'up') {
            $target = $this->db->where('urutan <', $currentUrutan)
                               ->order_by('urutan', 'DESC')
                               ->limit(1)
                               ->get($this->table_rules)
                               ->row_array();
        } else {
            $target = $this->db->where('urutan >', $currentUrutan)
                               ->order_by('urutan', 'ASC')
                               ->limit(1)
                               ->get($this->table_rules)
                               ->row_array();
        }

        if (!$target) {
            return ['status' => false, 'message' => 'Tingkat approval sudah berada di posisi paling ' . ($direction === 'up' ? 'atas' : 'bawah') . '.'];
        }

        $this->db->trans_start();

        $targetUrutan = (int)$target['urutan'];
        $this->db->where('id', (int)$current['id'])->update($this->table_rules, ['urutan' => $targetUrutan]);
        $this->db->where('id', (int)$target['id'])->update($this->table_rules, ['urutan' => $currentUrutan]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return ['status' => false, 'message' => 'Gagal memindahkan urutan approval.'];
        }

        return ['status' => true, 'message' => 'Urutan approval berhasil disesuaikan.'];
    }

    /**
     * Aturan aktif yang berlaku untuk modul tertentu ('klinis' | 'lingkungan').
     */
    protected function _rules_for($modul) {
        $out = [];
        foreach ($this->get_rules(true) as $r) {
            $bu = $r['berlaku_untuk'] ?? 'semua';
            if ($bu === 'semua' || $bu === $modul) {
                $out[] = $r;
            }
        }
        return $out;
    }

    /**
     * Inisialisasi Rantai Approval Berjenjang untuk Order Pendaftaran Tertentu
     */
    public function init_order_approval($pendaftaranId) {
        $pendaftaranId = (int)$pendaftaranId;
        
        // Cek jika sudah pernah diinisialisasi
        $existing = $this->db->where('pendaftaran_id', $pendaftaranId)->count_all_results($this->table_tracking);
        if ($existing > 0) {
            return true;
        }

        $rules = $this->_rules_for('klinis');
        if (empty($rules)) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $batch = [];
        $isFirst = true;

        foreach ($rules as $r) {
            $status = $isFirst ? 'pending' : 'waiting'; // Step 1 langsung pending, step selanjutnya menunggu
            $batch[] = [
                'pendaftaran_id' => $pendaftaranId,
                'rule_id' => (int)$r['id'],
                'urutan' => (int)$r['urutan'],
                'pegawai_id' => (int)$r['pegawai_id'],
                'pegawai_nama' => $r['nama_formatted'],
                'pegawai_nip' => $r['nip'],
                'pegawai_jabatan' => $r['pegawai_jabatan'],
                'jabatan_approval' => $r['jabatan_approval'],
                'status' => $status,
                'tanda_tangan' => $r['pegawai_tanda_tangan'] ?: null,
                'created_at' => $now,
                'updated_at' => $now
            ];
            $isFirst = false;
        }

        if (!empty($batch)) {
            $this->db->insert_batch($this->table_tracking, $batch);
        }

        return true;
    }

    /**
     * Dapatkan Seluruh Rantai Approval untuk Suatu Order
     */
    public function get_order_approvals($pendaftaranId) {
        $rows = $this->db->where('pendaftaran_id', (int)$pendaftaranId)
                         ->order_by('urutan', 'ASC')
                         ->get($this->table_tracking)
                         ->result_array();

        // Jika belum ada tracking tapi order berstatus validasi/processing, coba inisialisasi otomatis
        if (empty($rows)) {
            $this->init_order_approval($pendaftaranId);
            $rows = $this->db->where('pendaftaran_id', (int)$pendaftaranId)
                             ->order_by('urutan', 'ASC')
                             ->get($this->table_tracking)
                             ->result_array();
        }

        return $rows;
    }

    /**
     * Eksekusi Pengesahan / Approval Tingkat Tertentu (Berjenjang)
     */
    public function approve_step($pendaftaranId, $stepUrutan, $approvalData, $userId = null, $userName = 'Petugas') {
        $pendaftaranId = (int)$pendaftaranId;
        $stepUrutan = (int)$stepUrutan;

        $reg = $this->db->get_where('pendaftaran', ['id' => $pendaftaranId])->row_array();
        if (!$reg) {
            return ['status' => false, 'message' => 'Data pendaftaran tidak ditemukan.'];
        }
        if ($reg['kategori_pemeriksaan'] === 'lingkungan') {
            return ['status' => false, 'message' => 'Pendaftaran uji lingkungan diverifikasi melalui menu Verifikasi Hasil Lingkungan.'];
        }
        if ($reg['status_order'] !== 'validasi') {
            return ['status' => false, 'message' => 'Approval hanya dapat dilakukan setelah hasil lengkap dikirim ke Validasi (status saat ini: ' . strtoupper($reg['status_order']) . ').'];
        }
        $kosong = $this->db->where('pendaftaran_id', $pendaftaranId)
            ->where('tipe_item !=', 'template') // baris paket hanya header; hasil ada di sub-parameter
            ->group_start()->where('hasil_nilai IS NULL', null, false)->or_where('hasil_nilai', '')->group_end()
            ->count_all_results('pendaftaran_detail');
        if ($kosong > 0) {
            return ['status' => false, 'message' => 'Masih ada ' . $kosong . ' parameter tanpa hasil. Lengkapi hasil sebelum diverifikasi.'];
        }

        $approvals = $this->get_order_approvals($pendaftaranId);
        if (empty($approvals)) {
            return ['status' => false, 'message' => 'Rantai approval untuk pemeriksaan ini belum dikonfigurasi.'];
        }

        // Cari step yang dimaksud
        $targetStep = null;
        $prevStepApproved = true;

        foreach ($approvals as $app) {
            $appUrutan = (int)$app['urutan'];
            if ($appUrutan < $stepUrutan && $app['status'] !== 'approved') {
                $prevStepApproved = false;
            }
            if ($appUrutan === $stepUrutan) {
                $targetStep = $app;
            }
        }

        if (!$targetStep) {
            return ['status' => false, 'message' => 'Tahapan approval nomor urut ' . $stepUrutan . ' tidak ditemukan.'];
        }

        if (!$prevStepApproved) {
            return ['status' => false, 'message' => 'Approval harus berjenjang! Tahapan approval sebelumnya belum disahkan.'];
        }

        if ($targetStep['status'] === 'approved') {
            return ['status' => false, 'message' => 'Tahapan approval nomor urut ' . $stepUrutan . ' sudah disahkan sebelumnya.'];
        }

        $finalTtd = !empty($approvalData['tanda_tangan']) ? $approvalData['tanda_tangan'] : (!empty($targetStep['tanda_tangan']) ? $targetStep['tanda_tangan'] : null);
        if (empty($finalTtd)) {
            return ['status' => false, 'message' => 'Setiap tingkatan approval wajib memiliki tanda tangan digital! Silakan goreskan tanda tangan pada canvas atau upload file tanda tangan.'];
        }

        $now = date('Y-m-d H:i:s');
        $ip = $this->input->ip_address();

        $this->db->trans_start();

        // 1. Update Step Target Jadi Approved
        $updateStep = [
            'status' => 'approved',
            'catatan' => trim($approvalData['catatan'] ?? ''),
            'tanda_tangan' => $finalTtd,
            'approved_by_user_id' => $userId,
            'approved_by_user_name' => $userName,
            'waktu_approval' => $now,
            'ip_address' => $ip,
            'updated_at' => $now
        ];

        $this->db->where('id', (int)$targetStep['id'])->update($this->table_tracking, $updateStep);

        // Jika pegawai belum punya master tanda tangan, simpan tanda tangan ini ke profil pegawai
        if (!empty($finalTtd) && !empty($targetStep['pegawai_id'])) {
            $pegawaiCheck = $this->db->where('id', (int)$targetStep['pegawai_id'])->get('pegawai')->row_array();
            if ($pegawaiCheck && empty($pegawaiCheck['tanda_tangan'])) {
                $this->db->where('id', (int)$targetStep['pegawai_id'])->update('pegawai', ['tanda_tangan' => $finalTtd]);
            }
        }

        // 2. Buka Step Berikutnya (Urutan + 1) dari 'waiting' menjadi 'pending'
        $nextStep = $this->db->where('pendaftaran_id', $pendaftaranId)
                             ->where('urutan >', $stepUrutan)
                             ->order_by('urutan', 'ASC')
                             ->limit(1)
                             ->get($this->table_tracking)
                             ->row_array();

        $isAllCompleted = false;

        if ($nextStep) {
            $this->db->where('id', (int)$nextStep['id'])
                     ->update($this->table_tracking, [
                         'status' => 'pending',
                         'updated_at' => $now
                     ]);
        } else {
            // Tidak ada step berikutnya lagi -> SEMUA TINGKATAN APPROVAL SELESAI!
            // Rilis hasil ke customer: status_order = 'completed'
            $isAllCompleted = true;

            $this->db->where('id', $pendaftaranId)->update('pendaftaran', [
                'status_order' => 'completed',
                'waktu_hasil_selesai' => $now,
                'updated_at' => $now
            ]);

            $this->db->where('pendaftaran_id', $pendaftaranId)->update('pendaftaran_detail', [
                'status_pengujian' => 'completed'
            ]);
        }

        // Log audit trail
        $this->db->insert('pendaftaran_log', [
            'pendaftaran_id' => $pendaftaranId,
            'action' => 'APPROVAL_HASIL_STEP_' . $stepUrutan,
            'description' => sprintf(
                "Approval Tingkat %d (%s) disahkan oleh %s (%s). Catatan: %s. %s",
                $stepUrutan,
                $targetStep['jabatan_approval'],
                $targetStep['pegawai_nama'],
                $userName,
                $approvalData['catatan'] ?? '-',
                $isAllCompleted ? "SELURUH APPROVAL LENGKAP. Hasil resmi dirilis ke customer." : "Menunggu approval tingkat selanjutnya."
            ),
            'user_id' => $userId,
            'user_name' => $userName
        ]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return ['status' => false, 'message' => 'Gagal memproses approval berjenjang ke database.'];
        }

        return [
            'status' => true,
            'message' => $isAllCompleted 
                ? 'Semua tahapan approval berjenjang telah lengkap! Hasil resmi pemeriksaan kini dirilis dan siap diserahkan ke customer.'
                : sprintf('Approval Tingkat %d (%s) berhasil disahkan. Berkas diteruskan ke approval tingkat selanjutnya.', $stepUrutan, $targetStep['jabatan_approval']),
            'is_all_completed' => $isAllCompleted,
            'waktu_approval' => $now
        ];
    }

    /**
     * Ulang rantai approval klinis (hasil direvisi / ditolak). Semua tanda tangan dibatalkan.
     */
    public function reset_order_approvals($pendaftaranId) {
        $this->db->where('pendaftaran_id', (int)$pendaftaranId)->delete($this->table_tracking);
    }

    public function count_approved($pendaftaranId) {
        return (int)$this->db->where('pendaftaran_id', (int)$pendaftaranId)
            ->where('status', 'approved')->count_all_results($this->table_tracking);
    }

    // =====================================================================
    // RANTAI APPROVAL UJI LINGKUNGAN (env_order_approvals)
    // =====================================================================

    public function env_init($orderId) {
        $orderId = (int)$orderId;
        if ($this->db->where('order_id', $orderId)->count_all_results('env_order_approvals') > 0) {
            return true;
        }
        $rules = $this->_rules_for('lingkungan');
        if (empty($rules)) return false;

        $now = date('Y-m-d H:i:s');
        $batch = [];
        foreach ($rules as $i => $r) {
            $batch[] = [
                'order_id' => $orderId,
                'rule_id' => (int)$r['id'],
                'urutan' => $i + 1,
                'pegawai_id' => (int)$r['pegawai_id'],
                'pegawai_nama' => $r['nama_formatted'],
                'pegawai_nip' => $r['nip'],
                'jabatan_approval' => $r['jabatan_approval'],
                'status' => $i === 0 ? 'pending' : 'waiting',
                'tanda_tangan' => $r['pegawai_tanda_tangan'] ?: null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        $this->db->insert_batch('env_order_approvals', $batch);
        return true;
    }

    public function env_get($orderId) {
        $rows = $this->db->where('order_id', (int)$orderId)->order_by('urutan', 'ASC')->get('env_order_approvals')->result_array();
        if (empty($rows)) {
            $this->env_init($orderId);
            $rows = $this->db->where('order_id', (int)$orderId)->order_by('urutan', 'ASC')->get('env_order_approvals')->result_array();
        }
        return $rows;
    }

    public function env_reset($orderId) {
        $this->db->where('order_id', (int)$orderId)->delete('env_order_approvals');
    }

    public function env_count_approved($orderId) {
        return (int)$this->db->where('order_id', (int)$orderId)->where('status', 'approved')->count_all_results('env_order_approvals');
    }

    public function env_check_keabsahan($orderId) {
        $rows = $this->env_get($orderId);
        $signed = 0;
        foreach ($rows as $r) {
            if ($r['status'] === 'approved' && !empty($r['tanda_tangan'])) $signed++;
        }
        $total = count($rows);
        return [
            'is_sah' => ($total > 0 && $signed === $total),
            'total_steps' => $total,
            'signed_count' => $signed,
        ];
    }

    public function env_approve_step($orderId, $urutan, $data, $userId = null, $userName = 'Petugas') {
        $orderId = (int)$orderId;
        $urutan = (int)$urutan;
        $order = $this->db->get_where('env_orders', ['id' => $orderId])->row_array();
        if (!$order) return ['status' => false, 'message' => 'Order lingkungan tidak ditemukan.'];
        if ($order['status'] !== 'validasi') {
            return ['status' => false, 'message' => 'Verifikasi hanya dapat dilakukan setelah hasil dikirim ke tahap Validasi (status saat ini: ' . strtoupper($order['status']) . ').'];
        }

        $rows = $this->env_get($orderId);
        if (empty($rows)) return ['status' => false, 'message' => 'Rantai verifikasi lingkungan belum dikonfigurasi.'];

        $target = null;
        foreach ($rows as $r) {
            if ((int)$r['urutan'] < $urutan && $r['status'] !== 'approved') {
                return ['status' => false, 'message' => 'Verifikasi harus berjenjang! Tahapan sebelumnya belum disahkan.'];
            }
            if ((int)$r['urutan'] === $urutan) $target = $r;
        }
        if (!$target) return ['status' => false, 'message' => 'Tahapan verifikasi tidak ditemukan.'];
        if ($target['status'] === 'approved') return ['status' => false, 'message' => 'Tahapan ini sudah disahkan.'];

        $ttd = !empty($data['tanda_tangan']) ? $data['tanda_tangan'] : $target['tanda_tangan'];
        if (empty($ttd)) return ['status' => false, 'message' => 'Setiap tingkatan wajib memiliki tanda tangan digital.'];

        $now = date('Y-m-d H:i:s');
        $this->db->trans_start();
        $this->db->where('id', (int)$target['id'])->update('env_order_approvals', [
            'status' => 'approved',
            'catatan' => trim($data['catatan'] ?? ''),
            'tanda_tangan' => $ttd,
            'approved_by_user_id' => $userId,
            'approved_by_user_name' => $userName,
            'waktu_approval' => $now,
            'ip_address' => $this->input->ip_address(),
            'updated_at' => $now,
        ]);

        $next = $this->db->where('order_id', $orderId)->where('urutan >', $urutan)->order_by('urutan', 'ASC')->limit(1)
            ->get('env_order_approvals')->row_array();
        $done = false;
        if ($next) {
            $this->db->where('id', (int)$next['id'])->update('env_order_approvals', ['status' => 'pending', 'updated_at' => $now]);
        } else {
            $done = true;
            $this->db->where('id', $orderId)->update('env_orders', ['status' => 'completed', 'updated_at' => $now]);
            if (!empty($order['pendaftaran_id'])) {
                $this->db->where('id', (int)$order['pendaftaran_id'])->update('pendaftaran', [
                    'status_order' => 'completed', 'waktu_hasil_selesai' => $now, 'updated_at' => $now
                ]);
            }
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) return ['status' => false, 'message' => 'Gagal menyimpan verifikasi.'];

        return [
            'status' => true,
            'is_all_completed' => $done,
            'message' => $done ? 'Seluruh tahapan verifikasi lengkap. LHU kini sah dan dapat dicetak.'
                               : 'Tahap ' . $urutan . ' (' . $target['jabatan_approval'] . ') disahkan. Diteruskan ke tahap berikutnya.',
        ];
    }

    /**
     * Evaluasi Keabsahan Hasil Uji Lab Berdasarkan Kelengkapan Tanda Tangan Approval Berjenjang
     * 
     * Syarat Hasil Sah:
     * 1. Seluruh tahapan approval yang dikonfigurasi telah berstatus 'approved'
     * 2. Setiap tahapan approval memiliki tanda tangan digital (tidak boleh kosong)
     * 3. Jika masa berjenjang belum selesai (belum semua menandatangani), status adalah BELUM SAH
     */
    public function check_keabsahan($pendaftaranId) {
        $pendaftaranId = (int)$pendaftaranId;
        $approvals = $this->get_order_approvals($pendaftaranId);
        
        $totalSteps = count($approvals);
        $approvedCount = 0;
        $signedCount = 0;
        $pendingSteps = [];
        $waitingSteps = [];
        
        foreach ($approvals as $app) {
            $hasSign = !empty($app['tanda_tangan']);
            if ($app['status'] === 'approved' && $hasSign) {
                $approvedCount++;
                $signedCount++;
            } elseif ($app['status'] === 'approved' && !$hasSign) {
                $pendingSteps[] = [
                    'urutan' => (int)$app['urutan'],
                    'jabatan' => $app['jabatan_approval'],
                    'pegawai' => $app['pegawai_nama'],
                    'status' => 'approved_no_sign',
                    'issue' => 'Disahkan tetapi tanda tangan digital belum tersemat'
                ];
            } elseif ($app['status'] === 'pending') {
                $pendingSteps[] = [
                    'urutan' => (int)$app['urutan'],
                    'jabatan' => $app['jabatan_approval'],
                    'pegawai' => $app['pegawai_nama'],
                    'status' => 'pending',
                    'issue' => 'Giliran aktif (Menunggu verifikasi & tanda tangan)'
                ];
            } else {
                $waitingSteps[] = [
                    'urutan' => (int)$app['urutan'],
                    'jabatan' => $app['jabatan_approval'],
                    'pegawai' => $app['pegawai_nama'],
                    'status' => 'waiting',
                    'issue' => 'Menunggu urutan sebelumnya selesai disahkan'
                ];
            }
        }
        
        $wajibSemuaTtd = (get_setting('approval_wajib_semua_ttd', '1') === '1');
        // Hasil belum sah TIDAK PERNAH boleh dicetak: mode selalu 'block'.
        $modeBelumLengkap = 'block';
        $teksWatermark = get_setting('approval_teks_watermark', 'HASIL BELUM SAH • MENUNGGU APPROVAL BERJENJANG');
        $keteranganHukum = get_setting('approval_keterangan_hukum', 'Hasil pemeriksaan ini BELUM SAH secara hukum dan belum dapat diserahkan ke pelanggan karena proses verifikasi berjenjang belum lengkap bertandatangan.');
        
        // Hasil dianggap SAH jika ada aturan approval dan SEMUA step berstatus approved serta bertandatangan
        $isSah = ($totalSteps > 0 && $signedCount === $totalSteps);
        
        return [
            'is_sah' => $isSah,
            'total_steps' => $totalSteps,
            'signed_count' => $signedCount,
            'approved_count' => $approvedCount,
            'remaining_count' => max(0, $totalSteps - $signedCount),
            'pending_steps' => $pendingSteps,
            'waiting_steps' => $waitingSteps,
            'wajib_semua_ttd' => $wajibSemuaTtd,
            'mode_belum_lengkap' => $modeBelumLengkap,
            'teks_watermark' => $teksWatermark,
            'keterangan_hukum' => $keteranganHukum,
            'status_label' => $isSah ? 'HASIL RESMI SAH' : 'BELUM SAH (MASA BERJENJANG)',
            'status_badge_class' => $isSah ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-rose-100 text-rose-900 border-rose-300'
        ];
    }
}
