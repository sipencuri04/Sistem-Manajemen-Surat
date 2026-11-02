-- =============================================================================
-- UPDATE: Fitur Permohonan Surat oleh Siswa (formulir publik tanpa login)
-- Jalankan SEKALI pada database yang sudah terpasang sebelumnya
-- (phpMyAdmin -> pilih db_persuratan_smk -> tab Import -> pilih file ini).
-- Instalasi baru tidak perlu file ini; sudah termasuk di db_persuratan_smk.sql.
-- =============================================================================
SET NAMES utf8mb4;

ALTER TABLE `letter_types`
  ADD COLUMN `allow_public` TINYINT(1) NOT NULL DEFAULT 0
  COMMENT '1 = siswa dapat mengajukan lewat formulir publik' AFTER `is_active`;

-- -----------------------------------------------------------------------------
-- 8. letter_requests : permohonan surat dari siswa melalui formulir publik
--    (tanpa login). TU memeriksa lalu menerbitkan surat atau menolak.
-- -----------------------------------------------------------------------------
CREATE TABLE `letter_requests` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tracking_code`    CHAR(10)     NOT NULL COMMENT 'Kode lacak untuk siswa',
  `letter_type_id`   INT UNSIGNED NOT NULL,
  `requester_name`   VARCHAR(150) NOT NULL,
  `requester_class`  VARCHAR(50)  NOT NULL DEFAULT '',
  `requester_phone`  VARCHAR(20)  NOT NULL,
  `requester_email`  VARCHAR(150) NULL DEFAULT NULL COMMENT 'Alamat Gmail/Email Siswa' COMMENT 'Nomor WhatsApp/HP yang dapat dihubungi',
  `content_data`     JSON         NULL COMMENT 'Isian formulir jenis surat',
  `requester_note`   VARCHAR(500) NULL DEFAULT NULL,
  `status`           VARCHAR(10)  NOT NULL DEFAULT 'pending' COMMENT 'pending | approved | taken | rejected',
  `admin_note`       VARCHAR(500) NULL DEFAULT NULL COMMENT 'Alasan tolak / info pengambilan',
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

UPDATE `letter_types` SET `allow_public` = 1 WHERE `code` IN ('SKA', 'SKL', 'SKPKL');
