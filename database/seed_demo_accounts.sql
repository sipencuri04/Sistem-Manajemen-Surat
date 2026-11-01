-- =============================================================================
-- SQL SEED: Akun Demo 4 Role Persuratan Sekolah
-- Gunakan skrip ini di phpMyAdmin cPanel untuk membuat / mereset akun demo.
-- =============================================================================

INSERT INTO `users` (
  `username`, `password_hash`, `full_name`, `nip`, `position`, `phone`, `email`, `role`, `is_active`, `failed_attempts`, `created_at`
) VALUES
-- 1. Kepala Sekolah (kepsek / kepsek123)
(
  'kepsek',
  '$2y$10$QgLtdX8om.ourXfsCYaTAeMpLq2uWVGXpkNmpO/oJu0hL2UGxUEy6',
  'Drs. H. Ahmad Dahlan, M.Pd',
  '19680512 199403 1 005',
  'Kepala Sekolah',
  '081234567801',
  'kepsek@sekolah.sch.id',
  'kepala_sekolah',
  1,
  0,
  NOW()
),

-- 2. Petugas Tata Usaha (tu / tu123)
(
  'tu',
  '$2y$10$Kbl88ne9JbcPd2MGDysXNuiyaeSlSACGmkcP12A0yj115wPYEfsw6',
  'Siti Rahmawati, S.AP',
  '19820715 200604 2 018',
  'Staf Tata Usaha / Urusan Persuratan',
  '081234567802',
  'tu@sekolah.sch.id',
  'tu',
  1,
  0,
  NOW()
),

-- 3. Guru / Pegawai Penerima Disposisi (pegawai1 / pegawai123)
(
  'pegawai1',
  '$2y$10$I3.XCVXAIfxmgvsg7Q9Y2.dGZcAGvm1e7j1llwII.OFgOrTeSDMoK',
  'Bambang Wijaya, S.Pd',
  '19850320 200902 1 004',
  'Guru / Koordinator Kurikulum',
  '081234567803',
  'bambang@sekolah.sch.id',
  'pegawai',
  1,
  0,
  NOW()
),

-- 4. Superuser / Administrator TI (superuser / superuser123)
(
  'superuser',
  '$2y$10$YXut0hOpquTFS7ZOPbZG2.Ry6GM8k9EU3.j0WYIsM/JyNANNQQfxC',
  'Superuser Sistem / IT',
  NULL,
  'Administrator Sistem & TI',
  '081234567800',
  'superuser@sekolah.sch.id',
  'superuser',
  1,
  0,
  NOW()
)

ON DUPLICATE KEY UPDATE
  `password_hash`   = VALUES(`password_hash`),
  `full_name`       = VALUES(`full_name`),
  `nip`             = VALUES(`nip`),
  `position`        = VALUES(`position`),
  `phone`           = VALUES(`phone`),
  `email`           = VALUES(`email`),
  `role`            = VALUES(`role`),
  `is_active`       = 1,
  `failed_attempts` = 0,
  `locked_until`    = NULL,
  `updated_at`      = NOW();

