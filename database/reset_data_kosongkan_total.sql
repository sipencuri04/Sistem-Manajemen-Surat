-- =============================================================================
-- SCRIPT RESET DATA: BERSIHKAN / KOSONGKAN TOTAL SELURUH DATA TRANSAKSI
-- SMK MUHAMMADIYAH BANDONGAN
-- =============================================================================
-- Script ini menghapus SELURUH data surat masuk, surat keluar, permohonan siswa,
-- tiket antrean, disposisi, dan nomor urut counter surat.
-- Data Pengguna (Users), Template Jenis Surat, dan Profil Sekolah TETAP AMAN.
-- Jalankan file ini di phpMyAdmin (Tab Import / Tab SQL).
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Hapus riwayat lampiran dan penomoran surat
DELETE FROM `letter_files`;
DELETE FROM `letter_counters`;

-- 2. Hapus alur disposisi surat masuk
DELETE FROM `disposition_activities`;
DELETE FROM `disposition_recipients`;
DELETE FROM `dispositions`;

-- 3. Hapus permohonan siswa & surat
DELETE FROM `letter_requests`;
DELETE FROM `incoming_letters`;
DELETE FROM `letters`;

-- 4. Hapus tiket antrean
DELETE FROM `queue_tickets`;

-- 5. Hapus notifikasi terkait transaksi
DELETE FROM `notifications` WHERE `type` IN ('surat_masuk', 'persetujuan', 'disposisi', 'permohonan');

-- 6. Kembalikan Auto Increment ID ke 1
ALTER TABLE `letter_files` AUTO_INCREMENT = 1;
ALTER TABLE `disposition_activities` AUTO_INCREMENT = 1;
ALTER TABLE `disposition_recipients` AUTO_INCREMENT = 1;
ALTER TABLE `dispositions` AUTO_INCREMENT = 1;
ALTER TABLE `letter_requests` AUTO_INCREMENT = 1;
ALTER TABLE `incoming_letters` AUTO_INCREMENT = 1;
ALTER TABLE `letters` AUTO_INCREMENT = 1;
ALTER TABLE `queue_tickets` AUTO_INCREMENT = 1;

SET FOREIGN_KEY_CHECKS = 1;

-- Selesai! Seluruh data operasional telah bersih 100%. Sistem siap diuji ulang dari awal.

