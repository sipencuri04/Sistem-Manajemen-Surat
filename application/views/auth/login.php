<?php defined('BASEPATH') OR exit('No direct script access allowed');
$flash = $this->session->flashdata('flash');
$school_name = trim($school['school_name']) !== '' ? $school['school_name'] : 'SMK Muhammadiyah Bandongan';
?><!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow">
	<title>Masuk &middot; <?= trim($school['school_name']) !== '' ? e($school['school_name']) : 'Sistem Manajemen Persuratan' ?></title>
	
	<!-- High-Speed Preload & Non-blocking Icons -->
	<link rel="preload" href="<?= asset_url('assets/vendor/bootstrap/css/bootstrap.min.css') ?>" as="style">
	<link rel="preload" href="<?= asset_url('assets/css/app.css') ?>" as="style">
	<link rel="stylesheet" href="<?= asset_url('assets/vendor/bootstrap/css/bootstrap.min.css') ?>">
	<link rel="stylesheet" href="<?= asset_url('assets/css/app.css') ?>">
	<link rel="stylesheet" href="<?= asset_url('assets/vendor/fontawesome/css/all.min.css') ?>" media="print" onload="this.media='all'">
	<noscript><link rel="stylesheet" href="<?= asset_url('assets/vendor/fontawesome/css/all.min.css') ?>"></noscript>
</head>
<body class="login-body">

<?php if ($has_logo): ?>
	<img src="<?= site_url('media/logo') ?>" class="login-watermark" alt="Watermark">
<?php endif; ?>

<main class="login-card">
	<!-- Left Side Hero Banner -->
	<div class="login-hero-panel d-none d-lg-flex">
		<div class="hero-content-wrap">
			<!-- Header / Brand -->
			<div class="hero-brand">
				<div class="hero-brand-logo">
					<?php if ($has_logo): ?>
						<img src="<?= site_url('media/logo') ?>" alt="Logo">
					<?php else: ?>
						<i class="fa-solid fa-graduation-cap text-primary"></i>
					<?php endif; ?>
				</div>
				<div>
					<h3 class="hero-brand-title mb-0"><?= e($school_name) ?></h3>
					<div class="hero-brand-motto">Religius &bull; Berilmu &bull; Berprestasi</div>
				</div>
			</div>

			<!-- Welcome Section -->
			<div class="hero-intro">
				<div class="hero-greeting">Selamat Datang di</div>
				<h1 class="hero-main-title">Sistem Manajemen Persuratan</h1>
				<div class="hero-school-sub"><?= e($school_name) ?></div>
				<p class="hero-desc">
					Sistem informasi untuk memudahkan pengelolaan surat masuk, surat keluar, disposisi dan arsip surat di lingkungan sekolah.
				</p>

				<!-- 3 Feature Highlights -->
				<div class="hero-features">
					<div class="hero-feature-item">
						<div class="feature-icon-box bg-feature-blue">
							<i class="fa-solid fa-file-lines"></i>
						</div>
						<div>
							<div class="feature-title">Pembuatan Surat Resmi Sekolah</div>
							<div class="feature-subtitle">Mudah, cepat dan terstruktur</div>
						</div>
					</div>

					<div class="hero-feature-item">
						<div class="feature-icon-box bg-feature-green">
							<i class="fa-solid fa-shield-halved"></i>
						</div>
						<div>
							<div class="feature-title">Penomoran Otomatis &amp; Anti-Duplikat</div>
							<div class="feature-subtitle">Sesuai standar administrasi sekolah</div>
						</div>
					</div>

					<div class="hero-feature-item">
						<div class="feature-icon-box bg-feature-purple">
							<i class="fa-solid fa-cloud-arrow-down"></i>
						</div>
						<div>
							<div class="feature-title">Arsip Digital &amp; PDF A4</div>
							<div class="feature-subtitle">Aman dan mudah diakses</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Decorative Waves at bottom -->
		<div class="hero-waves-container">
			<svg viewBox="0 0 500 120" preserveAspectRatio="none" class="hero-waves-svg">
				<defs>
					<linearGradient id="waveGrad1" x1="0%" y1="0%" x2="100%" y2="0%">
						<stop offset="0%" stop-color="#0052cc" stop-opacity="0.95" />
						<stop offset="50%" stop-color="#0077ff" stop-opacity="0.95" />
						<stop offset="100%" stop-color="#00c0f9" stop-opacity="0.95" />
					</linearGradient>
					<linearGradient id="waveGrad2" x1="0%" y1="0%" x2="100%" y2="0%">
						<stop offset="0%" stop-color="#0066ff" stop-opacity="0.75" />
						<stop offset="50%" stop-color="#00aaff" stop-opacity="0.85" />
						<stop offset="100%" stop-color="#00e5ff" stop-opacity="0.95" />
					</linearGradient>
				</defs>
				<path d="M0,45 C150,95 320,-10 500,60 L500,120 L0,120 Z" fill="url(#waveGrad1)"></path>
				<path d="M0,75 C180,20 280,110 500,50 L500,120 L0,120 Z" fill="url(#waveGrad2)"></path>
			</svg>
		</div>
	</div>

	<!-- Right Side Form Panel -->
	<div class="login-form-panel">
		<!-- Top Right Tagline -->
		<div class="login-tagline-wrap">
			<div class="tagline-main">Satu Sistem,</div>
			<div class="tagline-sub">Untuk Pelayanan yang Lebih Baik</div>
			<div class="tagline-bar"></div>
		</div>

		<!-- Form Header -->
		<div>
			<div class="login-header-group">
				<div class="login-avatar-circle">
					<i class="fa-solid fa-user"></i>
				</div>
				<div>
					<h2 class="login-heading">Masuk</h2>
					<p class="login-subheading">Silakan login menggunakan akun Anda</p>
				</div>
			</div>

			<!-- Flash & Alert Messages -->
			<?php if ($this->input->get('logout')): ?>
				<div class="login-alert login-alert-info">
					<i class="fa-solid fa-circle-info me-2 text-primary fa-lg"></i>
					<span>Anda telah keluar dari aplikasi.</span>
				</div>
			<?php endif; ?>
			<?php if ($flash): ?>
				<div class="login-alert login-alert-<?= $flash['type'] === 'success' ? 'info' : 'danger' ?>">
					<i class="fa-solid fa-<?= $flash['type'] === 'success' ? 'circle-check text-success' : 'triangle-exclamation text-danger' ?> me-2 fa-lg"></i>
					<span><?= e($flash['message']) ?></span>
				</div>
			<?php endif; ?>
			<?php if ($error): ?>
				<div class="login-alert login-alert-danger">
					<i class="fa-solid fa-triangle-exclamation me-2 fa-lg"></i>
					<span><?= e($error) ?></span>
				</div>
			<?php endif; ?>

			<!-- Login Form -->
			<?= form_open('login', array('autocomplete' => 'off', 'novalidate' => 'novalidate')) ?>
				<div class="mb-3">
					<label for="username" class="modern-field-label">
						<i class="fa-solid fa-user-tag me-1 text-muted"></i> Username
					</label>
					<div class="modern-input-group">
						<span class="modern-input-icon"><i class="fa-solid fa-user"></i></span>
						<input type="text" class="modern-field-input" id="username" name="username" maxlength="50"
							placeholder="Masukkan username Anda" value="<?= e(set_value('username', '', FALSE)) ?>" required autofocus>
					</div>
				</div>

				<div class="mb-4">
					<label for="password" class="modern-field-label">
						<i class="fa-solid fa-lock me-1 text-muted"></i> Password
					</label>
					<div class="modern-input-group">
						<span class="modern-input-icon"><i class="fa-solid fa-lock"></i></span>
						<input type="password" class="modern-field-input" id="password" name="password"
							placeholder="Masukkan password Anda" required>
						<button type="button" class="modern-toggle-password" data-toggle-password="#password" aria-label="Tampilkan password">
							<i class="fa-regular fa-eye"></i>
						</button>
					</div>
				</div>

				<button type="submit" class="btn btn-modern-login w-100">
					<i class="fa-solid fa-right-to-bracket me-2"></i> Masuk
				</button>
			<?= form_close() ?>

			<!-- Divider -->
			<div class="modern-divider">
				<span>atau</span>
			</div>

			<!-- Public Student Request Button -->
			<a href="<?= site_url('permohonan') ?>" class="btn btn-modern-secondary w-100">
				<i class="fa-solid fa-paper-plane me-2"></i> Ajukan Permohonan Surat
			</a>
		</div>

		<!-- Footer -->
		<div class="login-footer-copy text-center">
			&copy; <?= date('Y') ?> <?= e($school_name) ?>
		</div>
	</div>
</main>

<script src="<?= asset_url('assets/js/app.js') ?>"></script>
</body>
</html>
