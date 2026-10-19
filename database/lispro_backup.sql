-- MariaDB dump 10.17  Distrib 10.4.11-MariaDB, for osx10.10 (x86_64)
--
-- Host: localhost    Database: lispro
-- ------------------------------------------------------
-- Server version	10.4.11-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `akun_bayar`
--

DROP TABLE IF EXISTS `akun_bayar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `akun_bayar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_bayar` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_bayar` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kode_rekening` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipe_transaksi` enum('cash','qris','debit','kredit','transfer') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cash',
  `nama_bank` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nomor_rekening_bank` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `atas_nama` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `biaya_admin` decimal(10,2) NOT NULL DEFAULT 0.00,
  `urutan` int(11) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_bayar` (`kode_bayar`),
  KEY `idx_tipe` (`tipe_transaksi`),
  KEY `idx_kode_rekening` (`kode_rekening`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `akun_bayar`
--

LOCK TABLES `akun_bayar` WRITE;
/*!40000 ALTER TABLE `akun_bayar` DISABLE KEYS */;
INSERT INTO `akun_bayar` VALUES (1,'CASH-01','Kasir Tunai Loket','1.1.01.01','cash',NULL,NULL,'Bendahara Penerimaan BLUD',0.00,1,1,'2026-10-17 19:50:17','2026-10-17 19:50:17'),(2,'QRIS-BRKS','QRIS Bank Riau Kepri Syariah','1.1.02.05','qris','Bank Riau Kepri Syariah','9360012345678','BBLKES PROV RIAU',0.70,2,1,'2026-10-17 19:50:17','2026-10-17 19:50:17'),(3,'EDC-BRI','Kartu Debit EDC Bank BRI','1.1.02.02','debit','Bank BRI','001201000888301','BLUD LABKES RIAU PENERIMAAN',0.00,3,1,'2026-10-17 19:50:17','2026-10-17 19:50:17'),(4,'TRF-BLUD','Transfer Rekening Giro BLUD','1.1.02.01','transfer','Bank Riau Kepri Syariah','1010800012345','BLUD LABKES PROVINSI RIAU',0.00,4,1,'2026-10-17 19:50:17','2026-10-17 19:50:17');
/*!40000 ALTER TABLE `akun_bayar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `akun_piutang`
--

DROP TABLE IF EXISTS `akun_piutang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `akun_piutang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_piutang` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_piutang` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kode_rekening` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategori_penjamin` enum('bpjs','pks_perusahaan','faskes_perujuk','asuransi_lain') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'bpjs',
  `jatuh_tempo_hari` int(11) NOT NULL DEFAULT 30,
  `kontak_person` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_telepon` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_piutang` (`kode_piutang`),
  KEY `idx_penjamin` (`kategori_penjamin`),
  KEY `idx_kode_rekening` (`kode_rekening`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `akun_piutang`
--

LOCK TABLES `akun_piutang` WRITE;
/*!40000 ALTER TABLE `akun_piutang` DISABLE KEYS */;
INSERT INTO `akun_piutang` VALUES (1,'PIUT-BPJS','BPJS Kesehatan KC Pekanbaru','1.1.03.01','bpjs',30,'dr. Hendra (PIC Klaim)','0761-33214','klaim.pekanbaru@bpjs-kesehatan.go.id','Jl. Jenderal Sudirman No. 120, Pekanbaru',1,'2026-10-17 19:50:17','2026-10-17 19:50:17'),(2,'PIUT-PHR','PT Pertamina Hulu Rokan (PHR)','1.1.03.02','pks_perusahaan',45,'Bpk. Rizky (HR-Medical)','0761-945000','medical.rokan@phr.pertamina.com','Rumbai Camp, Pekanbaru, Riau',1,'2026-10-17 19:50:17','2026-10-17 19:50:17'),(3,'PIUT-RAPP','PT Riau Andalan Pulp & Paper (RAPP)','1.1.03.02','pks_perusahaan',45,'Ibu Maya (Occupational Health)','0761-491000','health_safety@aprilasia.com','Pangkalan Kerinci, Pelalawan, Riau',1,'2026-10-17 19:50:17','2026-10-17 19:50:17'),(4,'PIUT-PKM-SKJ','Puskesmas Sukajadi Pekanbaru','1.1.03.03','faskes_perujuk',30,'Ns. Dewi (Koordinator Lab)','0812-7654-3210','pkm.sukajadi@pekanbaru.go.id','Jl. Melur No. 45, Sukajadi, Pekanbaru',1,'2026-10-17 19:50:17','2026-10-17 19:50:17'),(5,'PIUT-ASR-PRU','Prudential Life Assurance','1.1.03.04','asuransi_lain',21,'Admedika Provider Desk','021-29647000','claim.desk@admedika.co.id','Jakarta Selatan',1,'2026-10-17 19:50:17','2026-10-17 19:50:17');
/*!40000 ALTER TABLE `akun_piutang` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `akun_rekening`
--

DROP TABLE IF EXISTS `akun_rekening`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `akun_rekening` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_rekening` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_rekening` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategori_id` int(11) DEFAULT NULL,
  `kategori` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `posisi_normal` enum('D','K') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'D',
  `level` tinyint(1) NOT NULL DEFAULT 1,
  `parent_kode` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_kas_bank` tinyint(1) NOT NULL DEFAULT 0,
  `is_piutang` tinyint(1) NOT NULL DEFAULT 0,
  `is_pendapatan` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `keterangan` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_rekening` (`kode_rekening`),
  KEY `idx_parent` (`parent_kode`),
  KEY `idx_kategori` (`kategori`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_kategori_id` (`kategori_id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `akun_rekening`
--

LOCK TABLES `akun_rekening` WRITE;
/*!40000 ALTER TABLE `akun_rekening` DISABLE KEYS */;
INSERT INTO `akun_rekening` VALUES (1,'1','ASET',1,'aset','D',1,NULL,0,0,0,1,'Induk Utama Aktiva / Aset BLUD','2026-10-17 19:49:30','2026-10-17 19:49:30'),(2,'2','KEWAJIBAN',2,'kewajiban','K',1,NULL,0,0,0,1,'Induk Utama Liabilitas / Kewajiban','2026-10-17 19:49:30','2026-10-17 19:49:30'),(3,'3','EKUITAS',3,'ekuitas','K',1,NULL,0,0,0,1,'Induk Utama Ekuitas Dana','2026-10-17 19:49:30','2026-10-17 19:49:30'),(4,'4','PENDAPATAN',4,'pendapatan','K',1,NULL,0,0,0,1,'Induk Utama Pendapatan Layanan Lab','2026-10-17 19:49:30','2026-10-17 19:49:30'),(5,'5','BEBAN',5,'beban','D',1,NULL,0,0,0,1,'Induk Utama Beban Operasional','2026-10-17 19:49:30','2026-10-17 19:49:30'),(6,'6','PENDAPATAN NON-OPERASIONAL',6,'pendapatan_non_operasional','K',1,NULL,0,0,0,1,'Induk Utama Pendapatan Non-Operasional','2026-10-17 19:49:30','2026-10-17 19:49:30'),(7,'7','BEBAN NON-OPERASIONAL',7,'beban_non_operasional','D',1,NULL,0,0,0,1,'Induk Utama Beban Non-Operasional','2026-10-17 19:49:30','2026-10-17 19:49:30'),(8,'1.1','Aset Lancar',1,'aset','D',2,'1',0,0,0,1,'Aset yang dapat dicairkan dalam 1 tahun','2026-10-17 19:49:30','2026-10-17 19:49:30'),(9,'1.2','Aset Tetap Peralatan Laboratorium',1,'aset','D',2,'1',0,0,0,1,'Aset berwujud peralatan & mesin lab','2026-10-17 19:49:30','2026-10-17 19:49:30'),(10,'2.1','Kewajiban Jangka Pendek',2,'kewajiban','K',2,'2',0,0,0,1,'Kewajiban lancar jatuh tempo < 1 tahun','2026-10-17 19:49:30','2026-10-17 19:49:30'),(11,'3.1','Ekuitas BLUD Labkesda',3,'ekuitas','K',2,'3',0,0,0,1,'Modal & Ekuitas Donasi / Saldo Anggaran','2026-10-17 19:49:30','2026-10-17 19:49:30'),(12,'4.1','Pendapatan Jasa Layanan Laboratorium',4,'pendapatan','K',2,'4',0,0,1,1,'Penerimaan operasional pengujian lab','2026-10-17 19:49:30','2026-10-17 19:49:30'),(13,'5.1','Beban Operasional Laboratorium',5,'beban','D',2,'5',0,0,0,1,'Biaya operasional lab & BMHP','2026-10-17 19:49:30','2026-10-17 19:49:30'),(14,'1.1.01','Kas dan Setara Kas',1,'aset','D',3,'1.1',1,0,0,1,'Kas Tunai di Loket & Petty Cash','2026-10-17 19:49:30','2026-10-17 19:49:30'),(15,'1.1.02','Kas di Bank (Rekening Bank)',1,'aset','D',3,'1.1',1,0,0,1,'Sub Rekening Bank BLUD & Penerimaan','2026-10-17 19:49:30','2026-10-17 19:49:30'),(16,'1.1.03','Piutang Pelayanan Laboratorium',1,'aset','D',3,'1.1',0,1,0,1,'Sub Piutang Pasien, BPJS & Rekanan','2026-10-17 19:49:30','2026-10-17 19:49:30'),(17,'1.1.04','Persediaan Reagen & BHP Lab',1,'aset','D',3,'1.1',0,0,0,1,'Persediaan reagen kimia & konsumabel','2026-10-17 19:49:30','2026-10-17 19:49:30'),(18,'1.2.01','Peralatan dan Mesin Laboratorium',1,'aset','D',3,'1.2',0,0,0,1,'Instrumen Hematology & Chemistry Analyzer','2026-10-17 19:49:30','2026-10-17 19:49:30'),(19,'2.1.01','Utang Usaha Pembelian Reagen',2,'kewajiban','K',3,'2.1',0,0,0,1,'Utang kepada distributor reagen/BHP','2026-10-17 19:49:30','2026-10-17 19:49:30'),(20,'4.1.01','Pendapatan Tarif Pasien Mandiri',4,'pendapatan','K',3,'4.1',0,0,1,1,'Penerimaan Pasien Umum / Walk-in','2026-10-17 19:49:30','2026-10-17 19:49:30'),(21,'4.1.02','Pendapatan Klaim JKN / BPJS',4,'pendapatan','K',3,'4.1',0,0,1,1,'Penerimaan Klaim BPJS Kesehatan','2026-10-17 19:49:30','2026-10-17 19:49:30'),(22,'4.1.03','Pendapatan Kerjasama PKS Perusahaan',4,'pendapatan','K',3,'4.1',0,0,1,1,'Penerimaan MCU Perusahaan Mitra','2026-10-17 19:49:30','2026-10-17 19:49:30'),(23,'4.1.04','Pendapatan Rujukan Puskesmas & RS',4,'pendapatan','K',3,'4.1',0,0,1,1,'Penerimaan Rujukan Faskes Luar','2026-10-17 19:49:30','2026-10-17 19:49:30'),(24,'5.1.01','Beban Reagen & Bahan Kimia Lab',5,'beban','D',3,'5.1',0,0,0,1,'Biaya Pemakaian Reagen Rutin','2026-10-17 19:49:30','2026-10-17 19:49:30'),(25,'5.1.02','Beban Pemeliharaan & Kalibrasi Alat',5,'beban','D',3,'5.1',0,0,0,1,'Biaya Kalibrasi Alat & Service Kontrak','2026-10-17 19:49:30','2026-10-17 19:49:30'),(26,'1.1.01.01','Kas di Bendahara Penerimaan / Kasir Lab',1,'aset','D',4,'1.1.01',1,0,0,1,'Uang Kas Tunai di Loket Pembayaran','2026-10-17 19:49:30','2026-10-17 19:49:30'),(27,'1.1.01.02','Kas Kecil Operasional Laboratorium',1,'aset','D',4,'1.1.01',1,0,0,1,'Petty Cash Pengeluaran Harian','2026-10-17 19:49:30','2026-10-17 19:49:30'),(28,'1.1.02.01','Bank Riau Kepri Syariah (Giro BLUD)',1,'aset','D',4,'1.1.02',1,0,0,1,'Rekening Operasional Giro BRK Syariah','2026-10-17 19:49:30','2026-10-17 19:49:30'),(29,'1.1.02.02','Bank BRI (Penerimaan Kasir EDC)',1,'aset','D',4,'1.1.02',1,0,0,1,'Rekening Penampungan EDC BRI Loket','2026-10-17 19:49:30','2026-10-17 19:49:30'),(30,'1.1.02.03','Bank Mandiri (Rekening Operasional Lab)',1,'aset','D',4,'1.1.02',1,0,0,1,'Rekening Bank Mandiri Operasional','2026-10-17 19:49:30','2026-10-17 19:49:30'),(31,'1.1.02.04','Bank BNI (Rekening Pengeluaran BLUD)',1,'aset','D',4,'1.1.02',1,0,0,1,'Rekening Bank BNI Pengeluaran','2026-10-17 19:49:30','2026-10-17 19:49:30'),(32,'1.1.02.05','QRIS Bank Riau Kepri Syariah',1,'aset','D',4,'1.1.02',1,0,0,1,'Kanal QRIS Dinamis Loket Kasir','2026-10-17 19:49:30','2026-10-17 19:49:30'),(33,'1.1.03.01','Piutang BPJS Kesehatan',1,'aset','D',4,'1.1.03',0,1,0,1,'Piutang Klaim JKN BPJS Kesehatan','2026-10-17 19:49:30','2026-10-17 19:49:30'),(34,'1.1.03.02','Piutang PKS Perusahaan & Korporasi',1,'aset','D',4,'1.1.03',0,1,0,1,'Piutang Rekanan PT PHR, PT RAPP, dll','2026-10-17 19:49:30','2026-10-17 19:49:30'),(35,'1.1.03.03','Piutang Faskes Perujuk & Puskesmas',1,'aset','D',4,'1.1.03',0,1,0,1,'Piutang Retribusi Puskesmas & RS Perujuk','2026-10-17 19:49:30','2026-10-17 19:49:30'),(36,'1.1.03.04','Piutang Asuransi Swasta Komersial',1,'aset','D',4,'1.1.03',0,1,0,1,'Piutang Klaim Asuransi Komersial','2026-10-17 19:49:30','2026-10-17 19:49:30'),(37,'1.1.04.01','Persediaan Reagen Hematologi',1,'aset','D',4,'1.1.04',0,0,0,1,'Reagen Diluent, Lyse, Cleaner','2026-10-17 19:49:30','2026-10-17 19:49:30'),(38,'1.1.04.02','Persediaan Reagen Kimia Klinik',1,'aset','D',4,'1.1.04',0,0,0,1,'Reagen SGOT, SGPT, Kolesterol, Glukosa','2026-10-17 19:49:30','2026-10-17 19:49:30'),(39,'1.1.04.03','Persediaan BHP Medis & Tabung Sampel',1,'aset','D',4,'1.1.04',0,0,0,1,'Vacutainer, Jarum Spuit, Lanset, Kapas','2026-10-17 19:49:30','2026-10-17 19:49:30');
/*!40000 ALTER TABLE `akun_rekening` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clients`
--

DROP TABLE IF EXISTS `clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_type` enum('perorangan','perusahaan') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'perorangan',
  `client_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `identity_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` enum('L','P') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `blood_type` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pic_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pic_phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contract_discount` decimal(5,2) NOT NULL DEFAULT 0.00,
  `payment_term` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Tunai',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `notes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `client_code` (`client_code`),
  KEY `idx_client_type` (`client_type`),
  KEY `idx_client_code` (`client_code`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clients`
--

LOCK TABLES `clients` WRITE;
/*!40000 ALTER TABLE `clients` DISABLE KEYS */;
INSERT INTO `clients` VALUES (2,'perorangan','PSN-2026-0002','Nurul Hidayati','3201015608950001','P','1995-08-16','B',NULL,NULL,'081387654321','nurul.h@example.com','Jl. Merak No. 12, Kebayoran Baru',0.00,'Tunai','active','Pasien tes gula darah rutin','2026-10-16 18:51:25','2026-10-16 18:51:25'),(10,'perorangan','PSN-2026-0007','Log Test Pasien',NULL,NULL,NULL,NULL,NULL,NULL,'081211122233','log@pasien.com','Jl. Log',0.00,'Tunai','active','Test logging perorangan','2026-10-16 19:16:38','2026-10-16 19:16:38'),(11,'perorangan','PSN-2026-0008','PT. SAMSUNG','1212121','L','2026-10-16','B',NULL,NULL,'085365811832','moiz@gmail.com','jalan',0.00,'Tunai','active',NULL,'2026-10-16 19:17:15','2026-10-16 19:17:15'),(13,'perorangan','PSN-2026-0009','Pasien Uji Otomatis',NULL,'L','1995-05-15',NULL,NULL,NULL,'081234567890',NULL,NULL,0.00,'Tunai','active',NULL,'2026-10-17 09:41:33','2026-10-17 09:41:33'),(14,'perusahaan','CORP-2026-0001','PT Pertamina Hulu Rokan','01.000.123.4-216.000',NULL,NULL,NULL,'Bambang Supriyanto','081234567800','0761-987654','health.environment@phr.co.id','Rumbai Camp, Pekanbaru, Riau',10.00,'Kredit 30 Hari','active',NULL,'2026-10-18 19:57:53','2026-10-18 19:57:53'),(15,'perusahaan','CORP-2026-0002','PT Riau Andalan Pulp and Paper (RAPP)','01.000.456.7-216.000',NULL,NULL,NULL,'Hendra Wijaya','081378901234','0761-456789','clinic.mcu@aprilasia.com','Pangkalan Kerinci, Pelalawan, Riau',15.00,'Kredit 45 Hari','active',NULL,'2026-10-18 19:57:53','2026-10-18 19:57:53'),(16,'perusahaan','CORP-2026-0003','MoizCare Indonesia',NULL,NULL,NULL,NULL,'Ahmad Tohar','085365811832','085365811832','tohargraciaz9@gmail.com','Jln. Teropong',0.00,'Tunai / Mandiri','active',NULL,'2026-10-18 20:43:01','2026-10-18 20:43:01'),(17,'perusahaan','CORP-2026-0004','RS Harapan Bunda Riau','12.345.678.9-123.000',NULL,NULL,NULL,'dr. Budi Santoso','081234567890','081299887766',NULL,'Jl. Sudirman No. 45 Pekanbaru',10.00,'Kredit 30 Hari','active',NULL,'2026-10-18 20:54:48','2026-10-18 20:54:48');
/*!40000 ALTER TABLE `clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kategori_akun`
--

DROP TABLE IF EXISTS `kategori_akun`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kategori_akun` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_kategori` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_kategori` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `posisi_normal` enum('D','K') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'D',
  `urutan` int(11) NOT NULL DEFAULT 1,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `deskripsi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_kategori` (`kode_kategori`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kategori_akun`
--

LOCK TABLES `kategori_akun` WRITE;
/*!40000 ALTER TABLE `kategori_akun` DISABLE KEYS */;
INSERT INTO `kategori_akun` VALUES (1,'1','ASET','aset','D',1,1,1,'Seluruh hak dan kekayaan entitas (Kas, Bank, Piutang, Persediaan, Aset Tetap)','2026-10-17 13:01:46',NULL),(2,'2','KEWAJIBAN','kewajiban','K',2,1,1,'Liabilitas / kewajiban masa kini (Utang Usaha, Utang Pajak, Beban Akrual)','2026-10-17 13:01:46',NULL),(3,'3','EKUITAS','ekuitas','K',3,1,1,'Hak residual atas aset entitas setelah dikurangi seluruh liabilitas','2026-10-17 13:01:46',NULL),(4,'4','PENDAPATAN','pendapatan','K',4,1,1,'Pendapatan operasional dari tarif layanan lab, pasien mandiri & klaim penjamin','2026-10-17 13:01:46',NULL),(5,'5','BEBAN','beban','D',5,1,1,'Beban operasional pelayanan lab, BMHP, reagen, pemeliharaan & jasa medis','2026-10-17 13:01:46',NULL),(6,'6','PENDAPATAN NON-OPERASIONAL','pendapatan_non_operasional','K',6,0,1,'Pendapatan di luar aktivitas utama (Jasa Giro / Bunga Bank, Klaim Asuransi)','2026-10-17 13:01:46',NULL),(7,'7','BEBAN NON-OPERASIONAL','beban_non_operasional','D',7,0,1,'Beban di luar aktivitas operasional (Biaya Administrasi Bank, Pajak Bunga)','2026-10-17 13:01:46',NULL),(8,'8','PEMBIAYAAN','pembiayaan','D',8,0,1,'Transaksi penerimaan dan pengeluaran pembiayaan BLUD','2026-10-17 13:01:46',NULL);
/*!40000 ALTER TABLE `kategori_akun` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lab_categories`
--

DROP TABLE IF EXISTS `lab_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lab_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `category_code` (`category_code`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lab_categories`
--

LOCK TABLES `lab_categories` WRITE;
/*!40000 ALTER TABLE `lab_categories` DISABLE KEYS */;
INSERT INTO `lab_categories` VALUES (1,'HEMATOLOGI','Hematologi','Pemeriksaan darah lengkap, hemostasis, dan komponen seluler darah',1,1,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(2,'URINALISA','Urinalisa','Pemeriksaan urine rutin, kimia urine, sedimen mikroskopis',2,1,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(3,'K_KLINIK','Kimia Klinik','Pemeriksaan fungsi hati, ginjal, profil lipid, gula darah, elektrolit',3,1,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(4,'PARASITOLOGI','Parasitologi','Pemeriksaan feses rutin, malaria, mikologi, parasit saluran cerna',4,1,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(5,'IMMUNOLOGI','Imunologi & Serologi','Penanda infeksi HBsAg, Anti-HCV, HIV, Widal, Typhoid, Dengue NS1',5,1,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(6,'KULTUR','Kultur & Mikrobiologi','Kultur kuman, tes resistensi antibiotik, pewarnaan gram/BTA',6,1,'2026-10-16 22:52:30','2026-10-16 22:52:30');
/*!40000 ALTER TABLE `lab_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lab_methods`
--

DROP TABLE IF EXISTS `lab_methods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lab_methods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `method_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `method_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `standard_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Contoh: IKM/5.4.40/LKL-PR, Standar SOP',
  `instrument_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nama Analyzer/Alat Otomatis, misal: Mindray BC-5380',
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `method_code` (`method_code`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lab_methods`
--

LOCK TABLES `lab_methods` WRITE;
/*!40000 ALTER TABLE `lab_methods` DISABLE KEYS */;
INSERT INTO `lab_methods` VALUES (1,'MET-AUTO-BC','Automatic B.C Counter','IKM/5.4.40/LKL-PR','Hematology Analyzer 5-Diff','Penghitungan sel darah otomatis berbasis laser scatter dan impedansi',1,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(2,'MET-WESTERGREEN','Westergreen','IKM/5.4.12/LKL-PR','Rak & Pipet Westergreen Manual','Metode laju endap darah standar 1 jam',1,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(3,'MET-MANUAL-MIC','Manual (Mikroskopis)','IKM/5.4.05/LKL-PR','Mikroskop Binokuler & Counter Sel','Pemeriksaan hitung jenis leukosit dan sedimen preparat apus darah tepi',1,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(4,'MET-LEE-WHITE','Lee dan White','IKM/5.4.18/LKL-PR','Waterbath & Tabung Reaksi','Waktu pembekuan darah lengkap tabung bertahap',1,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(5,'MET-DUKE','Duke','IKM/5.4.19/LKL-PR','Lancet & Kertas Saring','Waktu perdarahan metode tusukan cuping telinga / jari',1,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(6,'MET-RUMPLE-LEEDE','Rumple Leede','IKM/5.4.22/LKL-PR','Tensimeter & Stetoskop','Uji fragilitas kapiler (RL Test) pembendungan manset',1,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(7,'MET-SLIDE-AGGLU','Slide / Aglutinasi','IKM/5.4.01/LKL-PR','Kaca Objek & Reagen Antisera','Penetapan golongan darah ABO dan Rhesus',1,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(8,'MET-PHOTOMETRIC','Fotometrik Kimia Klinis','IKM/5.4.30/LKL-PR','Chemistry Analyzer Otomatis','Pemeriksaan enzim, substrat, dan protein enzimatik',1,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(9,'MET-STRIP-REFLECT','Reflektansi Strip Urin','IKM/5.4.15/LKL-PR','Urine Analyzer Strip','Pemeriksaan kimiawi 10-11 parameter urine strip otomatis',1,'2026-10-16 22:52:30','2026-10-16 22:52:30');
/*!40000 ALTER TABLE `lab_methods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lab_parameters`
--

DROP TABLE IF EXISTS `lab_parameters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lab_parameters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `method_id` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL COMMENT 'ID parent jika sub-parameter, misal Diff Count',
  `parameter_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parameter_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Satuan, misal g/dl, /mm 3, %, f l, pg',
  `data_type` enum('numeric','text','options') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'numeric',
  `options_list` text COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Pilihan kualitatif dipisah koma jika data_type=options',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `parameter_code` (`parameter_code`),
  KEY `fk_param_category` (`category_id`),
  KEY `fk_param_method` (`method_id`),
  KEY `fk_param_parent` (`parent_id`),
  CONSTRAINT `fk_param_category` FOREIGN KEY (`category_id`) REFERENCES `lab_categories` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_param_method` FOREIGN KEY (`method_id`) REFERENCES `lab_methods` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_param_parent` FOREIGN KEY (`parent_id`) REFERENCES `lab_parameters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lab_parameters`
--

LOCK TABLES `lab_parameters` WRITE;
/*!40000 ALTER TABLE `lab_parameters` DISABLE KEYS */;
INSERT INTO `lab_parameters` VALUES (1,1,1,NULL,'HB','Haemoglobin','g/dl','numeric',NULL,1,1,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(2,1,1,NULL,'LEU','Leukosit','/mm 3','numeric',NULL,2,1,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(3,1,2,NULL,'LED','L.E.D (Laju Endap Darah)','mm / jam','numeric',NULL,3,1,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(4,1,3,NULL,'DIFF_COUNT','Diff. Count (Hitung Jenis)','%','text',NULL,4,1,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(5,1,3,4,'DIFF_BASO','Basofil','%','numeric',NULL,5,1,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(6,1,3,4,'DIFF_EOS','Eosinofil','%','numeric',NULL,6,1,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(7,1,3,4,'DIFF_STAF','Staf (Batang)','%','numeric',NULL,7,1,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(8,1,3,4,'DIFF_SEG','Segmen','%','numeric',NULL,8,1,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(9,1,3,4,'DIFF_LIMF','Limfosit','%','numeric',NULL,9,1,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(10,1,3,4,'DIFF_MONO','Monosit','%','numeric',NULL,10,1,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(11,1,1,NULL,'RBC','Eritrosit','/mm 3','numeric',NULL,11,1,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(12,1,1,NULL,'PLT','Trombosit','/mm 3','numeric',NULL,12,1,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(13,1,1,NULL,'HCT','Hematokrit','%','numeric',NULL,13,1,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(14,1,4,NULL,'CT','C.T (Clotting Time)','Menit','numeric',NULL,14,1,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(15,1,5,NULL,'BT','B.T (Bleeding Time)','Menit','numeric',NULL,15,1,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(16,1,6,NULL,'RL_TEST','R.L Test (Rumple Leede)','-','options',NULL,16,1,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(17,1,3,NULL,'RETIKULOSIT','Retikulosit','%','numeric',NULL,17,1,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(18,1,3,NULL,'RETRAKSI_BEKUAN','Retraksi Bekuan','%','numeric',NULL,18,1,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(19,1,1,NULL,'MCV','M.C.V','f l','numeric',NULL,19,1,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(20,1,1,NULL,'MCH','M.C.H','pg','numeric',NULL,20,1,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(21,1,1,NULL,'MCHC','M.C.H.C','%','numeric',NULL,21,1,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(22,1,7,NULL,'GOL_DARAH','Golongan Darah & Rhesus','-','options',NULL,22,1,'2026-10-16 22:52:30','2026-10-16 22:52:30');
/*!40000 ALTER TABLE `lab_parameters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lab_reference_ranges`
--

DROP TABLE IF EXISTS `lab_reference_ranges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lab_reference_ranges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parameter_id` int(11) NOT NULL,
  `gender` enum('ALL','L','P') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ALL' COMMENT 'ALL=Semua, L=Laki-laki, P=Perempuan',
  `age_classification` enum('semua_umur','neonatus','bayi','anak','remaja','dewasa','lansia','hamil') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'semua_umur',
  `age_min_days` int(11) NOT NULL DEFAULT 0 COMMENT 'Umur minimum dalam hari',
  `age_max_days` int(11) NOT NULL DEFAULT 36500 COMMENT 'Umur maksimum dalam hari (100 tahun)',
  `age_label` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Dewasa',
  `min_val` decimal(12,3) DEFAULT NULL COMMENT 'Batas bawah normal untuk tipe numerik',
  `max_val` decimal(12,3) DEFAULT NULL COMMENT 'Batas atas normal untuk tipe numerik',
  `text_normal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nilai teks normal jika non-numerik, misal Negatif',
  `critical_low` decimal(12,3) DEFAULT NULL COMMENT 'Batas kritis panik rendah',
  `critical_high` decimal(12,3) DEFAULT NULL COMMENT 'Batas kritis panik tinggi',
  `reference_display` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Teks tampilan pada cetak hasil, misal 13,5 - 16,5',
  `notes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_ref_parameter` (`parameter_id`),
  CONSTRAINT `fk_ref_parameter` FOREIGN KEY (`parameter_id`) REFERENCES `lab_parameters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lab_reference_ranges`
--

LOCK TABLES `lab_reference_ranges` WRITE;
/*!40000 ALTER TABLE `lab_reference_ranges` DISABLE KEYS */;
INSERT INTO `lab_reference_ranges` VALUES (1,1,'L','dewasa',6570,36500,'Dewasa (Pria)',13.500,16.500,NULL,7.000,20.000,'♂ 13,5 - 16,5',NULL,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(2,1,'P','dewasa',6570,36500,'Dewasa (Wanita)',12.000,15.000,NULL,7.000,20.000,'♀ 12,0 - 15,0',NULL,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(3,1,'ALL','anak',365,6569,'Anak-anak (1-17 th)',11.000,14.500,NULL,7.000,18.000,'11,0 - 14,5',NULL,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(4,1,'ALL','bayi',30,364,'Bayi (1-12 bln)',10.500,13.500,NULL,6.500,17.500,'10,5 - 13,5',NULL,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(5,2,'L','dewasa',6570,36500,'Dewasa (Pria)',4700.000,9600.000,NULL,2000.000,30000.000,'Lk 4,7 - 9,6 rb',NULL,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(6,2,'P','dewasa',6570,36500,'Dewasa (Wanita)',4900.000,12300.000,NULL,2000.000,30000.000,'Pr 4,9 - 12,3 rb',NULL,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(7,2,'ALL','anak',365,6569,'Anak-anak (1-12 th)',5000.000,14500.000,NULL,2000.000,30000.000,'5,0 - 14,5 rb',NULL,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(8,3,'L','semua_umur',0,36500,'Pria (Semua Umur)',0.000,10.000,'< 10',NULL,100.000,'♂ < 10',NULL,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(9,3,'P','semua_umur',0,36500,'Wanita (Semua Umur)',0.000,15.000,'< 15',NULL,100.000,'♀ < 15',NULL,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(10,5,'ALL','semua_umur',0,36500,'Semua Umur',0.000,1.000,NULL,NULL,NULL,'0 - 1',NULL,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(11,6,'ALL','semua_umur',0,36500,'Semua Umur',1.000,3.000,NULL,NULL,NULL,'1 - 3',NULL,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(12,7,'ALL','semua_umur',0,36500,'Semua Umur',2.000,6.000,NULL,NULL,NULL,'2 - 6',NULL,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(13,8,'ALL','semua_umur',0,36500,'Semua Umur',50.000,70.000,NULL,NULL,NULL,'50 - 70',NULL,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(14,9,'ALL','semua_umur',0,36500,'Semua Umur',20.000,40.000,NULL,NULL,NULL,'20 - 40',NULL,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(15,10,'ALL','semua_umur',0,36500,'Semua Umur',2.000,8.000,NULL,NULL,NULL,'2 - 8',NULL,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(16,11,'L','dewasa',6570,36500,'Dewasa (Pria)',4370000.000,5630000.000,NULL,NULL,NULL,'♂ 4,37 - 5,63 jt',NULL,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(17,11,'P','dewasa',6570,36500,'Dewasa (Wanita)',3900000.000,5100000.000,NULL,NULL,NULL,'♀ 3,9 - 5,1 jt',NULL,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(18,12,'L','dewasa',6570,36500,'Dewasa (Pria)',145000.000,355000.000,NULL,50000.000,1000000.000,'Lk 145 - 355 rb',NULL,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(19,12,'P','dewasa',6570,36500,'Dewasa (Wanita)',150000.000,330000.000,NULL,50000.000,1000000.000,'Pr 150 - 330 rb',NULL,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(20,13,'L','dewasa',6570,36500,'Dewasa (Pria)',41.000,50.000,NULL,20.000,60.000,'Lk 41 - 50',NULL,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(21,13,'P','dewasa',6570,36500,'Dewasa (Wanita)',37.000,45.000,NULL,20.000,60.000,'Pr 37 - 45',NULL,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(22,14,'ALL','semua_umur',0,36500,'Semua Umur',9.000,15.000,NULL,NULL,NULL,'9 - 15',NULL,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(23,15,'ALL','semua_umur',0,36500,'Semua Umur',1.000,3.000,NULL,NULL,NULL,'1 - 3',NULL,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(24,16,'ALL','semua_umur',0,36500,'Semua Umur',NULL,NULL,'Negatif',NULL,NULL,'Negatif',NULL,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(25,17,'ALL','semua_umur',0,36500,'Semua Umur',0.500,1.500,NULL,NULL,NULL,'0,5 - 1,5',NULL,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(26,18,'ALL','semua_umur',0,36500,'Semua Umur',40.000,60.000,NULL,NULL,NULL,'40 - 60',NULL,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(27,19,'ALL','semua_umur',0,36500,'Semua Umur',82.000,92.000,NULL,NULL,NULL,'82 - 92',NULL,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(28,20,'ALL','semua_umur',0,36500,'Semua Umur',27.000,31.000,NULL,NULL,NULL,'27 - 31',NULL,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(29,21,'ALL','semua_umur',0,36500,'Semua Umur',32.000,37.000,NULL,NULL,NULL,'32 - 37',NULL,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(30,22,'ALL','semua_umur',0,36500,'Semua Umur',NULL,NULL,'A / B / AB / O (Rh + / -)',NULL,NULL,'Sesuai Aglutinasi',NULL,'2026-10-16 22:52:30','2026-10-16 22:52:30');
/*!40000 ALTER TABLE `lab_reference_ranges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lab_tariffs`
--

DROP TABLE IF EXISTS `lab_tariffs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lab_tariffs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_type` enum('parameter','template') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'parameter',
  `item_id` int(11) NOT NULL COMMENT 'ID dari lab_parameters atau lab_templates',
  `tariff_group` enum('umum','perusahaan','bpjs','asuransi') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'umum',
  `jasa_manajemen` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Jasa Manajemen / Operasional Lab',
  `jasa_dokter` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Jasa Dokter Penanggung Jawab / Otorisasi',
  `jasa_petugas` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Jasa Petugas / Analis Laboratorium',
  `kso` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'KSO (Kerja Sama Operasional sewa alat)',
  `bhp` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'BHP (Bahan Habis Pakai / Reagen / Tabung)',
  `total_tarif` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Total = manajemen + dokter + petugas + kso + bhp',
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_tariff_item` (`item_type`,`item_id`,`tariff_group`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lab_tariffs`
--

LOCK TABLES `lab_tariffs` WRITE;
/*!40000 ALTER TABLE `lab_tariffs` DISABLE KEYS */;
INSERT INTO `lab_tariffs` VALUES (1,'template',1,'umum',15000.00,20000.00,15000.00,15000.00,15000.00,80000.00,'Paket Hematologi Darah Lengkap 5 Diff Tarif Pasien Umum','2026-10-16 22:52:30','2026-10-16 22:52:30'),(2,'template',1,'perusahaan',12000.00,16000.00,12000.00,12000.00,13000.00,65000.00,'Paket Hematologi Darah Lengkap Rekanan/Perusahaan (Diskon Khusus)','2026-10-16 22:52:30','2026-10-16 22:52:30'),(3,'template',2,'umum',10000.00,15000.00,10000.00,5000.00,10000.00,50000.00,'Paket Hemostasis Sederhana','2026-10-16 22:52:30','2026-10-16 22:52:30'),(4,'parameter',1,'umum',3000.00,5000.00,4000.00,4000.00,4000.00,20000.00,'Haemoglobin Satuan','2026-10-16 22:52:30','2026-10-16 22:52:30'),(5,'parameter',2,'umum',3000.00,5000.00,4000.00,4000.00,4000.00,20000.00,'Leukosit Satuan','2026-10-16 22:52:30','2026-10-16 22:52:30'),(6,'parameter',3,'umum',2000.00,4000.00,4000.00,2000.00,3000.00,15000.00,'Laju Endap Darah (LED) Westergreen','2026-10-16 22:52:30','2026-10-16 22:52:30'),(7,'parameter',11,'umum',3000.00,5000.00,4000.00,4000.00,4000.00,20000.00,'Eritrosit Satuan','2026-10-16 22:52:30','2026-10-16 22:52:30'),(8,'parameter',12,'umum',3000.00,5000.00,4000.00,4000.00,4000.00,20000.00,'Trombosit Satuan','2026-10-16 22:52:30','2026-10-16 22:52:30'),(9,'parameter',13,'umum',2000.00,4000.00,4000.00,2000.00,3000.00,15000.00,'Hematokrit Satuan','2026-10-16 22:52:30','2026-10-16 22:52:30'),(10,'parameter',14,'umum',2000.00,4000.00,4000.00,1000.00,4000.00,15000.00,'Masa Pembekuan (CT)','2026-10-16 22:52:30','2026-10-16 22:52:30'),(11,'parameter',15,'umum',2000.00,4000.00,4000.00,1000.00,4000.00,15000.00,'Masa Perdarahan (BT)','2026-10-16 22:52:30','2026-10-16 22:52:30'),(12,'parameter',16,'umum',2000.00,5000.00,5000.00,1000.00,2000.00,15000.00,'Rumple Leede Test','2026-10-16 22:52:30','2026-10-16 22:52:30'),(13,'parameter',22,'umum',3000.00,5000.00,5000.00,2000.00,5000.00,20000.00,'Golongan Darah ABO & Rhesus','2026-10-16 22:52:30','2026-10-16 22:52:30');
/*!40000 ALTER TABLE `lab_tariffs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lab_template_items`
--

DROP TABLE IF EXISTS `lab_template_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lab_template_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_id` int(11) NOT NULL,
  `parameter_id` int(11) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_tpl_param` (`template_id`,`parameter_id`),
  KEY `fk_ti_parameter` (`parameter_id`),
  CONSTRAINT `fk_ti_parameter` FOREIGN KEY (`parameter_id`) REFERENCES `lab_parameters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ti_template` FOREIGN KEY (`template_id`) REFERENCES `lab_templates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lab_template_items`
--

LOCK TABLES `lab_template_items` WRITE;
/*!40000 ALTER TABLE `lab_template_items` DISABLE KEYS */;
INSERT INTO `lab_template_items` VALUES (1,1,1,1),(2,1,2,2),(3,1,3,3),(4,1,5,4),(5,1,6,5),(6,1,7,6),(7,1,8,7),(8,1,9,8),(9,1,10,9),(10,1,11,10),(11,1,12,11),(12,1,13,12),(13,1,19,13),(14,1,20,14),(15,1,21,15),(16,2,14,1),(17,2,15,2),(18,2,16,3),(19,2,18,4);
/*!40000 ALTER TABLE `lab_template_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lab_templates`
--

DROP TABLE IF EXISTS `lab_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lab_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `template_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `template_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `template_code` (`template_code`),
  KEY `fk_tpl_category` (`category_id`),
  CONSTRAINT `fk_tpl_category` FOREIGN KEY (`category_id`) REFERENCES `lab_categories` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lab_templates`
--

LOCK TABLES `lab_templates` WRITE;
/*!40000 ALTER TABLE `lab_templates` DISABLE KEYS */;
INSERT INTO `lab_templates` VALUES (1,1,'PKT-DL-01','Darah Lengkap 5 Diff (Hematologi Rutin)','Pemeriksaan hematologi lengkap otomatis meliputi Hb, Leukosit, LED, Diff Count, Eritrosit, Trombosit, Hematokrit, dan Indeks Eritrosit (MCV, MCH, MCHC)',1,1,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(2,1,'PKT-HEMOSTASIS-01','Skrining Hemostasis Sederhana','Pemeriksaan fungsi hemostasis pembekuan darah: CT, BT, Rumple Leede, Retraksi Bekuan',1,2,'2026-10-16 22:52:30','2026-10-16 22:52:30'),(3,2,'PKT-URIN-01','Urin Rutin Lengkap','Pemeriksaan makroskopis, kimia 10 parameter strip, dan sedimen mikroskopis urine',1,3,'2026-10-16 22:52:30','2026-10-16 22:52:30');
/*!40000 ALTER TABLE `lab_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `master_dokter`
--

DROP TABLE IF EXISTS `master_dokter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `master_dokter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_dokter` varchar(150) NOT NULL,
  `spesialisasi` varchar(100) DEFAULT 'Dokter Umum',
  `faskes_asal` varchar(150) DEFAULT NULL,
  `sip_nip` varchar(50) DEFAULT NULL,
  `telepon` varchar(30) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `master_dokter`
--

LOCK TABLES `master_dokter` WRITE;
/*!40000 ALTER TABLE `master_dokter` DISABLE KEYS */;
INSERT INTO `master_dokter` VALUES (1,'dr. Anita, Sp.PD','Spesialis Penyakit Dalam','RSUD Arifin Achmad','SIP/440/2025/089','081234567801',NULL,1,'2026-10-18 21:44:27','2026-10-18 21:44:27'),(2,'dr. Syarifah, Sp.A','Spesialis Anak','Klinik Bunda Medika','SIP/440/2025/112','081234567802',NULL,1,'2026-10-18 21:44:27','2026-10-18 21:44:27'),(3,'dr. Hendra Pratama, Sp.OG','Spesialis Obstetri & Ginekologi','RS Awal Bros Panam','SIP/440/2025/205','081234567803',NULL,1,'2026-10-18 21:44:27','2026-10-18 21:44:27'),(4,'dr. Rini Suryani','Dokter Umum','Puskesmas Sail Pekanbaru','SIP/440/2026/014','081234567804',NULL,1,'2026-10-18 21:44:27','2026-10-18 21:44:27');
/*!40000 ALTER TABLE `master_dokter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menus`
--

DROP TABLE IF EXISTS `menus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `menu_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `menu_url` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `menu_icon` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'circle',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `fk_menus_parent` (`parent_id`),
  CONSTRAINT `fk_menus_parent` FOREIGN KEY (`parent_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menus`
--

LOCK TABLES `menus` WRITE;
/*!40000 ALTER TABLE `menus` DISABLE KEYS */;
INSERT INTO `menus` VALUES (1,NULL,'Dashboard','dashboard','layout-dashboard',1,1),(2,NULL,'Pendaftaran & Order LIS','pendaftaran','clipboard-signature',2,1),(3,NULL,'Sampling & Antrian Lab','pendaftaran/antrian_sampling','test-tubes',3,1),(4,NULL,'Input Hasil Lab','hasil_lab','flask-conical',4,1),(5,NULL,'Validasi & Otorisasi','validasi','check-circle-2',5,1),(6,NULL,'Laporan & Rekap','laporan','file-spreadsheet',6,1),(7,NULL,'Master Data','#master','database',8,1),(8,25,'Tarif & Parameter Lab','tarif','list-plus',1,1),(9,7,'Dokter Pengirim','master/dokter','stethoscope',2,1),(10,7,'Nilai Rujukan Kritis','master/nilai_rujukan','activity',3,1),(11,NULL,'Pengaturan Sistem','#settings','settings',11,1),(12,11,'Manajemen Pengguna','users','users',3,1),(13,11,'Hak Akses (RBAC)','roles','shield-check',4,1),(14,11,'Pengaturan Sistem','pengaturan','sliders-horizontal',1,1),(15,7,'Master Klien (Pasien & PT)','client','contact-2',1,1),(16,25,'Template / Paket Lab','tarif/template','layers',2,1),(17,25,'Metode Pemeriksaan','tarif/metode','microscope',3,1),(18,11,'Manajemen Menu','menu','menu',5,1),(19,NULL,'Kepegawaian','#kepegawaian','briefcase',10,1),(20,19,'Data Pegawai','pegawai','users',1,1),(21,19,'Setting Approval','kepegawaian/approval_setting','user-cog',2,1),(22,19,'Pengajuan Cuti','kepegawaian/pengajuan_cuti','calendar-days',3,1),(23,19,'Approval Cuti','kepegawaian/approval_cuti','check-check',4,1),(24,19,'Laporan Cuti','kepegawaian/laporan_cuti','file-bar-chart',5,1),(25,NULL,'Master Tarif','#master_tarif','badge-percent',7,1),(26,NULL,'Akun Rekening','#rekening','wallet',9,1),(27,26,'Bagan Akun (COA)','rekening/coa','file-spreadsheet',1,1),(28,26,'Rekening Tahun','rekening/tahun','calendar-clock',2,1),(29,26,'Akun Bayar','rekening/akun_bayar','banknote',3,1),(30,26,'Akun Piutang','rekening/akun_piutang','hand-coins',4,1),(31,11,'Setting Approval','pengaturan/approval','stamp',2,1);
/*!40000 ALTER TABLE `menus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pegawai`
--

DROP TABLE IF EXISTS `pegawai`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pegawai` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nip` varchar(18) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nik` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gelar_depan` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_lengkap` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gelar_belakang` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tempat_lahir` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `jenis_kelamin` enum('L','P') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'L',
  `agama` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Islam',
  `status_pernikahan` enum('Belum Kawin','Kawin','Janda','Duda') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Belum Kawin',
  `golongan_darah` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_pegawai` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PNS',
  `kedudukan_pegawai` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Aktif',
  `nomor_sk_cpns` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tmt_cpns` date DEFAULT NULL,
  `nomor_sk_pns` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tmt_pns` date DEFAULT NULL,
  `mkg_tahun` int(11) DEFAULT 0,
  `mkg_bulan` int(11) DEFAULT 0,
  `kode_golongan` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_pangkat` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tmt_golongan` date DEFAULT NULL,
  `nomor_sk_kp` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tmt_kgb_terakhir` date DEFAULT NULL,
  `tmt_kgb_berikutnya` date DEFAULT NULL,
  `jenis_jabatan` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Fungsional Tertentu (JFT)',
  `nama_jabatan` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenjang_jabatan` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tmt_jabatan` date DEFAULT NULL,
  `kelas_jabatan` int(11) DEFAULT 7,
  `unit_kerja_induk` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Dinas Kesehatan Provinsi Riau',
  `satuan_kerja` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'UPT Laboratorium Kesehatan Provinsi Riau',
  `seksi_ruangan` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Instalasi Laboratorium Klinik',
  `nomor_str` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tgl_terbit_str` date DEFAULT NULL,
  `masa_berlaku_str` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Seumur Hidup',
  `nomor_sip` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `masa_berlaku_sip` date DEFAULT NULL,
  `sertifikasi_khusus` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tingkat_pendidikan` enum('SMA/SMK','D-I','D-III','D-IV','S-1','S-2','S-3','Profesi','Spesialis') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'D-III',
  `jurusan` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_institusi` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tahun_lulus` int(11) DEFAULT NULL,
  `nomor_ijazah` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `npwp` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_bpjs_kesehatan` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_taspen` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_karpeg` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_karis_karsu` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_payroll` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Bank Riau Kepri Syariah',
  `nomor_rekening` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telepon` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanda_tangan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_nip` (`nip`),
  KEY `idx_status` (`status_pegawai`),
  KEY `idx_seksi` (`seksi_ruangan`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pegawai`
--

LOCK TABLES `pegawai` WRITE;
/*!40000 ALTER TABLE `pegawai` DISABLE KEYS */;
INSERT INTO `pegawai` VALUES (1,'197805122005011004','1471011205780002','dr.','Zulkifli Mansur','Sp.PK, M.Kes','Pekanbaru','1978-05-12','L','Islam','Kawin','B+','PNS','Aktif','813/BKD-PP/2005/142','2005-01-01','821/BKD-PP/2006/205','2006-03-01',19,2,'IV/b','Pembina Tingkat I','2022-04-01','823.4/BKD/KP/2022/412','2024-01-01','2026-01-01','Struktural','Kepala UPT Laboratorium Kesehatan','Eselon III/a','2021-08-15',12,'Dinas Kesehatan Provinsi Riau','UPT Laboratorium Kesehatan Provinsi Riau','Pimpinan & Tata Usaha','14.1.1.100.1.19.012345','2019-06-10','Seumur Hidup','503/DPMPTSP/SIP-D/2022/089','2027-05-12','Validasi Hasil Laboratorium Terakreditasi, Good Laboratory Practice (GLP), Biosafety Officer','Spesialis','Spesialis Patologi Klinik (Sp.PK)','Universitas Andalas',2011,'UNAND/FK/SP-PK/2011/045','08.123.456.7-216.000','0001234567890','197805122005011004','K.0987654','KU.123456','Bank Riau Kepri Syariah','101-08-12345','dr.zulkifli@riau.go.id','081275123456','Jl. Diponegoro No. 45, Pekanbaru, Riau',NULL,'uploads/signatures/appr_8_step4_1792343489.png','2026-10-17 12:52:14','2026-10-19 00:11:29'),(2,'198504182008012003','1471025804850001','','Nurhasanah','S.Tr.Kes, M.Biomed','Bangkinang','1985-04-18','P','Islam','Kawin','O+','PNS','Aktif','813/BKD/2008/042','2008-01-01','821/BKD/2009/115','2009-02-01',16,1,'III/d','Penata Tingkat I','2023-04-01','823.3/BKD/KP/2023/189','2024-01-01','2026-01-01','Fungsional Tertentu (JFT)','Pranata Laboratorium Kesehatan Ahli Muda','Muda','2020-03-01',9,'Dinas Kesehatan Provinsi Riau','UPT Laboratorium Kesehatan Provinsi Riau','Instalasi Hematologi & Urinalisa','14.2.1.200.2.20.098765','2020-04-15','Seumur Hidup','503/DPMPTSP/SIP-ATLM/2023/044','2028-04-18','Teknik Flebotomi Klinis, Pengendalian Mutu Internal Hematologi Sysmex, ISO 15189','S-2','Magister Ilmu Biomedis','Universitas Riau',2018,'UNRI/FMIPA/M-BIOMED/2018/112','09.456.789.1-216.000','0002345678901','198504182008012003','K.1122334','KU.654321','Bank Riau Kepri Syariah','101-08-54321','nurhasanah.atlm@riau.go.id','081365987654','Jl. Paus No. 12, Marpoyan Damai, Pekanbaru',NULL,'uploads/signatures/ttd_2_1792345331.png','2026-10-17 12:52:14','2026-10-19 00:42:11'),(3,'199208242015021002','1471032408920005','','Rahmat Hidayat','A.Md.AK','Dumai','1992-08-24','L','Islam','Kawin','A+','PNS','Aktif','813/BKD/2015/091','2015-02-01','821/BKD/2016/184','2016-04-01',9,0,'III/a','Penata Muda','2023-10-01','823.3/BKD/KP/2023/502','2023-02-01','2025-02-01','Fungsional Tertentu (JFT)','Pranata Laboratorium Kesehatan Penyelia','Penyelia','2021-01-10',8,'Dinas Kesehatan Provinsi Riau','UPT Laboratorium Kesehatan Provinsi Riau','Instalasi Kimia Klinik','14.2.1.100.1.21.054321','2021-08-20','Seumur Hidup','503/DPMPTSP/SIP-ATLM/2022/108','2027-08-24','Pemeriksaan Kimia Darah Fotometrik & ISE, Maintenance Mindray BS-480','D-III','D-III Analis Kesehatan','Poltekkes Kemenkes Riau',2013,'POLTEKKES-RIAU/AK/2013/088','10.789.012.3-216.000','0003456789012','199208242015021002','K.3344556','KI.778899','Bank Riau Kepri Syariah','101-08-77889','rahmat.hidayat@riau.go.id','085278123456','Jl. HR. Soebrantas Km. 12, Panam, Pekanbaru',NULL,'uploads/signatures/ttd_3_1792345345.png','2026-10-17 12:52:14','2026-10-19 00:42:25'),(4,'199411032024212001','1471044311940003','','Siti Rahmawati','S.Si','Siak Sri Indrapura','1994-11-03','P','Islam','Belum Kawin','AB+','PPPK','Aktif','810/PPPK-DINKES/2024/015','2024-03-01','810/PPPK-DINKES/2024/015','2024-03-01',0,6,'X','Golongan X (PPPK S-1)','2024-03-01','810/PPPK-DINKES/2024/015','2024-03-01','2026-03-01','Fungsional Tertentu (JFT)','Pranata Laboratorium Kesehatan Ahli Pertama','Pertama','2024-03-01',8,'Dinas Kesehatan Provinsi Riau','UPT Laboratorium Kesehatan Provinsi Riau','Instalasi Mikrobiologi & PCR','14.2.1.200.2.22.077889','2022-09-01','Seumur Hidup','503/DPMPTSP/SIP-PPPK/2024/012','2029-11-03','Biomolekuler & Real-Time RT-PCR, Biosafety Level 2 (BSL-2) Enhancement','S-1','Biologi (MIPA)','Universitas Riau',2017,'UNRI/FMIPA/BIO/2017/092','12.345.678.9-216.000','0004567890123','199411032024212001','PPPK.2024.0042','-','Bank Riau Kepri Syariah','101-08-99001','siti.rahmawati@riau.go.id','082170123456','Jl. Kartama Gg. Melati No. 8, Pekanbaru',NULL,NULL,'2026-10-17 12:52:14','2026-10-17 12:52:14'),(5,'198809152010011005','1471011509880004','','Agus Hendrawan','S.KM, M.Kes','Rengat','1988-09-15','L','Islam','Kawin','O+','PNS','Aktif','813/BKD/2010/067','2010-01-01','821/BKD/2011/143','2011-03-01',14,2,'III/c','Penata','2022-10-01','823.3/BKD/KP/2022/339','2024-01-01','2026-01-01','Fungsional Tertentu (JFT)','Tenaga Sanitasi Lingkungan Ahli Muda','Muda','2020-07-01',9,'Dinas Kesehatan Provinsi Riau','UPT Laboratorium Kesehatan Provinsi Riau','Instalasi Kimia Air & Makanan Minuman','14.3.1.100.1.20.033445','2020-11-10','Seumur Hidup','503/DPMPTSP/SIP-SAN/2021/078','2026-09-15','Analisis Kualitas Air Bersih & Limbah B3, Uji Spektrofotometri AAS & UV-Vis, ISO 17025','S-2','Magister Kesehatan Lingkungan','Universitas Indonesia',2016,'UI/FKM/M-KESLING/2016/077','07.654.321.0-216.000','0005678901234','198809152010011005','K.4455667','KU.889900','Bank Riau Kepri Syariah','101-08-33445','agus.hendrawan@riau.go.id','081276543210','Jl. Teratai No. 19, Sukajadi, Pekanbaru',NULL,'uploads/signatures/appr_8_step5_1792343493.png','2026-10-17 12:52:14','2026-10-19 00:11:33'),(7,'123232323232','123232323232','dr','Ahmad Tohar','S.Kom','Pekanbaru','1993-09-20','L','Islam','Belum Kawin','A+','PNS','Aktif',NULL,NULL,'021/902/121212','2026-10-17',5,5,NULL,NULL,'2026-10-17','23232','2026-10-17',NULL,'Fungsional Tertentu (JFT)','sdsds','sdsds','2026-10-30',8,'Dinas Kesehatan Provinsi Riau','UPT Laboratorium Kesehatan Provinsi Riau','Pimpinan & Tata Usaha','2323','2026-10-17','Seumur Hidup','S03/KLKLKL/LLKLKLKL','2026-10-17','SDSDS','D-III','SDSD','SDSD',2019,NULL,'121212','0909090','909090','009090',NULL,'Bank Riau Kepri Syariah','SDSD','graciaz@gmail.com','232','DSDSDS',NULL,NULL,'2026-10-17 13:08:01','2026-10-17 13:08:01'),(10,'199008172020121004','1471011708900001',NULL,'dr. Maya Sartika','Sp.PK',NULL,NULL,'L','Islam','Belum Kawin',NULL,'PNS','Aktif',NULL,NULL,NULL,NULL,0,0,'III/c','Penata',NULL,NULL,NULL,NULL,'Fungsional Tertentu (JFT)','Dokter Spesialis Patologi Klinik',NULL,NULL,7,'Dinas Kesehatan Provinsi Riau','UPT Laboratorium Kesehatan Provinsi Riau','Instalasi Kimia Klinik','14.1.2.100.1.22.099887',NULL,'Seumur Hidup',NULL,NULL,NULL,'D-III',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Bank Riau Kepri Syariah',NULL,NULL,'081299887766',NULL,NULL,'uploads/signatures/appr_8_step3_1792343486.png','2026-10-17 13:59:13','2026-10-19 00:11:26');
/*!40000 ALTER TABLE `pegawai` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pegawai_referensi`
--

DROP TABLE IF EXISTS `pegawai_referensi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pegawai_referensi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kategori` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kode` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `keterangan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_kategori` (`kategori`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pegawai_referensi`
--

LOCK TABLES `pegawai_referensi` WRITE;
/*!40000 ALTER TABLE `pegawai_referensi` DISABLE KEYS */;
INSERT INTO `pegawai_referensi` VALUES (1,'golongan_ruang','I/a','Juru Muda','Golongan I/a - Juru Muda (Golongan I)',1,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(2,'golongan_ruang','I/b','Juru Muda Tingkat I','Golongan I/b - Juru Muda Tingkat I',2,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(3,'golongan_ruang','I/c','Juru','Golongan I/c - Juru',3,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(4,'golongan_ruang','I/d','Juru Tingkat I','Golongan I/d - Juru Tingkat I',4,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(5,'golongan_ruang','II/a','Pengatur Muda','Golongan II/a - Pengatur Muda (SMA/SMK)',5,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(6,'golongan_ruang','II/b','Pengatur Muda Tingkat I','Golongan II/b - Pengatur Muda Tingkat I',6,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(7,'golongan_ruang','II/c','Pengatur','Golongan II/c - Pengatur (D-III)',7,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(8,'golongan_ruang','II/d','Pengatur Tingkat I','Golongan II/d - Pengatur Tingkat I',8,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(9,'golongan_ruang','III/a','Penata Muda','Golongan III/a - Penata Muda (S-1 / D-IV)',9,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(10,'golongan_ruang','III/b','Penata Muda Tingkat I','Golongan III/b - Penata Muda Tingkat I (S-2)',10,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(11,'golongan_ruang','III/c','Penata','Golongan III/c - Penata (S-3)',11,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(12,'golongan_ruang','III/d','Penata Tingkat I','Golongan III/d - Penata Tingkat I',12,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(13,'golongan_ruang','IV/a','Pembina','Golongan IV/a - Pembina',13,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(14,'golongan_ruang','IV/b','Pembina Tingkat I','Golongan IV/b - Pembina Tingkat I',14,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(15,'golongan_ruang','IV/c','Pembina Utama Muda','Golongan IV/c - Pembina Utama Muda',15,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(16,'golongan_ruang','IV/d','Pembina Utama Madya','Golongan IV/d - Pembina Utama Madya',16,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(17,'golongan_ruang','IV/e','Pembina Utama','Golongan IV/e - Pembina Utama',17,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(18,'golongan_ruang','VII','Golongan VII (PPPK D-III)','PPPK Jenjang Kualifikasi D-III',18,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(19,'golongan_ruang','IX','Golongan IX (PPPK D-IV / S-1)','PPPK Jenjang Kualifikasi S-1 / D-IV',19,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(20,'golongan_ruang','X','Golongan X (PPPK S-1 Profesi)','PPPK Jenjang Profesi / Dokter / Apoteker',20,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(21,'kedudukan_hukum','Aktif','Aktif Bekerja','Pegawai aktif menjalankan tugas kedinasan',1,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(22,'kedudukan_hukum','Tugas Belajar','Tugas Belajar','Melaksanakan tugas belajar dengan beasiswa/resmi',2,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(23,'kedudukan_hukum','Izin Belajar','Izin Belajar','Melanjutkan studi atas biaya mandiri diluar jam kerja',3,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(24,'kedudukan_hukum','Cuti Di Luar Tanggungan','Cuti Di Luar Tanggungan Negara (CLTN)','Cuti di luar tanggungan negara sementara',4,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(25,'kedudukan_hukum','Diberhentikan Sementara','Diberhentikan Sementara','Status pemberhentian sementara dari jabatan',5,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(26,'kedudukan_hukum','Pensiun','Pensiun','Mencapai batas usia pensiun (BUP)',6,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(27,'jenis_jabatan','Fungsional Tertentu (JFT)','Fungsional Tertentu (JFT Tenaga Kesehatan)','Jabatan Fungsional Nakes (ATLM, Dokter, Sanitarian, dll)',1,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(28,'jenis_jabatan','Struktural','Struktural / Manajerial Lab','Kepala UPT, Kasubbag TU, Koordinator Pelayanan',2,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(29,'jenis_jabatan','Pelaksana (JFU)','Pelaksana / Fungsional Umum (JFU)','Staf Administrasi, Pengadministrasi Keuangan, Resepsionis',3,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(30,'jenis_jabatan','Pimpinan Tinggi','Jabatan Pimpinan Tinggi (JPT)','Pejabat Pimpinan Tinggi Pratama / Madya',4,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(31,'seksi_ruangan','Pimpinan & Tata Usaha','Subbag Tata Usaha & Kepegawaian','Pimpinan & Administrasi Tata Usaha UPT Labkesda',1,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(32,'seksi_ruangan','Instalasi Laboratorium Klinik','Instalasi Laboratorium Klinik Terpadu','Pelayanan diagnostik laboratorium klinik komprehensif',2,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(33,'seksi_ruangan','Instalasi Hematologi & Urinalisa','Instalasi Hematologi, Urinalisa & Cairan Tubuh','Pemeriksaan darah lengkap, hemostasis, dan sedimen urin',3,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(34,'seksi_ruangan','Instalasi Kimia Klinik','Instalasi Kimia Klinik & Imunologi','Pemeriksaan fungsi hati, ginjal, lipid, elektrolit, dan serologi',4,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(35,'seksi_ruangan','Instalasi Mikrobiologi & PCR','Instalasi Mikrobiologi, Kultur & Biomolekuler/PCR','Kultur kuman, uji resistensi antibiotik, dan amplifikasi PCR',5,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(36,'seksi_ruangan','Instalasi Kimia Air & Makanan Minuman','Instalasi Kimia Lingkungan, Air & Makanan Minuman (Kesmas)','Laboratorium Kesehatan Masyarakat, uji fisika/kimia air & pangan',6,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(37,'seksi_ruangan','Instalasi Flebotomi & Registrasi Sampel','Instalasi Pengambilan Sampel (Flebotomi) & Loket Penerimaan','Sampling darah vena, desentralisasi spesimen & triage pasien',7,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(38,'seksi_ruangan','Mutu, K3 & Akreditasi Lab','Instalasi Pengendalian Mutu (ISO 15189/17025) & K3','Pemantapan mutu internal/eksternal dan kalibrasi alat ukur',8,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(39,'bank_payroll','BRKS','Bank Riau Kepri Syariah','Bank Pembangunan Daerah Riau Kepri Syariah (Payroll Resmi Pemprov)',1,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(40,'bank_payroll','MANDIRI','Bank Mandiri (Persero) Tbk','Rekening Payroll & Operasional Bank Mandiri',2,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(41,'bank_payroll','BRI','Bank Rakyat Indonesia (BRI)','Bank Rakyat Indonesia Kantor Cabang Pekanbaru',3,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(42,'bank_payroll','BNI','Bank Negara Indonesia (BNI)','Bank Negara Indonesia 1946',4,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(43,'bank_payroll','BSI','Bank Syariah Indonesia (BSI)','Bank Syariah Indonesia Tbk',5,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(44,'bank_payroll','BCA','Bank Central Asia (BCA)','PT Bank Central Asia Tbk',6,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(45,'status_pegawai','PNS','PNS (Pegawai Negeri Sipil)','Aparatur Sipil Negara Berstatus Pegawai Negeri Sipil Tetap',1,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(46,'status_pegawai','CPNS','CPNS (Calon Pegawai Negeri Sipil)','Calon PNS dalam masa prajabatan (Latsar) 80%',2,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(47,'status_pegawai','PPPK','PPPK (Pemerintah Perjanjian Kerja)','ASN dengan perjanjian kerja berjangka (UU ASN No. 20/2023)',3,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(48,'status_pegawai','Non-ASN / BLUD','Non-ASN / Tenaga BLUD','Pegawai Profesional Non-PNS Badan Layanan Umum Daerah',4,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(49,'status_pegawai','Honorer / Kontrak','Tenaga Kontrak Daerah / Honorer','Tenaga Kontrak Kegiatan / Pendukung Laboratorium',5,1,'2026-10-17 08:12:29','2026-10-17 08:12:29'),(51,'seksi_ruangan','SEK-09','Instalasi Pengolahan Air Limbah','Unit Pengolahan IPAL Labkesda',9,1,'2026-10-17 08:30:48','2026-10-17 08:30:48'),(57,'seksi_ruangan','SEK-10','Laboratorium Toksikologi Medis','Unit Uji Toksikologi',10,1,'2026-10-17 08:48:12','2026-10-17 08:48:12'),(59,'bank_payroll','BNI','Bank Negara Indonesia (BNI)','Payroll Pegawai',7,1,'2026-10-17 08:59:18','2026-10-17 08:59:18');
/*!40000 ALTER TABLE `pegawai_referensi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pendaftaran`
--

DROP TABLE IF EXISTS `pendaftaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pendaftaran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nomor_registrasi` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomor_billing` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `barcode_sampel` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `tipe_pasien` enum('mandiri','rujukan_faskes','perusahaan_lingkungan') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'mandiri',
  `tanggal_pendaftaran` date NOT NULL,
  `jam_pendaftaran` time NOT NULL,
  `nama_pasien` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nik_pasien` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nomor_rm` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_kelamin` enum('L','P') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `usia_tahun` int(3) DEFAULT NULL,
  `usia_bulan` int(2) DEFAULT NULL,
  `telepon` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kota_kabupaten` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'Kota Pekanbaru',
  `instansi_pengirim` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dokter_pengirim` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nomor_surat_pengantar` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kategori_pemeriksaan` enum('klinis','lingkungan','mcu','lainnya') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'klinis',
  `jenis_sampel` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'Darah Vena / Serum',
  `diagnosa_klinis` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alur_pembayaran` enum('mandiri_langsung','piutang_pks') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'mandiri_langsung',
  `kelompok_tarif` enum('umum','perusahaan','bpjs','asuransi') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'umum',
  `subtotal_tarif` decimal(12,2) NOT NULL DEFAULT 0.00,
  `diskon_persen` decimal(5,2) NOT NULL DEFAULT 0.00,
  `diskon_nominal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_biaya` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_jasa_manajemen` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_jasa_dokter` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_jasa_petugas` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_kso` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_bhp` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status_pembayaran` enum('UNPAID','PAID','GUARANTEED','CANCELLED') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'UNPAID',
  `metode_pembayaran` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `waktu_bayar` datetime DEFAULT NULL,
  `nominal_bayar` decimal(12,2) NOT NULL DEFAULT 0.00,
  `nominal_kembali` decimal(12,2) NOT NULL DEFAULT 0.00,
  `kasir_id` int(11) DEFAULT NULL,
  `akun_bayar_id` int(11) DEFAULT NULL,
  `akun_piutang_id` int(11) DEFAULT NULL,
  `nomor_kuitansi` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nomor_pks_spk` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instansi_penjamin` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pic_penjamin` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `catatan_penjamin` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_penagihan` enum('belum_ditagih','invoiced','lunas') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'belum_ditagih',
  `nomor_invoice` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_order` enum('registered','sampling','processing','validasi','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'registered',
  `waktu_sampling` datetime DEFAULT NULL,
  `petugas_sampling` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `catatan` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dokter_pj` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `analis_nama` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kesimpulan_saran` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `waktu_hasil_selesai` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nomor_registrasi` (`nomor_registrasi`),
  UNIQUE KEY `nomor_billing` (`nomor_billing`),
  UNIQUE KEY `barcode_sampel` (`barcode_sampel`),
  KEY `idx_nomor_registrasi` (`nomor_registrasi`),
  KEY `idx_nomor_billing` (`nomor_billing`),
  KEY `idx_barcode_sampel` (`barcode_sampel`),
  KEY `idx_client_id` (`client_id`),
  KEY `idx_tanggal` (`tanggal_pendaftaran`),
  KEY `idx_status_pembayaran` (`status_pembayaran`),
  KEY `idx_status_order` (`status_order`),
  KEY `idx_alur_pembayaran` (`alur_pembayaran`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pendaftaran`
--

LOCK TABLES `pendaftaran` WRITE;
/*!40000 ALTER TABLE `pendaftaran` DISABLE KEYS */;
INSERT INTO `pendaftaran` VALUES (1,'REG-20261017-0001','BIL-20261017-0001','SMP-261017-0001',1,'mandiri','2026-10-17','08:30:00','Ahmad Fadillah','3201011504920002','PSN-2026-0001','L','1992-04-15',34,6,'081298765432','ahmad.fadillah@example.com','Jl. Sudirman No. 45, Pekanbaru','Kota Pekanbaru','Datang Sendiri (Walk-in)','dr. Anita Sp.PD',NULL,'klinis','Darah Vena / Serum','Pemeriksaan Rutin Gula Darah & Profil Lipid','mandiri_langsung','umum',125000.00,0.00,0.00,125000.00,25000.00,35000.00,35000.00,10000.00,20000.00,'PAID','QRIS Dinamis','2026-10-17 16:03:34',125000.00,0.00,1,NULL,NULL,'KWT-20261017-0001',NULL,NULL,NULL,NULL,'belum_ditagih',NULL,'sampling',NULL,NULL,'Pasien puasa 10 jam. Siap pengambilan sampel darah.',NULL,NULL,NULL,NULL,1,'2026-10-17 16:03:34','2026-10-17 16:03:34'),(2,'REG-20261017-0002','BIL-20261017-0002','SMP-261017-0002',2,'rujukan_faskes','2026-10-17','09:15:00','Nurul Hidayati','3201015608950001','PSN-2026-0002','P','1995-08-16',31,2,'081387654321','nurul.h@example.com','Jl. Merak No. 12, Pekanbaru','Kota Pekanbaru','Puskesmas Rumbai','dr. Syarifah','440/PKM-RB/X/2026/089','klinis','Darah EDTA','Suspek DBD / Demam Berdarah Dengue','mandiri_langsung','umum',85000.00,0.00,0.00,85000.00,15000.00,25000.00,25000.00,5000.00,15000.00,'GUARANTEED','SPK / PKS Mitra','2026-10-17 11:10:58',0.00,0.00,NULL,NULL,NULL,NULL,'SPK/PKM-RB/2026/089','Puskesmas Rumbai','dr. Syarifah','Klaim tagihan kapitasi/non-kapitasi BLUD','belum_ditagih',NULL,'sampling',NULL,NULL,'Surat rujukan puskesmas terlampir. Menunggu di kasir.',NULL,NULL,NULL,NULL,1,'2026-10-17 16:03:34','2026-10-17 16:10:58'),(3,'REG-20261017-0003','BIL-20261017-0003','SMP-261017-0003',NULL,'perusahaan_lingkungan','2026-10-17','10:00:00','Sampel Air Limbah Outlet PT. Riau Pulp','01.234.567.8-012.000','CORP-2026-0001',NULL,NULL,NULL,NULL,'08119871234','env@riaupulp.co.id','Komp. Pabrik Industri Pangkalan Kerinci','Kab. Pelalawan','PT. Riau Andalan Pulp & Paper','Bambang Kusuma, SE','045/SPK-ENV/RAPP/X/2026','lingkungan','Air Limbah Industri (2 Liter)','Uji Baku Mutu Lingkungan Triwulan IV Pemprov Riau','piutang_pks','perusahaan',450000.00,10.00,45000.00,405000.00,80000.00,120000.00,120000.00,40000.00,45000.00,'GUARANTEED','SPK/PKS Korporat','2026-10-17 16:03:34',0.00,0.00,1,NULL,NULL,NULL,'PKS/LABKES/2026/012','PT. Riau Andalan Pulp & Paper','Bambang Kusuma (Manager Lingkungan)',NULL,'belum_ditagih',NULL,'sampling',NULL,NULL,'Jalur SPK Resmi. Invoice ditagihkan akhir bulan sesuai PKS BLUD.',NULL,NULL,NULL,NULL,1,'2026-10-17 16:03:34','2026-10-17 16:03:34'),(4,'REG-20261017-0004','BIL-20261017-0004','SMP-261017-0004',NULL,'mandiri','2026-10-17','11:10:45','Budi Santoso',NULL,NULL,NULL,NULL,NULL,NULL,'081234567890',NULL,NULL,'Kota Pekanbaru',NULL,NULL,NULL,'klinis','Darah Vena / Serum',NULL,'mandiri_langsung','umum',15000.00,0.00,0.00,15000.00,2000.00,4000.00,4000.00,1000.00,4000.00,'PAID','Cash','2026-10-17 11:10:50',20000.00,5000.00,1,NULL,NULL,'KWT-20261017-0002',NULL,NULL,NULL,NULL,'belum_ditagih',NULL,'sampling',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2026-10-17 16:10:45','2026-10-17 16:10:50'),(5,'REG-20261017-0005','BIL-20261017-0005','SMP-261017-0005',2,'mandiri','2026-10-17','11:20:45','Nurul Hidayati','3201015608950001','PSN-2026-0002','P','1995-08-16',31,NULL,'081387654321','nurul.h@example.com','Jl. Merak No. 12, Kebayoran Baru','Kota Pekanbaru',NULL,NULL,NULL,'klinis','Darah Vena / Serum',NULL,'mandiri_langsung','umum',50000.00,0.00,0.00,50000.00,10000.00,15000.00,10000.00,5000.00,10000.00,'PAID','Tunai / Cash','2026-10-17 11:22:37',100000.00,50000.00,1,NULL,NULL,'KWT-20261017-0003',NULL,NULL,NULL,NULL,'belum_ditagih',NULL,'completed',NULL,NULL,NULL,'dr. Hj. Siti Rahmah, Sp.PK, M.Kes','Administrator LIS','suds','2026-10-18 17:42:21',1,'2026-10-17 16:20:45','2026-10-18 17:42:21'),(6,'REG-20261018-0001','BIL-20261018-0001','SMP-261018-0001',NULL,'mandiri','2026-10-18','15:09:56','Budi Santoso Mandiri',NULL,NULL,'L',NULL,35,NULL,'081298765432',NULL,NULL,'Kota Pekanbaru',NULL,NULL,NULL,'klinis','Darah EDTA (Hematologi)',NULL,'mandiri_langsung','umum',80000.00,0.00,0.00,80000.00,15000.00,20000.00,15000.00,15000.00,15000.00,'PAID','Kasir Tunai Loket (1.1.01.01)','2026-10-18 15:10:28',100000.00,20000.00,1,1,NULL,'KWT-20261018-0001',NULL,NULL,NULL,NULL,'belum_ditagih',NULL,'completed',NULL,NULL,NULL,'dr. Hj. Siti Rahmah, Sp.PK, M.Kes','Siti Rahmawati, A.Md.AK','Hasil profil hematologi dalam batas normal. Tidak ditemukan kelainan morfologi sel darah.','2026-10-18 15:10:55',1,'2026-10-18 20:09:56','2026-10-18 15:10:55'),(7,'REG-20261018-0002','BIL-20261018-0002','SMP-261018-0002',14,'perusahaan_lingkungan','2026-10-18','15:11:27','Karyawan PT PHR (Budi Setiawan)',NULL,NULL,'L',NULL,40,NULL,'081378901234',NULL,NULL,'Kota Pekanbaru',NULL,NULL,NULL,'klinis','Darah EDTA (Hematologi)',NULL,'piutang_pks','perusahaan',65000.00,10.00,6500.00,58500.00,12000.00,16000.00,12000.00,12000.00,13000.00,'GUARANTEED','Piutang: PT Pertamina Hulu Rokan (PHR) (1.1.03.02)','2026-10-18 15:11:38',0.00,0.00,NULL,NULL,2,NULL,'SPK-PHR-LABKES-2026-088','PT Pertamina Hulu Rokan','Bambang Supriyanto','Klaim MCU Karyawan Termin 45 Hari','belum_ditagih',NULL,'validasi',NULL,NULL,'Hasil telah diverifikasi dan sah.','dr. Budi Santoso, Sp.PK','Siti Rahmawati, A.Md.AK','Medical Check Up MCU Karyawan Fit to Work. Parameter hematologi baik.','2026-10-18 17:55:03',1,'2026-10-18 20:11:27','2026-10-19 00:15:18'),(8,'REG-20261018-0003','BIL-20261018-0003','SMP-261018-0003',16,'rujukan_faskes','2026-10-18','16:24:01','MoizCare Indonesia',NULL,'CORP-2026-0003','L',NULL,NULL,NULL,'085365811832','tohargraciaz9@gmail.com','Jln. Teropong','Kota Pekanbaru','MoizCare Indonesia',NULL,NULL,'klinis','Darah Vena / Serum',NULL,'mandiri_langsung','umum',130000.00,0.00,0.00,130000.00,25000.00,35000.00,25000.00,20000.00,25000.00,'PAID','Kasir Tunai Loket (1.1.01.01)','2026-10-18 16:25:47',150000.00,20000.00,1,1,NULL,'KWT-20261018-0002',NULL,NULL,NULL,NULL,'belum_ditagih',NULL,'completed','2026-10-18 17:12:27','Administrator LIS','oke sesuai','dr. Hj. Siti Rahmah, Sp.PK, M.Kes','Siti Rahmawati, A.Md.AK','Hasil pemeriksaan parameter dalam rentang normal dan stabil.','2026-10-18 19:11:33',1,'2026-10-18 21:24:01','2026-10-18 19:11:33');
/*!40000 ALTER TABLE `pendaftaran` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pendaftaran_approval`
--

DROP TABLE IF EXISTS `pendaftaran_approval`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pendaftaran_approval` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pendaftaran_id` int(11) NOT NULL,
  `rule_id` int(11) DEFAULT NULL,
  `urutan` int(11) NOT NULL,
  `pegawai_id` int(11) NOT NULL,
  `pegawai_nama` varchar(150) NOT NULL,
  `pegawai_nip` varchar(50) DEFAULT NULL,
  `pegawai_jabatan` varchar(150) DEFAULT NULL,
  `jabatan_approval` varchar(150) NOT NULL,
  `status` enum('waiting','pending','approved','rejected') NOT NULL DEFAULT 'waiting',
  `catatan` text DEFAULT NULL,
  `tanda_tangan` longtext DEFAULT NULL,
  `approved_by_user_id` int(11) DEFAULT NULL,
  `approved_by_user_name` varchar(100) DEFAULT NULL,
  `waktu_approval` datetime DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_pendaftaran` (`pendaftaran_id`),
  KEY `idx_urutan` (`urutan`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pendaftaran_approval`
--

LOCK TABLES `pendaftaran_approval` WRITE;
/*!40000 ALTER TABLE `pendaftaran_approval` DISABLE KEYS */;
INSERT INTO `pendaftaran_approval` VALUES (1,8,1,1,3,'Rahmat Hidayat, A.Md.AK','199208242015021002','Pranata Laboratorium Kesehatan Penyelia','Analis Pemeriksa Lab (ATLM)','approved','Hasil analitik sesuai kontrol mutu','uploads/signatures/appr_8_step1_1792343473.png',1,'Administrator LIS','2026-10-18 19:11:13','127.0.0.1','2026-10-18 19:10:49','2026-10-18 19:11:13'),(2,8,2,2,2,'Nurhasanah, S.Tr.Kes, M.Biomed','198504182008012003','Pranata Laboratorium Kesehatan Ahli Muda','Koordinator Teknis & Mutu Lab','approved','Verifikasi mutu analitik lengkap','uploads/signatures/appr_8_step2_1792343483.png',1,'Administrator LIS','2026-10-18 19:11:23','127.0.0.1','2026-10-18 19:10:49','2026-10-18 19:11:23'),(3,8,3,3,10,'dr. Maya Sartika, Sp.PK','199008172020121004','Dokter Spesialis Patologi Klinik','Dokter Penanggung Jawab Sp.PK','approved','Evaluasi klinis patologi sesuai rujukan','uploads/signatures/appr_8_step3_1792343486.png',1,'Administrator LIS','2026-10-18 19:11:26','127.0.0.1','2026-10-18 19:10:49','2026-10-18 19:11:26'),(4,8,4,4,1,'dr. Zulkifli Mansur, Sp.PK, M.Kes','197805122005011004','Kepala UPT Laboratorium Kesehatan','Kepala UPT Laboratorium Kesehatan','approved','Disetujui untuk penerbitan laporan','uploads/signatures/appr_8_step4_1792343489.png',1,'Administrator LIS','2026-10-18 19:11:29','127.0.0.1','2026-10-18 19:10:49','2026-10-18 19:11:29'),(5,8,5,5,5,'Agus Hendrawan, S.KM, M.Kes','198809152010011005','Tenaga Sanitasi Lingkungan Ahli Muda','Penanggung Jawab Kesehatan Lingkungan','approved','Verifikasi akhir lengkap, hasil siap diserahkan ke pelanggan','uploads/signatures/appr_8_step5_1792343493.png',1,'Administrator LIS','2026-10-18 19:11:33','127.0.0.1','2026-10-18 19:10:49','2026-10-18 19:11:33'),(11,6,1,1,3,'Rahmat Hidayat, A.Md.AK','199208242015021002','Pranata Laboratorium Kesehatan Penyelia','Analis Pemeriksa Lab (ATLM)','pending',NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-18 19:10:49','2026-10-18 19:10:49'),(12,6,2,2,2,'Nurhasanah, S.Tr.Kes, M.Biomed','198504182008012003','Pranata Laboratorium Kesehatan Ahli Muda','Koordinator Teknis & Mutu Lab','waiting',NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-18 19:10:49','2026-10-18 19:10:49'),(13,6,3,3,10,'dr. Maya Sartika, Sp.PK','199008172020121004','Dokter Spesialis Patologi Klinik','Dokter Penanggung Jawab Sp.PK','waiting',NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-18 19:10:49','2026-10-18 19:10:49'),(14,6,4,4,1,'dr. Zulkifli Mansur, Sp.PK, M.Kes','197805122005011004','Kepala UPT Laboratorium Kesehatan','Kepala UPT Laboratorium Kesehatan','waiting',NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-18 19:10:49','2026-10-18 19:10:49'),(15,6,5,5,5,'Agus Hendrawan, S.KM, M.Kes','198809152010011005','Tenaga Sanitasi Lingkungan Ahli Muda','Penanggung Jawab Kesehatan Lingkungan','waiting',NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-18 19:10:49','2026-10-18 19:10:49'),(16,5,1,1,3,'Rahmat Hidayat, A.Md.AK','199208242015021002','Pranata Laboratorium Kesehatan Penyelia','Analis Pemeriksa Lab (ATLM)','pending',NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-18 19:10:49','2026-10-18 19:10:49'),(17,5,2,2,2,'Nurhasanah, S.Tr.Kes, M.Biomed','198504182008012003','Pranata Laboratorium Kesehatan Ahli Muda','Koordinator Teknis & Mutu Lab','waiting',NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-18 19:10:49','2026-10-18 19:10:49'),(18,5,3,3,10,'dr. Maya Sartika, Sp.PK','199008172020121004','Dokter Spesialis Patologi Klinik','Dokter Penanggung Jawab Sp.PK','waiting',NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-18 19:10:49','2026-10-18 19:10:49'),(19,5,4,4,1,'dr. Zulkifli Mansur, Sp.PK, M.Kes','197805122005011004','Kepala UPT Laboratorium Kesehatan','Kepala UPT Laboratorium Kesehatan','waiting',NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-18 19:10:49','2026-10-18 19:10:49'),(20,5,5,5,5,'Agus Hendrawan, S.KM, M.Kes','198809152010011005','Tenaga Sanitasi Lingkungan Ahli Muda','Penanggung Jawab Kesehatan Lingkungan','waiting',NULL,NULL,NULL,NULL,NULL,NULL,'2026-10-18 19:10:49','2026-10-18 19:10:49'),(21,7,1,1,3,'Rahmat Hidayat, A.Md.AK','199208242015021002','Pranata Laboratorium Kesehatan Penyelia','Analis Pemeriksa Lab (ATLM)','pending',NULL,'uploads/signatures/appr_8_step1_1792343473.png',NULL,NULL,NULL,NULL,'2026-10-18 19:15:22','2026-10-18 19:15:22'),(22,7,2,2,2,'Nurhasanah, S.Tr.Kes, M.Biomed','198504182008012003','Pranata Laboratorium Kesehatan Ahli Muda','Koordinator Teknis & Mutu Lab','waiting',NULL,'uploads/signatures/appr_8_step2_1792343483.png',NULL,NULL,NULL,NULL,'2026-10-18 19:15:22','2026-10-18 19:15:22'),(23,7,3,3,10,'dr. Maya Sartika, Sp.PK','199008172020121004','Dokter Spesialis Patologi Klinik','Dokter Penanggung Jawab Sp.PK','waiting',NULL,'uploads/signatures/appr_8_step3_1792343486.png',NULL,NULL,NULL,NULL,'2026-10-18 19:15:22','2026-10-18 19:15:22'),(24,7,4,4,1,'dr. Zulkifli Mansur, Sp.PK, M.Kes','197805122005011004','Kepala UPT Laboratorium Kesehatan','Kepala UPT Laboratorium Kesehatan','waiting',NULL,'uploads/signatures/appr_8_step4_1792343489.png',NULL,NULL,NULL,NULL,'2026-10-18 19:15:22','2026-10-18 19:15:22'),(25,7,5,5,5,'Agus Hendrawan, S.KM, M.Kes','198809152010011005','Tenaga Sanitasi Lingkungan Ahli Muda','Penanggung Jawab Kesehatan Lingkungan','waiting',NULL,'uploads/signatures/appr_8_step5_1792343493.png',NULL,NULL,NULL,NULL,'2026-10-18 19:15:22','2026-10-18 19:15:22');
/*!40000 ALTER TABLE `pendaftaran_approval` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pendaftaran_detail`
--

DROP TABLE IF EXISTS `pendaftaran_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pendaftaran_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pendaftaran_id` int(11) NOT NULL,
  `tipe_item` enum('parameter','template') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'parameter',
  `item_id` int(11) NOT NULL,
  `kode_item` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_item` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategori_lab` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metode_uji` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `satuan` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nilai_rujukan` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jasa_manajemen` decimal(12,2) NOT NULL DEFAULT 0.00,
  `jasa_dokter` decimal(12,2) NOT NULL DEFAULT 0.00,
  `jasa_petugas` decimal(12,2) NOT NULL DEFAULT 0.00,
  `kso` decimal(12,2) NOT NULL DEFAULT 0.00,
  `bhp` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tarif` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status_pengujian` enum('pending','sampling','processing','validasi','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `hasil_nilai` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hasil_flag` enum('normal','low','high','critical') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `catatan_hasil` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `analis_id` int(11) DEFAULT NULL,
  `waktu_input_hasil` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_pendaftaran_id` (`pendaftaran_id`),
  KEY `idx_item` (`tipe_item`,`item_id`),
  CONSTRAINT `fk_pendaftaran_detail_reg` FOREIGN KEY (`pendaftaran_id`) REFERENCES `pendaftaran` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pendaftaran_detail`
--

LOCK TABLES `pendaftaran_detail` WRITE;
/*!40000 ALTER TABLE `pendaftaran_detail` DISABLE KEYS */;
INSERT INTO `pendaftaran_detail` VALUES (1,1,'parameter',1,'GLU','Glukosa Puasa','Kimia Klinik','GOD-PAP','mg/dL','70 - 100',10000.00,15000.00,15000.00,5000.00,10000.00,55000.00,'pending',NULL,NULL,NULL,NULL,NULL,'2026-10-17 16:03:34'),(2,1,'parameter',2,'CHOL','Kolesterol Total','Kimia Klinik','CHOD-PAP','mg/dL','< 200',15000.00,20000.00,20000.00,5000.00,10000.00,70000.00,'pending',NULL,NULL,NULL,NULL,NULL,'2026-10-17 16:03:34'),(3,2,'template',1,'DL','Darah Lengkap (Hematologi Analisis)','Hematologi','Flowcytometry','-','Standar Hematologi',15000.00,25000.00,25000.00,5000.00,15000.00,85000.00,'pending',NULL,NULL,NULL,NULL,NULL,'2026-10-17 16:03:34'),(4,3,'parameter',3,'BOD','BOD (Biochemical Oxygen Demand)','Kesehatan Lingkungan','Winkler Titrimetri','mg/L','< 50.0',40000.00,60000.00,60000.00,20000.00,20000.00,200000.00,'pending',NULL,NULL,NULL,NULL,NULL,'2026-10-17 16:03:34'),(5,3,'parameter',4,'COD','COD (Chemical Oxygen Demand)','Kesehatan Lingkungan','Refluks Tertutup Spektrofotometri','mg/L','< 100.0',40000.00,60000.00,60000.00,20000.00,25000.00,205000.00,'pending',NULL,NULL,NULL,NULL,NULL,'2026-10-17 16:03:34'),(6,4,'parameter',15,'BT','B.T (Bleeding Time)','Hematologi','Duke','Menit','-',2000.00,4000.00,4000.00,1000.00,4000.00,15000.00,'pending',NULL,NULL,NULL,NULL,NULL,'2026-10-17 16:10:45'),(7,5,'template',2,'PKT-HEMOSTASIS-01','Skrining Hemostasis Sederhana','Hematologi','Standar Paket','-','-',10000.00,15000.00,10000.00,5000.00,10000.00,50000.00,'pending',NULL,NULL,NULL,NULL,NULL,'2026-10-17 16:20:45'),(8,5,'template',3,'PKT-URIN-01','Urin Rutin Lengkap','Urinalisa','Standar Paket','-','-',0.00,0.00,0.00,0.00,0.00,0.00,'pending',NULL,NULL,NULL,NULL,NULL,'2026-10-17 16:20:45'),(9,6,'template',1,'PKT-DL-01','Darah Lengkap 5 Diff (Hematologi Rutin)','Hematologi','Flow Cytometry','-','-',15000.00,20000.00,15000.00,15000.00,15000.00,80000.00,'pending',NULL,NULL,NULL,NULL,NULL,'2026-10-18 20:09:56'),(10,6,'parameter',1,'HB','Haemoglobin','Hematologi','Automatic B.C Counter','g/dl','♂ 13,5 - 16,5',0.00,0.00,0.00,0.00,0.00,0.00,'completed','14.8','normal','',1,'2026-10-18 15:10:55','2026-10-18 20:09:56'),(11,6,'parameter',2,'LEU','Leukosit','Hematologi','Automatic B.C Counter','/mm 3','Lk 4,7 - 9,6 rb',0.00,0.00,0.00,0.00,0.00,0.00,'completed','7500','normal','',1,'2026-10-18 15:10:55','2026-10-18 20:09:56'),(12,6,'parameter',3,'LED','L.E.D (Laju Endap Darah)','Hematologi','Westergreen','mm / jam','♂ < 10',0.00,0.00,0.00,0.00,0.00,0.00,'completed','8','normal','',1,'2026-10-18 15:10:55','2026-10-18 20:09:56'),(13,6,'parameter',5,'DIFF_BASO','Basofil','Hematologi','Manual (Mikroskopis)','%','0 - 1',0.00,0.00,0.00,0.00,0.00,0.00,'completed','0','normal','',1,'2026-10-18 15:10:55','2026-10-18 20:09:56'),(14,6,'parameter',6,'DIFF_EOS','Eosinofil','Hematologi','Manual (Mikroskopis)','%','1 - 3',0.00,0.00,0.00,0.00,0.00,0.00,'completed','2','normal','',1,'2026-10-18 15:10:55','2026-10-18 20:09:56'),(15,6,'parameter',7,'DIFF_STAF','Staf (Batang)','Hematologi','Manual (Mikroskopis)','%','2 - 6',0.00,0.00,0.00,0.00,0.00,0.00,'completed','3','normal','',1,'2026-10-18 15:10:55','2026-10-18 20:09:56'),(16,6,'parameter',8,'DIFF_SEG','Segmen','Hematologi','Manual (Mikroskopis)','%','50 - 70',0.00,0.00,0.00,0.00,0.00,0.00,'completed','62','normal','',1,'2026-10-18 15:10:55','2026-10-18 20:09:56'),(17,6,'parameter',9,'DIFF_LIMF','Limfosit','Hematologi','Manual (Mikroskopis)','%','20 - 40',0.00,0.00,0.00,0.00,0.00,0.00,'completed','28','normal','',1,'2026-10-18 15:10:55','2026-10-18 20:09:56'),(18,6,'parameter',10,'DIFF_MONO','Monosit','Hematologi','Manual (Mikroskopis)','%','2 - 8',0.00,0.00,0.00,0.00,0.00,0.00,'completed','5','normal','',1,'2026-10-18 15:10:55','2026-10-18 20:09:56'),(19,6,'parameter',11,'RBC','Eritrosit','Hematologi','Automatic B.C Counter','/mm 3','♂ 4,37 - 5,63 jt',0.00,0.00,0.00,0.00,0.00,0.00,'completed','4.8','normal','',1,'2026-10-18 15:10:55','2026-10-18 20:09:56'),(20,6,'parameter',12,'PLT','Trombosit','Hematologi','Automatic B.C Counter','/mm 3','Lk 145 - 355 rb',0.00,0.00,0.00,0.00,0.00,0.00,'completed','280000','normal','',1,'2026-10-18 15:10:55','2026-10-18 20:09:56'),(21,6,'parameter',13,'HCT','Hematokrit','Hematologi','Automatic B.C Counter','%','Lk 41 - 50',0.00,0.00,0.00,0.00,0.00,0.00,'completed','45','normal','',1,'2026-10-18 15:10:55','2026-10-18 20:09:56'),(22,6,'parameter',19,'MCV','M.C.V','Hematologi','Automatic B.C Counter','f l','82 - 92',0.00,0.00,0.00,0.00,0.00,0.00,'completed','88','normal','',1,'2026-10-18 15:10:55','2026-10-18 20:09:56'),(23,6,'parameter',20,'MCH','M.C.H','Hematologi','Automatic B.C Counter','pg','27 - 31',0.00,0.00,0.00,0.00,0.00,0.00,'completed','29','normal','',1,'2026-10-18 15:10:55','2026-10-18 20:09:56'),(24,6,'parameter',21,'MCHC','M.C.H.C','Hematologi','Automatic B.C Counter','%','32 - 37',0.00,0.00,0.00,0.00,0.00,0.00,'completed','34','normal','',1,'2026-10-18 15:10:55','2026-10-18 20:09:56'),(25,7,'template',1,'PKT-DL-01','Darah Lengkap 5 Diff (Hematologi Rutin)','Hematologi','Flow Cytometry','-','-',12000.00,16000.00,12000.00,12000.00,13000.00,65000.00,'validasi',NULL,NULL,NULL,NULL,NULL,'2026-10-18 20:11:27'),(26,7,'parameter',1,'HB','Haemoglobin','Hematologi','Automatic B.C Counter','g/dl','♂ 13,5 - 16,5',0.00,0.00,0.00,0.00,0.00,0.00,'validasi','15.2','normal','',1,'2026-10-18 15:11:47','2026-10-18 20:11:27'),(27,7,'parameter',2,'LEU','Leukosit','Hematologi','Automatic B.C Counter','/mm 3','Lk 4,7 - 9,6 rb',0.00,0.00,0.00,0.00,0.00,0.00,'validasi','6800','normal','',1,'2026-10-18 15:11:47','2026-10-18 20:11:27'),(28,7,'parameter',3,'LED','L.E.D (Laju Endap Darah)','Hematologi','Westergreen','mm / jam','♂ < 10',0.00,0.00,0.00,0.00,0.00,0.00,'validasi','6','normal','',1,'2026-10-18 15:11:47','2026-10-18 20:11:27'),(29,7,'parameter',5,'DIFF_BASO','Basofil','Hematologi','Manual (Mikroskopis)','%','0 - 1',0.00,0.00,0.00,0.00,0.00,0.00,'validasi','0','normal','',1,'2026-10-18 15:11:47','2026-10-18 20:11:27'),(30,7,'parameter',6,'DIFF_EOS','Eosinofil','Hematologi','Manual (Mikroskopis)','%','1 - 3',0.00,0.00,0.00,0.00,0.00,0.00,'validasi','2','normal','',1,'2026-10-18 15:11:47','2026-10-18 20:11:27'),(31,7,'parameter',7,'DIFF_STAF','Staf (Batang)','Hematologi','Manual (Mikroskopis)','%','2 - 6',0.00,0.00,0.00,0.00,0.00,0.00,'validasi','2','normal','',1,'2026-10-18 15:11:47','2026-10-18 20:11:27'),(32,7,'parameter',8,'DIFF_SEG','Segmen','Hematologi','Manual (Mikroskopis)','%','50 - 70',0.00,0.00,0.00,0.00,0.00,0.00,'validasi','58','normal','',1,'2026-10-18 15:11:47','2026-10-18 20:11:27'),(33,7,'parameter',9,'DIFF_LIMF','Limfosit','Hematologi','Manual (Mikroskopis)','%','20 - 40',0.00,0.00,0.00,0.00,0.00,0.00,'validasi','32','normal','',1,'2026-10-18 15:11:47','2026-10-18 20:11:27'),(34,7,'parameter',10,'DIFF_MONO','Monosit','Hematologi','Manual (Mikroskopis)','%','2 - 8',0.00,0.00,0.00,0.00,0.00,0.00,'validasi','6','normal','',1,'2026-10-18 15:11:47','2026-10-18 20:11:27'),(35,7,'parameter',11,'RBC','Eritrosit','Hematologi','Automatic B.C Counter','/mm 3','♂ 4,37 - 5,63 jt',0.00,0.00,0.00,0.00,0.00,0.00,'validasi','5.1','normal','',1,'2026-10-18 15:11:47','2026-10-18 20:11:27'),(36,7,'parameter',12,'PLT','Trombosit','Hematologi','Automatic B.C Counter','/mm 3','Lk 145 - 355 rb',0.00,0.00,0.00,0.00,0.00,0.00,'validasi','245000','normal','',1,'2026-10-18 15:11:47','2026-10-18 20:11:27'),(37,7,'parameter',13,'HCT','Hematokrit','Hematologi','Automatic B.C Counter','%','Lk 41 - 50',0.00,0.00,0.00,0.00,0.00,0.00,'validasi','46','normal','',1,'2026-10-18 15:11:47','2026-10-18 20:11:27'),(38,7,'parameter',19,'MCV','M.C.V','Hematologi','Automatic B.C Counter','f l','82 - 92',0.00,0.00,0.00,0.00,0.00,0.00,'validasi','90','normal','',1,'2026-10-18 15:11:47','2026-10-18 20:11:27'),(39,7,'parameter',20,'MCH','M.C.H','Hematologi','Automatic B.C Counter','pg','27 - 31',0.00,0.00,0.00,0.00,0.00,0.00,'validasi','30','normal','',1,'2026-10-18 15:11:47','2026-10-18 20:11:27'),(40,7,'parameter',21,'MCHC','M.C.H.C','Hematologi','Automatic B.C Counter','%','32 - 37',0.00,0.00,0.00,0.00,0.00,0.00,'validasi','33','normal','',1,'2026-10-18 15:11:47','2026-10-18 20:11:27'),(41,8,'template',1,'PKT-DL-01','Darah Lengkap 5 Diff (Hematologi Rutin)','Hematologi','Standar Paket','-','-',15000.00,20000.00,15000.00,15000.00,15000.00,80000.00,'completed',NULL,NULL,NULL,NULL,NULL,'2026-10-18 21:24:01'),(42,8,'parameter',1,'HB','Haemoglobin','Hematologi','Automatic B.C Counter','g/dl','♂ 13,5 - 16,5',0.00,0.00,0.00,0.00,0.00,0.00,'completed','14.8','normal','',1,'2026-10-18 17:54:42','2026-10-18 21:24:01'),(43,8,'parameter',2,'LEU','Leukosit','Hematologi','Automatic B.C Counter','/mm 3','Lk 4,7 - 9,6 rb',0.00,0.00,0.00,0.00,0.00,0.00,'completed','7200','normal','',1,'2026-10-18 17:54:42','2026-10-18 21:24:01'),(44,8,'parameter',3,'LED','L.E.D (Laju Endap Darah)','Hematologi','Westergreen','mm / jam','♂ < 10',0.00,0.00,0.00,0.00,0.00,0.00,'completed','8','normal','',1,'2026-10-18 17:54:42','2026-10-18 21:24:01'),(45,8,'parameter',5,'DIFF_BASO','Basofil','Hematologi','Manual (Mikroskopis)','%','0 - 1',0.00,0.00,0.00,0.00,0.00,0.00,'completed',NULL,NULL,NULL,NULL,NULL,'2026-10-18 21:24:01'),(46,8,'parameter',6,'DIFF_EOS','Eosinofil','Hematologi','Manual (Mikroskopis)','%','1 - 3',0.00,0.00,0.00,0.00,0.00,0.00,'completed',NULL,NULL,NULL,NULL,NULL,'2026-10-18 21:24:01'),(47,8,'parameter',7,'DIFF_STAF','Staf (Batang)','Hematologi','Manual (Mikroskopis)','%','2 - 6',0.00,0.00,0.00,0.00,0.00,0.00,'completed',NULL,NULL,NULL,NULL,NULL,'2026-10-18 21:24:01'),(48,8,'parameter',8,'DIFF_SEG','Segmen','Hematologi','Manual (Mikroskopis)','%','50 - 70',0.00,0.00,0.00,0.00,0.00,0.00,'completed',NULL,NULL,NULL,NULL,NULL,'2026-10-18 21:24:01'),(49,8,'parameter',9,'DIFF_LIMF','Limfosit','Hematologi','Manual (Mikroskopis)','%','20 - 40',0.00,0.00,0.00,0.00,0.00,0.00,'completed',NULL,NULL,NULL,NULL,NULL,'2026-10-18 21:24:01'),(50,8,'parameter',10,'DIFF_MONO','Monosit','Hematologi','Manual (Mikroskopis)','%','2 - 8',0.00,0.00,0.00,0.00,0.00,0.00,'completed',NULL,NULL,NULL,NULL,NULL,'2026-10-18 21:24:01'),(51,8,'parameter',11,'RBC','Eritrosit','Hematologi','Automatic B.C Counter','/mm 3','♂ 4,37 - 5,63 jt',0.00,0.00,0.00,0.00,0.00,0.00,'completed',NULL,NULL,NULL,NULL,NULL,'2026-10-18 21:24:01'),(52,8,'parameter',12,'PLT','Trombosit','Hematologi','Automatic B.C Counter','/mm 3','Lk 145 - 355 rb',0.00,0.00,0.00,0.00,0.00,0.00,'completed',NULL,NULL,NULL,NULL,NULL,'2026-10-18 21:24:01'),(53,8,'parameter',13,'HCT','Hematokrit','Hematologi','Automatic B.C Counter','%','Lk 41 - 50',0.00,0.00,0.00,0.00,0.00,0.00,'completed',NULL,NULL,NULL,NULL,NULL,'2026-10-18 21:24:01'),(54,8,'parameter',19,'MCV','M.C.V','Hematologi','Automatic B.C Counter','f l','82 - 92',0.00,0.00,0.00,0.00,0.00,0.00,'completed',NULL,NULL,NULL,NULL,NULL,'2026-10-18 21:24:01'),(55,8,'parameter',20,'MCH','M.C.H','Hematologi','Automatic B.C Counter','pg','27 - 31',0.00,0.00,0.00,0.00,0.00,0.00,'completed',NULL,NULL,NULL,NULL,NULL,'2026-10-18 21:24:01'),(56,8,'parameter',21,'MCHC','M.C.H.C','Hematologi','Automatic B.C Counter','%','32 - 37',0.00,0.00,0.00,0.00,0.00,0.00,'completed',NULL,NULL,NULL,NULL,NULL,'2026-10-18 21:24:01'),(57,8,'template',2,'PKT-HEMOSTASIS-01','Skrining Hemostasis Sederhana','Hematologi','Standar Paket','-','-',10000.00,15000.00,10000.00,5000.00,10000.00,50000.00,'completed',NULL,NULL,NULL,NULL,NULL,'2026-10-18 21:24:01'),(58,8,'parameter',14,'CT','C.T (Clotting Time)','Hematologi','Lee dan White','Menit','9 - 15',0.00,0.00,0.00,0.00,0.00,0.00,'completed',NULL,NULL,NULL,NULL,NULL,'2026-10-18 21:24:01'),(59,8,'parameter',15,'BT','B.T (Bleeding Time)','Hematologi','Duke','Menit','1 - 3',0.00,0.00,0.00,0.00,0.00,0.00,'completed',NULL,NULL,NULL,NULL,NULL,'2026-10-18 21:24:01'),(60,8,'parameter',16,'RL_TEST','R.L Test (Rumple Leede)','Hematologi','Rumple Leede','-','Negatif',0.00,0.00,0.00,0.00,0.00,0.00,'completed',NULL,NULL,NULL,NULL,NULL,'2026-10-18 21:24:01'),(61,8,'parameter',18,'RETRAKSI_BEKUAN','Retraksi Bekuan','Hematologi','Manual (Mikroskopis)','%','40 - 60',0.00,0.00,0.00,0.00,0.00,0.00,'completed',NULL,NULL,NULL,NULL,NULL,'2026-10-18 21:24:01'),(62,5,'parameter',14,'CT','C.T (Clotting Time)','Hematologi','Lee dan White','Menit','9 - 15',0.00,0.00,0.00,0.00,0.00,0.00,'completed','34','critical','23',1,'2026-10-18 17:42:21','2026-10-18 22:41:26'),(63,5,'parameter',15,'BT','B.T (Bleeding Time)','Hematologi','Duke','Menit','1 - 3',0.00,0.00,0.00,0.00,0.00,0.00,'completed','2','normal','23',1,'2026-10-18 17:42:21','2026-10-18 22:41:26'),(64,5,'parameter',16,'RL_TEST','R.L Test (Rumple Leede)','Hematologi','Rumple Leede','-','Negatif',0.00,0.00,0.00,0.00,0.00,0.00,'completed','323','normal','23',1,'2026-10-18 17:42:21','2026-10-18 22:41:26'),(65,5,'parameter',18,'RETRAKSI_BEKUAN','Retraksi Bekuan','Hematologi','Manual (Mikroskopis)','%','40 - 60',0.00,0.00,0.00,0.00,0.00,0.00,'completed','23','critical','23',1,'2026-10-18 17:42:21','2026-10-18 22:41:26');
/*!40000 ALTER TABLE `pendaftaran_detail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pendaftaran_log`
--

DROP TABLE IF EXISTS `pendaftaran_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pendaftaran_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pendaftaran_id` int(11) NOT NULL,
  `action` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_log_pendaftaran_id` (`pendaftaran_id`),
  CONSTRAINT `fk_pendaftaran_log_reg` FOREIGN KEY (`pendaftaran_id`) REFERENCES `pendaftaran` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pendaftaran_log`
--

LOCK TABLES `pendaftaran_log` WRITE;
/*!40000 ALTER TABLE `pendaftaran_log` DISABLE KEYS */;
INSERT INTO `pendaftaran_log` VALUES (1,4,'REGISTRASI_ORDER','Registrasi Baru: REG-20261017-0004 (Budi Santoso). Tipe: MANDIRI. Jalur Bayar: MANDIRI LANGSUNG. Total Biaya: Rp 15.000',1,'Administrator LIS','2026-10-17 16:10:45'),(2,4,'VALIDASI_PEMBAYARAN_KASIR','Pembayaran Lunas Kasir (PAID). Metode: Cash. Bayar: Rp 20.000. Kembali: Rp 5.000. No. Kuitansi: KWT-20261017-0002. Barcode Sampel SMP-261017-0004 Aktif Siap Sampling.',1,'Administrator LIS','2026-10-17 16:10:50'),(3,2,'VERIFIKASI_PKS_MITRA','Verifikasi PKS Mitra / Piutang Berhasil (GUARANTEED). No. PKS: SPK/PKM-RB/2026/089 | Penjamin: Puskesmas Rumbai | PIC: dr. Syarifah. Barcode Sampel SMP-261017-0002 Aktif.',1,'Administrator LIS','2026-10-17 16:10:58'),(4,5,'REGISTRASI_ORDER','Registrasi Baru: REG-20261017-0005 (Nurul Hidayati). Tipe: MANDIRI. Jalur Bayar: MANDIRI LANGSUNG. Total Biaya: Rp 50.000',1,'Administrator LIS','2026-10-17 16:20:45'),(5,5,'VALIDASI_PEMBAYARAN_KASIR','Pembayaran Lunas Kasir (PAID). Metode: Tunai / Cash. Bayar: Rp 100.000. Kembali: Rp 50.000. No. Kuitansi: KWT-20261017-0003. Barcode Sampel SMP-261017-0005 Aktif Siap Sampling.',1,'Administrator LIS','2026-10-17 16:22:37'),(6,6,'REGISTRASI_ORDER','Registrasi Baru: REG-20261018-0001 (Budi Santoso Mandiri). Tipe: MANDIRI. Jalur Bayar: MANDIRI LANGSUNG. Total Biaya: Rp 80.000',1,'Administrator LIS','2026-10-18 20:09:56'),(7,6,'VALIDASI_PEMBAYARAN_KASIR','Pembayaran Lunas Kasir (PAID). Channel: Kasir Tunai Loket (1.1.01.01) | Akun Bayar ID: 1 | Bayar: Rp 100.000 | Kembali: Rp 20.000 | No. Kuitansi: KWT-20261018-0001',1,'Administrator LIS','2026-10-18 20:10:28'),(8,6,'VALIDASI_HASIL_SELESAI','Hasil Lab Disimpan (COMPLETED). Dokter PJ: dr. Hj. Siti Rahmah, Sp.PK, M.Kes | Analis: Siti Rahmawati, A.Md.AK | Status Order: COMPLETED',1,'Administrator LIS','2026-10-18 20:10:55'),(9,7,'REGISTRASI_ORDER','Registrasi Baru: REG-20261018-0002 (Karyawan PT PHR (Budi Setiawan)). Tipe: PERUSAHAAN LINGKUNGAN. Jalur Bayar: PIUTANG PKS. Total Biaya: Rp 58.500',1,'Administrator LIS','2026-10-18 20:11:27'),(10,7,'VERIFIKASI_PKS_MITRA','Verifikasi Jaminan Piutang Mitra (GUARANTEED). Penjamin: PT Pertamina Hulu Rokan | Akun Piutang ID: 2 | No. PKS/SPK: SPK-PHR-LABKES-2026-088 | PIC: Bambang Supriyanto',1,'Administrator LIS','2026-10-18 20:11:38'),(11,7,'VALIDASI_HASIL_SELESAI','Hasil Lab Disimpan (COMPLETED). Dokter PJ: dr. Hj. Siti Rahmah, Sp.PK, M.Kes | Analis: Siti Rahmawati, A.Md.AK | Status Order: COMPLETED',1,'Administrator LIS','2026-10-18 20:11:47'),(12,8,'REGISTRASI_ORDER','Registrasi Baru: REG-20261018-0003 (MoizCare Indonesia). Tipe: RUJUKAN FASKES. Jalur Bayar: MANDIRI LANGSUNG. Total Biaya: Rp 130.000',1,'Administrator LIS','2026-10-18 21:24:01'),(13,8,'VALIDASI_PEMBAYARAN_KASIR','Pembayaran Lunas Kasir (PAID). Channel: Kasir Tunai Loket (1.1.01.01) | Akun Bayar ID: 1 | Bayar: Rp 150.000 | Kembali: Rp 20.000 | No. Kuitansi: KWT-20261018-0002',1,'Administrator LIS','2026-10-18 21:25:47'),(14,8,'UPDATE_STATUS_ORDER','Status order diubah menjadi \'PROCESSING\'. Catatan: Spesimen telah diambil dan diserahkan ke analis laboratorium',1,'Administrator LIS','2026-10-18 22:12:27'),(15,8,'INPUT_HASIL_LAB','Hasil Lab Disimpan (PROCESSING). Dokter PJ: dr. Hj. Siti Rahmah, Sp.PK, M.Kes | Analis: Siti Rahmawati, A.Md.AK | Status Order: PROCESSING',1,'Administrator LIS','2026-10-18 22:18:57'),(16,8,'VALIDASI_OTORISASI_SPPK','Otorisasi & Validasi Klinis oleh Dokter dr. Hj. Siti Rahmah, Sp.PK, M.Kes. Status Order: COMPLETED.',1,'Administrator LIS','2026-10-18 22:19:30'),(17,5,'INPUT_HASIL_LAB','Hasil Lab Disimpan (PROCESSING). Dokter PJ: dr. Hj. Siti Rahmah, Sp.PK, M.Kes | Analis: Administrator LIS | Status Order: PROCESSING',1,'Administrator LIS','2026-10-18 22:41:45'),(18,5,'INPUT_HASIL_LAB','Hasil Lab Disimpan (PROCESSING). Dokter PJ: dr. Hj. Siti Rahmah, Sp.PK, M.Kes | Analis: Administrator LIS | Status Order: PROCESSING',1,'Administrator LIS','2026-10-18 22:42:15'),(19,5,'VALIDASI_HASIL_SELESAI','Hasil Lab Disimpan (COMPLETED). Dokter PJ: dr. Hj. Siti Rahmah, Sp.PK, M.Kes | Analis: Administrator LIS | Status Order: COMPLETED',1,'Administrator LIS','2026-10-18 22:42:21'),(20,8,'VALIDASI_OTORISASI_SPPK','Otorisasi & Validasi Klinis oleh Dokter dr. Hj. Siti Rahmah, Sp.PK, M.Kes. Status Order: COMPLETED.',1,'Administrator LIS','2026-10-18 22:53:52'),(21,8,'INPUT_HASIL_LAB','Hasil Lab Disimpan (VALIDASI). Dokter PJ: dr. Hj. Siti Rahmah, Sp.PK, M.Kes | Analis: Siti Rahmawati, A.Md.AK | Status Order: VALIDASI',1,'Administrator LIS','2026-10-18 22:54:42'),(22,7,'VALIDASI_OTORISASI_SPPK','Otorisasi & Validasi Klinis oleh Dokter dr. Budi Santoso, Sp.PK. Status Order: COMPLETED.',1,'Administrator LIS','2026-10-18 22:55:03'),(23,8,'APPROVAL_HASIL_STEP_1','Approval Tingkat 1 (Analis Pemeriksa Lab (ATLM)) disahkan oleh Rahmat Hidayat, A.Md.AK (Administrator LIS). Catatan: Hasil analitik sesuai kontrol mutu. Menunggu approval tingkat selanjutnya.',1,'Administrator LIS','2026-10-19 00:11:13'),(24,8,'APPROVAL_HASIL_STEP_2','Approval Tingkat 2 (Koordinator Teknis & Mutu Lab) disahkan oleh Nurhasanah, S.Tr.Kes, M.Biomed (Administrator LIS). Catatan: Verifikasi mutu analitik lengkap. Menunggu approval tingkat selanjutnya.',1,'Administrator LIS','2026-10-19 00:11:23'),(25,8,'APPROVAL_HASIL_STEP_3','Approval Tingkat 3 (Dokter Penanggung Jawab Sp.PK) disahkan oleh dr. Maya Sartika, Sp.PK (Administrator LIS). Catatan: Evaluasi klinis patologi sesuai rujukan. Menunggu approval tingkat selanjutnya.',1,'Administrator LIS','2026-10-19 00:11:26'),(26,8,'APPROVAL_HASIL_STEP_4','Approval Tingkat 4 (Kepala UPT Laboratorium Kesehatan) disahkan oleh dr. Zulkifli Mansur, Sp.PK, M.Kes (Administrator LIS). Catatan: Disetujui untuk penerbitan laporan. Menunggu approval tingkat selanjutnya.',1,'Administrator LIS','2026-10-19 00:11:29'),(27,8,'APPROVAL_HASIL_STEP_5','Approval Tingkat 5 (Penanggung Jawab Kesehatan Lingkungan) disahkan oleh Agus Hendrawan, S.KM, M.Kes (Administrator LIS). Catatan: Verifikasi akhir lengkap, hasil siap diserahkan ke pelanggan. SELURUH APPROVAL LENGKAP. Hasil resmi dirilis ke customer.',1,'Administrator LIS','2026-10-19 00:11:33');
/*!40000 ALTER TABLE `pendaftaran_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pengaturan`
--

DROP TABLE IF EXISTS `pengaturan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pengaturan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_group` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aplikasi',
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `setting_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text',
  `label` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `idx_group` (`setting_group`)
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengaturan`
--

LOCK TABLES `pengaturan` WRITE;
/*!40000 ALTER TABLE `pengaturan` DISABLE KEYS */;
INSERT INTO `pengaturan` VALUES (1,'aplikasi','nama_aplikasi','Moiz Laboratorium System','text','Nama Aplikasi LIS','Nama sistem yang ditampilkan pada header dan dokumen resmi',1,'2026-10-17 16:39:19','2026-10-17 11:57:24'),(2,'aplikasi','singkatan_aplikasi','LIS PRO RIAU','text','Singkatan / Brand Singkat','Nama ringkas brand pada sidebar dan favicon',2,'2026-10-17 16:39:19','2026-10-17 11:57:24'),(3,'aplikasi','nama_instansi','UPT Laboratorium Kesehatan Provinsi Riau','text','Nama Resmi Instansi','Nama faskes laboratorium pengelola BLUD',3,'2026-10-17 16:39:19','2026-10-17 11:57:24'),(4,'aplikasi','instansi_induk','Dinas Kesehatan Provinsi Riau','text','Instansi Induk / Pemerintah','Pemerintah daerah atau kementerian pengampu',4,'2026-10-17 16:39:19','2026-10-17 11:57:24'),(5,'aplikasi','slogan_aplikasi','Akurat, Cepat, Terpercaya, dan Terstandar Mutu Nasional','text','Slogan / Motto Pelayanan','Motto pelayanan laboratorium',5,'2026-10-17 16:39:19','2026-10-17 11:57:24'),(6,'aplikasi','nomor_izin_lab','445/DINKES-LAB/2024/008','text','No. Izin Operasional / Registrasi Faskes','Nomor izin operasional laboratorium dari Kemenkes/Dinkes',6,'2026-10-17 16:39:19','2026-10-17 11:57:24'),(7,'aplikasi','tipe_laboratorium','Laboratorium Kesehatan Daerah (Labkesda BLUD)','select','Tipe & Kelas Laboratorium','Kualifikasi jenis laboratorium',7,'2026-10-17 16:39:19','2026-10-17 11:57:24'),(8,'aplikasi','alamat_lengkap','Jl. Cut Nyak Dien No. 01, Kel. Sukajadi','textarea','Alamat Lengkap Kantor / Lab','Alamat gedung operasional laboratorium',8,'2026-10-17 16:39:19','2026-10-17 11:57:24'),(9,'aplikasi','kota_kabupaten','Kota Pekanbaru','text','Kota / Kabupaten','Kota domisili faskes',9,'2026-10-17 16:39:19','2026-10-17 11:57:24'),(10,'aplikasi','provinsi','Riau','text','Provinsi','Provinsi domisili faskes',10,'2026-10-17 16:39:19','2026-10-17 11:57:24'),(11,'aplikasi','kode_pos','28121','text','Kode Pos','Kode pos wilayah operasional',11,'2026-10-17 16:39:19','2026-10-17 11:57:24'),(12,'aplikasi','telepon','(0761) 23456','text','Nomor Telepon Kantor','Nomor telepon resmi kantor',12,'2026-10-17 16:39:19','2026-10-17 11:57:24'),(13,'aplikasi','whatsapp_layanan','0812-7567-8901','text','WhatsApp Layanan & Helpdesk','Nomor kontak WhatsApp interaktif pelanggan',13,'2026-10-17 16:39:19','2026-10-17 11:57:24'),(14,'aplikasi','email_resmi','labkes@riau.go.id','text','Email Resmi Laboratorium','Alamat surat elektronik resmi laboratorium',14,'2026-10-17 16:39:19','2026-10-17 11:57:24'),(15,'aplikasi','website_resmi','https://labkesda.riau.go.id','text','Website Resmi Faskes','Portal informasi dan hasil online faskes',15,'2026-10-17 16:39:19','2026-10-17 11:57:24'),(16,'aplikasi','kepala_lab','dr. H. Hendra Saputra, Sp.PK','text','Nama Kepala Laboratorium','Pimpinan / Kepala UPT Labkes',16,'2026-10-17 16:39:19','2026-10-17 11:57:24'),(17,'aplikasi','nip_kepala_lab','19780512 200501 1 004','text','NIP Kepala Laboratorium','Nomor Induk Pegawai pimpinan',17,'2026-10-17 16:39:19','2026-10-17 11:57:24'),(18,'aplikasi','pangkat_kepala_lab','Pembina (Gol. IV/a)','text','Pangkat / Golongan Kepala Lab','Pangkat dinas kepegawaian pimpinan',18,'2026-10-17 16:39:19','2026-10-17 11:57:24'),(19,'aplikasi','dokter_penanggung_jawab','dr. Hj. Siti Rahmah, Sp.PK, M.Kes','text','Dokter Penanggung Jawab Teknis','Dokter Spesialis Patologi Klinis penandatangan hasil',19,'2026-10-17 16:39:19','2026-10-17 11:57:24'),(20,'aplikasi','sip_dokter','503/SIP-D/2023/0421','text','SIP Dokter Penanggung Jawab','Surat Izin Praktik dokter spesialis',20,'2026-10-17 16:39:19','2026-10-17 11:57:24'),(21,'aplikasi','kasi_pelayanan','Ahmad Zulkarnain, S.Tr.Kes','text','Kepala Seksi Pelayanan & Mutu','Penanggung jawab administrasi operasional',21,'2026-10-17 16:39:19','2026-10-17 11:57:24'),(22,'aplikasi','nip_kasi_pelayanan','19850314 200902 1 002','text','NIP Kasi Pelayanan','Nomor induk pegawai kepala seksi',22,'2026-10-17 16:39:19','2026-10-17 11:57:24'),(23,'aplikasi','kop_header_line1','PEMERINTAH PROVINSI RIAU','text','Kop Cetak Baris 1','Pemerintah daerah / organisasi induk',23,'2026-10-17 16:39:19','2026-10-17 11:57:24'),(24,'aplikasi','kop_header_line2','DINAS KESEHATAN','text','Kop Cetak Baris 2','Dinas pengampu',24,'2026-10-17 16:39:19','2026-10-17 11:57:24'),(25,'aplikasi','kop_header_line3','UPT LABORATORIUM KESEHATAN DAN LINGKUNGAN','text','Kop Cetak Baris 3','Unit pelaksana teknis faskes',25,'2026-10-17 16:39:19','2026-10-17 11:57:24'),(26,'aplikasi','kop_footer_note','Hasil pemeriksaan ini sah dicetak otomatis oleh LIS PRO dan terakreditasi KAN ISO/IEC 17025','textarea','Catatan Kaki Hasil & Kuitansi','Disclaimer hukum dan akreditasi pada lembar cetak',26,'2026-10-17 16:39:19','2026-10-17 11:57:24'),(27,'aplikasi','masa_berlaku_hasil','30','number','Masa Berlaku Hasil Uji (Hari)','Batas waktu validitas lembar hasil pemeriksaan',27,'2026-10-17 16:39:19','2026-10-17 11:57:24'),(28,'aplikasi','biaya_administrasi_default','15000','number','Biaya Administrasi Default (Rp)','Tarif jasa pendaftaran dasar jika ada',28,'2026-10-17 16:39:19','2026-10-17 11:57:24'),(29,'aplikasi','zona_waktu','Asia/Jakarta','text','Zona Waktu Server','WIB / GMT+7',29,'2026-10-17 16:39:19','2026-10-17 11:57:24'),(30,'ai','ai_provider','gemini','select','Provider Mesin AI','Penyedia LLM untuk analisis medis laboratorium',30,'2026-10-17 16:39:19','2026-10-17 11:53:32'),(31,'ai','ai_api_key','AIzaSy_demo_key_123','text','API Key AI Provider','Kunci rahasia API Google Gemini atau OpenAI',31,'2026-10-17 16:39:19','2026-10-17 11:53:32'),(32,'ai','ai_model','gemini-1.5-flash','text','Model AI yang Digunakan','Versi model LLM (contoh: gemini-1.5-flash / gpt-4o)',32,'2026-10-17 16:39:19','2026-10-17 11:53:32'),(33,'ai','ai_auto_interpretation','1','boolean','Interpretasi Klinis Otomatis','Aktifkan ringkasan cerdas nilai abnormal hasil lab',33,'2026-10-17 16:39:19','2026-10-17 11:53:32'),(34,'ai','ai_critical_alert','1','boolean','Peringatan Nilai Kritis Lab (Critical Value Alert)','Notifikasi instan jika parameter menyentuh batas bahaya',34,'2026-10-17 16:39:19','2026-10-17 11:53:32'),(35,'ai','ai_clinical_summary','1','boolean','Ringkasan Resume Medis Pasien','Pembuatan resume komprehensif riwayat lab pasien',35,'2026-10-17 16:39:19','2026-10-17 11:53:32'),(36,'ai','ai_prompt_template','Anda adalah asisten AI medis laboratorium klinik spesialis Patologi Klinis.','textarea','System Prompt Template AI','Instruksi dasar untuk asisten kecerdasan buatan',36,'2026-10-17 16:39:19','2026-10-17 11:53:32'),(37,'bridging','satusehat_active','1','boolean','Aktivasi Bridging SatuSehat','Interoperabilitas Kemenkes RI FHIR R4',37,'2026-10-17 16:39:19','2026-10-17 11:53:39'),(38,'bridging','satusehat_env','development','select','Environment SatuSehat','Development / Staging / Production',38,'2026-10-17 16:39:19','2026-10-17 11:53:39'),(39,'bridging','satusehat_org_id','100000123','text','Organization ID SatuSehat','ID Faskes dari DTO Kemenkes',39,'2026-10-17 16:39:19','2026-10-17 11:53:39'),(40,'bridging','satusehat_client_id','client_labkes_dev','text','Client ID SatuSehat','Kunci klien OAuth2',40,'2026-10-17 16:39:19','2026-10-17 11:53:39'),(41,'bridging','satusehat_client_secret','secret_xyz','text','Client Secret SatuSehat','Kunci rahasia OAuth2',41,'2026-10-17 16:39:19','2026-10-17 11:53:39'),(42,'bridging','satusehat_auth_url','https://api-satusehat-dev.dto.kemkes.go.id/oauth2/v1','text','Auth URL SatuSehat','Endpoint autentikasi token FHIR',42,'2026-10-17 16:39:19','2026-10-17 11:53:39'),(43,'bridging','satusehat_fhir_url','https://api-satusehat-dev.dto.kemkes.go.id/fhir-r4/v1','text','FHIR Base URL SatuSehat','Endpoint DiagnosticReport & Specimen',43,'2026-10-17 16:39:19','2026-10-17 11:53:39'),(44,'bridging','analyzer_protocol','ASTM_E1394','select','Protokol Alat Laboratorium','ASTM E1394 atau HL7 v2.x',44,'2026-10-17 16:39:19','2026-10-17 11:53:39'),(45,'bridging','analyzer_ip','192.168.1.150','text','IP Host Alat Analyzer','Alamat jaringan analyzer hematologi/kimia',45,'2026-10-17 16:39:19','2026-10-17 11:53:39'),(46,'bridging','analyzer_port','5000','number','Port Komunikasi Alat','Port TCP/IP listening',46,'2026-10-17 16:39:19','2026-10-17 11:53:39'),(47,'bridging','his_webhook_url','http://simrs.rsud.riau.go.id/api/lis/callback','text','Webhook Callback SIMRS / HIS','Kirim otomatis hasil ke SIMRS Faskes Pengirim',47,'2026-10-17 16:39:19','2026-10-17 11:53:39'),(48,'bridging','his_api_key','his_tok_9281a8b7c6','text','Secret Token API SIMRS','Token verifikasi payload webhook',48,'2026-10-17 16:39:19','2026-10-17 11:53:39'),(49,'aplikasi','logo_instansi','uploads/pengaturan/logo_instansi_1792231824.svg','file','Logo Instansi Laboratorium','File gambar logo instansi resmi pada sisi kiri kop dokumen',1,'2026-10-17 17:02:19','2026-10-17 12:10:24'),(50,'aplikasi','logo_kan','uploads/pengaturan/logo_kan_1792231820.svg','file','Logo Akreditasi KAN / ISO','File gambar logo akreditasi atau penjaminan mutu pada sisi kanan kop dokumen',2,'2026-10-17 17:02:19','2026-10-17 12:10:20'),(51,'approval','approval_wajib_semua_ttd','1','boolean','Wajib Semua TTD Lengkap','Hasil laboratorium hanya sah jika semua tahapan approval telah disahkan dan bertandatangan',1,'2026-10-19 16:02:21','2026-10-19 11:13:21'),(52,'approval','approval_mode_belum_lengkap','watermark','select','Perlakuan Hasil Belum Lengkap','Mode saat masa berjenjang belum lengkap bertandatangan (watermark atau block)',2,'2026-10-19 16:02:21','2026-10-19 11:13:21'),(53,'approval','approval_teks_watermark','HASIL BELUM SAH • MENUNGGU APPROVAL BERJENJANG','text','Teks Watermark Belum Sah','Teks watermark diagonal pada lembar hasil yang belum lengkap bertandatangan',3,'2026-10-19 16:02:21','2026-10-19 11:13:21'),(54,'approval','approval_keterangan_hukum','Hasil pemeriksaan ini BELUM SAH secara hukum dan belum dapat diserahkan ke pelanggan karena proses verifikasi berjenjang belum lengkap bertandatangan.','textarea','Keterangan Hukum Belum Sah','Peringatan legalitas pada dokumen yang belum sah',4,'2026-10-19 16:02:21','2026-10-19 11:13:21');
/*!40000 ALTER TABLE `pengaturan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rekening_tahun`
--

DROP TABLE IF EXISTS `rekening_tahun`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rekening_tahun` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tahun` smallint(4) NOT NULL,
  `kode_rekening` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `saldo_awal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `target_anggaran` decimal(15,2) NOT NULL DEFAULT 0.00,
  `realisasi_debet` decimal(15,2) NOT NULL DEFAULT 0.00,
  `realisasi_kredit` decimal(15,2) NOT NULL DEFAULT 0.00,
  `saldo_akhir` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` enum('aktif','tutup_buku') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `keterangan` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_tahun_kode` (`tahun`,`kode_rekening`),
  KEY `idx_tahun` (`tahun`),
  KEY `idx_kode` (`kode_rekening`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rekening_tahun`
--

LOCK TABLES `rekening_tahun` WRITE;
/*!40000 ALTER TABLE `rekening_tahun` DISABLE KEYS */;
INSERT INTO `rekening_tahun` VALUES (1,2026,'1.1.01.01',15000000.00,500000000.00,125450000.00,72000000.00,68450000.00,'aktif','Kasir Loket 2026','2026-10-17 19:49:57','2026-10-17 19:49:57'),(2,2026,'1.1.01.02',5000000.00,60000000.00,45000000.00,42000000.00,8000000.00,'aktif','Petty Cash Lab 2026','2026-10-17 19:49:57','2026-10-17 19:49:57'),(3,2026,'1.1.02.01',250000000.00,1500000000.00,480000000.00,355000000.00,375000000.00,'aktif','Giro BRK Syariah 2026','2026-10-17 19:49:57','2026-10-17 19:49:57'),(4,2026,'1.1.02.02',50000000.00,400000000.00,110000000.00,70000000.00,90000000.00,'aktif','EDC BRI 2026','2026-10-17 19:49:57','2026-10-17 19:49:57'),(5,2026,'1.1.02.05',10000000.00,150000000.00,32000000.00,16400000.00,25600000.00,'aktif','QRIS BRKS 2026','2026-10-17 19:49:57','2026-10-17 19:49:57'),(6,2026,'1.1.03.01',35000000.00,300000000.00,85000000.00,68000000.00,52000000.00,'aktif','Klaim BPJS 2026','2026-10-17 19:49:57','2026-10-17 19:49:57'),(7,2026,'1.1.03.02',80000000.00,600000000.00,190000000.00,150000000.00,120000000.00,'aktif','PKS PHR/RAPP 2026','2026-10-17 19:49:57','2026-10-17 19:49:57'),(8,2026,'1.1.03.03',20000000.00,150000000.00,45000000.00,32000000.00,33000000.00,'aktif','Rujukan Puskesmas 2026','2026-10-17 19:49:57','2026-10-17 19:49:57'),(9,2026,'4.1.01',0.00,800000000.00,0.00,285400000.00,285400000.00,'aktif','Pendapatan Mandiri 2026','2026-10-17 19:49:57','2026-10-17 19:49:57'),(10,2026,'4.1.02',0.00,500000000.00,0.00,180000000.00,180000000.00,'aktif','Pendapatan BPJS 2026','2026-10-17 19:49:57','2026-10-17 19:49:57'),(11,2026,'4.1.03',0.00,750000000.00,0.00,215000000.00,215000000.00,'aktif','Pendapatan PKS 2026','2026-10-17 19:49:57','2026-10-17 19:49:57'),(12,2025,'1.1.01.01',10000000.00,450000000.00,320000000.00,305000000.00,25000000.00,'tutup_buku','Kasir Loket 2025 Tutup Buku','2026-10-17 19:49:57','2026-10-17 19:49:57'),(13,2025,'1.1.02.01',180000000.00,1200000000.00,950000000.00,980000000.00,150000000.00,'tutup_buku','BRK Syariah 2025 Tutup Buku','2026-10-17 19:49:57','2026-10-17 19:49:57'),(14,2025,'4.1.01',0.00,700000000.00,0.00,465000000.00,465000000.00,'tutup_buku','Pendapatan Mandiri 2025 Tutup Buku','2026-10-17 19:49:57','2026-10-17 19:49:57');
/*!40000 ALTER TABLE `rekening_tahun` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `can_view` tinyint(1) NOT NULL DEFAULT 0,
  `can_create` tinyint(1) NOT NULL DEFAULT 0,
  `can_edit` tinyint(1) NOT NULL DEFAULT 0,
  `can_delete` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_role_menu` (`role_id`,`menu_id`),
  KEY `fk_perm_menu` (`menu_id`),
  CONSTRAINT `fk_perm_menu` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_perm_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
INSERT INTO `role_permissions` VALUES (1,1,1,1,1,1,1),(2,1,2,1,1,1,1),(3,1,3,1,1,1,1),(4,1,4,1,1,1,1),(5,1,5,1,1,1,1),(6,1,6,1,1,1,1),(7,1,7,1,1,1,1),(8,1,8,1,1,1,1),(9,1,9,1,1,1,1),(10,1,10,1,1,1,1),(11,1,11,1,1,1,1),(12,1,12,1,1,1,1),(13,1,13,1,1,1,1),(14,1,14,1,1,1,1),(15,2,1,1,0,0,0),(16,2,4,1,0,1,0),(17,2,5,1,1,1,0),(18,2,6,1,0,0,0),(19,3,1,1,0,0,0),(20,3,3,1,0,0,0),(21,3,4,1,1,1,0),(22,3,5,1,0,0,0),(23,3,6,1,1,0,0),(24,4,1,1,0,0,0),(25,4,2,1,1,1,0),(26,4,3,1,1,1,0),(27,4,6,1,1,0,0),(28,1,15,1,1,1,1),(29,2,15,1,0,0,0),(30,3,15,1,0,0,0),(31,4,15,1,1,1,1),(32,1,16,1,1,1,1),(33,1,17,1,1,1,1),(34,2,8,1,0,0,0),(35,2,16,1,0,0,0),(36,2,17,1,0,0,0),(37,3,8,1,0,0,0),(38,3,16,1,0,0,0),(39,3,17,1,0,0,0),(40,4,8,1,1,1,0),(41,4,16,1,1,1,0),(42,4,17,1,1,1,0),(44,1,18,1,1,1,1),(45,1,19,1,1,1,1),(46,1,20,1,1,1,1),(47,1,21,1,1,1,1),(48,1,22,1,1,1,1),(49,1,23,1,1,1,1),(50,1,24,1,1,1,1),(51,2,2,1,0,0,0),(52,3,2,1,0,0,0),(62,1,25,1,1,1,1),(63,1,26,1,1,1,1),(79,1,27,1,1,1,1),(80,1,28,1,1,1,1),(81,1,29,1,1,1,1),(82,1,30,1,1,1,1),(83,1,31,1,1,1,1),(84,2,31,1,0,1,0);
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_name` (`role_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'admin','Administrator Sistem','Akses penuh seluruh konfigurasi dan data LIS','2026-10-16 18:00:07'),(2,'dokter','Dokter Penanggung Jawab','Validasi & otorisasi hasil lab, ekspertise klinis','2026-10-16 18:00:07'),(3,'analis','Analis Laboratorium','Pengerjaan sampel lab, input hasil, kontrol mutu','2026-10-16 18:00:07'),(4,'registrasi','Petugas Registrasi','Pendaftaran pasien, billing & order pemeriksaan','2026-10-16 18:00:07');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `setting_approval_hasil`
--

DROP TABLE IF EXISTS `setting_approval_hasil`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `setting_approval_hasil` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `urutan` int(11) NOT NULL,
  `pegawai_id` int(11) NOT NULL,
  `jabatan_approval` varchar(150) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_urutan` (`urutan`),
  KEY `idx_pegawai` (`pegawai_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `setting_approval_hasil`
--

LOCK TABLES `setting_approval_hasil` WRITE;
/*!40000 ALTER TABLE `setting_approval_hasil` DISABLE KEYS */;
INSERT INTO `setting_approval_hasil` VALUES (1,1,3,'Analis Pemeriksa Lab (ATLM)','Verifikasi hasil pengujian analitik alat dan pengerjaan duplo/manual',1,1,'2026-10-18 23:59:41','2026-10-18 19:42:25'),(2,2,2,'Koordinator Teknis & Mutu Lab','Verifikasi kendali mutu (QC/Kalibrasi) dan kelayakan hasil pra-analitik/analitik',1,1,'2026-10-18 23:59:41','2026-10-18 23:59:41'),(3,3,10,'Dokter Penanggung Jawab Sp.PK','Evaluasi korelasi klinis, evaluasi flag kritis, dan ekspertise patologi klinik',1,1,'2026-10-18 23:59:41','2026-10-18 23:59:41'),(4,4,1,'Kepala UPT Laboratorium Kesehatan','Pengesahan dan pelepasan (release) resmi sertifikat hasil uji kepada customer',1,1,'2026-10-18 23:59:41','2026-10-19 00:03:06'),(5,5,5,'Penanggung Jawab Kesehatan Lingkungan','Verifikasi standar biosafety dan validasi baku mutu sampel lingkungan',1,1,'2026-10-18 19:02:55','2026-10-19 00:03:06'),(6,6,2,'Kepala UPT Laboratorium Kesehatan','nnknk',1,1,'2026-10-18 19:42:11','2026-10-18 19:42:11');
/*!40000 ALTER TABLE `setting_approval_hasil` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `fk_users_role` (`role_id`),
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,1,'admin','$2y$10$HAn4kxwG4FBpG7Jztot7aezrfRlhfPt4wiOnSXcFX6/LZUSWU3bjO','Administrator LIS','admin@lispro.local','081234567890',1,'2026-10-18 19:12:21','2026-10-16 18:00:07','2026-10-19 00:12:21'),(2,2,'dokter','$2y$10$HAn4kxwG4FBpG7Jztot7aezrfRlhfPt4wiOnSXcFX6/LZUSWU3bjO','dr. Budi Santoso, Sp.PK','dokter.budi@lispro.local','081234567891',1,'2026-10-18 19:13:36','2026-10-16 18:00:07','2026-10-19 00:13:36'),(3,3,'analis','$2y$10$HAn4kxwG4FBpG7Jztot7aezrfRlhfPt4wiOnSXcFX6/LZUSWU3bjO','Siti Rahmawati, A.Md.AK','siti.analis@lispro.local','081234567892',1,'2026-10-17 11:34:40','2026-10-16 18:00:07','2026-10-17 16:34:40'),(4,4,'petugas','$2y$10$HAn4kxwG4FBpG7Jztot7aezrfRlhfPt4wiOnSXcFX6/LZUSWU3bjO','Dewi Kartika','dewi.reg@lispro.local','081234567893',1,'2026-10-16 14:43:51','2026-10-16 18:00:07','2026-10-16 19:43:51');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-19 21:35:21
