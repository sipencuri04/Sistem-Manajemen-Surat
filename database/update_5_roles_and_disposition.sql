-- =============================================================================
-- MIGRATION: Sistem 5 Role, Surat Masuk, Disposisi & Delegasi,
--            Notifikasi Internal, dan Audit Log
-- =============================================================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Penyesuaian tabel users (tambah profil & role tambahan)
ALTER TABLE `users`
  MODIFY COLUMN `role` VARCHAR(30) NOT NULL DEFAULT 'tu' COMMENT 'superuser | kepala_sekolah | tu | pegawai | admin';

-- Tambah kolom nip, position, phone bila belum ada
-- (Dijalankan secara aman melalui Schema_upgrade.php)

-- 2. Tabel incoming_letters (Surat Masuk)
CREATE TABLE IF NOT EXISTS `incoming_letters` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `agenda_number`    VARCHAR(50)  NOT NULL COMMENT 'Nomor agenda surat masuk',
  `letter_number`    VARCHAR(100) NOT NULL COMMENT 'Nomor surat dari instansi pengirim',
  `letter_date`      DATE         NOT NULL COMMENT 'Tanggal yang tertera di surat',
  `received_date`    DATE         NOT NULL COMMENT 'Tanggal surat diterima di sekolah',
  `sender`           VARCHAR(200) NOT NULL COMMENT 'Asal / pengirim surat',
  `recipient`        VARCHAR(200) NULL DEFAULT NULL COMMENT 'Tujuan / ditujukan kepada',
  `subject`          VARCHAR(255) NOT NULL COMMENT 'Perihal surat',
  `description`      TEXT         NULL DEFAULT NULL COMMENT 'Ringkasan isi surat',
  `file_path`        VARCHAR(255) NULL DEFAULT NULL COMMENT 'Path file scan berkas di storage/',
  `file_name`        VARCHAR(255) NULL DEFAULT NULL COMMENT 'Nama file asli saat diunggah',
  `status`           VARCHAR(20)  NOT NULL DEFAULT 'baru' COMMENT 'baru | diteruskan | didisposisi | selesai | diarsipkan',
  `created_by`       INT UNSIGNED NULL DEFAULT NULL,
  `created_at`       DATETIME     NOT NULL,
  `updated_at`       DATETIME     NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_incoming_agenda` (`agenda_number`),
  KEY `idx_incoming_received` (`received_date`),
  KEY `idx_incoming_status` (`status`),
  KEY `fk_incoming_user` (`created_by`),
  CONSTRAINT `fk_incoming_user` FOREIGN KEY (`created_by`)
    REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabel dispositions (Disposisi Surat oleh Kepala Sekolah)
CREATE TABLE IF NOT EXISTS `dispositions` (
  `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `incoming_letter_id` INT UNSIGNED NULL DEFAULT NULL COMMENT 'Referensi surat masuk (jika ada)',
  `outgoing_letter_id` INT UNSIGNED NULL DEFAULT NULL COMMENT 'Referensi surat keluar (jika ada)',
  `disposition_number` VARCHAR(50)  NULL DEFAULT NULL COMMENT 'Nomor lembar disposisi',
  `disposition_date`   DATE         NOT NULL COMMENT 'Tanggal disposisi diberikan',
  `deadline_date`      DATE         NULL DEFAULT NULL COMMENT 'Tenggat waktu penyelesaian',
  `priority`           VARCHAR(20)  NOT NULL DEFAULT 'biasa' COMMENT 'biasa | penting | segera | rahasia',
  `instruction`        TEXT         NOT NULL COMMENT 'Instruksi kepala sekolah',
  `notes`              TEXT         NULL DEFAULT NULL COMMENT 'Catatan tambahan',
  `status`             VARCHAR(20)  NOT NULL DEFAULT 'menunggu' COMMENT 'menunggu | diproses | selesai | dibatalkan',
  `created_by`         INT UNSIGNED NOT NULL COMMENT 'Kepala sekolah pembuat disposisi',
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

-- 4. Tabel disposition_recipients (Penerima Disposisi & Pelaporan Progres Pegawai)
CREATE TABLE IF NOT EXISTS `disposition_recipients` (
  `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `disposition_id`     INT UNSIGNED NOT NULL,
  `user_id`            INT UNSIGNED NOT NULL COMMENT 'Pegawai penerima disposisi',
  `status`             VARCHAR(30)  NOT NULL DEFAULT 'menunggu' COMMENT 'menunggu | diproses | menunggu_informasi | selesai',
  `progress_percent`   TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `progress_notes`     TEXT         NULL DEFAULT NULL COMMENT 'Catatan laporan tindak lanjut pegawai',
  `evidence_file`      VARCHAR(255) NULL DEFAULT NULL COMMENT 'Bukti hasil tindak lanjut di storage/',
  `evidence_name`      VARCHAR(255) NULL DEFAULT NULL,
  `completed_at`       DATETIME     NULL DEFAULT NULL,
  `delegated_to`       INT UNSIGNED NULL DEFAULT NULL COMMENT 'Penerima delegasi ulang jika dialihkan',
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

-- 5. Tabel disposition_activities (Riwayat & Log Perjalanan Disposisi)
CREATE TABLE IF NOT EXISTS `disposition_activities` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `disposition_id` INT UNSIGNED NOT NULL,
  `user_id`        INT UNSIGNED NULL DEFAULT NULL,
  `action`         VARCHAR(50)  NOT NULL COMMENT 'dibuat | dilihat | update_progres | selesai | didelegasikan | diubah',
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

-- 6. Tabel notifications (Notifikasi Internal Pengguna)
CREATE TABLE IF NOT EXISTS `notifications` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED NOT NULL COMMENT 'Penerima notifikasi',
  `title`      VARCHAR(150) NOT NULL,
  `message`    TEXT         NOT NULL,
  `link`       VARCHAR(255) NULL DEFAULT NULL,
  `type`       VARCHAR(30)  NOT NULL DEFAULT 'info' COMMENT 'disposisi | persetujuan | permohonan | warning | info',
  `is_read`    TINYINT(1)   NOT NULL DEFAULT 0,
  `created_at` DATETIME     NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_notif_user_read` (`user_id`, `is_read`, `created_at`),
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Tabel audit_logs (Perekaman Aktivitas Sensitif & Keamanan)
CREATE TABLE IF NOT EXISTS `audit_logs` (
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

-- 8. Tambahkan kolom persetujuan di tabel letters (jika belum ada)
-- ALTER TABLE `letters` ADD COLUMN `approval_status` VARCHAR(30) NOT NULL DEFAULT 'disetujui' AFTER `status`;
-- ALTER TABLE `letters` ADD COLUMN `approved_by` INT UNSIGNED NULL DEFAULT NULL AFTER `approval_status`;
-- ALTER TABLE `letters` ADD COLUMN `approved_at` DATETIME NULL DEFAULT NULL AFTER `approved_by`;
-- ALTER TABLE `letters` ADD COLUMN `rejection_reason` TEXT NULL DEFAULT NULL AFTER `approved_at`;

SET FOREIGN_KEY_CHECKS = 1;
