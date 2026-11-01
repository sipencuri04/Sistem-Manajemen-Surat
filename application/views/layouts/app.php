<?php defined('BASEPATH') OR exit('No direct script access allowed');
$flash = $this->session->flashdata('flash');
$school_label = trim($school['school_name']) !== '' ? $school['school_name'] : 'Identitas sekolah belum diisi';
$user_role = isset($current_user['role']) ? strtolower($current_user['role']) : 'admin';

// Konstruksi Menu Dinamis Berdasarkan 5 Role
$menu = array();
$menu[] = array('url' => 'dashboard', 'icon' => 'fa-gauge-high', 'label' => 'Dashboard', 'active' => menu_active(array('dashboard')));

if ($user_role === 'pegawai')
{
	// Menu Pegawai / Guru
	$menu[] = array('url' => 'disposisi-saya', 'icon' => 'fa-clipboard-check', 'label' => 'Disposisi Saya', 'active' => menu_active(array('disposisi-saya')), 'badge' => isset($my_pending_dispositions) ? (int) $my_pending_dispositions : 0);
	$menu[] = array('url' => 'arsip', 'icon' => 'fa-box-archive', 'label' => 'Arsip Surat', 'active' => menu_active(array('arsip')));
}
elseif ($user_role === 'kepala_sekolah')
{
	// Menu Kepala Sekolah
	$menu[] = array('url' => 'surat-masuk', 'icon' => 'fa-inbox', 'label' => 'Surat Masuk', 'active' => menu_active(array('surat-masuk')), 'badge' => isset($pending_incoming_count) ? (int) $pending_incoming_count : 0);
	$menu[] = array('url' => 'disposisi', 'icon' => 'fa-share-nodes', 'label' => 'Disposisi Surat', 'active' => menu_active(array('disposisi')));
	$menu[] = array('url' => 'persetujuan-surat', 'icon' => 'fa-signature', 'label' => 'Persetujuan Surat', 'active' => menu_active(array('persetujuan-surat')), 'badge' => isset($pending_approvals_count) ? (int) $pending_approvals_count : 0);
	$menu[] = array('url' => 'surat', 'icon' => 'fa-envelopes-bulk', 'label' => 'Data Surat Keluar', 'active' => menu_active(array('surat'), array('buat')));
	$menu[] = array('url' => 'arsip', 'icon' => 'fa-box-archive', 'label' => 'Arsip Surat', 'active' => menu_active(array('arsip')));
}
elseif ($user_role === 'superuser')
{
	// Menu Superuser / Developer
	$menu[] = array('url' => 'pengguna', 'icon' => 'fa-users-gear', 'label' => 'Kelola Pengguna', 'active' => menu_active(array('pengguna')));
	$menu[] = array('url' => 'surat-masuk', 'icon' => 'fa-inbox', 'label' => 'Surat Masuk', 'active' => menu_active(array('surat-masuk')));
	$menu[] = array('url' => 'disposisi', 'icon' => 'fa-share-nodes', 'label' => 'Disposisi Surat', 'active' => menu_active(array('disposisi')));
	$menu[] = array('url' => 'surat', 'icon' => 'fa-envelopes-bulk', 'label' => 'Data Surat Keluar', 'active' => menu_active(array('surat'), array('buat')));
	$menu[] = array('url' => 'permohonan-masuk', 'icon' => 'fa-graduation-cap', 'label' => 'Permohonan Siswa', 'active' => menu_active(array('permohonan-masuk')), 'badge' => isset($pending_requests) ? (int) $pending_requests : 0);
	$menu[] = array('url' => 'arsip', 'icon' => 'fa-box-archive', 'label' => 'Arsip Surat', 'active' => menu_active(array('arsip')));
	$menu[] = array('url' => 'log-aktivitas', 'icon' => 'fa-clock-rotate-left', 'label' => 'Audit Log', 'active' => menu_active(array('log-aktivitas')));
	$menu[] = array('url' => 'log-sistem', 'icon' => 'fa-triangle-exclamation', 'label' => 'Log Sistem', 'active' => menu_active(array('log-sistem')));
	$menu[] = array('url' => 'backup', 'icon' => 'fa-database', 'label' => 'Backup Database', 'active' => menu_active(array('backup')));
	$menu[] = array('url' => 'pengaturan', 'icon' => 'fa-school', 'label' => 'Pengaturan Sekolah', 'active' => menu_active(array('pengaturan')));
	$menu[] = array('url' => 'jenis-surat', 'icon' => 'fa-layer-group', 'label' => 'Jenis Surat', 'active' => menu_active(array('jenis-surat')));
}
else
{
	// Menu Tata Usaha (TU) & Admin Legacy
	$menu[] = array('url' => 'surat/buat', 'icon' => 'fa-file-circle-plus', 'label' => 'Buat Surat', 'active' => menu_active(array('surat/buat')));
	$menu[] = array('url' => 'surat', 'icon' => 'fa-envelopes-bulk', 'label' => 'Data Surat Keluar', 'active' => menu_active(array('surat'), array('buat')));
	$menu[] = array('url' => 'surat-masuk', 'icon' => 'fa-inbox', 'label' => 'Surat Masuk', 'active' => menu_active(array('surat-masuk')), 'badge' => isset($pending_incoming_count) ? (int) $pending_incoming_count : 0);
	$menu[] = array('url' => 'disposisi', 'icon' => 'fa-share-nodes', 'label' => 'Disposisi Surat', 'active' => menu_active(array('disposisi')));
	$menu[] = array('url' => 'permohonan-masuk', 'icon' => 'fa-graduation-cap', 'label' => 'Permohonan Siswa', 'active' => menu_active(array('permohonan-masuk')), 'badge' => isset($pending_requests) ? (int) $pending_requests : 0);
	$menu[] = array('url' => 'arsip', 'icon' => 'fa-box-archive', 'label' => 'Arsip Surat', 'active' => menu_active(array('arsip')));
	$menu[] = array('url' => 'jenis-surat', 'icon' => 'fa-layer-group', 'label' => 'Jenis Surat', 'active' => menu_active(array('jenis-surat')));
	$menu[] = array('url' => 'pengaturan', 'icon' => 'fa-school', 'label' => 'Pengaturan Sekolah', 'active' => menu_active(array('pengaturan')));
}

$menu[] = array('url' => 'akun', 'icon' => 'fa-user-gear', 'label' => 'Pengaturan Akun', 'active' => menu_active(array('akun')));
?><!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow">
	<title><?= e($page_title) ?> &middot; Persuratan SMK</title>
	<link rel="stylesheet" href="<?= asset_url('assets/vendor/bootstrap/css/bootstrap.min.css') ?>">
	<link rel="stylesheet" href="<?= asset_url('assets/vendor/fontawesome/css/all.min.css') ?>">
	<link rel="stylesheet" href="<?= asset_url('assets/css/app.css') ?>">
</head>
<body>
<div class="app">
	<!-- Sidebar -->
	<aside class="sidebar" id="sidebar">
		<div class="sidebar-brand">
			<span class="brand-icon"><i class="fa-solid fa-envelope-open-text"></i></span>
			<div class="lh-sm">
				<div class="fw-bold">Persuratan SMK</div>
				<small class="text-white-50 text-truncate d-block" style="max-width:170px"><?= e($school_label) ?></small>
			</div>
		</div>
		<nav class="sidebar-nav">
			<?php foreach ($menu as $m): ?>
				<a href="<?= site_url($m['url']) ?>" class="nav-link <?= $m['active'] ?>">
					<i class="fa-solid <?= $m['icon'] ?> fa-fw"></i><span><?= e($m['label']) ?></span>
					<?php if ( ! empty($m['badge'])): ?><span class="nav-badge" title="Pemberitahuan aktif"><?= (int) $m['badge'] ?></span><?php endif; ?>
				</a>
			<?php endforeach; ?>
			<?= form_open('logout', array('class' => 'mt-2', 'id' => 'logoutForm')) ?>
				<button type="submit" class="nav-link w-100 text-start border-0 bg-transparent">
					<i class="fa-solid fa-right-from-bracket fa-fw"></i><span>Logout</span>
				</button>
			<?= form_close() ?>
		</nav>
	</aside>
	<div class="sidebar-backdrop" data-sidebar-close></div>

	<!-- Konten -->
	<div class="main">
		<header class="topbar">
			<button class="btn btn-light d-lg-none me-2" type="button" data-sidebar-toggle aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
			<h1 class="h5 mb-0 fw-semibold text-navy text-truncate"><?= e($page_title) ?></h1>
			<div class="ms-auto d-flex align-items-center gap-2">
				<?php if (in_array($user_role, array('tu', 'superuser', 'admin'), TRUE)): ?>
					<a href="<?= site_url('surat/buat') ?>" class="btn btn-primary btn-sm d-none d-sm-inline-flex align-items-center"><i class="fa-solid fa-plus me-1"></i> Surat Baru</a>
				<?php endif; ?>

				<!-- Dropdown Notifikasi Internal -->
				<div class="dropdown">
					<button class="btn btn-light btn-sm position-relative d-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false" title="Pusat Notifikasi">
						<i class="fa-solid fa-bell"></i>
						<?php if ( ! empty($unread_notifications_count)): ?>
							<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:0.65rem;">
								<?= (int) $unread_notifications_count ?>
							</span>
						<?php endif; ?>
					</button>
					<div class="dropdown-menu dropdown-menu-end shadow" style="width: 330px; max-width: 90vw;">
						<div class="d-flex justify-content-between align-items-center p-2 border-bottom">
							<strong class="small text-navy"><i class="fa-solid fa-bell me-1 text-warning"></i> Notifikasi Internal</strong>
							<a href="<?= site_url('notifikasi') ?>" class="small text-decoration-none">Buka Semua</a>
						</div>
						<div class="list-group list-group-flush small" style="max-height: 280px; overflow-y: auto;">
							<?php if (empty($recent_notifications)): ?>
								<div class="p-3 text-center text-muted">Tidak ada notifikasi baru.</div>
							<?php else: foreach ($recent_notifications as $rn): ?>
								<a href="<?= site_url('notifikasi/baca/'.$rn['id']) ?>" class="list-group-item list-group-item-action p-2 <?= ! $rn['is_read'] ? 'bg-primary-subtle' : '' ?>">
									<div class="fw-bold text-navy text-truncate"><?= e($rn['title']) ?></div>
									<div class="text-secondary small text-truncate"><?= e($rn['message']) ?></div>
									<div class="text-muted" style="font-size: 0.7rem;"><?= e(tgl_indo($rn['created_at'])) ?> <?= e(substr($rn['created_at'], 11, 5)) ?></div>
								</a>
							<?php endforeach; endif; ?>
						</div>
					</div>
				</div>

				<!-- Dropdown Profil Pengguna -->
				<div class="dropdown">
					<button class="btn btn-light btn-sm dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false">
						<span class="avatar me-2"><?= e(strtoupper(substr($current_user['full_name'], 0, 1))) ?></span>
						<div class="text-start d-none d-md-block lh-1 me-1">
							<span class="fw-bold d-block text-truncate" style="max-width:140px;"><?= e($current_user['full_name']) ?></span>
							<span class="text-muted" style="font-size: 0.72rem;"><?= e(role_title($current_user['role'])) ?></span>
						</div>
					</button>
					<ul class="dropdown-menu dropdown-menu-end">
						<li>
							<div class="px-3 py-1">
								<span class="d-block fw-bold text-navy"><?= e($current_user['full_name']) ?></span>
								<small class="text-muted"><?= role_badge($current_user['role']) ?></small>
							</div>
						</li>
						<li><hr class="dropdown-divider"></li>
						<li><a class="dropdown-item" href="<?= site_url('akun') ?>"><i class="fa-solid fa-user-gear me-2"></i>Pengaturan Akun</a></li>
						<?php if (in_array($user_role, array('superuser', 'admin'), TRUE)): ?>
							<li><a class="dropdown-item" href="<?= site_url('pengguna') ?>"><i class="fa-solid fa-users-gear me-2"></i>Kelola Pengguna</a></li>
							<li><a class="dropdown-item" href="<?= site_url('log-aktivitas') ?>"><i class="fa-solid fa-clock-rotate-left me-2"></i>Audit Log</a></li>
						<?php endif; ?>
						<li><button class="dropdown-item text-danger" type="submit" form="logoutForm"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</button></li>
					</ul>
				</div>
			</div>
		</header>

		<main class="content">
			<?php if ($flash): ?>
				<div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
					<?php $icons = array('success' => 'fa-circle-check', 'danger' => 'fa-circle-xmark', 'warning' => 'fa-triangle-exclamation', 'info' => 'fa-circle-info'); ?>
					<i class="fa-solid <?= isset($icons[$flash['type']]) ? $icons[$flash['type']] : 'fa-circle-info' ?> me-1"></i>
					<?= e($flash['message']) ?>
					<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
				</div>
			<?php endif; ?>

			<?= $content ?>
		</main>
		<footer class="footer">
			&copy; <?= date('Y') ?> <?= e($school_label) ?> &middot; Sistem Manajemen Persuratan
		</footer>
	</div>
</div>

<!-- Modal konfirmasi (dipakai oleh form ber-atribut data-confirm) -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header border-0">
				<h5 class="modal-title"><i class="fa-solid fa-triangle-exclamation text-danger me-2"></i>Konfirmasi</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
			</div>
			<div class="modal-body pt-0" id="confirmMessage">Yakin?</div>
			<div class="modal-footer border-0">
				<button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
				<button type="button" class="btn btn-danger" id="confirmYes">Ya, lanjutkan</button>
			</div>
		</div>
	</div>
</div>

<script src="<?= asset_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
<?php if ( ! empty($use_chart)): ?>
<script src="<?= asset_url('assets/vendor/chartjs/chart.umd.js') ?>"></script>
<?php endif; ?>
<script src="<?= asset_url('assets/js/app.js') ?>"></script>
</body>
</html>
