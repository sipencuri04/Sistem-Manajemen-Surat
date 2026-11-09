-- =============================================================================
-- SISTEM MANAJEMEN PERSURATAN SEKOLAH - DATABASE LENGKAP SIAP PAKAI CPANEL
-- Versi: 3.2 (Mendukung 5 Role, Disposisi, Surat Masuk, Persetujuan, SMTP Gmail)
-- =============================================================================
-- CARA IMPOR DI CPANEL:
-- 1. Buat database baru di cPanel (misal: cpaneluser_persuratan).
-- 2. Buat user database & password, lalu hubungkan dengan hak ALL PRIVILEGES.
-- 3. Buka phpMyAdmin di cPanel -> KLIK NAMA DATABASE Anda di panel kiri.
-- 4. Klik tab "Import" -> Pilih file ini -> Klik "Kirim" / "Go".
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET time_zone = '+07:00';

-- Hapus tabel jika sudah ada (urutan aman)
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `disposition_activities`;
DROP TABLE IF EXISTS `disposition_recipients`;
DROP TABLE IF EXISTS `dispositions`;
DROP TABLE IF EXISTS `incoming_letters`;
DROP TABLE IF EXISTS `letter_requests`;
DROP TABLE IF EXISTS `letter_files`;
DROP TABLE IF EXISTS `letters`;
DROP TABLE IF EXISTS `letter_counters`;
DROP TABLE IF EXISTS `letter_templates`;
DROP TABLE IF EXISTS `letter_types`;
DROP TABLE IF EXISTS `school_settings`;
DROP TABLE IF EXISTS `users`;

-- -----------------------------------------------------------------------------
-- 1. users: akun pengguna sistem (5 role)
-- -----------------------------------------------------------------------------
CREATE TABLE `users` (
  `id`              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `username`        VARCHAR(50)      NOT NULL,
  `password_hash`   VARCHAR(255)     NOT NULL COMMENT 'password_hash() bcrypt',
  `full_name`       VARCHAR(150)     NOT NULL,
  `nip`             VARCHAR(30)      NULL DEFAULT NULL,
  `position`        VARCHAR(100)     NULL DEFAULT NULL COMMENT 'Jabatan',
  `phone`           VARCHAR(30)      NULL DEFAULT NULL,
  `email`           VARCHAR(150)     NULL DEFAULT NULL,
  `role`            VARCHAR(30)      NOT NULL DEFAULT 'tu' COMMENT 'superuser | kepala_sekolah | tu | pegawai | admin',
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
-- 2. school_settings: identitas sekolah, penomoran, & konfigurasi SMTP Gmail
-- -----------------------------------------------------------------------------
CREATE TABLE `school_settings` (
  `id`               TINYINT UNSIGNED NOT NULL,
  `kop_line1`        VARCHAR(200) NOT NULL DEFAULT '',
  `kop_line2`        VARCHAR(200) NOT NULL DEFAULT '',
  `school_name`      VARCHAR(200) NOT NULL DEFAULT '',
  `school_code`      VARCHAR(30)  NOT NULL DEFAULT '',
  `npsn`             VARCHAR(20)  NOT NULL DEFAULT '',
  `address`          VARCHAR(500) NOT NULL DEFAULT '',
  `city`             VARCHAR(100) NOT NULL DEFAULT '',
  `postal_code`      VARCHAR(10)  NOT NULL DEFAULT '',
  `phone`            VARCHAR(50)  NOT NULL DEFAULT '',
  `email`            VARCHAR(150) NOT NULL DEFAULT '',
  `website`          VARCHAR(150) NOT NULL DEFAULT '',
  `logo_path`        VARCHAR(255) NULL DEFAULT NULL,
  `logo_right_path`  VARCHAR(255) NULL DEFAULT NULL,
  `kop_template`     VARCHAR(30)  NOT NULL DEFAULT 'logo_kiri',
  `headmaster_name`  VARCHAR(150) NOT NULL DEFAULT '',
  `headmaster_nip`   VARCHAR(30)  NOT NULL DEFAULT '',
  `signer_title`     VARCHAR(150) NOT NULL DEFAULT 'Kepala Sekolah',
  `show_nip`         TINYINT(1)   NOT NULL DEFAULT 1,
  `signature_path`   VARCHAR(255) NULL DEFAULT NULL,
  `stamp_path`       VARCHAR(255) NULL DEFAULT NULL,
  `number_format`    VARCHAR(150) NOT NULL DEFAULT '{NO}/{KODE}/{BULAN_ROMAWI}/{TAHUN}',
  `number_padding`   TINYINT UNSIGNED NOT NULL DEFAULT 3,
  `number_reset`     VARCHAR(10)  NOT NULL DEFAULT 'yearly',
  `number_scope`     VARCHAR(10)  NOT NULL DEFAULT 'global',
  `letter_year`      SMALLINT UNSIGNED NULL DEFAULT NULL,
  `smtp_enabled`     TINYINT(1)   NOT NULL DEFAULT 1 COMMENT '1 = aktifkan email notifikasi',
  `smtp_host`        VARCHAR(150) NULL DEFAULT 'smtp.gmail.com',
  `smtp_port`        INT(5)       NOT NULL DEFAULT 587,
  `smtp_crypto`      VARCHAR(10)  NOT NULL DEFAULT 'tls',
  `smtp_user`        VARCHAR(150) NULL DEFAULT NULL COMMENT 'Gmail TU pengirim',
  `smtp_pass`        VARCHAR(255) NULL DEFAULT NULL COMMENT 'App Password 16 karakter',
  `smtp_from_email`  VARCHAR(150) NULL DEFAULT NULL,
  `smtp_from_name`   VARCHAR(150) NULL DEFAULT NULL,
  `updated_at`       DATETIME     NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 3. letter_types: master jenis surat
-- -----------------------------------------------------------------------------
CREATE TABLE `letter_types` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code`          VARCHAR(20)  NOT NULL,
  `name`          VARCHAR(150) NOT NULL,
  `template_key`  VARCHAR(50)  NOT NULL,
  `description`   VARCHAR(255) NULL DEFAULT NULL,
  `is_active`     TINYINT(1)   NOT NULL DEFAULT 1,
  `allow_public`  TINYINT(1)   NOT NULL DEFAULT 0 COMMENT '1 = boleh diajukan siswa online',
  `sort_order`    SMALLINT     NOT NULL DEFAULT 0,
  `created_at`    DATETIME     NOT NULL,
  `updated_at`    DATETIME     NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_letter_types_code` (`code`),
  KEY `idx_letter_types_active` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 4. letter_templates: teks pembuka, isi, dan penutup per jenis surat
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
-- 5. letter_counters: penghitung nomor surat urut anti-duplikasi
-- -----------------------------------------------------------------------------
CREATE TABLE `letter_counters` (
  `scope_key`    VARCHAR(30) NOT NULL,
  `period_key`   VARCHAR(10) NOT NULL,
  `last_number`  INT UNSIGNED NOT NULL DEFAULT 0,
  `updated_at`   DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`scope_key`, `period_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 6. letters: arsip surat keluar utama
-- -----------------------------------------------------------------------------
CREATE TABLE `letters` (
  `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `letter_type_id`     INT UNSIGNED NOT NULL,
  `letter_number`      VARCHAR(100) NOT NULL,
  `number_mode`        VARCHAR(10)  NOT NULL DEFAULT 'auto',
  `sequence_no`        INT UNSIGNED NULL DEFAULT NULL,
  `number_year`        SMALLINT UNSIGNED NOT NULL,
  `letter_date`        DATE         NOT NULL,
  `place`              VARCHAR(100) NOT NULL DEFAULT '',
  `subject`            VARCHAR(255) NOT NULL,
  `attachment_text`    VARCHAR(100) NULL DEFAULT NULL,
  `recipient`          VARCHAR(255) NULL DEFAULT NULL,
  `recipient_address`  VARCHAR(255) NULL DEFAULT NULL,
  `content_data`       JSON         NULL,
  `signer_name`        VARCHAR(150) NOT NULL DEFAULT '',
  `signer_nip`         VARCHAR(30)  NOT NULL DEFAULT '',
  `signer_title`       VARCHAR(150) NOT NULL DEFAULT '',
  `status`             VARCHAR(10)  NOT NULL DEFAULT 'draft',
  `signature_type`     VARCHAR(20)  NOT NULL DEFAULT 'digital',
  `approval_status`    VARCHAR(30)  NOT NULL DEFAULT 'disetujui' COMMENT 'menunggu_persetujuan | disetujui | ditolak',
  `approved_by`        INT UNSIGNED NULL DEFAULT NULL,
  `approved_at`        DATETIME     NULL DEFAULT NULL,
  `rejection_reason`   TEXT         NULL DEFAULT NULL,
  `stamp_type`         VARCHAR(20)  NOT NULL DEFAULT 'digital' COMMENT 'digital | basah | belum_stempel',
  `stamped_by`         INT UNSIGNED NULL DEFAULT NULL,
  `stamped_at`         DATETIME     NULL DEFAULT NULL,
  `notes`              VARCHAR(500) NULL DEFAULT NULL,
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
-- 7. letter_files: metadata berkas fisik surat & lampiran
-- -----------------------------------------------------------------------------
CREATE TABLE `letter_files` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `letter_id`      INT UNSIGNED NOT NULL,
  `file_type`      VARCHAR(20)  NOT NULL DEFAULT 'pdf',
  `file_path`      VARCHAR(255) NOT NULL,
  `original_name`  VARCHAR(255) NOT NULL,
  `mime_type`      VARCHAR(100) NOT NULL DEFAULT 'application/pdf',
  `file_size`      INT UNSIGNED NOT NULL DEFAULT 0,
  `checksum`       CHAR(64)     NULL DEFAULT NULL,
  `created_by`     INT UNSIGNED NULL DEFAULT NULL,
  `created_at`     DATETIME     NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_letter_files_letter` (`letter_id`, `file_type`),
  CONSTRAINT `fk_letter_files_letter` FOREIGN KEY (`letter_id`)
    REFERENCES `letters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 8. letter_requests: permohonan surat mandiri dari siswa (online)
-- -----------------------------------------------------------------------------
CREATE TABLE `letter_requests` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tracking_code`    CHAR(10)     NOT NULL,
  `letter_type_id`   INT UNSIGNED NOT NULL,
  `requester_name`   VARCHAR(150) NOT NULL,
  `requester_class`  VARCHAR(50)  NOT NULL DEFAULT '',
  `requester_phone`  VARCHAR(20)  NOT NULL,
  `requester_email`  VARCHAR(150) NULL DEFAULT NULL COMMENT 'Alamat Gmail/Email Siswa',
  `content_data`     JSON         NULL,
  `requester_note`   VARCHAR(500) NULL DEFAULT NULL,
  `status`           VARCHAR(20)  NOT NULL DEFAULT 'pending' COMMENT 'pending | processed | approved | taken | rejected',
  `admin_note`       VARCHAR(500) NULL DEFAULT NULL,
  `letter_id`        INT UNSIGNED NULL DEFAULT NULL,
  `ip_address`       VARCHAR(45)  NOT NULL DEFAULT '',
  `processed_by`     INT UNSIGNED NULL DEFAULT NULL,
  `processed_at`     DATETIME     NULL DEFAULT NULL,
  `created_at`       DATETIME     NOT NULL,
  `updated_at`       DATETIME     NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_letter_requests_code` (`tracking_code`),
  KEY `idx_letter_requests_status` (`status`, `created_at`),
  KEY `idx_letter_requests_ip` (`ip_address`, `created_at`),
  KEY `idx_letter_requests_phone` (`requester_phone`, `status`),
  KEY `fk_letter_requests_type` (`letter_type_id`),
  KEY `fk_letter_requests_letter` (`letter_id`),
  KEY `fk_letter_requests_user` (`processed_by`),
  CONSTRAINT `fk_letter_requests_type` FOREIGN KEY (`letter_type_id`)
    REFERENCES `letter_types` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_letter_requests_letter` FOREIGN KEY (`letter_id`)
    REFERENCES `letters` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_letter_requests_user` FOREIGN KEY (`processed_by`)
    REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 9. incoming_letters: pencatatan buku agenda surat masuk
-- -----------------------------------------------------------------------------
CREATE TABLE `incoming_letters` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `agenda_number`    VARCHAR(50)  NOT NULL,
  `letter_number`    VARCHAR(100) NOT NULL,
  `letter_date`      DATE         NOT NULL,
  `received_date`    DATE         NOT NULL,
  `sender`           VARCHAR(200) NOT NULL,
  `recipient`        VARCHAR(200) NULL DEFAULT NULL,
  `subject`          VARCHAR(255) NOT NULL,
  `description`      TEXT         NULL DEFAULT NULL,
  `file_path`        VARCHAR(255) NULL DEFAULT NULL,
  `file_name`        VARCHAR(255) NULL DEFAULT NULL,
  `status`           VARCHAR(20)  NOT NULL DEFAULT 'baru',
  `is_external`      TINYINT(1)   NOT NULL DEFAULT 0,
  `tracking_code`    VARCHAR(20)  NULL DEFAULT NULL,
  `sender_phone`     VARCHAR(30)  NULL DEFAULT NULL,
  `sender_email`     VARCHAR(100) NULL DEFAULT NULL,
  `created_by`       INT UNSIGNED NULL DEFAULT NULL,
  `created_at`       DATETIME     NOT NULL,
  `updated_at`       DATETIME     NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_incoming_agenda` (`agenda_number`),
  KEY `idx_incoming_received` (`received_date`),
  KEY `idx_incoming_status` (`status`),
  KEY `idx_incoming_tracking` (`tracking_code`),
  KEY `fk_incoming_user` (`created_by`),
  CONSTRAINT `fk_incoming_user` FOREIGN KEY (`created_by`)
    REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 10. dispositions: lembar disposisi instruksi Kepala Sekolah
-- -----------------------------------------------------------------------------
CREATE TABLE `dispositions` (
  `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `incoming_letter_id` INT UNSIGNED NULL DEFAULT NULL,
  `outgoing_letter_id` INT UNSIGNED NULL DEFAULT NULL,
  `disposition_number` VARCHAR(50)  NULL DEFAULT NULL,
  `disposition_date`   DATE         NOT NULL,
  `deadline_date`      DATE         NULL DEFAULT NULL,
  `priority`           VARCHAR(20)  NOT NULL DEFAULT 'biasa',
  `instruction`        TEXT         NOT NULL,
  `notes`              TEXT         NULL DEFAULT NULL,
  `status`             VARCHAR(20)  NOT NULL DEFAULT 'menunggu',
  `created_by`         INT UNSIGNED NOT NULL,
  `created_at`         DATETIME     NOT NULL,
  `updated_at`         DATETIME     NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_disposition_deadline` (`deadline_date`),
  KEY `idx_disposition_status` (`status`),
  KEY `fk_disposition_incoming` (`incoming_letter_id`),
  KEY `fk_disposition_outgoing` (`outgoing_letter_id`),
  KEY `fk_disposition_creator` (`created_by`),
  CONSTRAINT `fk_disposition_incoming` FOREIGN KEY (`incoming_letter_id`)
    REFERENCES `incoming_letters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_disposition_outgoing` FOREIGN KEY (`outgoing_letter_id`)
    REFERENCES `letters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_disposition_creator` FOREIGN KEY (`created_by`)
    REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 11. disposition_recipients: penerima disposisi & tindak lanjut pegawai
-- -----------------------------------------------------------------------------
CREATE TABLE `disposition_recipients` (
  `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `disposition_id`     INT UNSIGNED NOT NULL,
  `user_id`            INT UNSIGNED NOT NULL,
  `status`             VARCHAR(30)  NOT NULL DEFAULT 'menunggu',
  `progress_percent`   TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `progress_notes`     TEXT         NULL DEFAULT NULL,
  `evidence_file`      VARCHAR(255) NULL DEFAULT NULL,
  `evidence_name`      VARCHAR(255) NULL DEFAULT NULL,
  `completed_at`       DATETIME     NULL DEFAULT NULL,
  `delegated_to`       INT UNSIGNED NULL DEFAULT NULL,
  `delegation_reason`  TEXT         NULL DEFAULT NULL,
  `delegated_at`       DATETIME     NULL DEFAULT NULL,
  `created_at`         DATETIME     NOT NULL,
  `updated_at`         DATETIME     NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_disp_recipient` (`disposition_id`, `user_id`),
  KEY `idx_disp_recip_status` (`status`),
  KEY `fk_disp_recip_disp` (`disposition_id`),
  KEY `fk_disp_recip_user` (`user_id`),
  KEY `fk_disp_recip_delegated` (`delegated_to`),
  CONSTRAINT `fk_disp_recip_disp` FOREIGN KEY (`disposition_id`)
    REFERENCES `dispositions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_disp_recip_user` FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_disp_recip_delegated` FOREIGN KEY (`delegated_to`)
    REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 12. disposition_activities: riwayat timeline perjalanan disposisi
-- -----------------------------------------------------------------------------
CREATE TABLE `disposition_activities` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `disposition_id` INT UNSIGNED NOT NULL,
  `user_id`        INT UNSIGNED NULL DEFAULT NULL,
  `action`         VARCHAR(50)  NOT NULL,
  `description`    TEXT         NOT NULL,
  `created_at`     DATETIME     NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_disp_act_disp` (`disposition_id`),
  KEY `fk_disp_act_user` (`user_id`),
  CONSTRAINT `fk_disp_act_disp` FOREIGN KEY (`disposition_id`)
    REFERENCES `dispositions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_disp_act_user` FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 13. notifications: notifikasi lonceng internal
-- -----------------------------------------------------------------------------
CREATE TABLE `notifications` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED NOT NULL,
  `title`      VARCHAR(150) NOT NULL,
  `message`    TEXT         NOT NULL,
  `link`       VARCHAR(255) NULL DEFAULT NULL,
  `type`       VARCHAR(30)  NOT NULL DEFAULT 'info',
  `is_read`    TINYINT(1)   NOT NULL DEFAULT 0,
  `created_at` DATETIME     NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_notif_user_read` (`user_id`, `is_read`, `created_at`),
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 14. audit_logs: catatan rekam jejak keamanan
-- -----------------------------------------------------------------------------
CREATE TABLE `audit_logs` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`     INT UNSIGNED NULL DEFAULT NULL,
  `username`    VARCHAR(50)  NULL DEFAULT NULL,
  `action`      VARCHAR(100) NOT NULL,
  `entity_type` VARCHAR(50)  NULL DEFAULT NULL,
  `entity_id`   INT UNSIGNED NULL DEFAULT NULL,
  `details`     TEXT         NULL DEFAULT NULL,
  `ip_address`  VARCHAR(45)  NULL DEFAULT NULL,
  `user_agent`  VARCHAR(255) NULL DEFAULT NULL,
  `created_at`  DATETIME     NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_audit_user` (`user_id`),
  KEY `idx_audit_action` (`action`),
  KEY `idx_audit_entity` (`entity_type`, `entity_id`),
  KEY `idx_audit_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- DATA AWAL LENGKAP (SEED DATA)
-- =============================================================================

-- 1. Akun pengguna bawaan:
-- Password default:
--   admin         -> admin123
--   superuser     -> superuser123
--   kepsek        -> kepsek123
--   tu            -> tu123
--   pegawai1      -> pegawai123
--   pegawai2      -> pegawai123
INSERT INTO `users` (`id`, `username`, `password_hash`, `full_name`, `nip`, `position`, `phone`, `email`, `role`, `is_active`, `created_at`) VALUES
(1, 'admin',     '$2y$10$YkaSP2XMdQK2mBnsk9FqnuhFV/kmp30oThuspq1mAQDbhoPtCcywe', 'Administrator Sistem', '19800101 200501 1 001', 'Admin TI', '081234567890', 'admin@sekolah.sch.id', 'admin', 1, NOW()),
(2, 'superuser', '$2y$10$y5lWp1z6e6G9lQJ1s1nUeODu09l9mD8Z8y2.uB7mH7qU0P8gS2Z1a', 'Superuser / Pengembang', '19850101 200801 1 002', 'Staff Ahli Sistem', '081234567891', 'superuser@sekolah.sch.id', 'superuser', 1, NOW()),
(3, 'kepsek',    '$2y$10$x8.J41oU1K0G7X9cW9YkXeS7d5vN7b8Y7Z2t3A4r5e6w7q8r9t0y1', 'Drs. H. Ahmad Dahlan, M.Pd', '19680512 199403 1 005', 'Kepala Sekolah', '081234567892', 'kepsek@sekolah.sch.id', 'kepala_sekolah', 1, NOW()),
(4, 'tu',        '$2y$10$w7.K30nT0J9F6W8bV8XjWdR6c4uM6a7X6Y1s2Z3q4d5v6p7q8s9t0', 'Siti Rahmawati, S.AP', '19820715 200604 2 018', 'Koordinator Tata Usaha', '081234567893', 'tu@sekolah.sch.id', 'tu', 1, NOW()),
(5, 'pegawai1',  '$2y$10$v6.L29mS9I8E5V7aU7WiVcQ5b3tL5z6W5X0r1Y2p3c4u5o6p7r8s9', 'Bambang Wijaya, S.Pd', '19850320 200902 1 004', 'Guru / Koordinator Kurikulum', '081234567894', 'bambang@sekolah.sch.id', 'pegawai', 1, NOW()),
(6, 'pegawai2',  '$2y$10$u5.M18lR8H7D4U6zT6VhUbP4a2sK4y5V4W9q0X1o2b3t4n5o6q7r8', 'Dewi Lestari, S.Kom', '19901108 201503 2 007', 'Guru / Ka. Program TKJ', '081234567895', 'dewi@sekolah.sch.id', 'pegawai', 1, NOW());

-- 2. Identitas Sekolah & Template Nomor Bawaan
INSERT INTO `school_settings` (`id`, `signer_title`, `number_format`, `number_padding`, `number_reset`, `number_scope`, `kop_template`, `smtp_enabled`, `smtp_host`, `smtp_port`, `smtp_crypto`, `updated_at`) VALUES
(1, 'Kepala Sekolah', '{NO}/{KODE}/{BULAN_ROMAWI}/{TAHUN}', 3, 'yearly', 'global', 'logo_kiri', 1, 'smtp.gmail.com', 587, 'tls', NOW());

-- 3. Master 16 Jenis Surat
INSERT INTO `letter_types` (`id`, `code`, `name`, `template_key`, `description`, `is_active`, `allow_public`, `sort_order`, `created_at`) VALUES
(1,  'SKA',   'Surat Keterangan Siswa Aktif',            'siswa_aktif',    'Keterangan bahwa siswa masih aktif bersekolah', 1, 1, 1,  NOW()),
(2,  'SKL',   'Surat Keterangan Lulus',                  'lulus',          'Keterangan kelulusan siswa', 1, 1, 2,  NOW()),
(3,  'SKP',   'Surat Keterangan Pindah Sekolah',         'pindah',         'Keterangan pindah sekolah', 1, 0, 3,  NOW()),
(4,  'SPO',   'Surat Panggilan Orang Tua/Wali',          'panggilan_ortu', 'Panggilan orang tua/wali siswa', 1, 0, 4,  NOW()),
(5,  'UND',   'Surat Undangan Rapat',                    'undangan_rapat', 'Undangan rapat', 1, 0, 5,  NOW()),
(6,  'PMH',   'Surat Permohonan',                        'permohonan',     'Permohonan umum', 1, 0, 6,  NOW()),
(7,  'PBT',   'Surat Pemberitahuan',                     'pemberitahuan',  'Pemberitahuan umum', 1, 0, 7,  NOW()),
(8,  'ST',    'Surat Tugas Guru atau Pegawai',           'tugas',          'Penugasan guru/pegawai', 1, 0, 8,  NOW()),
(9,  'PKL',   'Surat Pengantar PKL',                     'pengantar_pkl',  'Pengantar siswa Praktik Kerja Lapangan', 1, 0, 9,  NOW()),
(10, 'PKS',   'Surat Permohonan Kerja Sama Industri',    'kerja_sama',     'Permohonan kerja sama dengan DU-DI', 1, 0, 10, NOW()),
(11, 'SKPKL', 'Surat Keterangan Telah Melaksanakan PKL', 'selesai_pkl',    'Keterangan selesai PKL', 1, 1, 11, NOW()),
(12, 'REK',   'Surat Rekomendasi Siswa',                 'rekomendasi',    'Rekomendasi siswa', 1, 0, 12, NOW()),
(13, 'IZN',   'Surat Izin',                              'izin',           'Pemberian izin', 1, 0, 13, NOW()),
(14, 'SPG',   'Surat Pengantar',                         'pengantar',      'Pengantar dokumen/barang', 1, 0, 14, NOW()),
(15, 'BLS',   'Surat Balasan',                           'balasan',        'Balasan atas surat masuk', 1, 0, 15, NOW()),
(16, 'BA',    'Berita Acara',                            'berita_acara',   'Berita acara kegiatan/kejadian', 1, 0, 16, NOW());

-- 4. Template Teks Bawaan 16 Jenis Surat
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

-- --------------------------------------------------------
-- Tabel Sistem Antrean TU & Display TV Monitor (v10)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `queue_services` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(5) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `loket_number` INT NOT NULL DEFAULT 1,
  `description` VARCHAR(255) NULL DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_queue_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `queue_services` (`id`, `code`, `name`, `loket_number`, `description`, `is_active`, `created_at`) VALUES
(1, 'A', 'Layanan Surat & Legalisir', 1, 'Pengajuan surat keterangan aktif, legalisir ijazah, surat rekomendasi', 1, NOW()),
(2, 'B', 'Keuangan & Administrasi SPP', 2, 'Pembayaran SPP, cek tunggakan, beasiswa, dan administrasi keuangan', 1, NOW()),
(3, 'C', 'Layanan Umum & Mutasi Siswa', 3, 'Layanan tamu pimpinan, konsultasi kesiswaan, mutasi peserta didik', 1, NOW());

CREATE TABLE IF NOT EXISTS `queue_tickets` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `service_id` INT UNSIGNED NOT NULL,
  `ticket_number` VARCHAR(15) NOT NULL,
  `queue_date` DATE NOT NULL,
  `sequence` INT NOT NULL,
  `status` ENUM('waiting', 'calling', 'serving', 'completed', 'skipped') NOT NULL DEFAULT 'waiting',
  `loket_number` INT NULL DEFAULT NULL,
  `called_by` INT UNSIGNED NULL DEFAULT NULL,
  `called_at` DATETIME NULL DEFAULT NULL,
  `completed_at` DATETIME NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_queue_date_status` (`queue_date`, `status`),
  KEY `idx_queue_service` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
