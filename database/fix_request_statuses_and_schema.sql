-- =============================================================================
-- SINKRONISASI STATUS PERMOHONAN SISWA DENGAN LIFECYCLE SURAT KELUAR
-- SMK Muhammadiyah Bandongan
-- =============================================================================
-- Jalankan file ini di phpMyAdmin (Database: db_persuratan_smk atau trisnait_manajemensurat)
-- untuk menyinkronkan status permohonan yang rancu / salah status sebelumnya.

SET NAMES utf8mb4;

-- 1. Pastikan kolom status muat hingga 20 karakter
ALTER TABLE `letter_requests` 
  MODIFY COLUMN `status` VARCHAR(20) NOT NULL DEFAULT 'pending' 
  COMMENT 'pending | processed | approved | taken | rejected';

-- 2. Perbaiki permohonan yang suratnya masih menunggu persetujuan / revisi Kepala Sekolah
--    (Sebelumnya salah masuk ke Siap Diambil / Sudah Diambil, padahal belum ditandatangani Kepsek)
UPDATE `letter_requests` r
INNER JOIN `letters` l ON l.id = r.letter_id
SET r.status = 'processed',
    r.admin_note = CONCAT('Surat telah dibuat oleh TU (No. ', l.letter_number, '), sedang menunggu persetujuan & tanda tangan Kepala Sekolah.')
WHERE r.letter_id IS NOT NULL
  AND (l.approval_status IS NULL OR l.approval_status != 'disetujui')
  AND r.status IN ('approved', 'taken');

-- 3. Perbaiki permohonan yang suratnya sudah ditandatangani Kepsek tetapi belum distempel TU
--    (Harus berstatus 'processed' karena berkas resmi belum lengkap distempel)
UPDATE `letter_requests` r
INNER JOIN `letters` l ON l.id = r.letter_id
SET r.status = 'processed',
    r.admin_note = CONCAT('Surat telah disahkan Kepala Sekolah (No. ', l.letter_number, '), sedang menunggu pembubuhan stempel resmi Tata Usaha.')
WHERE r.letter_id IS NOT NULL
  AND l.approval_status = 'disetujui'
  AND (l.stamp_type IS NULL OR l.stamp_type = 'belum_stempel')
  AND r.status IN ('approved', 'taken');

-- 4. Pastikan surat yang SUDAH ditandatangani Kepsek DAN SUDAH distempel TU berstatus 'approved' jika belum diambil
UPDATE `letter_requests` r
INNER JOIN `letters` l ON l.id = r.letter_id
SET r.status = 'approved',
    r.admin_note = 'Surat sudah selesai disahkan Kepala Sekolah & distempel resmi TU. Silakan ambil fisik surat di ruang TU pada jam kerja atau unduh salinan resminya.'
WHERE r.letter_id IS NOT NULL
  AND l.approval_status = 'disetujui'
  AND l.stamp_type IN ('digital', 'basah')
  AND r.status = 'processed';
