<?php defined('BASEPATH') OR exit('No direct script access allowed');
$school_name = trim($school['school_name']) !== '' ? $school['school_name'] : 'Layanan Persuratan Sekolah';
?><!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<meta name="theme-color" content="#156ae8">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="default">
	<meta name="robots" content="noindex, nofollow">
	<title><?= e($page_title) ?> &middot; <?= e($school_name) ?></title>
	<link rel="stylesheet" href="<?= asset_url('assets/vendor/bootstrap/css/bootstrap.min.css') ?>">
	<link rel="stylesheet" href="<?= asset_url('assets/vendor/fontawesome/css/all.min.css') ?>">
	<link rel="stylesheet" href="<?= asset_url('assets/css/app.css') ?>">
</head>
<body class="public-body">

<!-- App Top Bar (Sticky Frosted Header) -->
<header class="app-topbar">
	<div class="container-lg">
		<a href="<?= site_url('permohonan') ?>" class="d-flex align-items-center gap-2 text-decoration-none min-w-0">
			<div class="app-logo-badge">
				<?php if ($has_logo): ?>
					<img src="<?= site_url('media/logo') ?>" alt="Logo">
				<?php else: ?>
					<i class="fa-solid fa-graduation-cap text-primary fs-5"></i>
				<?php endif; ?>
			</div>
			<div class="min-w-0 lh-sm">
				<div class="app-brand-name text-truncate"><?= e($school_name) ?></div>
				<div class="app-brand-sub text-truncate">Layanan Surat Siswa</div>
			</div>
		</a>
		<nav class="app-topnav d-none d-lg-flex" aria-label="Menu portal">
			<a href="<?= site_url('permohonan') ?>" class="<?= current_url() == site_url('permohonan') ? 'active' : '' ?>"><i class="fa-solid fa-house me-1"></i>Beranda</a>
			<a href="<?= site_url('permohonan') ?>#daftarSurat"><i class="fa-solid fa-file-circle-plus me-1"></i>Ajukan Surat</a>
			<a href="<?= site_url('kirim-surat') ?>" class="<?= strpos(current_url(), 'kirim-surat') !== false ? 'active' : '' ?>"><i class="fa-solid fa-building-columns me-1"></i>Kirim Surat Instansi</a>
			<a href="#modalAlur" data-bs-toggle="modal"><i class="fa-regular fa-circle-question me-1"></i>Bantuan</a>
		</nav>
		<div class="d-flex align-items-center gap-2 flex-shrink-0">
			<a href="<?= site_url('permohonan/cek') ?>" class="btn-app-track" aria-label="Cek status permohonan">
				<i class="fa-solid fa-magnifying-glass"></i>
				<span>Cek Status</span>
			</a>
		</div>
	</div>
</header>

<!-- Main App Content -->
<main class="container-lg py-3 py-md-4 flex-grow-1">
	<?= $content ?>
</main>

<!-- Minimal Footer for Desktop -->
<footer class="container-lg pb-3 pt-2 small text-muted text-center d-none d-md-block">
	&copy; <?= date('Y') ?> <?= e($school_name) ?>
	<?php if ($school['phone'] !== ''): ?> &middot; Telp. <?= e($school['phone']) ?><?php endif; ?>
	&middot; <a href="<?= site_url('login') ?>" class="text-muted text-decoration-none"><i class="fa-solid fa-lock me-1"></i>Login Petugas</a>
</footer>

<!-- Mobile Bottom Navigation Dock -->
<nav class="app-bottom-dock d-flex d-lg-none" aria-label="Navigasi Aplikasi">
	<a href="<?= site_url('permohonan') ?>" class="dock-item <?= current_url() == site_url('permohonan') ? 'active' : '' ?>">
		<i class="fa-solid fa-house"></i>
		<span>Beranda</span>
	</a>
	<a href="<?= site_url('permohonan') ?>#daftarSurat" class="dock-item">
		<i class="fa-solid fa-file-circle-plus"></i>
		<span>Ajukan</span>
	</a>
	<a href="<?= site_url('kirim-surat') ?>" class="dock-item <?= strpos(current_url(), 'kirim-surat') !== false ? 'active' : '' ?>">
		<i class="fa-solid fa-building-columns"></i>
		<span>Instansi</span>
	</a>
	<a href="<?= site_url('permohonan/cek') ?>" class="dock-item <?= strpos(current_url(), 'permohonan/cek') !== false ? 'active' : '' ?>">
		<i class="fa-solid fa-magnifying-glass"></i>
		<span>Lacak</span>
	</a>
	<a href="#modalAlur" data-bs-toggle="modal" class="dock-item">
		<i class="fa-regular fa-circle-question"></i>
		<span>Bantuan</span>
	</a>
</nav>

<!-- Modal Panduan & Alur Permohonan -->
<div class="modal fade" id="modalAlur" tabindex="-1" aria-labelledby="modalAlurLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content border-0 shadow-lg rounded-4">
			<div class="modal-header border-bottom-0 pb-0">
				<h5 class="modal-title fw-bold text-navy" id="modalAlurLabel">
					<i class="fa-solid fa-circle-info text-primary me-2"></i>Panduan &amp; Alur Permohonan
				</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
			</div>
			<div class="modal-body pt-2">
				<p class="text-muted small mb-3">Ikuti 4 langkah mudah untuk mengurus surat keterangan sekolah secara online:</p>
				<div class="d-flex flex-column gap-3">
					<div class="d-flex align-items-start gap-3">
						<div class="step-number-circle step-1 flex-shrink-0">1</div>
						<div>
							<div class="fw-bold small text-dark">Pilih &amp; Isi Formulir</div>
							<div class="small text-muted">Pilih jenis surat yang dibutuhkan dan isi data NISN, nama, serta keperluan dengan benar.</div>
						</div>
					</div>
					<div class="d-flex align-items-start gap-3">
						<div class="step-number-circle step-2 flex-shrink-0">2</div>
						<div>
							<div class="fw-bold small text-dark">Simpan Kode Permohonan</div>
							<div class="small text-muted">Screenshot atau catat 10 digit kode unik yang muncul setelah permohonan berhasil dikirim.</div>
						</div>
					</div>
					<div class="d-flex align-items-start gap-3">
						<div class="step-number-circle step-3 flex-shrink-0">3</div>
						<div>
							<div class="fw-bold small text-dark">Verifikasi oleh Petugas TU</div>
							<div class="small text-muted">Petugas tata usaha akan memvalidasi data dan membuatkan nomor surat resmi pada jam kerja.</div>
						</div>
					</div>
					<div class="d-flex align-items-start gap-3">
						<div class="step-number-circle step-4 flex-shrink-0">4</div>
						<div>
							<div class="fw-bold small text-dark">Cetak Mandiri / Ambil Surat</div>
							<div class="small text-muted">Setelah status "Siap", Anda bisa download &amp; cetak PDF langsung atau mengambil fisik surat di ruang TU.</div>
						</div>
					</div>
				</div>
				<div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
					<a href="<?= site_url('kirim-surat') ?>" class="small text-primary text-decoration-none fw-semibold">
						<i class="fa-solid fa-building-columns me-1"></i>Portal Kirim Surat Dinas
					</a>
					<a href="<?= site_url('login') ?>" class="small text-muted text-decoration-none">
						<i class="fa-solid fa-shield-halved me-1"></i>Login Petugas TU
					</a>
				</div>
			</div>
		</div>
	</div>
</div>

<script src="<?= asset_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= asset_url('assets/js/app.js') ?>"></script>
</body>
</html>
