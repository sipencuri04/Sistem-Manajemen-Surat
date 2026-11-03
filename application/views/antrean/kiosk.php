<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?= e($page_title) ?> &middot; <?= e($school['school_name'] ?: 'SMK Muhammadiyah Bandongan') ?></title>
	<link rel="stylesheet" href="<?= asset_url('assets/vendor/bootstrap/css/bootstrap.min.css') ?>">
	<link rel="stylesheet" href="<?= asset_url('assets/vendor/fontawesome/css/all.min.css') ?>">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
	<style>
		body {
			font-family: 'Plus Jakarta Sans', -apple-system, sans-serif;
			background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 60%, #1d4ed8 100%);
			min-height: 100vh;
			display: flex;
			flex-direction: column;
			color: #fff;
			user-select: none;
			-webkit-user-select: none;
		}
		.kiosk-card {
			transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
			cursor: pointer;
			border: 2px solid rgba(255, 255, 255, 0.15);
			background: rgba(255, 255, 255, 0.95);
			color: #0f172a;
			border-radius: 20px;
			box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
		}
		.kiosk-card:hover {
			transform: translateY(-8px) scale(1.02);
			border-color: #60a5fa;
			box-shadow: 0 20px 35px rgba(37, 99, 235, 0.4);
		}
		.kiosk-card:active {
			transform: scale(0.98);
		}
		.kiosk-card.disabled {
			pointer-events: none;
			opacity: 0.6;
		}
		.service-code {
			width: 65px;
			height: 65px;
			border-radius: 16px;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 28px;
			font-weight: 900;
		}
		.clock-badge {
			background: rgba(255, 255, 255, 0.12);
			backdrop-filter: blur(10px);
			border: 1px solid rgba(255, 255, 255, 0.2);
		}
	</style>
</head>
<body>

<header class="py-3 px-4 border-bottom border-white border-opacity-10">
	<div class="container-fluid d-flex justify-content-between align-items-center">
		<div class="d-flex align-items-center gap-3">
			<img src="<?= asset_url('assets/img/logo-sekolah.png') ?>" alt="Logo" class="rounded" style="width: 48px; height: 48px; object-fit: contain;" onerror="this.src='<?= asset_url('assets/img/icon.png') ?>'">
			<div>
				<h5 class="mb-0 fw-bold text-uppercase tracking-wider"><?= e($school['school_name'] ?: 'SMK MUHAMMADIYAH BANDONGAN') ?></h5>
				<small class="text-white-50">Sistem Antrean Layanan Mandiri Tata Usaha (Kiosk)</small>
			</div>
		</div>

		<div class="d-flex align-items-center gap-3">
			<div class="clock-badge px-4 py-2 rounded-pill d-flex align-items-center gap-2 shadow-sm">
				<i class="fa-regular fa-clock text-warning"></i>
				<span id="liveClock" class="fw-bold font-monospace fs-5">00:00:00 WIB</span>
			</div>
			<a href="<?= site_url('antrean/display') ?>" target="_blank" class="btn btn-outline-light btn-sm rounded-pill px-3">
				<i class="fa-solid fa-tv me-1"></i> Monitor TV
			</a>
			<a href="<?= site_url('login') ?>" class="btn btn-light btn-sm rounded-pill px-3 shadow-sm">
				<i class="fa-solid fa-user-lock me-1"></i> Login Staf
			</a>
		</div>
	</div>
</header>

<main class="flex-grow-1 d-flex align-items-center py-5">
	<div class="container">
		<div class="text-center mb-5">
			<span class="badge bg-white bg-opacity-25 text-white px-3 py-2 rounded-pill fs-6 mb-2 border border-white border-opacity-25">
				<i class="fa-solid fa-hand-pointer me-1"></i> Sentuh Layar untuk Mengambil Tiket
			</span>
			<h1 class="display-6 fw-black text-white text-uppercase mb-2">PILIH JENIS LAYANAN ANDA</h1>
			<p class="text-white-50 fs-5 mb-0">Silakan pilih loket pelayanan di bawah ini untuk mendapatkan nomor antrean</p>
		</div>

		<div class="row g-4 justify-content-center">
			<?php 
			$card_colors = array(
				'A' => array('bg' => 'bg-primary text-white', 'btn' => 'btn-primary', 'accent' => '#2563eb', 'icon' => 'fa-file-signature'),
				'B' => array('bg' => 'bg-success text-white', 'btn' => 'btn-success', 'accent' => '#16a34a', 'icon' => 'fa-money-bill-wave'),
				'C' => array('bg' => 'bg-warning text-dark',  'btn' => 'btn-warning', 'accent' => '#ca8a04', 'icon' => 'fa-users-gear'),
			);
			?>

			<?php foreach ($services as $srv): 
				$color = isset($card_colors[$srv['code']]) ? $card_colors[$srv['code']] : array('bg' => 'bg-secondary text-white', 'btn' => 'btn-secondary', 'accent' => '#64748b', 'icon' => 'fa-bell');
				$waiting = isset($stats[$srv['id']]['waiting']) ? $stats[$srv['id']]['waiting'] : 0;
			?>
				<div class="col-lg-4 col-md-6">
					<div class="card kiosk-card h-100 p-4 service-btn" onclick="ambilTiket(<?= (int)$srv['id'] ?>, '<?= e(addslashes($srv['name'])) ?>')">
						<div class="d-flex align-items-center gap-3 mb-3">
							<div class="service-code <?= $color['bg'] ?> shadow-sm">
								<?= e($srv['code']) ?>
							</div>
							<div class="flex-grow-1">
								<span class="badge bg-light text-muted border px-2 py-1 rounded-pill small">
									Loket <?= (int) $srv['loket_number'] ?>
								</span>
								<h4 class="fw-bold mb-0 text-dark mt-1"><?= e($srv['name']) ?></h4>
							</div>
						</div>

						<p class="text-muted small flex-grow-1 mb-4" style="min-height: 40px;">
							<?= e($srv['description'] ?: 'Pelayanan administrasi dan persuratan di loket TU.') ?>
						</p>

						<div class="pt-3 border-top d-flex justify-content-between align-items-center">
							<div class="small">
								<span class="text-muted">Sedang Menunggu:</span>
								<strong class="text-dark fs-6 ms-1"><?= (int) $waiting ?> Orang</strong>
							</div>
							<button type="button" class="btn <?= $color['btn'] ?> px-4 py-2 rounded-pill fw-bold shadow-sm">
								<i class="fa-solid fa-ticket me-1"></i> AMBIL TIKET
							</button>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</main>

<footer class="py-3 text-center text-white-50 small border-top border-white border-opacity-10">
	&copy; <?= date('Y') ?> <?= e($school['school_name'] ?: 'SMK Muhammadiyah Bandongan') ?> &middot; Sistem Manajemen Persuratan &amp; Antrean Digital
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
		const isRemembered = await window.posPrinter.autoConnect();
		updatePrinterBadge(isRemembered);
	} else {
		const btn = document.getElementById('btnPairPrinter');
		if (btn) btn.style.display = 'none'; // Sembunyikan jika browser tidak support serial
	}
});

// Update status tombol printer
function updatePrinterBadge(connected) {
	const btn = document.getElementById('btnPairPrinter');
	const txt = document.getElementById('printerStatusText');
	if (!btn || !txt) return;

	if (connected) {
		btn.className = 'btn btn-success btn-sm rounded-pill px-3 shadow-sm';
		txt.innerHTML = '<i class="fa-solid fa-check me-1"></i> Bluetooth Printer Siap';
	} else {
		btn.className = 'btn btn-outline-info btn-sm rounded-pill px-3 shadow-sm';
		txt.textContent = 'Sambungkan Printer Bluetooth';
	}
}

// Handler klik tombol sambungkan printer (hanya 1x seumur hidup per browser)
async function initPosPrinter() {
	if (!window.posPrinter) return;
	try {
		const ok = await window.posPrinter.requestPort();
		updatePrinterBadge(ok);
		if (ok) {
			alert('Printer Bluetooth berhasil terhubung!\nSelanjutnya tiket akan dicetak otomatis seketika dengan teks pekat.');
		}
	} catch (err) {
		alert('Gagal menyambungkan port: ' + err.message);
	}
}

// Ambil Tiket dengan Proteksi Anti-Spam / Anti-Crash
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
