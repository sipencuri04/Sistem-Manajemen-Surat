-- =============================================================================
-- Sistem Manajemen Persuratan SMK - Struktur & Data Awal
-- Kompatibel: MySQL 5.7+ / MySQL 8 / MariaDB 10.2+ (XAMPP, Laragon, shared hosting)
-- Impor melalui phpMyAdmin: pilih database -> tab Import -> pilih file ini.
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET time_zone = '+07:00';

-- Buat database (lewati bagian ini bila database sudah dibuat dari cPanel)
CREATE DATABASE IF NOT EXISTS `db_persuratan_smk`
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_persuratan_smk`;

DROP TABLE IF EXISTS `letter_files`;
DROP TABLE IF EXISTS `letters`;
DROP TABLE IF EXISTS `letter_counters`;
DROP TABLE IF EXISTS `letter_templates`;
DROP TABLE IF EXISTS `letter_types`;
DROP TABLE IF EXISTS `school_settings`;
DROP TABLE IF EXISTS `users`;

-- -----------------------------------------------------------------------------
-- 1. users : akun administrator / Tata Usaha
-- -----------------------------------------------------------------------------
CREATE TABLE `users` (
  `id`              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `username`        VARCHAR(50)      NOT NULL,
  `password_hash`   VARCHAR(255)     NOT NULL COMMENT 'password_hash() PHP (bcrypt)',
  `full_name`       VARCHAR(150)     NOT NULL,
  `email`           VARCHAR(150)     NULL DEFAULT NULL,
  `role`            VARCHAR(30)      NOT NULL DEFAULT 'admin',
  `is_active`       TINYINT(1)       NOT NULL DEFAULT 1,
  `failed_attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `locked_until`    DATETIME         NULL DEFAULT NULL,
  `last_login_at`   DATETIME         NULL DEFAULT NULL,
  `created_at`      DATETIME         NOT NULL,
  `updated_at`      DATETIME         NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 2. school_settings : identitas sekolah & konfigurasi penomoran (1 baris, id=1)
-- -----------------------------------------------------------------------------
CREATE TABLE `school_settings` (
  `id`               TINYINT UNSIGNED NOT NULL,
  `kop_line1`        VARCHAR(200) NOT NULL DEFAULT '' COMMENT 'Baris atas kop, mis. instansi induk',
  `kop_line2`        VARCHAR(200) NOT NULL DEFAULT '' COMMENT 'Baris kedua kop (opsional)',
  `school_name`      VARCHAR(200) NOT NULL DEFAULT '',
  `school_code`      VARCHAR(30)  NOT NULL DEFAULT '' COMMENT 'Kode singkat sekolah utk token {SEKOLAH}',
  `npsn`             VARCHAR(20)  NOT NULL DEFAULT '',
  `address`          VARCHAR(500) NOT NULL DEFAULT '',
  `city`             VARCHAR(100) NOT NULL DEFAULT '' COMMENT 'Tempat penerbitan surat',
  `postal_code`      VARCHAR(10)  NOT NULL DEFAULT '',
  `phone`            VARCHAR(50)  NOT NULL DEFAULT '',
  `email`            VARCHAR(150) NOT NULL DEFAULT '',
  `website`          VARCHAR(150) NOT NULL DEFAULT '',
  `logo_path`        VARCHAR(255) NULL DEFAULT NULL,
  `logo_right_path`  VARCHAR(255) NULL DEFAULT NULL,
  `kop_template`     VARCHAR(30)  NOT NULL DEFAULT 'logo_kiri' COMMENT 'logo_kiri | logo_kiri_kanan | tanpa_logo',
  `headmaster_name`  VARCHAR(150) NOT NULL DEFAULT '',
  `headmaster_nip`   VARCHAR(30)  NOT NULL DEFAULT '',
  `signer_title`     VARCHAR(150) NOT NULL DEFAULT 'Kepala Sekolah',
  `show_nip`         TINYINT(1)   NOT NULL DEFAULT 1,
  `signature_path`   VARCHAR(255) NULL DEFAULT NULL COMMENT 'Gambar tanda tangan resmi (opsional)',
  `stamp_path`       VARCHAR(255) NULL DEFAULT NULL COMMENT 'Gambar stempel resmi (opsional)',
  `number_format`    VARCHAR(150) NOT NULL DEFAULT '{NO}/{KODE}/{BULAN_ROMAWI}/{TAHUN}',
  `number_padding`   TINYINT UNSIGNED NOT NULL DEFAULT 3,
  `number_reset`     VARCHAR(10)  NOT NULL DEFAULT 'yearly' COMMENT 'yearly | monthly | never',
  `number_scope`     VARCHAR(10)  NOT NULL DEFAULT 'global' COMMENT 'global | per_type',
  `letter_year`      SMALLINT UNSIGNED NULL DEFAULT NULL COMMENT 'Tahun surat tetap; NULL = ikut tanggal surat',
  `updated_at`       DATETIME     NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 3. letter_types : master jenis surat
-- -----------------------------------------------------------------------------
CREATE TABLE `letter_types` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code`          VARCHAR(20)  NOT NULL COMMENT 'Kode jenis, dipakai pada token {KODE}',
  `name`          VARCHAR(150) NOT NULL,
  `template_key`  VARCHAR(50)  NOT NULL COMMENT 'Kunci di config/letter_templates.php',
  `description`   VARCHAR(255) NULL DEFAULT NULL,
  `is_active`     TINYINT(1)   NOT NULL DEFAULT 1,
  `sort_order`    SMALLINT     NOT NULL DEFAULT 0,
  `created_at`    DATETIME     NOT NULL,
  `updated_at`    DATETIME     NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_letter_types_code` (`code`),
  KEY `idx_letter_types_active` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 4. letter_templates : teks pembuka/isi/penutup per jenis surat (dapat diedit)
-- -----------------------------------------------------------------------------
CREATE TABLE `letter_templates` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `letter_type_id`  INT UNSIGNED NOT NULL,
  `opening_text`    TEXT NULL,
  `body_text`       TEXT NULL,
  `closing_text`    TEXT NULL,
  `updated_at`      DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_letter_templates_type` (`letter_type_id`),
  CONSTRAINT `fk_letter_templates_type` FOREIGN KEY (`letter_type_id`)
    REFERENCES `letter_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 5. letter_counters : penghitung nomor urut (tidak pernah mundur,
--    sehingga nomor yang sudah terpakai tidak dipakai ulang walau surat dihapus)
-- -----------------------------------------------------------------------------
CREATE TABLE `letter_counters` (
  `scope_key`    VARCHAR(30) NOT NULL COMMENT 'global atau type:<id>',
  `period_key`   VARCHAR(10) NOT NULL COMMENT '2026, 2026-10, atau all',
  `last_number`  INT UNSIGNED NOT NULL DEFAULT 0,
  `updated_at`   DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`scope_key`, `period_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 6. letters : data utama surat
--    Field umum disimpan sebagai kolom terstruktur (dapat dicari & diindeks),
--    field khusus tiap jenis surat disimpan di `content_data` (JSON).
-- -----------------------------------------------------------------------------
CREATE TABLE `letters` (
  `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `letter_type_id`     INT UNSIGNED NOT NULL,
  `letter_number`      VARCHAR(100) NOT NULL,
  `number_mode`        VARCHAR(10)  NOT NULL DEFAULT 'auto' COMMENT 'auto | manual',
  `sequence_no`        INT UNSIGNED NULL DEFAULT NULL,
  `number_year`        SMALLINT UNSIGNED NOT NULL,
  `letter_date`        DATE         NOT NULL,
  `place`              VARCHAR(100) NOT NULL DEFAULT '',
  `subject`            VARCHAR(255) NOT NULL COMMENT 'Perihal',
  `attachment_text`    VARCHAR(100) NULL DEFAULT NULL COMMENT 'Lampiran',
  `recipient`          VARCHAR(255) NULL DEFAULT NULL COMMENT 'Tujuan/penerima',
  `recipient_address`  VARCHAR(255) NULL DEFAULT NULL,
  `content_data`       JSON         NULL,
  `signer_name`        VARCHAR(150) NOT NULL DEFAULT '',
  `signer_nip`         VARCHAR(30)  NOT NULL DEFAULT '',
  `signer_title`       VARCHAR(150) NOT NULL DEFAULT '',
  `status`             VARCHAR(10)  NOT NULL DEFAULT 'draft' COMMENT 'draft | final',
  `notes`              VARCHAR(500) NULL DEFAULT NULL COMMENT 'Catatan internal (tidak dicetak)',
  `created_by`         INT UNSIGNED NULL DEFAULT NULL,
  `updated_by`         INT UNSIGNED NULL DEFAULT NULL,
  `created_at`         DATETIME     NOT NULL,
  `updated_at`         DATETIME     NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_letters_number` (`letter_number`),
  KEY `idx_letters_type_date` (`letter_type_id`, `letter_date`),
  KEY `idx_letters_date` (`letter_date`),
  KEY `idx_letters_year` (`number_year`),
  KEY `idx_letters_subject` (`subject`(100)),
  KEY `idx_letters_status` (`status`),
  KEY `fk_letters_created_by` (`created_by`),
  KEY `fk_letters_updated_by` (`updated_by`),
  CONSTRAINT `fk_letters_type` FOREIGN KEY (`letter_type_id`)
    REFERENCES `letter_types` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_letters_created_by` FOREIGN KEY (`created_by`)
    REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_letters_updated_by` FOREIGN KEY (`updated_by`)
    REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 7. letter_files : metadata file PDF arsip / lampiran
--    File fisik disimpan di folder storage/ (dilindungi .htaccess),
--    database hanya menyimpan path relatifnya.
-- -----------------------------------------------------------------------------
CREATE TABLE `letter_files` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `letter_id`      INT UNSIGNED NOT NULL,
  `file_type`      VARCHAR(20)  NOT NULL DEFAULT 'pdf' COMMENT 'pdf | lampiran',
  `file_path`      VARCHAR(255) NOT NULL COMMENT 'Relatif terhadap folder storage/',
  `original_name`  VARCHAR(255) NOT NULL,
  `mime_type`      VARCHAR(100) NOT NULL DEFAULT 'application/pdf',
  `file_size`      INT UNSIGNED NOT NULL DEFAULT 0,
  `checksum`       CHAR(64)     NULL DEFAULT NULL COMMENT 'SHA-256',
  `created_by`     INT UNSIGNED NULL DEFAULT NULL,
  `created_at`     DATETIME     NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_letter_files_letter` (`letter_id`, `file_type`),
  CONSTRAINT `fk_letter_files_letter` FOREIGN KEY (`letter_id`)
    REFERENCES `letters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- DATA AWAL
-- =============================================================================

-- Akun administrator contoh -> username: admin | password: admin123
-- WAJIB diganti setelah login pertama (menu Pengaturan Akun).
INSERT INTO `users` (`id`, `username`, `password_hash`, `full_name`, `email`, `role`, `is_active`, `created_at`) VALUES
(1, 'admin', '$2y$10$YkaSP2XMdQK2mBnsk9FqnuhFV/kmp30oThuspq1mAQDbhoPtCcywe', 'Administrator Tata Usaha', NULL, 'admin', 1, NOW());

-- Identitas sekolah sengaja dikosongkan: isi melalui menu Pengaturan Sekolah.
INSERT INTO `school_settings` (`id`, `signer_title`, `number_format`, `number_padding`, `number_reset`, `number_scope`, `kop_template`, `updated_at`) VALUES
(1, 'Kepala Sekolah', '{NO}/{KODE}/{BULAN_ROMAWI}/{TAHUN}', 3, 'yearly', 'global', 'logo_kiri', NOW());

INSERT INTO `letter_types` (`id`, `code`, `name`, `template_key`, `description`, `is_active`, `sort_order`, `created_at`) VALUES
(1,  'SKA',  'Surat Keterangan Siswa Aktif',            'siswa_aktif',    'Keterangan bahwa siswa masih aktif bersekolah', 1, 1,  NOW()),
(2,  'SKL',  'Surat Keterangan Lulus',                  'lulus',          'Keterangan kelulusan siswa', 1, 2,  NOW()),
(3,  'SKP',  'Surat Keterangan Pindah Sekolah',         'pindah',         'Keterangan pindah sekolah', 1, 3,  NOW()),
(4,  'SPO',  'Surat Panggilan Orang Tua/Wali',          'panggilan_ortu', 'Panggilan orang tua/wali siswa', 1, 4,  NOW()),
(5,  'UND',  'Surat Undangan Rapat',                    'undangan_rapat', 'Undangan rapat', 1, 5,  NOW()),
(6,  'PMH',  'Surat Permohonan',                        'permohonan',     'Permohonan umum', 1, 6,  NOW()),
(7,  'PBT',  'Surat Pemberitahuan',                     'pemberitahuan',  'Pemberitahuan umum', 1, 7,  NOW()),
(8,  'ST',   'Surat Tugas Guru atau Pegawai',           'tugas',          'Penugasan guru/pegawai', 1, 8,  NOW()),
(9,  'PKL',  'Surat Pengantar PKL',                     'pengantar_pkl',  'Pengantar siswa Praktik Kerja Lapangan', 1, 9,  NOW()),
(10, 'PKS',  'Surat Permohonan Kerja Sama Industri',    'kerja_sama',     'Permohonan kerja sama dengan DU-DI', 1, 10, NOW()),
(11, 'SKPKL','Surat Keterangan Telah Melaksanakan PKL', 'selesai_pkl',    'Keterangan selesai PKL', 1, 11, NOW()),
(12, 'REK',  'Surat Rekomendasi Siswa',                 'rekomendasi',    'Rekomendasi siswa', 1, 12, NOW()),
(13, 'IZN',  'Surat Izin',                              'izin',           'Pemberian izin', 1, 13, NOW()),
(14, 'SPG',  'Surat Pengantar',                         'pengantar',      'Pengantar dokumen/barang', 1, 14, NOW()),
(15, 'BLS',  'Surat Balasan',                           'balasan',        'Balasan atas surat masuk', 1, 15, NOW()),
(16, 'BA',   'Berita Acara',                            'berita_acara',   'Berita acara kegiatan/kejadian', 1, 16, NOW());

INSERT INTO `letter_templates` (`letter_type_id`, `opening_text`, `body_text`, `closing_text`, `updated_at`) VALUES
(1,  'Yang bertanda tangan di bawah ini, {jabatan_penandatangan} {nama_sekolah}, menerangkan dengan sebenarnya bahwa:',
     'adalah benar siswa {nama_sekolah} yang masih aktif mengikuti kegiatan belajar mengajar pada Tahun Pelajaran {tahun_pelajaran}. Surat keterangan ini dibuat untuk keperluan {keperluan}.',
     'Demikian surat keterangan ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.', NOW()),
(2,  'Yang bertanda tangan di bawah ini, {jabatan_penandatangan} {nama_sekolah}, menerangkan bahwa:',
     'telah menyelesaikan pendidikan di {nama_sekolah} pada Tahun Pelajaran {tahun_pelajaran} dan dinyatakan LULUS terhitung sejak tanggal {tanggal_kelulusan}.',
     'Demikian surat keterangan ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.', NOW()),
(3,  'Yang bertanda tangan di bawah ini, {jabatan_penandatangan} {nama_sekolah}, menerangkan bahwa:',
     'Siswa tersebut di atas mengajukan pindah sekolah ke {sekolah_tujuan} dengan alasan {alasan_pindah}, terhitung mulai tanggal {tanggal_efektif}. Sejak tanggal tersebut, hak dan kewajiban siswa yang bersangkutan di {nama_sekolah} dinyatakan berakhir.',
     'Demikian surat keterangan ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.', NOW()),
(4,  'Dengan hormat,\nSehubungan dengan adanya hal yang perlu dibicarakan mengenai putra/putri Bapak/Ibu:',
     'kami mengharapkan kehadiran Bapak/Ibu Orang Tua/Wali siswa di sekolah pada:',
     'Mengingat pentingnya hal tersebut, kami sangat mengharapkan kehadiran Bapak/Ibu tepat pada waktunya. Atas perhatian dan kerja samanya, kami ucapkan terima kasih.', NOW()),
(5,  'Dengan hormat,\nKami mengharapkan kehadiran Bapak/Ibu pada rapat yang akan dilaksanakan pada:',
     '',
     'Mengingat pentingnya acara tersebut, kami mohon Bapak/Ibu dapat hadir tepat pada waktunya. Atas perhatian dan kehadirannya, kami ucapkan terima kasih.', NOW()),
(6,  'Dengan hormat,', '',
     'Demikian surat permohonan ini kami sampaikan. Atas perhatian dan kerja sama Bapak/Ibu, kami ucapkan terima kasih.', NOW()),
(7,  'Dengan hormat,', '',
     'Demikian pemberitahuan ini kami sampaikan. Atas perhatiannya, kami ucapkan terima kasih.', NOW()),
(8,  '{jabatan_penandatangan} {nama_sekolah} dengan ini memberikan tugas kepada:', '',
     'Demikian surat tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab dan agar melaporkan hasilnya setelah tugas selesai.', NOW()),
(9,  'Dengan hormat,\nDalam rangka pelaksanaan Praktik Kerja Lapangan (PKL) bagi siswa {nama_sekolah}, bersama ini kami mengirimkan siswa sebagai berikut:',
     'untuk melaksanakan PKL di {nama_instansi} mulai tanggal {tanggal_mulai} sampai dengan {tanggal_selesai}.',
     'Kami mohon Bapak/Ibu berkenan menerima dan membimbing siswa tersebut selama pelaksanaan PKL. Atas perhatian dan kerja samanya, kami ucapkan terima kasih.', NOW()),
(10, 'Dengan hormat,\nDalam rangka meningkatkan mutu pendidikan serta keselarasan kompetensi lulusan dengan kebutuhan dunia usaha dan dunia industri, {nama_sekolah} bermaksud mengajukan permohonan kerja sama dengan {nama_instansi} dalam bentuk:',
     '',
     'Besar harapan kami agar permohonan ini dapat diterima. Atas perhatian dan kerja samanya, kami ucapkan terima kasih.', NOW()),
(11, 'Yang bertanda tangan di bawah ini, {jabatan_penandatangan} {nama_sekolah}, menerangkan bahwa:',
     'telah melaksanakan Praktik Kerja Lapangan (PKL) di {nama_instansi} mulai tanggal {tanggal_mulai} sampai dengan {tanggal_selesai}.',
     'Demikian surat keterangan ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.', NOW()),
(12, 'Yang bertanda tangan di bawah ini, {jabatan_penandatangan} {nama_sekolah}, memberikan rekomendasi kepada:',
     'Siswa tersebut di atas kami rekomendasikan untuk {tujuan_rekomendasi}.',
     'Demikian surat rekomendasi ini dibuat untuk dapat dipergunakan sebagaimana mestinya.', NOW()),
(13, 'Yang bertanda tangan di bawah ini, {jabatan_penandatangan} {nama_sekolah}, memberikan izin kepada:', '',
     'Demikian surat izin ini dibuat untuk dapat dipergunakan sebagaimana mestinya.', NOW()),
(14, 'Dengan hormat,\nBersama ini kami kirimkan kepada Bapak/Ibu:', '',
     'Demikian surat pengantar ini kami sampaikan untuk dapat diterima dan dipergunakan sebagaimana mestinya.', NOW()),
(15, 'Dengan hormat,\nMenindaklanjuti surat Bapak/Ibu Nomor {nomor_surat_rujukan} tanggal {tanggal_surat_rujukan} perihal {perihal_rujukan}, dengan ini kami sampaikan hal-hal sebagai berikut:', '',
     'Demikian surat balasan ini kami sampaikan. Atas perhatian dan kerja samanya, kami ucapkan terima kasih.', NOW()),
(16, 'Pada hari ini, {hari_tanggal}, pukul {waktu}, bertempat di {tempat}, telah dilaksanakan {nama_kegiatan} dengan uraian sebagai berikut:', '',
     'Demikian berita acara ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.', NOW());
