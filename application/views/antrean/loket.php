<?php defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->view('layouts/app', array('page_title' => $page_title));
?>

<div class="row g-3 mb-4">
	<div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-2">
		<div>
			<h2 class="h4 fw-bold text-navy mb-1"><i class="fa-solid fa-users-line text-primary me-2"></i>Panel Pemanggil Antrean Tata Usaha</h2>
			<p class="text-muted small mb-0">Kelola dan panggil nomor antrean siswa/tamu menuju loket pelayanan TU.</p>
		</div>
		<div class="d-flex align-items-center gap-2">
			<a href="<?= site_url('antrean/display') ?>" target="_blank" class="btn btn-outline-primary btn-sm fw-bold">
				<i class="fa-solid fa-tv me-1"></i> Buka Layar Display TV
			</a>
			<a href="<?= site_url('antrean/kiosk') ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
				<i class="fa-solid fa-tablet-screen-button me-1"></i> Mesin Kiosk Tiket
			</a>
			<a href="<?= site_url('antrean/reset') ?>" class="btn btn-outline-danger btn-sm" data-confirm="PERINGATAN: Seluruh tiket antrean hari ini akan direset kembali ke nomor awal. Lanjutkan?">
				<i class="fa-solid fa-rotate-left me-1"></i> Reset Hari Ini
			</a>
		</div>
	</div>
</div>

<!-- Pilih Loket Kerja -->
<div class="card border-0 shadow-sm mb-4">
	<div class="card-body p-3">
		<form method="GET" action="<?= site_url('antrean/loket') ?>" class="row g-2 align-items-center">
			<div class="col-auto">
				<label class="col-form-label fw-bold text-navy small"><i class="fa-solid fa-desktop me-1"></i> Loket Kerja Anda:</label>
			</div>
			<div class="col-md-4 col-sm-6">
				<select name="service_id" class="form-select form-select-sm fw-semibold" onchange="this.form.submit()">
					<?php foreach ($services as $srv): ?>
						<option value="<?= (int) $srv['id'] ?>" <?= $selected_id === (int) $srv['id'] ? 'selected' : '' ?>>
							LOKET <?= (int) $srv['loket_number'] ?> &mdash; <?= e($srv['name']) ?> (Kode <?= e($srv['code']) ?>)
						</option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="col-auto ms-auto d-flex gap-3 small text-muted">
				<div>Total Tiket: <strong class="text-dark"><?= (int) $stats['total'] ?></strong></div>
				<div>Menunggu: <strong class="text-warning"><?= (int) $stats['waiting'] ?></strong></div>
				<div>Selesai: <strong class="text-success"><?= (int) $stats['completed'] ?></strong></div>
			</div>
		</form>
	</div>
</div>

<div class="row g-4 mb-4">
	<!-- Hero Control Box -->
	<div class="col-lg-5">
		<div class="card border-0 shadow-sm h-100 text-center overflow-hidden">
			<div class="card-header bg-navy text-white py-3">
				<span class="badge bg-primary px-3 py-1 rounded-pill fw-bold text-uppercase">
					LOKET <?= !empty($current_service) ? (int) $current_service['loket_number'] : 1 ?>
				</span>
				<h5 class="fw-bold mb-0 mt-2 text-white"><?= !empty($current_service) ? e($current_service['name']) : 'Layanan TU' ?></h5>
			</div>
			<div class="card-body p-4 d-flex flex-column justify-content-center">
				<div class="small text-muted text-uppercase fw-semibold mb-1">Nomor Sedang Dilayani</div>
				<div class="display-1 fw-black text-primary my-2" id="servingTicketNumber" style="font-family: monospace; font-weight: 900; letter-spacing: 2px;">
					<?= !empty($active_ticket) ? e($active_ticket['ticket_number']) : '---' ?>
				</div>
				<div id="servingStatusBadge" class="mb-4">
					<?php if (!empty($active_ticket)): ?>
						<span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill">
							<i class="fa-solid fa-volume-high me-1"></i> Sedang Dipanggil / Dilayani
						</span>
					<?php else: ?>
						<span class="badge bg-light text-muted border px-3 py-2 rounded-pill">
							Belum ada antrean yang dipanggil
						</span>
					<?php endif; ?>
				</div>

				<!-- Tombol-tombol Pemanggil -->
				<div class="d-grid gap-2">
					<button type="button" class="btn btn-primary btn-lg fw-bold shadow-sm py-3" onclick="panggilBerikutnya()">
						<i class="fa-solid fa-forward-step me-2"></i> Panggil Berikutnya
					</button>
					<div class="row g-2">
						<div class="col-6">
							<button type="button" class="btn btn-outline-warning w-100 fw-bold py-2" id="btnRecall" onclick="panggilUlang()" <?= empty($active_ticket) ? 'disabled' : '' ?>>
								<i class="fa-solid fa-rotate-right me-1"></i> Panggil Ulang
							</button>
						</div>
						<div class="col-6">
							<button type="button" class="btn btn-outline-danger w-100 fw-bold py-2" id="btnSkip" onclick="lewati()" <?= empty($active_ticket) ? 'disabled' : '' ?>>
								<i class="fa-solid fa-forward me-1"></i> Lewati
							</button>
						</div>
					</div>
					<button type="button" class="btn btn-success fw-bold py-2 mt-1" id="btnComplete" onclick="selesai()" <?= empty($active_ticket) ? 'disabled' : '' ?>>
						<i class="fa-solid fa-check-double me-1"></i> Selesai Melayani
					</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Status Seluruh Loket & Antrean Hari Ini -->
	<div class="col-lg-7">
		<div class="card border-0 shadow-sm h-100">
			<div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
				<h6 class="fw-bold mb-0 text-navy"><i class="fa-solid fa-list-check text-primary me-2"></i>Riwayat &amp; Antrean Hari Ini</h6>
				<button class="btn btn-light btn-sm" onclick="location.reload()" title="Refresh"><i class="fa-solid fa-arrows-rotate"></i></button>
			</div>
			<div class="card-body p-0">
				<div class="table-responsive" style="max-height: 380px;">
					<table class="table table-hover align-middle mb-0 small">
						<thead class="table-light sticky-top">
							<tr>
								<th>No Tiket</th>
								<th>Layanan</th>
								<th>Loket</th>
								<th>Waktu Ambil</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody id="queueTableBody">
							<?php if (empty($today_tickets)): ?>
								<tr><td colspan="5" class="text-center text-muted py-4">Belum ada tiket antrean yang diambil hari ini.</td></tr>
							<?php else: ?>
								<?php foreach ($today_tickets as $t): ?>
									<tr>
										<td><strong class="text-primary font-monospace fs-6"><?= e($t['ticket_number']) ?></strong></td>
										<td><?= e($t['service_name']) ?></td>
										<td><span class="badge text-bg-light border">Loket <?= (int) $t['loket_number'] ?></span></td>
										<td><?= date('H:i', strtotime($t['created_at'])) ?></td>
										<td>
											<?php 
											$badges = array(
												'waiting'   => '<span class="badge bg-warning-subtle text-warning border border-warning-subtle">Menunggu</span>',
												'calling'   => '<span class="badge bg-primary-subtle text-primary border border-primary-subtle">Dipanggil</span>',
												'serving'   => '<span class="badge bg-info-subtle text-info border border-info-subtle">Dilayani</span>',
												'completed' => '<span class="badge bg-success-subtle text-success border border-success-subtle">Selesai</span>',
												'skipped'   => '<span class="badge bg-danger-subtle text-danger border border-danger-subtle">Dilewati</span>',
											);
											echo isset($badges[$t['status']]) ? $badges[$t['status']] : e($t['status']);
											?>
										</td>
									</tr>
								<?php endforeach; ?>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
let currentActiveId = <?= !empty($active_ticket) ? (int) $active_ticket['id'] : 'null' ?>;
const serviceId = <?= (int) $selected_id ?>;
const csrfName = '<?= $this->security->get_csrf_token_name() ?>';
const csrfHash = '<?= $this->security->get_csrf_hash() ?>';

function panggilBerikutnya() {
	fetch('<?= site_url("antrean/api_call_next") ?>', {
		method: 'POST',
		headers: { 
			'Content-Type': 'application/x-www-form-urlencoded',
			'X-Requested-With': 'XMLHttpRequest' 
		},
		body: 'service_id=' + serviceId + '&' + csrfName + '=' + encodeURIComponent(csrfHash)
	})
	.then(res => res.json())
	.then(data => {
		if (data.success) {
			currentActiveId = data.ticket.id;
			document.getElementById('servingTicketNumber').textContent = data.ticket.ticket_number;
			document.getElementById('servingStatusBadge').innerHTML = `
				<span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill">
					<i class="fa-solid fa-volume-high me-1"></i> Sedang Dipanggil ke Layar TV
				</span>
			`;
			document.getElementById('btnRecall').disabled = false;
			document.getElementById('btnSkip').disabled = false;
			document.getElementById('btnComplete').disabled = false;
		} else {
			alert(data.message || 'Tidak ada antrean berikutnya.');
		}
	});
}

function panggilUlang() {
	if (!currentActiveId) return;
	fetch('<?= site_url("antrean/api_recall") ?>', {
		method: 'POST',
		headers: { 
			'Content-Type': 'application/x-www-form-urlencoded',
			'X-Requested-With': 'XMLHttpRequest' 
		},
		body: 'ticket_id=' + currentActiveId + '&' + csrfName + '=' + encodeURIComponent(csrfHash)
	})
	.then(res => res.json())
	.then(data => {
		if (data.success) {
			const badge = document.getElementById('servingStatusBadge');
			badge.innerHTML = `<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2 rounded-pill"><i class="fa-solid fa-rotate-right me-1"></i> Panggilan Ulang Terkirim ke Layar TV</span>`;
		}
	});
}

function selesai() {
	if (!currentActiveId) return;
	fetch('<?= site_url("antrean/api_complete") ?>', {
		method: 'POST',
		headers: { 
			'Content-Type': 'application/x-www-form-urlencoded',
			'X-Requested-With': 'XMLHttpRequest' 
		},
		body: 'ticket_id=' + currentActiveId + '&' + csrfName + '=' + encodeURIComponent(csrfHash)
	})
	.then(res => res.json())
	.then(data => {
		if (data.success) {
			location.reload();
		}
	});
}

function lewati() {
	if (!currentActiveId) return;
	if (!confirm('Lewati nomor antrean ini?')) return;
	fetch('<?= site_url("antrean/api_skip") ?>', {
		method: 'POST',
		headers: { 
			'Content-Type': 'application/x-www-form-urlencoded',
			'X-Requested-With': 'XMLHttpRequest' 
		},
		body: 'ticket_id=' + currentActiveId + '&' + csrfName + '=' + encodeURIComponent(csrfHash)
	})
	.then(res => res.json())
	.then(data => {
		if (data.success) {
			location.reload();
		}
	});
}
</script>

<?php $this->load->view('layouts/footer'); ?>

