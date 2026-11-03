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
			backdrop-filter: blur(8px);
			border: 1px solid rgba(255, 255, 255, 0.2);
			border-radius: 14px;
			padding: 8px 18px;
		}
	</style>
</head>
<body>

<header class="py-3 px-4 border-bottom border-white border-opacity-10">
	<div class="container-fluid d-flex justify-content-between align-items-center">
		<div class="d-flex align-items-center gap-3">
			<div class="bg-white rounded-3 p-2 d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px;">
				<i class="fa-solid fa-school text-primary fs-4"></i>
			</div>
			<div>
				<h5 class="fw-bold mb-0 text-white text-uppercase tracking-wide"><?= e($school['school_name'] ?: 'SMK MUHAMMADIYAH BANDONGAN') ?></h5>
				<small class="text-white-50">Sistem Antrean Pelayanan Tata Usaha Mandiri</small>
			</div>
		</div>

		<div class="d-flex align-items-center gap-3">
			<div class="clock-badge text-end d-none d-md-block">
				<div class="fw-bold fs-5 text-white" id="liveClock">--:--:--</div>
				<small class="text-white-50"><?= tgl_indo(date('Y-m-d')) ?></small>
			</div>
			<a href="<?= site_url('antrean/display') ?>" target="_blank" class="btn btn-outline-light btn-sm rounded-pill px-3 shadow-sm" title="Buka Layar Monitor TV">
				<i class="fa-solid fa-tv me-1"></i> Layar Display TV
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
				'B' => array('bg' => 'bg-success text-white', 'btn' => 'btn-success', 'accent' => '#16a34a', 'icon' => 'fa-wallet'),
				'C' => array('bg' => 'bg-warning text-dark', 'btn' => 'btn-warning', 'accent' => '#ca8a04', 'icon' => 'fa-users'),
			);
			foreach ($services as $srv): 
				$color = isset($card_colors[$srv['code']]) ? $card_colors[$srv['code']] : $card_colors['A'];
				$waiting = isset($lokets[$srv['loket_number']]) ? $lokets[$srv['loket_number']]['waiting_count'] : 0;
			?>
				<div class="col-lg-4 col-md-6">
					<div class="kiosk-card p-4 h-100 d-flex flex-column justify-content-between" onclick="ambilTiket(<?= (int) $srv['id'] ?>, '<?= e($srv['name']) ?>')">
						<div>
							<div class="d-flex justify-content-between align-items-start mb-3">
								<div class="service-code <?= $color['bg'] ?> shadow-sm">
									<?= e($srv['code']) ?>
								</div>
								<div class="text-end">
									<span class="badge bg-light text-muted border px-2 py-1">LOKET <?= (int) $srv['loket_number'] ?></span>
									<div class="small text-muted mt-1"><i class="fa-solid <?= $color['icon'] ?> me-1"></i> Mandiri</div>
								</div>
							</div>
							<h4 class="fw-bold mb-2 text-dark"><?= e($srv['name']) ?></h4>
							<p class="text-secondary small mb-4"><?= e($srv['description'] ?: 'Pelayanan cepat dan ramah di Loket ' . $srv['loket_number']) ?></p>
						</div>

						<div class="pt-3 border-top d-flex justify-content-between align-items-center">
							<div class="small text-muted">
								<i class="fa-solid fa-hourglass-half me-1 text-warning"></i> 
								Menunggu: <strong class="text-dark"><?= $waiting ?> Orang</strong>
							</div>
							<button type="button" class="btn <?= $color['btn'] ?> fw-bold rounded-pill px-4 shadow-sm">
								Ambil Tiket &rarr;
							</button>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- Informasi Tata Tertib -->
		<div class="mt-5 text-center">
			<div class="d-inline-flex align-items-center gap-3 bg-white bg-opacity-10 border border-white border-opacity-10 rounded-pill px-4 py-2 text-white-50 small">
				<span><i class="fa-solid fa-circle-info text-info me-1"></i> Setelah mengambil tiket, silakan duduk di ruang tunggu dan dengarkan panggilan suara pada monitor TV.</span>
			</div>
		</div>
	</div>
</main>

<footer class="py-3 text-center border-top border-white border-opacity-10 text-white-50 small">
	&copy; <?= date('Y') ?> <?= e($school['school_name'] ?: 'SMK Muhammadiyah Bandongan') ?> &middot; Sistem Manajemen Persuratan &amp; Antrean TU
</footer>

<!-- Modal Tampil & Cetak Tiket -->
<div class="modal fade" id="ticketModal" tabindex="-1" aria-labelledby="ticketModalLabel" aria-hidden="true" data-bs-backdrop="static">
	<div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
		<div class="modal-content rounded-4 border-0 shadow-lg">
			<div class="modal-header border-0 pb-0 justify-content-end">
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body p-4 pt-0 text-center" id="ticketModalBody">
				<div class="spinner-border text-primary my-4" role="status"></div>
				<p class="text-muted">Sedang mencetak nomor antrean...</p>
			</div>
			<div class="modal-footer border-0 p-3 pt-0 justify-content-center gap-2">
				<button type="button" class="btn btn-primary fw-bold rounded-pill px-4" onclick="printReceipt()"><i class="fa-solid fa-print me-1"></i> Cetak Struk</button>
				<button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Selesai</button>
			</div>
		</div>
	</div>
</div>

<script src="<?= asset_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
<script>
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

let currentTicketId = null;
const ticketModal = new bootstrap.Modal(document.getElementById('ticketModal'));

function ambilTiket(serviceId, serviceName) {
	const modalBody = document.getElementById('ticketModalBody');
	modalBody.innerHTML = `
		<div class="spinner-border text-primary my-4" role="status"></div>
		<p class="text-muted">Membuat tiket <strong>${serviceName}</strong>...</p>
	`;
	ticketModal.show();

	fetch('<?= site_url("antrean/ambil_tiket") ?>/' + serviceId, {
		method: 'POST',
		headers: { 'X-Requested-With': 'XMLHttpRequest' }
	})
	.then(res => res.json())
	.then(data => {
		if (data.success) {
			currentTicketId = data.ticket.id;
			modalBody.innerHTML = data.html;
		} else {
			modalBody.innerHTML = `<div class="alert alert-danger my-3">${data.message || 'Gagal membuat tiket antrean.'}</div>`;
		}
	})
	.catch(err => {
		modalBody.innerHTML = `<div class="alert alert-danger my-3">Terjadi kendala jaringan saat menghubungi server.</div>`;
	});
}

function printReceipt() {
	if (currentTicketId) {
		const printWin = window.open('<?= site_url("antrean/ambil_tiket") ?>/' + currentTicketId, '_blank', 'width=400,height=600');
		if (printWin) printWin.focus();
	}
}
</script>

</body>
</html>
