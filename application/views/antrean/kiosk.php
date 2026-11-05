<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?= e($page_title) ?> &middot; <?= e($school['school_name'] ?: 'SMK Muhammadiyah Bandongan') ?></title>
	
	<!-- Favicon -->
	<link rel="icon" type="image/png" href="<?= base_url('favicon.png') ?>">
	<link rel="shortcut icon" href="<?= base_url('favicon.ico') ?>">

	<!-- Preload Assets -->
	<link rel="preload" href="<?= asset_url('assets/img/logo-sekolah.png') ?>" as="image" type="image/png" fetchpriority="high">
	<link rel="preload" href="<?= asset_url('assets/vendor/bootstrap/css/bootstrap.min.css') ?>" as="style">
	<link rel="stylesheet" href="<?= asset_url('assets/vendor/bootstrap/css/bootstrap.min.css') ?>">
	<link rel="stylesheet" href="<?= asset_url('assets/vendor/fontawesome/css/all.min.css') ?>" media="print" onload="this.media='all'">
	<noscript><link rel="stylesheet" href="<?= asset_url('assets/vendor/fontawesome/css/all.min.css') ?>"></noscript>

	<style>
		:root {
			--page-bg: #f0f4f9;
			--navy-dark: #0f172a;
			--brand-blue: #0d5ecc;
			--brand-cyan: #38bdf8;
		}

		* {
			box-sizing: border-box;
		}

		body {
			font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
			background-color: var(--page-bg);
			min-height: 100vh;
			display: flex;
			flex-direction: column;
			color: #1e293b;
			user-select: none;
			-webkit-user-select: none;
			overflow-x: hidden;
			margin: 0;
			padding: 0;
		}

		/* 1. Header */
		.kiosk-header {
			background: #ffffff;
			border-bottom: 1px solid #e2e8f0;
			padding: 12px 32px;
			position: relative;
			z-index: 20;
		}
		.school-logo {
			width: 48px;
			height: 48px;
			object-fit: contain;
			filter: drop-shadow(0 2px 4px rgba(0,0,0,0.06));
		}
		.school-title {
			color: #0f2444;
			font-size: 16.5px;
			letter-spacing: -0.2px;
			line-height: 1.25;
		}
		.school-sub {
			color: #64748b;
			font-size: 12px;
			font-weight: 500;
		}
		.kiosk-clock-pill {
			background: #edf4fe;
			border: 1px solid #d8e6fa;
			border-radius: 50px;
			padding: 6px 16px;
			color: #0b326f;
			font-size: 13.5px;
		}
		.btn-navy-pill {
			background: #172b4d;
			color: #ffffff;
			border-radius: 50px;
			padding: 7px 18px;
			font-size: 13.5px;
			font-weight: 600;
			border: none;
			transition: all 0.2s ease;
		}
		.btn-navy-pill:hover {
			background: #0f1d34;
			color: #ffffff;
			transform: translateY(-1px);
		}
		.btn-primary-pill {
			background: #0d6efd;
			color: #ffffff;
			border-radius: 50px;
			padding: 7px 20px;
			font-size: 13.5px;
			font-weight: 600;
			border: none;
			transition: all 0.2s ease;
		}
		.btn-primary-pill:hover {
			background: #0b5ed7;
			color: #ffffff;
			transform: translateY(-1px);
			box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
		}

		/* 2. Hero Section */
		.kiosk-hero {
			background: linear-gradient(110deg, #0947b4 0%, #0d5ecc 42%, #1977f3 78%, #2563eb 100%);
			position: relative;
			padding: 38px 32px 85px 32px;
			overflow: hidden;
		}
		.kiosk-hero-welcome {
			font-size: 17px;
			font-weight: 500;
			color: #e0f2fe;
			letter-spacing: 0.2px;
		}
		.kiosk-hero-title {
			font-size: 38px;
			font-weight: 900;
			letter-spacing: -0.5px;
			line-height: 1.15;
			color: #ffffff;
		}
		.text-cyan {
			color: #38bdf8 !important;
		}
		.kiosk-hero-sub {
			font-size: 15px;
			color: rgba(255, 255, 255, 0.9);
			font-weight: 400;
			max-width: 480px;
		}
		.cyan-accent-line {
			width: 46px;
			height: 5px;
			background: #38bdf8;
			border-radius: 10px;
		}
		.kiosk-building-wrapper {
			position: absolute;
			right: 0;
			top: 0;
			bottom: 0;
			width: 48%;
			max-width: 520px;
			display: flex;
			align-items: center;
			justify-content: flex-end;
			pointer-events: none;
			overflow: hidden;
			z-index: 1;
		}
		.kiosk-building-img {
			height: 100%;
			width: 100%;
			object-fit: cover;
			object-position: right center;
			opacity: 0.96;
		}

		/* 3. Main Container & Cards Grid */
		.kiosk-content-wrapper {
			position: relative;
			z-index: 10;
			margin-top: -60px;
			flex: 1;
		}
		.kiosk-card {
			background: #ffffff;
			border-radius: 22px;
			border: 1px solid #eef2f6;
			box-shadow: 0 10px 25px -4px rgba(15, 23, 42, 0.07), 0 4px 10px -2px rgba(15, 23, 42, 0.03);
			padding: 24px;
			display: flex;
			flex-direction: column;
			justify-content: space-between;
			position: relative;
			overflow: hidden;
			transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
			cursor: pointer;
		}
		.kiosk-card:hover {
			transform: translateY(-6px);
			box-shadow: 0 20px 35px -8px rgba(15, 23, 42, 0.12);
		}
		.kiosk-card:active {
			transform: translateY(-2px) scale(0.99);
		}
		.kiosk-card.disabled {
			pointer-events: none;
			opacity: 0.6;
		}

		/* Radial Corner Accents per Theme */
		.card-radial-accent {
			position: absolute;
			top: 0;
			right: 0;
			width: 140px;
			height: 140px;
			pointer-events: none;
			border-radius: 0 22px 0 0;
		}
		.theme-blue .card-radial-accent {
			background: radial-gradient(circle at top right, rgba(14, 165, 233, 0.16) 0%, transparent 68%);
		}
		.theme-green .card-radial-accent {
			background: radial-gradient(circle at top right, rgba(22, 163, 74, 0.16) 0%, transparent 68%);
		}
		.theme-purple .card-radial-accent {
			background: radial-gradient(circle at top right, rgba(124, 58, 237, 0.16) 0%, transparent 68%);
		}

		/* Service Icon Box */
		.service-icon-box {
			width: 64px;
			height: 64px;
			border-radius: 18px;
			display: flex;
			align-items: center;
			justify-content: center;
			flex-shrink: 0;
		}
		.theme-blue .service-icon-box {
			background: #e0f2fe;
			color: #0284c7;
		}
		.theme-green .service-icon-box {
			background: #dcfce7;
			color: #16a34a;
		}
		.theme-purple .service-icon-box {
			background: #f3e8ff;
			color: #7c3aed;
		}

		.service-title {
			color: #0f172a;
			font-size: 19px;
			font-weight: 800;
			letter-spacing: -0.2px;
			line-height: 1.3;
		}
		.service-desc {
			color: #64748b;
			font-size: 13px;
			line-height: 1.5;
			min-height: 38px;
		}

		/* Waiting Box */
		.waiting-box {
			display: flex;
			align-items: center;
			gap: 12px;
		}
		.waiting-icon {
			font-size: 20px;
		}
		.theme-blue .waiting-icon { color: #0284c7; }
		.theme-green .waiting-icon { color: #16a34a; }
		.theme-purple .waiting-icon { color: #7c3aed; }

		.waiting-label {
			font-size: 11.5px;
			color: #64748b;
			font-weight: 600;
			text-transform: capitalize;
			line-height: 1.2;
		}
		.waiting-count {
			font-size: 17.5px;
			font-weight: 800;
			color: #0f172a;
			line-height: 1.2;
		}

		/* Full-width Action Buttons */
		.btn-kiosk-action {
			border-radius: 50px;
			padding: 12px 24px;
			font-weight: 700;
			font-size: 14.5px;
			color: #ffffff;
			border: none;
			display: flex;
			align-items: center;
			justify-content: space-between;
			transition: all 0.2s ease;
			box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
		}
		.theme-blue .btn-kiosk-action {
			background: #0d6efd;
		}
		.theme-blue .btn-kiosk-action:hover {
			background: #0b5ed7;
			box-shadow: 0 8px 20px rgba(13, 110, 253, 0.35);
		}
		.theme-green .btn-kiosk-action {
			background: #16a34a;
		}
		.theme-green .btn-kiosk-action:hover {
			background: #15803d;
			box-shadow: 0 8px 20px rgba(22, 163, 74, 0.35);
		}
		.theme-purple .btn-kiosk-action {
			background: #7c3aed;
		}
		.theme-purple .btn-kiosk-action:hover {
			background: #6d28d9;
			box-shadow: 0 8px 20px rgba(124, 58, 237, 0.35);
		}

		/* 4. Bottom Information Bar */
		.kiosk-info-pill {
			background: #ffffff;
			border-radius: 50px;
			border: 1px solid #e2e8f0;
			padding: 12px 28px;
			display: flex;
			align-items: center;
			gap: 16px;
			box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
		}
		.info-tag {
			font-size: 14.5px;
			color: #0d5ecc;
			display: flex;
			align-items: center;
			gap: 8px;
		}
		.info-divider {
			width: 1px;
			height: 20px;
			background: #cbd5e1;
		}
		.info-text {
			font-size: 13.5px;
			color: #475569;
			font-weight: 500;
		}

		@media (max-width: 991px) {
			.kiosk-hero {
				padding: 28px 20px 75px 20px;
			}
			.kiosk-hero-title {
				font-size: 30px;
			}
			.kiosk-building-wrapper {
				opacity: 0.3;
				width: 60%;
			}
			.kiosk-content-wrapper {
				margin-top: -50px;
			}
		}

		@media (max-width: 767px) {
			.kiosk-header {
				padding: 10px 16px;
			}
			.school-title {
				font-size: 14px;
			}
			.kiosk-clock-pill {
				display: none !important;
			}
			.kiosk-building-wrapper {
				display: none;
			}
			.kiosk-hero {
				padding: 24px 16px 65px 16px;
			}
			.kiosk-hero-title {
				font-size: 26px;
			}
			.kiosk-content-wrapper {
				margin-top: -45px;
			}
			.kiosk-info-pill {
				border-radius: 16px;
				flex-direction: column;
				align-items: flex-start;
				gap: 8px;
				padding: 14px 18px;
			}
			.info-divider {
				display: none;
			}
		}
	</style>
</head>
<body>

<!-- 1. HEADER ATAS -->
<header class="kiosk-header">
	<div class="container-fluid d-flex justify-content-between align-items-center">
		<!-- Brand & Identitas Sekolah -->
		<div class="d-flex align-items-center gap-3">
			<img src="<?= asset_url('assets/img/logo-sekolah.png') ?>" alt="Logo" class="school-logo" onerror="this.src='<?= asset_url('assets/img/icon.png') ?>'">
			<div>
				<h1 class="h6 mb-0 fw-bold school-title"><?= e($school['school_name'] ?: 'SMK Muhammadiyah Bandongan') ?></h1>
				<div class="school-sub">Sistem Antrean Layanan Mandiri Tata Usaha (Kiosk)</div>
			</div>
		</div>

		<!-- Tombol & Jam Kiosk -->
		<div class="d-flex align-items-center gap-2 gap-sm-3">
			<div class="kiosk-clock-pill d-flex align-items-center gap-2">
				<i class="fa-regular fa-clock text-primary"></i>
				<span id="liveClock" class="fw-bold font-monospace">00:00:00 WIB</span>
			</div>
			<a href="<?= site_url('antrean/display') ?>" target="_blank" class="btn btn-navy-pill d-inline-flex align-items-center">
				<i class="fa-solid fa-tv me-1"></i> Monitor TV
			</a>
			<a href="<?= site_url('login') ?>" class="btn btn-primary-pill d-inline-flex align-items-center shadow-sm">
				<i class="fa-solid fa-user me-1"></i> Login Staf
			</a>
		</div>
	</div>
</header>

<!-- 2. HERO BANNER BIRU DENGAN GRAFIK GEDUNG SEKOLAH -->
<section class="kiosk-hero">
	<div class="container-fluid px-3 px-lg-4 position-relative" style="z-index: 2;">
		<div class="row align-items-center">
			<div class="col-lg-7 col-md-8">
				<div class="kiosk-hero-welcome mb-1">Selamat Datang di</div>
				<h2 class="kiosk-hero-title mb-2">
					Layanan <span class="text-cyan">Tata Usaha</span>
				</h2>
				<p class="kiosk-hero-sub mb-3">
					Silakan pilih jenis layanan di bawah ini untuk mengambil nomor antrean.
				</p>
				<div class="cyan-accent-line"></div>
			</div>
		</div>
	</div>

	<!-- Foto Gedung SMK Muhammadiyah Bandongan di sebelah kanan -->
	<div class="kiosk-building-wrapper">
		<img src="<?= asset_url('assets/img/kiosk_school_banner_faded.png') ?>" alt="Gedung SMK Muhammadiyah Bandongan" class="kiosk-building-img" onerror="this.style.display='none'">
	</div>
</section>

<!-- 3. KARTU LAYANAN ANTREAN (GRID) -->
<main class="kiosk-content-wrapper px-3 px-lg-4 pb-3">
	<div class="container-fluid">
		<div class="row g-4 justify-content-center">
			<?php 
			// Pemetaan tema warna & ikon persis seperti mockup gambar
			$theme_presets = array(
				'A' => array('class' => 'theme-blue',   'icon' => 'fa-file-signature', 'btn_text' => 'Ambil Tiket'),
				'B' => array('class' => 'theme-green',  'icon' => 'fa-coins',          'btn_text' => 'Ambil Tiket'),
				'C' => array('class' => 'theme-purple', 'icon' => 'fa-graduation-cap', 'btn_text' => 'Ambil Tiket'),
			);

			$i = 0;
			$fallback_themes = array('theme-blue', 'theme-green', 'theme-purple');

			foreach ($services as $srv): 
				$code = strtoupper(trim($srv['code']));
				if (isset($theme_presets[$code])) {
					$theme = $theme_presets[$code];
				} else {
					$theme_class = $fallback_themes[$i % count($fallback_themes)];
					$theme = array('class' => $theme_class, 'icon' => 'fa-ticket', 'btn_text' => 'Ambil Tiket');
				}
				$waiting = isset($stats[$srv['id']]['waiting']) ? $stats[$srv['id']]['waiting'] : 0;
				$i++;
			?>
				<div class="col-lg-4 col-md-6">
					<div class="kiosk-card h-100 <?= $theme['class'] ?> service-btn" onclick="ambilTiket(<?= (int)$srv['id'] ?>, '<?= e(addslashes($srv['name'])) ?>')">
						<!-- Aksen kurva radial pojok kanan atas -->
						<div class="card-radial-accent"></div>

						<!-- Header Kartu: Ikon & Judul Layanan -->
						<div class="d-flex align-items-start gap-3 mb-3">
							<div class="service-icon-box shadow-sm">
								<i class="fa-solid <?= $theme['icon'] ?> fa-xl"></i>
							</div>
							<div class="flex-grow-1">
								<h3 class="service-title mb-1"><?= e($srv['name']) ?></h3>
								<p class="service-desc mb-0">
									<?= e($srv['description'] ?: 'Pelayanan administrasi dan persuratan di loket TU.') ?>
								</p>
							</div>
						</div>

						<!-- Status Jumlah Antrean yang Sedang Menunggu -->
						<div class="waiting-box my-3 pt-2">
							<div class="waiting-icon">
								<i class="fa-solid fa-users"></i>
							</div>
							<div>
								<div class="waiting-label">Sedang Menunggu</div>
								<div class="waiting-count"><?= (int) $waiting ?> Orang</div>
							</div>
						</div>

						<!-- Tombol Aksi Pill Penuh -->
						<button type="button" class="btn btn-kiosk-action w-100">
							<span class="d-flex align-items-center">
								<i class="fa-solid fa-ticket-simple me-2"></i>
								<?= $theme['btn_text'] ?>
							</span>
							<i class="fa-solid fa-chevron-right"></i>
						</button>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</main>

<!-- 4. FOOTER / INFORMASI ANTREAN (PILL BAR) -->
<footer class="px-3 px-lg-4 pb-4">
	<div class="container-fluid">
		<div class="kiosk-info-pill shadow-sm">
			<div class="info-tag fw-bold text-nowrap">
				<i class="fa-solid fa-circle-info fs-5"></i>
				<span>Informasi</span>
			</div>
			<div class="info-divider"></div>
			<div class="info-text">
				Ambil nomor antrean sesuai dengan kebutuhan layanan Anda. Nomor akan dipanggil pada monitor TV.
			</div>
		</div>
	</div>
</footer>

<!-- Modal Digital Card Tiket -->
<div class="modal fade" id="ticketModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
		<div class="modal-content rounded-4 border-0 shadow-lg p-2">
			<div class="modal-body p-3 text-center" id="ticketModalBody">
				<div class="spinner-border text-primary my-4" role="status"></div>
				<p class="text-muted">Sedang mengambil nomor antrean...</p>
			</div>
		</div>
	</div>
</div>

<!-- Hidden Iframe untuk Print Otomatis Tanpa Pop-up Blocker -->
<iframe id="printFrame" style="position:absolute; width:0px; height:0px; border:none; left:-9999px;"></iframe>

<script src="<?= asset_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
<!-- Driver POS Web Serial API Native ESC/POS -->
<script src="<?= asset_url('assets/js/pos-serial-printer.js') ?>"></script>
<script>
const SCHOOL_NAME = <?= json_encode($school['school_name'] ?: 'SMK MUHAMMADIYAH BANDONGAN') ?>;

// Jam Digital Kiosk
function updateClock() {
	const now = new Date();
	const h = String(now.getHours()).padStart(2, '0');
	const m = String(now.getMinutes()).padStart(2, '0');
	const s = String(now.getSeconds()).padStart(2, '0');
	const el = document.getElementById('liveClock');
	if (el) el.textContent = `${h}:${m}:${s} WIB`;
}
setInterval(updateClock, 1000);
updateClock();

let isProcessing = false;
let currentTicketId = null;
let ticketModal = null;

// Cek Auto-Remember Port saat halaman dimuat
document.addEventListener('DOMContentLoaded', async function() {
	ticketModal = new bootstrap.Modal(document.getElementById('ticketModal'));

	if (window.posPrinter && window.posPrinter.isSupported()) {
		await window.posPrinter.autoConnect();
	}
});

// Ambil Tiket dengan Proteksi Anti-Spam / Anti-Crash & Broadcast Instan 0.00s
function ambilTiket(serviceId, serviceName) {
	if (isProcessing) return; // Mencegah double click
	isProcessing = true;

	// Kunci semua kartu agar tidak bisa diklik berulang
	document.querySelectorAll('.kiosk-card').forEach(c => c.classList.add('disabled'));

	const modalBody = document.getElementById('ticketModalBody');
	modalBody.innerHTML = `
		<div class="spinner-border text-primary my-4" role="status"></div>
		<p class="text-muted">Membuat nomor antrean <strong>${serviceName}</strong>...</p>
	`;
	ticketModal.show();

	fetch('<?= site_url("antrean/ambil_tiket") ?>/' + serviceId + '?format=json', {
		method: 'GET',
		headers: { 'X-Requested-With': 'XMLHttpRequest' }
	})
	.then(res => {
		if (!res.ok) throw new Error('Respon server error: ' + res.status);
		return res.json();
	})
	.then(async data => {
		if (data.success) {
			currentTicketId = data.ticket.id;
			modalBody.innerHTML = data.html;

			// Broadcast sinyal instan ke Layar Loket TU & Display TV (0.00 detik)
			if (window.BroadcastChannel) {
				try {
					const bc = new BroadcastChannel('tu_queue_sync');
					bc.postMessage({ action: 'NEW_TICKET', ticket: data.ticket, time: Date.now() });
					bc.close();
				} catch(e) {}
			}
			try {
				localStorage.setItem('tu_queue_action', JSON.stringify({ action: 'NEW_TICKET', ticket: data.ticket, time: Date.now() }));
			} catch(e) {}

			// Eksekusi Cetak Tiket
			await executePrint(data.ticket);
		} else {
			modalBody.innerHTML = `
				<div class="alert alert-danger my-3">${data.message || 'Gagal membuat tiket antrean.'}</div>
				<button class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
			`;
		}
	})
	.catch(err => {
		console.error('Kiosk Error:', err);
		modalBody.innerHTML = `
			<div class="alert alert-danger my-3">
				<strong>Gagal menghubungi server.</strong><br>
				<small>Koneksi terganggu. Silakan coba lagi.</small>
			</div>
			<button class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
		`;
	})
	.finally(() => {
		// Buka kembali kunci klik setelah 2 detik
		setTimeout(() => {
			isProcessing = false;
			document.querySelectorAll('.kiosk-card').forEach(c => c.classList.remove('disabled'));
		}, 2000);
	});
}

// Eksekusi Cetak: Prioritas Web Serial ESC/POS murni -> Fallback ke Silent Iframe
async function executePrint(ticketData) {
	// 1. Coba cetak kilat via Web Serial API (ESC/POS murni)
	if (window.posPrinter && window.posPrinter.isConnected) {
		try {
			await window.posPrinter.printTicket(ticketData, SCHOOL_NAME);
			console.log('[POS-Serial] Cetak berhasil via ESC/POS murni!');
			return; // Selesai!
		} catch (err) {
			console.warn('[POS-Serial] Gagal serial, fallback ke iframe:', err);
		}
	}

	// 2. Fallback: Iframe HTML print
	printTicketSilently(ticketData.id);
}

// Print Diam-diam Tanpa Membuka Tab Baru (Fallback)
function printTicketSilently(ticketId) {
	const printFrame = document.getElementById('printFrame');
	if (!printFrame) return;

	// Muat halaman cetak ke dalam iframe tersembunyi
	printFrame.src = '<?= site_url("antrean/ambil_tiket") ?>/' + ticketId + '?autoprint=1';
}
</script>

</body>
</html>
