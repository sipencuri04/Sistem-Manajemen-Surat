<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Monitor Antrean &middot; <?= e($school['school_name'] ?: 'SMK Muhammadiyah Bandongan') ?></title>
	
	<!-- Favicon -->
	<link rel="icon" type="image/png" href="<?= base_url('favicon.png') ?>">
	<link rel="shortcut icon" href="<?= base_url('favicon.ico') ?>">

	<link rel="preload" href="<?= asset_url('assets/vendor/bootstrap/css/bootstrap.min.css') ?>" as="style">
	<link rel="stylesheet" href="<?= asset_url('assets/vendor/bootstrap/css/bootstrap.min.css') ?>">
	<link rel="stylesheet" href="<?= asset_url('assets/vendor/fontawesome/css/all.min.css') ?>" media="print" onload="this.media='all'">
	<noscript><link rel="stylesheet" href="<?= asset_url('assets/vendor/fontawesome/css/all.min.css') ?>"></noscript>

	<style>
		:root {
			--page-bg: #edf3fb;
			--header-gradient: linear-gradient(90deg, #072a63 0%, #0c3e8a 50%, #0f4699 100%);
			--brand-blue: #0d5ecc;
			--brand-cyan: #38bdf8;
			--text-navy: #0f172a;
			--card-border: #e2e8f0;
		}

		* { box-sizing: border-box; }

		body {
			font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
			background-color: var(--page-bg);
			color: #1e293b;
			min-height: 100vh;
			display: flex;
			flex-direction: column;
			margin: 0;
			padding: 0;
			overflow-x: hidden;
			user-select: none;
			-webkit-user-select: none;
		}

		/* 1. Header Bar Atas */
		.tv-header {
			background: var(--header-gradient);
			padding: 12px 32px;
			color: #ffffff;
			display: flex;
			justify-content: space-between;
			align-items: center;
			box-shadow: 0 4px 20px rgba(7, 42, 99, 0.25);
			position: relative;
			z-index: 20;
		}
		.school-logo-img {
			width: 48px;
			height: 48px;
			object-fit: contain;
			filter: drop-shadow(0 2px 6px rgba(0,0,0,0.25));
		}
		.tv-school-title {
			font-size: 18px;
			font-weight: 800;
			letter-spacing: 0.3px;
			line-height: 1.2;
			color: #ffffff;
			text-transform: uppercase;
		}
		.tv-school-sub {
			font-size: 12px;
			color: rgba(255, 255, 255, 0.8);
			font-weight: 500;
		}

		/* Kapsul Header Kanan */
		.header-datetime-capsule {
			background: rgba(255, 255, 255, 0.14);
			backdrop-filter: blur(8px);
			border: 1px solid rgba(255, 255, 255, 0.22);
			border-radius: 50px;
			padding: 6px 18px;
			color: #ffffff;
			font-size: 13.5px;
			display: flex;
			align-items: center;
			gap: 14px;
		}
		.header-pill-btn {
			background: rgba(255, 255, 255, 0.14);
			border: 1px solid rgba(255, 255, 255, 0.25);
			color: #ffffff;
			border-radius: 50px;
			padding: 6px 16px;
			font-size: 13px;
			font-weight: 600;
			transition: all 0.2s ease;
		}
		.header-pill-btn:hover {
			background: rgba(255, 255, 255, 0.25);
			color: #ffffff;
		}

		/* 2. Main Content Grid (2 Kolom) */
		.tv-main-container {
			flex: 1;
			padding: 20px 28px;
			display: flex;
			gap: 22px;
		}
		.tv-col-left {
			flex: 1.15;
			display: flex;
			flex-direction: column;
			gap: 18px;
		}
		.tv-col-right {
			flex: 1;
			display: flex;
			flex-direction: column;
			gap: 18px;
		}

		/* 3. Kartu Panggilan Antrean (Hero Card Biru) */
		.card-call-hero {
			background: linear-gradient(135deg, #0947b4 0%, #0d5ecc 48%, #1d72f2 100%);
			border-radius: 22px;
			padding: 24px 28px;
			box-shadow: 0 12px 30px rgba(13, 94, 204, 0.24);
			color: #ffffff;
			position: relative;
			overflow: hidden;
			display: flex;
			flex-direction: column;
			justify-content: space-between;
			min-height: 290px;
		}
		/* Dekorasi kurva organic di latar belakang kartu hero */
		.card-call-hero::after {
			content: '';
			position: absolute;
			right: -40px;
			bottom: -40px;
			width: 260px;
			height: 260px;
			background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, transparent 70%);
			border-radius: 50%;
			pointer-events: none;
		}

		.call-hero-header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			position: relative;
			z-index: 2;
		}
		.call-hero-title {
			font-size: 18px;
			font-weight: 800;
			letter-spacing: 0.2px;
			display: flex;
			align-items: center;
			gap: 10px;
		}
		.status-calling-badge {
			background: #16a34a;
			color: #ffffff;
			border-radius: 50px;
			padding: 5px 16px;
			font-size: 13px;
			font-weight: 700;
			display: inline-flex;
			align-items: center;
			gap: 6px;
			box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);
		}
		.status-calling-badge.is-idle {
			background: rgba(255, 255, 255, 0.2);
			box-shadow: none;
		}

		/* Loket Pill & Giant Ticket Number */
		.call-center-content {
			text-align: center;
			margin: 12px 0;
			position: relative;
			z-index: 2;
		}
		.loket-pill-badge {
			display: inline-block;
			background: rgba(255, 255, 255, 0.22);
			border: 1px solid rgba(255, 255, 255, 0.38);
			color: #ffffff;
			font-weight: 800;
			font-size: 15px;
			padding: 6px 28px;
			border-radius: 50px;
			letter-spacing: 0.5px;
			text-transform: uppercase;
			box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
		}
		.ticket-number-giant {
			font-size: 84px;
			font-weight: 900;
			color: #ffffff;
			line-height: 1.1;
			margin: 10px 0 6px 0;
			letter-spacing: 3px;
			font-family: 'JetBrains Mono', -apple-system, monospace;
			text-shadow: 0 4px 18px rgba(0, 0, 0, 0.22);
			transition: transform 0.3s ease;
		}
		.service-caption-text {
			font-size: 16px;
			font-weight: 600;
			color: rgba(255, 255, 255, 0.95);
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 8px;
		}

		/* Efek Animasi Gelombang Bergetar Saat Panggilan Aktif */
		.card-call-hero.is-calling-pulse {
			animation: heroPulse 1.1s infinite alternate;
		}
		@keyframes heroPulse {
			0% { box-shadow: 0 10px 25px rgba(13, 94, 204, 0.3); }
			100% { box-shadow: 0 0 40px rgba(56, 189, 248, 0.7); }
		}

		/* 4. Kartu Sambutan & Fasad Gedung Sekolah (Bawah Kiri) */
		.card-school-welcome {
			background: #ffffff;
			border-radius: 20px;
			border: 1px solid var(--card-border);
			box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);
			overflow: hidden;
			display: flex;
			align-items: center;
			position: relative;
			min-height: 140px;
		}
		.welcome-text-side {
			flex: 1.15;
			padding: 22px 26px;
			z-index: 2;
		}
		.welcome-intro {
			font-size: 14.5px;
			color: #64748b;
			font-weight: 500;
		}
		.welcome-school-name {
			font-size: 20px;
			font-weight: 800;
			color: #0f172a;
			margin: 2px 0 6px 0;
			letter-spacing: -0.3px;
		}
		.welcome-desc {
			font-size: 13px;
			color: #64748b;
			margin-bottom: 0;
			line-height: 1.45;
		}
		.welcome-image-side {
			position: absolute;
			right: 0;
			top: 0;
			bottom: 0;
			width: 46%;
			display: flex;
			align-items: center;
			justify-content: flex-end;
			overflow: hidden;
			z-index: 1;
		}
		.welcome-building-img {
			height: 100%;
			width: 100%;
			object-fit: cover;
			object-position: right center;
			mask-image: linear-gradient(to right, transparent 0%, black 28%);
			-webkit-mask-image: linear-gradient(to right, transparent 0%, black 28%);
		}

		/* 5. Kolom Kanan: Card Status Loket Pelayanan */
		.card-panel-white {
			background: #ffffff;
			border-radius: 20px;
			border: 1px solid var(--card-border);
			box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);
			padding: 20px 24px;
			display: flex;
			flex-direction: column;
		}
		.card-panel-header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 16px;
		}
		.card-panel-title {
			font-size: 16.5px;
			font-weight: 800;
			color: #0f172a;
			display: flex;
			align-items: center;
			gap: 8px;
		}

		/* List Item Loket Pelayanan */
		.loket-row-item {
			background: #ffffff;
			border-radius: 14px;
			padding: 10px 14px;
			border: 1px solid #f1f5f9;
			display: flex;
			align-items: center;
			justify-content: space-between;
			transition: all 0.2s ease;
			margin-bottom: 8px;
		}
		.loket-row-item:last-child {
			margin-bottom: 0;
		}
		.loket-row-item.is-active {
			background: #f8fbff;
			border-color: #dbeafe;
		}
		.loket-icon-sq {
			width: 42px;
			height: 42px;
			border-radius: 12px;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 18px;
			flex-shrink: 0;
			margin-right: 12px;
		}
		.icon-blue { background: #e0f2fe; color: #0284c7; }
		.icon-green { background: #dcfce7; color: #16a34a; }
		.icon-purple { background: #f3e8ff; color: #7c3aed; }

		.loket-title-txt {
			font-size: 14.5px;
			font-weight: 800;
			color: #0f172a;
			line-height: 1.2;
		}
		.loket-desc-txt {
			font-size: 12px;
			color: #64748b;
			margin-top: 1px;
		}

		/* Status Badges di Loket */
		.badge-status-pill {
			border-radius: 50px;
			padding: 5px 14px;
			font-size: 12px;
			font-weight: 700;
			display: inline-flex;
			align-items: center;
			gap: 6px;
			white-space: nowrap;
		}
		.badge-calling {
			background: #dcfce7;
			color: #15803d;
			border: 1px solid #bbf7d0;
		}
		.badge-serving {
			background: #dbeafe;
			color: #1d4ed8;
			border: 1px solid #bfdbfe;
		}
		.badge-waiting {
			background: #fef3c7;
			color: #b45309;
			border: 1px solid #fde68a;
		}

		/* 6. Card Riwayat Panggilan Terakhir */
		.recent-row-item {
			padding: 9px 4px;
			display: flex;
			align-items: center;
			border-bottom: 1px solid #f1f5f9;
		}
		.recent-row-item:last-child {
			border-bottom: none;
			padding-bottom: 0;
		}
		.recent-ticket-pill {
			border-radius: 20px;
			padding: 3px 12px;
			font-size: 12.5px;
			font-weight: 800;
			font-family: 'JetBrains Mono', monospace;
			margin-right: 14px;
		}
		.pill-blue   { background: #dbeafe; color: #1e40af; }
		.pill-green  { background: #dcfce7; color: #15803d; }
		.pill-purple { background: #f3e8ff; color: #7e22ce; }

		.recent-loket-txt {
			font-size: 13.5px;
			font-weight: 700;
			color: #1e293b;
		}
		.recent-time-txt {
			font-size: 12.5px;
			color: #64748b;
			font-family: monospace;
			margin-left: auto;
		}

		/* 7. Footer Running Text Pengumuman (Floating Pill) */
		.tv-footer-container {
			padding: 0 28px 16px 28px;
		}
		.tv-footer-pill {
			background: #ffffff;
			border-radius: 50px;
			border: 1px solid var(--card-border);
			padding: 9px 24px;
			display: flex;
			align-items: center;
			box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
		}
		.footer-badge-pill {
			background: #0d6efd;
			color: #ffffff;
			border-radius: 50px;
			padding: 5px 16px;
			font-size: 12.5px;
			font-weight: 800;
			letter-spacing: 0.5px;
			display: flex;
			align-items: center;
			gap: 6px;
			flex-shrink: 0;
		}
		.footer-v-divider {
			width: 1px;
			height: 20px;
			background-color: #cbd5e1;
			margin: 0 16px;
			flex-shrink: 0;
		}
		.footer-marquee-txt {
			font-size: 13.5px;
			font-weight: 600;
			color: #334155;
		}

		@media (max-width: 991px) {
			.tv-main-container {
				flex-direction: column;
				padding: 16px;
			}
			.ticket-number-giant {
				font-size: 64px;
			}
			.welcome-image-side {
				display: none;
			}
		}
	</style>
</head>
<body>

<!-- 1. HEADER ATAS (NAVBAR MONITOR) -->
<header class="tv-header">
	<div class="d-flex align-items-center gap-3">
		<img src="<?= asset_url('assets/img/logo-sekolah.png') ?>" alt="Logo Sekolah" class="school-logo-img" onerror="this.src='<?= asset_url('assets/img/icon.png') ?>'">
		<div>
			<div class="tv-school-title">
				<?= e($school['school_name'] ?: 'SMK MUHAMMADIYAH BANDONGAN') ?>
			</div>
			<div class="tv-school-sub">
				Monitor Antrean Pelayanan Tata Usaha Digital
			</div>
		</div>
	</div>

	<div class="d-flex align-items-center gap-3">
		<!-- Kapsul Tanggal & Jam Aktif -->
		<div class="header-datetime-capsule">
			<span id="liveHeaderDate"><i class="fa-regular fa-calendar me-1"></i> <?= tgl_indo(date('Y-m-d')) ?></span>
			<span id="liveHeaderTime" class="fw-bold font-monospace"><i class="fa-regular fa-clock me-1"></i> --:--:--</span>
		</div>

		<!-- Tombol Kontrol Suara & Layar Penuh -->
		<button id="btnHeaderAudio" class="btn header-pill-btn" onclick="toggleAudio()">
			<i class="fa-solid fa-volume-high me-1"></i> <span id="headerAudioText">Audio Aktif</span>
		</button>
		<button class="btn header-pill-btn px-3" onclick="toggleFullScreen()" title="Layar Penuh">
			<i class="fa-solid fa-expand"></i>
		</button>
	</div>
</header>

<!-- 2. MAIN CONTENT GRID (KOLOM KIRI & KANAN) -->
<main class="tv-main-container">
	
	<!-- KOLOM KIRI -->
	<div class="tv-col-left">
		
		<!-- Card Hero: Panggilan Antrean -->
		<div class="card-call-hero" id="mainCallHero">
			<div class="call-hero-header">
				<div class="call-hero-title">
					<i class="fa-solid fa-bullhorn fs-5"></i> Panggilan Antrean
				</div>
				<div class="status-calling-badge <?= !empty($last_called) ? '' : 'is-idle' ?>" id="callStatusBadge">
					<i class="fa-solid fa-volume-high me-1"></i> 
					<span id="callStatusText"><?= !empty($last_called) ? 'Sedang Memanggil' : 'Menunggu Panggilan' ?></span>
				</div>
			</div>

			<!-- Tampilan Nomor Tiket & Loket -->
			<div class="call-center-content">
				<div>
					<span class="loket-pill-badge" id="mainLoketBadge">
						LOKET <?= !empty($last_called) ? (int) $last_called['loket_number'] : 1 ?> (<?= !empty($last_called['service_code']) ? e($last_called['service_code']) : 'A' ?>)
					</span>
				</div>

				<div class="ticket-number-giant" id="mainTicketNumber">
					<?= !empty($last_called) ? e($last_called['ticket_number']) : 'A-001' ?>
				</div>

				<div class="service-caption-text" id="mainServiceCaption">
					<i class="fa-solid fa-file-lines me-2"></i>
					<span><?= !empty($last_called) ? e($last_called['service_name']) : 'Layanan Surat & Legalisir' ?></span>
				</div>
			</div>
		</div>

		<!-- Card Bawah Kiri: Sambutan & Fasad Gedung Sekolah -->
		<div class="card-school-welcome">
			<div class="welcome-text-side">
				<div class="welcome-intro">Selamat Datang di</div>
				<h2 class="welcome-school-name"><?= e($school['school_name'] ?: 'SMK Muhammadiyah Bandongan') ?></h2>
				<p class="welcome-desc">Pelayanan Tata Usaha yang Tertib, Cepat, dan Transparan.</p>
			</div>

			<div class="welcome-image-side">
				<img src="<?= asset_url('assets/img/kiosk_school_banner_faded.png') ?>" alt="Gedung SMK Muhammadiyah Bandongan" class="welcome-building-img" onerror="this.src='<?= asset_url('assets/img/gedung-smk.jpg') ?>'">
			</div>
		</div>

	</div>

	<!-- KOLOM KANAN -->
	<div class="tv-col-right">
		
		<!-- Card Atas Kanan: Status Loket Pelayanan -->
		<div class="card-panel-white">
			<div class="card-panel-header">
				<div class="card-panel-title">
					<i class="fa-solid fa-layer-group text-primary"></i> Status Loket Pelayanan
				</div>
			</div>

			<!-- List Status Tiap Loket (Loket 1, 2, 3) -->
			<div class="d-flex flex-column" id="loketRowsList">
				<?php 
				$service_icons = array(
					1 => array('icon' => 'fa-file-lines', 'class' => 'icon-blue'),
					2 => array('icon' => 'fa-coins',      'class' => 'icon-blue'),
					3 => array('icon' => 'fa-users',      'class' => 'icon-purple'),
				);

				foreach ($lokets as $num => $lk): 
					$ico = isset($service_icons[$num]) ? $service_icons[$num] : array('icon' => 'fa-desktop', 'class' => 'icon-blue');
					$status = isset($lk['status']) ? $lk['status'] : 'idle';
					$current = $lk['current_ticket'];
					$waiting = (int) $lk['waiting_count'];
				?>
					<div class="loket-row-item <?= $current !== '-' ? 'is-active' : '' ?>" id="loketRow_<?= $num ?>">
						<div class="d-flex align-items-center">
							<div class="loket-icon-sq <?= $ico['class'] ?>">
								<i class="fa-solid <?= $ico['icon'] ?>"></i>
							</div>
							<div>
								<div class="loket-title-txt">Loket <?= $num ?> (<?= e($lk['service_code']) ?>)</div>
								<div class="loket-desc-txt"><?= e($lk['service_name']) ?></div>
							</div>
						</div>

						<div id="loketBadgeContainer_<?= $num ?>">
							<?php if ($status === 'calling'): ?>
								<span class="badge-status-pill badge-calling">
									<i class="fa-solid fa-circle" style="font-size: 7px;"></i> Memanggil: <?= e($current) ?>
								</span>
							<?php elseif ($status === 'serving'): ?>
								<span class="badge-status-pill badge-serving">
									<i class="fa-solid fa-circle" style="font-size: 7px;"></i> Sedang Dilayani: <?= e($current) ?>
								</span>
							<?php else: ?>
								<span class="badge-status-pill badge-waiting">
									<i class="fa-solid fa-circle" style="font-size: 7px;"></i> Menunggu (<?= $waiting ?> Antrean)
								</span>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<!-- Card Bawah Kanan: Riwayat Panggilan Terakhir -->
		<div class="card-panel-white">
			<div class="card-panel-header">
				<div class="card-panel-title">
					<i class="fa-solid fa-clock-rotate-left text-primary"></i> Riwayat Panggilan Terakhir
				</div>
				<span class="badge bg-light text-secondary border rounded-pill px-3 py-1 fw-bold" style="font-size: 11.5px;">
					3 Panggilan Terakhir
				</span>
			</div>

			<!-- List 3 Riwayat Panggilan Terakhir -->
			<div class="d-flex flex-column" id="recentCallsList">
				<?php 
				$pill_classes = array('pill-blue', 'pill-green', 'pill-purple');
				if ( ! empty($recent_called)): 
					$idx = 0;
					foreach ($recent_called as $rc): 
						$pill_cls = $pill_classes[$idx % count($pill_classes)];
						$called_time = !empty($rc['called_at']) ? date('H:i:s', strtotime($rc['called_at'])) : '-';
						$idx++;
				?>
						<div class="recent-row-item">
							<span class="recent-ticket-pill <?= $pill_cls ?>"><?= e($rc['ticket_number']) ?></span>
							<span class="recent-loket-txt">Loket <?= (int) $rc['loket_number'] ?> &ndash; <?= e($rc['service_name']) ?></span>
							<span class="recent-time-txt"><i class="fa-regular fa-clock me-1"></i> <?= $called_time ?></span>
						</div>
					<?php endforeach; ?>
				<?php else: ?>
					<div class="recent-row-item">
						<span class="recent-ticket-pill pill-blue">A-004</span>
						<span class="recent-loket-txt">Loket 1 &ndash; Layanan Surat &amp; Legalisir</span>
						<span class="recent-time-txt"><i class="fa-regular fa-clock me-1"></i> 08:08:12</span>
					</div>
					<div class="recent-row-item">
						<span class="recent-ticket-pill pill-green">B-011</span>
						<span class="recent-loket-txt">Loket 2 &ndash; Keuangan &amp; Administrasi SPP</span>
						<span class="recent-time-txt"><i class="fa-regular fa-clock me-1"></i> 08:05:36</span>
					</div>
					<div class="recent-row-item">
						<span class="recent-ticket-pill pill-purple">C-002</span>
						<span class="recent-loket-txt">Loket 3 &ndash; Layanan Umum &amp; Mutasi Siswa</span>
						<span class="recent-time-txt"><i class="fa-regular fa-clock me-1"></i> 07:59:21</span>
					</div>
				<?php endif; ?>
			</div>
		</div>

	</div>

</main>

<!-- 3. FOOTER RUNNING TEXT (PENGUMUMAN) -->
<footer class="tv-footer-container">
	<div class="tv-footer-pill">
		<div class="footer-badge-pill">
			<i class="fa-solid fa-bullhorn"></i> PENGUMUMAN
		</div>
		<div class="footer-v-divider"></div>
		<div class="flex-grow-1 overflow-hidden" style="white-space: nowrap;">
			<marquee behavior="scroll" direction="left" scrollamount="6" class="footer-marquee-txt">
				<?= e($school['queue_running_text'] ?: 'Mohon menyiapkan dokumen administrasi sebelum menuju loket pelayanan. Terima kasih atas kerja sama Anda.') ?>
			</marquee>
		</div>
	</div>
</footer>

<script>
// Jam & Tanggal Digital Real-Time
const DAYS_ID = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
const MONTHS_ID = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

function updateClock() {
	const now = new Date();
	const h = String(now.getHours()).padStart(2, '0');
	const m = String(now.getMinutes()).padStart(2, '0');
	const s = String(now.getSeconds()).padStart(2, '0');

	const dateEl = document.getElementById('liveHeaderDate');
	if (dateEl) {
		dateEl.innerHTML = `<i class="fa-regular fa-calendar me-1"></i> ${DAYS_ID[now.getDay()]}, ${now.getDate()} ${MONTHS_ID[now.getMonth()]} ${now.getFullYear()}`;
	}

	const timeEl = document.getElementById('liveHeaderTime');
	if (timeEl) {
		timeEl.innerHTML = `<i class="fa-regular fa-clock me-1"></i> ${h}:${m}:${s}`;
	}
}
setInterval(updateClock, 1000);
updateClock();

// Fullscreen Toggle
function toggleFullScreen() {
	if (!document.fullscreenElement) {
		document.documentElement.requestFullscreen().catch(err => {});
	} else {
		if (document.exitFullscreen) document.exitFullscreen();
	}
}

// --------------------------------------------------------------------------
// AUDIO BELL CHIME & SPEECH SYNTHESIS ENGINE
// --------------------------------------------------------------------------
let audioContext = null;
let audioEnabled = true;

function initAudioContext() {
	if (!audioContext) {
		const AudioCtx = window.AudioContext || window.webkitAudioContext;
		if (AudioCtx) audioContext = new AudioCtx();
	}
	if (audioContext && audioContext.state === 'suspended') {
		audioContext.resume();
	}
}

function playDingDong() {
	if (!audioEnabled) return Promise.resolve();
	initAudioContext();
	if (!audioContext) return Promise.resolve();

	return new Promise((resolve) => {
		const now = audioContext.currentTime;

		// Nada 1: 587.33 Hz (D5)
		const osc1 = audioContext.createOscillator();
		const gain1 = audioContext.createGain();
		osc1.type = 'sine';
		osc1.frequency.setValueAtTime(587.33, now);
		gain1.gain.setValueAtTime(0.5, now);
		gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.6);
		osc1.connect(gain1);
		gain1.connect(audioContext.destination);
		osc1.start(now);
		osc1.stop(now + 0.6);

		// Nada 2: 880 Hz (A5)
		const osc2 = audioContext.createOscillator();
		const gain2 = audioContext.createGain();
		osc2.type = 'sine';
		osc2.frequency.setValueAtTime(880, now + 0.35);
		gain2.gain.setValueAtTime(0.6, now + 0.35);
		gain2.gain.exponentialRampToValueAtTime(0.001, now + 1.2);
		osc2.connect(gain2);
		gain2.connect(audioContext.destination);
		osc2.start(now + 0.35);
		osc2.stop(now + 1.2);

		setTimeout(resolve, 1100);
	});
}

function speakTicket(ticketNumber, loketNumber) {
	if (!audioEnabled || !('speechSynthesis' in window)) return;

	const parts = ticketNumber.split('-');
	const letter = parts[0] || '';
	const digits = parts[1] || '';

	let spellDigits = '';
	for (let i = 0; i < digits.length; i++) {
		const d = digits[i];
		if (d === '0') spellDigits += ' kosong';
		else if (d === '1') spellDigits += ' satu';
		else if (d === '2') spellDigits += ' dua';
		else if (d === '3') spellDigits += ' tiga';
		else if (d === '4') spellDigits += ' empat';
		else if (d === '5') spellDigits += ' lima';
		else if (d === '6') spellDigits += ' enam';
		else if (d === '7') spellDigits += ' tujuh';
		else if (d === '8') spellDigits += ' delapan';
		else if (d === '9') spellDigits += ' sembilan';
	}

	const textToSpeak = `Nomor antrean, ${letter}, ${spellDigits}, silakan menuju ke loket ${loketNumber}`;
	const utterance = new SpeechSynthesisUtterance(textToSpeak);
	utterance.lang = 'id-ID';
	utterance.rate = 0.9;
	utterance.pitch = 1.0;

	const voices = window.speechSynthesis.getVoices();
	const idVoice = voices.find(v => v.lang.includes('id') || v.lang.includes('ID'));
	if (idVoice) utterance.voice = idVoice;

	window.speechSynthesis.speak(utterance);
}

function announceTicket(ticketNumber, loketNumber) {
	playDingDong().then(() => {
		speakTicket(ticketNumber, loketNumber);
	});
}

function toggleAudio() {
	audioEnabled = !audioEnabled;
	initAudioContext();
	const btnText = document.getElementById('headerAudioText');
	const btn = document.getElementById('btnHeaderAudio');
	if (audioEnabled) {
		if (btnText) btnText.textContent = 'Audio Aktif';
		if (btn) btn.innerHTML = '<i class="fa-solid fa-volume-high me-1"></i> <span id="headerAudioText">Audio Aktif</span>';
		playDingDong();
	} else {
		if (btnText) btnText.textContent = 'Audio Mute';
		if (btn) btn.innerHTML = '<i class="fa-solid fa-volume-xmark me-1"></i> <span id="headerAudioText">Audio Mute</span>';
	}
}

document.body.addEventListener('click', function() {
	initAudioContext();
}, { once: true });

// --------------------------------------------------------------------------
// SMART REAL-TIME ENGINE (Version-Cached & Instant Sync)
// --------------------------------------------------------------------------
let lastCalledId = null;
let lastCalledTime = null;
let currentQueueVersion = '';
let pollIntervalTimer = null;

function pollDisplayData(force = false) {
	const url = '<?= site_url("antrean/api_display_data") ?>' + (force ? '' : '?v=' + encodeURIComponent(currentQueueVersion));
	fetch(url)
		.then(res => res.json())
		.then(data => {
			if (!data.success) return;

			if (data.changed === false) {
				return; // Tidak ada perubahan, hemat CPU
			}

			if (data.version) {
				currentQueueVersion = data.version;
			}

			// 1. Update Kartu Utama Panggilan
			if (data.last_called) {
				const call = data.last_called;
				const isNewCall = (call.id !== lastCalledId || call.called_at !== lastCalledTime);

				const ticketEl = document.getElementById('mainTicketNumber');
				if (ticketEl) ticketEl.textContent = call.ticket_number;

				const badgeEl = document.getElementById('mainLoketBadge');
				if (badgeEl) badgeEl.textContent = `LOKET ${call.loket_number} (${call.service_code || 'A'})`;

				const captionEl = document.getElementById('mainServiceCaption');
				if (captionEl) captionEl.innerHTML = `<i class="fa-solid fa-file-lines me-2"></i> <span>${call.service_name}</span>`;

				const heroCard = document.getElementById('mainCallHero');
				const callStatusBadge = document.getElementById('callStatusBadge');
				const callStatusText = document.getElementById('callStatusText');

				if (callStatusBadge && callStatusText) {
					callStatusBadge.classList.remove('is-idle');
					callStatusText.textContent = 'Sedang Memanggil';
				}

				if (isNewCall && lastCalledTime !== null) {
					if (heroCard) {
						heroCard.classList.add('is-calling-pulse');
						setTimeout(() => heroCard.classList.remove('is-calling-pulse'), 8000);
					}
					announceTicket(call.ticket_number, call.loket_number);
				}

				lastCalledId = call.id;
				lastCalledTime = call.called_at;
			}

			// 2. Update Status Loket Pelayanan
			if (data.lokets) {
				for (const [num, lk] of Object.entries(data.lokets)) {
					const rowEl = document.getElementById('loketRow_' + num);
					const badgeContainer = document.getElementById('loketBadgeContainer_' + num);
					if (!badgeContainer) continue;

					if (rowEl) {
						if (lk.current_ticket !== '-') {
							rowEl.classList.add('is-active');
						} else {
							rowEl.classList.remove('is-active');
						}
					}

					if (lk.status === 'calling') {
						badgeContainer.innerHTML = `<span class="badge-status-pill badge-calling"><i class="fa-solid fa-circle" style="font-size: 7px;"></i> Memanggil: ${lk.current_ticket}</span>`;
					} else if (lk.status === 'serving') {
						badgeContainer.innerHTML = `<span class="badge-status-pill badge-serving"><i class="fa-solid fa-circle" style="font-size: 7px;"></i> Sedang Dilayani: ${lk.current_ticket}</span>`;
					} else {
						badgeContainer.innerHTML = `<span class="badge-status-pill badge-waiting"><i class="fa-solid fa-circle" style="font-size: 7px;"></i> Menunggu (${lk.waiting_count} Antrean)</span>`;
					}
				}
			}

			// 3. Update Riwayat Panggilan Terakhir
			if (data.recent_called && data.recent_called.length > 0) {
				const recentList = document.getElementById('recentCallsList');
				if (recentList) {
					const pillClasses = ['pill-blue', 'pill-green', 'pill-purple'];
					let html = '';
					data.recent_called.forEach((rc, i) => {
						const pillCls = pillClasses[i % pillClasses.length];
						const timeStr = rc.called_at ? rc.called_at.substring(11, 19) : '--:--:--';
						html += `
							<div class="recent-row-item">
								<span class="recent-ticket-pill ${pillCls}">${rc.ticket_number}</span>
								<span class="recent-loket-txt">Loket ${rc.loket_number} &ndash; ${rc.service_name}</span>
								<span class="recent-time-txt"><i class="fa-regular fa-clock me-1"></i> ${timeStr}</span>
							</div>
						`;
					});
					recentList.innerHTML = html;
				}
			}
		})
		.catch(err => {});
}

// --------------------------------------------------------------------------
// SINKRONISASI INSTAN 0.00 DETIK VIA BROADCAST CHANNEL & STORAGE
// --------------------------------------------------------------------------
if (window.BroadcastChannel) {
	try {
		const queueChannel = new BroadcastChannel('tu_queue_sync');
		queueChannel.onmessage = function(e) {
			pollDisplayData(true);
		};
	} catch(e) {}
}

window.addEventListener('storage', function(e) {
	if (e.key === 'tu_queue_action') {
		pollDisplayData(true);
	}
});

// Polling adaptif
setInterval(() => pollDisplayData(false), 2500);
pollDisplayData(true);
</script>

</body>
</html>
