<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="ticket-digital-card text-center py-2">
	<div class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill small mb-2 fw-bold">
		<i class="fa-solid fa-circle-check me-1"></i> TIKET DIGITAL BERHASIL DIAMBIL
	</div>
	
	<div class="small text-muted text-uppercase fw-semibold"><?= e($school['school_name'] ?: 'SMK MUHAMMADIYAH BANDONGAN') ?></div>
	<div class="text-secondary small mb-2">Pelayanan Tata Usaha &amp; Administrasi</div>

	<!-- Nomor Tiket Raksasa -->
	<div class="card bg-primary bg-opacity-10 border-2 border-primary rounded-4 p-3 my-3 shadow-sm">
		<div class="small text-primary fw-bold text-uppercase" style="letter-spacing: 1px;">NOMOR ANTREAN ANDA</div>
		<div class="display-2 fw-black text-primary my-1 font-monospace" style="font-weight: 900; letter-spacing: 3px;">
			<?= e($ticket['ticket_number']) ?>
		</div>
		<div class="d-inline-block bg-primary text-white fw-bold px-3 py-1 rounded-pill small mt-1 shadow-sm">
			MENUJU LOKET <?= (int) $ticket['loket_number'] ?>
		</div>
		<div class="fw-bold text-dark mt-2"><?= e($ticket['service_name']) ?></div>
	</div>

	<!-- Panduan Siswa / Tamu (Tanpa Printer) -->
	<div class="alert alert-warning border-warning border-opacity-50 text-dark small py-2 px-3 rounded-3 text-start mb-3">
		<div class="fw-bold text-warning-emphasis mb-1">
			<i class="fa-solid fa-camera me-1"></i> Mode Digital (Tanpa Perlu Cetak Kertas):
		</div>
		<ul class="mb-0 ps-3">
			<li><strong>Foto layar ini</strong> dengan kamera HP atau hafalkan nomor Anda.</li>
			<li>Antrean di depan Anda saat ini: <strong><?= (int) $ticket['waiting_before'] ?> Orang</strong>.</li>
			<li>Silakan duduk di ruang tunggu dan perhatikan monitor TV saat nomor Anda dipanggil.</li>
		</ul>
	</div>

	<!-- Hitung Mundur Otomatis -->
	<div class="small text-muted mb-3">
		Layar ini akan otomatis tertutup dalam <strong id="kioskCountdown" class="text-primary font-monospace">10</strong> detik...
	</div>

	<!-- Tombol Selesai Utama -->
	<div class="d-grid gap-2">
		<button type="button" class="btn btn-success btn-lg fw-bold rounded-pill shadow-sm" data-bs-dismiss="modal">
			<i class="fa-solid fa-check me-1"></i> Selesai
		</button>
	</div>
</div>

<script>
// Auto close countdown 10 detik agar kiosk langsung siap untuk antrean berikutnya
(function() {
	let timeLeft = 10;
	const countEl = document.getElementById('kioskCountdown');
	if (window.kioskTimer) clearInterval(window.kioskTimer);
	window.kioskTimer = setInterval(() => {
		timeLeft--;
		if (countEl) countEl.textContent = timeLeft;
		if (timeLeft <= 0) {
			clearInterval(window.kioskTimer);
			const m = bootstrap.Modal.getInstance(document.getElementById('ticketModal'));
			if (m) m.hide();
		}
	}, 1000);
})();
</script>
