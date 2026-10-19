-- =========================================================================
-- MIGRATION: TABEL PENGATURAN SISTEM & APLIKASI LIS PRO
-- UPT Laboratorium Kesehatan Provinsi Riau
-- =========================================================================

CREATE TABLE IF NOT EXISTS `pengaturan` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_group` VARCHAR(50) NOT NULL DEFAULT 'aplikasi',
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT NULL,
    `setting_type` VARCHAR(20) NOT NULL DEFAULT 'text',
    `label` VARCHAR(150) NOT NULL,
    `description` VARCHAR(255) NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    INDEX `idx_group` (`setting_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEED DATA DEFAULT PENGATURAN APLIKASI
INSERT INTO `pengaturan` (`setting_group`, `setting_key`, `setting_value`, `setting_type`, `label`, `description`, `sort_order`, `created_at`, `updated_at`) VALUES
-- 1. IDENTITAS & BRANDING
('aplikasi', 'nama_aplikasi', 'LIS PRO - Laboratory Information System', 'text', 'Nama Aplikasi LIS', 'Nama sistem yang ditampilkan pada header dan dokumen resmi', 1, NOW(), NOW()),
('aplikasi', 'singkatan_aplikasi', 'LIS PRO LABKES RIAU', 'text', 'Singkatan / Brand Singkat', 'Nama ringkas brand pada sidebar dan favicon', 2, NOW(), NOW()),
('aplikasi', 'nama_instansi', 'UPT Laboratorium Kesehatan & Lingkungan Provinsi Riau', 'text', 'Nama Resmi Instansi', 'Nama faskes laboratorium pengelola BLUD', 3, NOW(), NOW()),
('aplikasi', 'instansi_induk', 'Dinas Kesehatan Provinsi Riau', 'text', 'Instansi Induk / Pemerintah', 'Pemerintah daerah atau kementerian pengampu', 4, NOW(), NOW()),
('aplikasi', 'slogan_aplikasi', 'Akurat, Cepat, Terpercaya, dan Terstandar Mutu Nasional', 'text', 'Slogan / Motto Pelayanan', 'Motto pelayanan laboratorium', 5, NOW(), NOW()),
('aplikasi', 'nomor_izin_lab', '445/DINKES-LAB/2024/008', 'text', 'No. Izin Operasional / Registrasi Faskes', 'Nomor izin operasional laboratorium dari Kemenkes/Dinkes', 6, NOW(), NOW()),
('aplikasi', 'tipe_laboratorium', 'Laboratorium Kesehatan Daerah (Labkesda BLUD)', 'select', 'Tipe & Kelas Laboratorium', 'Kualifikasi jenis laboratorium', 7, NOW(), NOW()),

-- 2. ALAMAT & KONTAK
('aplikasi', 'alamat_lengkap', 'Jl. Cut Nyak Dien No. 01, Kel. Sukajadi', 'textarea', 'Alamat Lengkap Kantor / Lab', 'Alamat gedung operasional laboratorium', 8, NOW(), NOW()),
('aplikasi', 'kota_kabupaten', 'Kota Pekanbaru', 'text', 'Kota / Kabupaten', 'Kota domisili faskes', 9, NOW(), NOW()),
('aplikasi', 'provinsi', 'Riau', 'text', 'Provinsi', 'Provinsi domisili faskes', 10, NOW(), NOW()),
('aplikasi', 'kode_pos', '28121', 'text', 'Kode Pos', 'Kode pos wilayah operasional', 11, NOW(), NOW()),
('aplikasi', 'telepon', '(0761) 23456', 'text', 'Nomor Telepon Kantor', 'Nomor telepon resmi kantor', 12, NOW(), NOW()),
('aplikasi', 'whatsapp_layanan', '0812-7567-8901', 'text', 'WhatsApp Layanan & Helpdesk', 'Nomor kontak WhatsApp interaktif pelanggan', 13, NOW(), NOW()),
('aplikasi', 'email_resmi', 'labkes@riau.go.id', 'text', 'Email Resmi Laboratorium', 'Alamat surat elektronik resmi laboratorium', 14, NOW(), NOW()),
('aplikasi', 'website_resmi', 'https://labkesda.riau.go.id', 'text', 'Website Resmi Faskes', 'Portal informasi dan hasil online faskes', 15, NOW(), NOW()),

-- 3. PEJABAT & PENANGGUNG JAWAB
('aplikasi', 'kepala_lab', 'dr. H. Hendra Saputra, Sp.PK', 'text', 'Nama Kepala Laboratorium', 'Pimpinan / Kepala UPT Labkes', 16, NOW(), NOW()),
('aplikasi', 'nip_kepala_lab', '19780512 200501 1 004', 'text', 'NIP Kepala Laboratorium', 'Nomor Induk Pegawai pimpinan', 17, NOW(), NOW()),
('aplikasi', 'pangkat_kepala_lab', 'Pembina (Gol. IV/a)', 'text', 'Pangkat / Golongan Kepala Lab', 'Pangkat dinas kepegawaian pimpinan', 18, NOW(), NOW()),
('aplikasi', 'dokter_penanggung_jawab', 'dr. Hj. Siti Rahmah, Sp.PK, M.Kes', 'text', 'Dokter Penanggung Jawab Teknis', 'Dokter Spesialis Patologi Klinis penandatangan hasil', 19, NOW(), NOW()),
('aplikasi', 'sip_dokter', '503/SIP-D/2023/0421', 'text', 'SIP Dokter Penanggung Jawab', 'Surat Izin Praktik dokter spesialis', 20, NOW(), NOW()),
('aplikasi', 'kasi_pelayanan', 'Ahmad Zulkarnain, S.Tr.Kes', 'text', 'Kepala Seksi Pelayanan & Mutu', 'Penanggung jawab administrasi operasional', 21, NOW(), NOW()),
('aplikasi', 'nip_kasi_pelayanan', '19850314 200902 1 002', 'text', 'NIP Kasi Pelayanan', 'Nomor induk pegawai kepala seksi', 22, NOW(), NOW()),

-- 4. KOP DOKUMEN & FORMAT KUITANSI
('aplikasi', 'kop_header_line1', 'PEMERINTAH PROVINSI RIAU', 'text', 'Kop Cetak Baris 1', 'Pemerintah daerah / organisasi induk', 23, NOW(), NOW()),
('aplikasi', 'kop_header_line2', 'DINAS KESEHATAN', 'text', 'Kop Cetak Baris 2', 'Dinas pengampu', 24, NOW(), NOW()),
('aplikasi', 'kop_header_line3', 'UPT LABORATORIUM KESEHATAN DAN LINGKUNGAN', 'text', 'Kop Cetak Baris 3', 'Unit pelaksana teknis faskes', 25, NOW(), NOW()),
('aplikasi', 'kop_footer_note', 'Hasil pemeriksaan ini sah dicetak otomatis oleh LIS PRO dan terakreditasi KAN ISO/IEC 17025 & ISO 15189.', 'textarea', 'Catatan Kaki Hasil & Kuitansi', 'Disclaimer hukum dan akreditasi pada lembar cetak', 26, NOW(), NOW()),
('aplikasi', 'masa_berlaku_hasil', '30', 'number', 'Masa Berlaku Hasil Uji (Hari)', 'Batas waktu validitas lembar hasil pemeriksaan', 27, NOW(), NOW()),
('aplikasi', 'biaya_administrasi_default', '15000', 'number', 'Biaya Administrasi Default (Rp)', 'Tarif jasa pendaftaran dasar jika ada', 28, NOW(), NOW()),
('aplikasi', 'zona_waktu', 'Asia/Jakarta', 'text', 'Zona Waktu Server', 'WIB / GMT+7', 29, NOW(), NOW()),

-- 5. GROUP AI (KECERDASAN BUATAN)
('ai', 'ai_provider', 'gemini', 'select', 'Provider Mesin AI', 'Penyedia LLM untuk analisis medis laboratorium', 30, NOW(), NOW()),
('ai', 'ai_api_key', '', 'text', 'API Key AI Provider', 'Kunci rahasia API Google Gemini atau OpenAI', 31, NOW(), NOW()),
('ai', 'ai_model', 'gemini-1.5-flash', 'text', 'Model AI yang Digunakan', 'Versi model LLM (contoh: gemini-1.5-flash / gpt-4o)', 32, NOW(), NOW()),
('ai', 'ai_auto_interpretation', '1', 'boolean', 'Interpretasi Klinis Otomatis', 'Aktifkan ringkasan cerdas nilai abnormal hasil lab', 33, NOW(), NOW()),
('ai', 'ai_critical_alert', '1', 'boolean', 'Peringatan Nilai Kritis Lab (Critical Value Alert)', 'Notifikasi instan jika parameter menyentuh batas bahaya', 34, NOW(), NOW()),
('ai', 'ai_clinical_summary', '1', 'boolean', 'Ringkasan Resume Medis Pasien', 'Pembuatan resume komprehensif riwayat lab pasien', 35, NOW(), NOW()),
('ai', 'ai_prompt_template', 'Anda adalah asisten AI medis laboratorium klinik spesialis Patologi Klinis untuk UPT Laboratorium Kesehatan Riau. Berikan analisis interpretasi klinis terhadap parameter abnormal secara ringkas, objektif, profesional, dan berbasis bukti medis terkini.', 'textarea', 'System Prompt Template AI', 'Instruksi dasar untuk asisten kecerdasan buatan', 36, NOW(), NOW()),

-- 6. GROUP BRIDGING & INTERFACING
('bridging', 'satusehat_active', '1', 'boolean', 'Aktivasi Bridging SatuSehat', 'Interoperabilitas Kemenkes RI FHIR R4', 37, NOW(), NOW()),
('bridging', 'satusehat_env', 'development', 'select', 'Environment SatuSehat', 'Development / Staging / Production', 38, NOW(), NOW()),
('bridging', 'satusehat_org_id', '100000123', 'text', 'Organization ID SatuSehat', 'ID Faskes dari DTO Kemenkes', 39, NOW(), NOW()),
('bridging', 'satusehat_client_id', 'client_labkesda_riau_dev', 'text', 'Client ID SatuSehat', 'Kunci klien OAuth2', 40, NOW(), NOW()),
('bridging', 'satusehat_client_secret', '', 'text', 'Client Secret SatuSehat', 'Kunci rahasia OAuth2', 41, NOW(), NOW()),
('bridging', 'satusehat_auth_url', 'https://api-satusehat-dev.dto.kemkes.go.id/oauth2/v1', 'text', 'Auth URL SatuSehat', 'Endpoint autentikasi token FHIR', 42, NOW(), NOW()),
('bridging', 'satusehat_fhir_url', 'https://api-satusehat-dev.dto.kemkes.go.id/fhir-r4/v1', 'text', 'FHIR Base URL SatuSehat', 'Endpoint DiagnosticReport & Specimen', 43, NOW(), NOW()),
('bridging', 'analyzer_protocol', 'ASTM_E1394', 'select', 'Protokol Alat Laboratorium', 'ASTM E1394 atau HL7 v2.x', 44, NOW(), NOW()),
('bridging', 'analyzer_ip', '192.168.1.150', 'text', 'IP Host Alat Analyzer', 'Alamat jaringan analyzer hematologi/kimia', 45, NOW(), NOW()),
('bridging', 'analyzer_port', '5000', 'number', 'Port Komunikasi Alat', 'Port TCP/IP listening', 46, NOW(), NOW()),
('bridging', 'his_webhook_url', 'http://simrs.rsud.riau.go.id/api/lis/callback', 'text', 'Webhook Callback SIMRS / HIS', 'Kirim otomatis hasil ke SIMRS Faskes Pengirim', 47, NOW(), NOW()),
('bridging', 'his_api_key', 'his_tok_9281a8b7c6', 'text', 'Secret Token API SIMRS', 'Token verifikasi payload webhook', 48, NOW(), NOW())
ON DUPLICATE KEY UPDATE `updated_at` = NOW();
