<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="ticket-receipt bg-white p-4 text-center rounded-4 border shadow-sm mx-auto" style="max-width: 320px; font-family: 'Courier New', Courier, monospace;">
	<div class="fw-bold text-uppercase fs-6 mb-1 text-dark"><?= e($school['school_name'] ?: 'SMK MUHAMMADIYAH BANDONGAN') ?></div>
	<div class="small text-muted mb-2">Layanan Tata Usaha Digital</div>
	<div class="border-top border-bottom border-dark border-dashed py-2 my-2">
		<div class="small text-muted text-uppercase">Nomor Antrean Anda</div>
		<div class="display-3 fw-black text-primary my-1" style="font-family: -apple-system, sans-serif; font-weight: 900; letter-spacing: 2px;">
			<?= e($ticket['ticket_number']) ?>
		</div>
		<div class="fw-bold text-dark text-uppercase"><?= e($ticket['service_name']) ?></div>
		<div class="badge bg-primary-subtle text-primary border border-primary-subtle mt-1">LOKET <?= (int) $ticket['loket_number'] ?></div>
	</div>
	<div class="small text-muted d-flex justify-content-between my-2 text-start">
		<span>Waktu Ambil:</span>
		<strong class="text-dark"><?= date('H:i', strtotime($ticket['created_at'])) ?> WIB</strong>
	</div>
	<div class="small text-muted d-flex justify-content-between text-start">
		<span>Antrean Di Depan:</span>
		<strong class="text-danger"><?= (int) $ticket['waiting_before'] ?> Orang</strong>
	</div>
	<div class="border-top border-dashed border-dark pt-2 mt-3 small text-muted fst-italic">
		Silakan menunggu hingga nomor Anda dipanggil pada layar monitor TV. Terima kasih.
	</div>
</div>
