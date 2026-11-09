<?php defined('BASEPATH') OR exit('No direct script access allowed');
$labels = Letter_request_model::$statuses;
$colors = array('pending' => 'warning', 'processed' => 'info', 'approved' => 'success', 'taken' => 'secondary', 'rejected' => 'danger');
$id = (int) $req['id'];
?>
<nav aria-label="breadcrumb">
	<ol class="breadcrumb small mb-2">
		<li class="breadcrumb-item"><a href="<?= site_url('permohonan-masuk') ?>">Permohonan Siswa</a></li>
		<li class="breadcrumb-item active" aria-current="page"><?= e($req['tracking_code']) ?></li>
	</ol>
</nav>

<div class="d-flex flex-wrap align-items-center gap-2 mb-3 page-actions">
	<div class="me-auto page-actions-title">
		<h2 class="h5 fw-bold text-navy mb-0"><?= e($req['type_name']) ?></h2>
		<div class="small text-muted">Kode <span class="letter-number"><?= e($req['tracking_code']) ?></span> &middot;
			<span class="badge rounded-pill text-bg-<?= $colors[$req['status']] ?>"><?= e($labels[$req['status']]) ?></span></div>
	</div>
	<?php if ($req['letter_id']): ?>
		<a href="<?= site_url('surat/detail/'.(int) $req['letter_id']) ?>" class="btn btn-outline-primary"><i class="fa-solid fa-envelope-open-text me-1"></i> Buka Surat Keluar</a>
	<?php endif; ?>
	<?php if ($req['status'] === 'pending' && $type && (int) $type['is_active']): ?>
		<a href="<?= site_url('surat/buat/'.(int) $req['letter_type_id'].'?permohonan='.$id) ?>" class="btn btn-outline-secondary btn-sm" title="Buka formulir penuh untuk edit manual tingkat lanjut">
			<i class="fa-solid fa-pen-to-square me-1"></i> Formulir Lengkap
		</a>
	<?php endif; ?>
	<?php if ($req['status'] === 'approved'): ?>
		<?= form_open('permohonan-masuk/diambil/'.$id, array('class' => 'd-inline')) ?>
			<button class="btn btn-success" type="submit"><i class="fa-solid fa-hand-holding me-1"></i> Tandai Sudah Diambil</button>
		<?= form_close() ?>
	<?php endif; ?>
</div>

<?php if ($req['status'] === 'processed'): ?>
	<div class="alert alert-info border d-flex align-items-center gap-3 mb-3 rounded-4 shadow-xs">
		<div class="fs-3 text-info"><i class="fa-solid fa-hourglass-start"></i></div>
		<div>
			<div class="fw-bold">Surat Sedang Diproses</div>
			<div class="small">
				<?php if (empty($req['approval_status']) || $req['approval_status'] === 'menunggu_persetujuan'): ?>
					Surat telah dibuat (<strong><?= e($req['letter_number'] ?: 'Draft') ?></strong>) dan saat ini <strong>menunggu tanda tangan Kepala Sekolah</strong> di menu Persetujuan Surat.
				<?php elseif ($req['approval_status'] === 'disetujui' && (empty($req['stamp_type']) || $req['stamp_type'] === 'belum_stempel')): ?>
					Surat telah disetujui Kepala Sekolah. Petugas TU silakan <a href="<?= site_url('surat/detail/'.(int) $req['letter_id']) ?>" class="fw-bold text-decoration-underline text-info-emphasis">membubuhkan stempel resmi</a> pada surat agar status otomatis menjadi <strong>Siap Diambil</strong>.
				<?php else: ?>
					Surat sedang dalam proses penyelesaian administrasi dan legalisasi.
				<?php endif; ?>
			</div>
		</div>
	</div>
<?php elseif ($req['status'] === 'approved'): ?>
	<div class="alert alert-success border d-flex align-items-center gap-3 mb-3 rounded-4 shadow-xs">
		<div class="fs-3 text-success"><i class="fa-solid fa-circle-check"></i></div>
		<div>
			<div class="fw-bold">Surat Resmi Selesai (Siap Diambil / Diunduh)</div>
			<div class="small">
				Surat telah disahkan Kepala Sekolah dan telah distempel oleh TU. Klik tombol <strong>Tandai Sudah Diambil</strong> di atas setelah fisik surat diserahkan ke pemohon.
			</div>
		</div>
	</div>
<?php elseif ($req['status'] === 'rejected'): ?>
	<div class="alert alert-danger border d-flex align-items-center gap-3 mb-3 rounded-4 shadow-xs">
		<div class="fs-3 text-danger"><i class="fa-solid fa-ban"></i></div>
		<div>
			<div class="fw-bold">Permohonan Ini Ditolak</div>
			<div class="small">
				Alasan penolakan: <em>"<?= e($req['admin_note']) ?>"</em>
			</div>
		</div>
	</div>
<?php endif; ?>

<div class="row g-3">
	<!-- Kolom Kiri: Card Data yang Diisi Siswa & Tombol Aksi Utama -->
	<div class="col-lg-7">
		<div class="card shadow-xs rounded-4 border overflow-hidden">
			<div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
				<h3 class="card-title h6 fw-bold text-navy mb-0">
					<i class="fa-solid fa-user-pen text-primary me-2"></i>Data yang Diisi Siswa
				</h3>
				<span class="badge bg-light text-secondary border font-monospace"><?= e($req['tracking_code']) ?></span>
			</div>
			
			<div class="card-body p-3 p-sm-4">
				<?php if ($req['status'] === 'pending'): ?>
					<div class="alert alert-warning small py-2 mb-3 rounded-3 border-0 bg-warning bg-opacity-10 text-dark">
						<i class="fa-solid fa-triangle-exclamation text-warning me-1"></i> Data diisi sendiri oleh siswa secara online. Periksa kecocokan data dengan buku induk sekolah sebelum menerbitkan surat.
					</div>
				<?php endif; ?>

				<dl class="dl-meta row mb-0">
				<?php if ($tpl): foreach ($tpl['fields'] as $name => $field): ?>
					<dt class="col-sm-5 text-muted small py-1"><?= e($field['label']) ?></dt>
					<dd class="col-sm-7 text-break py-1 fw-semibold text-dark">
					<?php if ($field['type'] === 'rows'): ?>
						<?php if (empty($values[$name])): ?>-<?php else: ?>
							<ol class="ps-3 mb-0"><?php foreach ($values[$name] as $row): ?><li><?= e(implode(' · ', array_filter(array_map('strval', $row), 'strlen'))) ?></li><?php endforeach; ?></ol>
						<?php endif; ?>
					<?php else: ?>
						<?= $values[$name] !== '' ? nl2br(e($values[$name])) : '<span class="text-muted fw-normal">-</span>' ?>
					<?php endif; ?>
					</dd>
				<?php endforeach; endif; ?>
				</dl>
			</div>

			<!-- Card Footer Action Buttons (Sesuai Permintaan User) -->
			<div class="card-footer bg-light p-3 border-top">
				<?php if ($req['status'] === 'pending'): ?>
					<div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
						<div class="d-flex flex-wrap gap-2">
							<!-- Button 1: Pratinjau Surat -->
							<button type="button" class="btn btn-outline-primary shadow-xs fw-semibold" data-bs-toggle="modal" data-bs-target="#modalPratinjauSurat">
								<i class="fa-solid fa-eye me-1"></i> Pratinjau Surat
							</button>

							<!-- Button 3: Tolak Permohonan -->
							<button type="button" class="btn btn-outline-danger shadow-xs fw-semibold" data-bs-toggle="modal" data-bs-target="#modalTolakPermohonan">
								<i class="fa-solid fa-ban me-1"></i> Tolak
							</button>
						</div>

						<!-- Button 2: Kirim ke Kepala Sekolah -->
						<button type="button" class="btn btn-primary shadow-sm fw-bold px-3 py-2" data-bs-toggle="modal" data-bs-target="#modalKirimKepsek">
							<i class="fa-solid fa-paper-plane me-1"></i> Kirim ke Kepala Sekolah
						</button>
					</div>
				<?php else: ?>
					<div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
						<span class="small text-muted">
							Status: <strong class="text-dark"><?= e($labels[$req['status']]) ?></strong>
							<?php if ($req['letter_number']): ?>&bull; No: <strong class="font-monospace text-primary"><?= e($req['letter_number']) ?></strong><?php endif; ?>
						</span>
						<?php if ($req['letter_id']): ?>
							<a href="<?= site_url('surat/detail/'.(int) $req['letter_id']) ?>" class="btn btn-primary btn-sm fw-semibold">
								<i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Buka Surat Resmi
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<!-- Kolom Kanan: Identitas Pemohon & Kontak Cepat -->
	<div class="col-lg-5">
		<div class="card shadow-xs rounded-4 border mb-3 overflow-hidden">
			<div class="card-header bg-white py-3 border-bottom">
				<h3 class="card-title h6 fw-bold text-navy mb-0">
					<i class="fa-solid fa-address-card text-primary me-2"></i>Informasi Pemohon
				</h3>
			</div>
			<div class="card-body p-3 p-sm-4">
				<dl class="dl-meta mb-0">
					<dt class="small text-muted">Nama Lengkap</dt>
					<dd class="fw-bold text-navy mb-3 fs-6">
						<?= e($req['requester_name']) ?>
						<?php if ($req['requester_class']): ?>
							<span class="badge bg-light text-secondary border ms-1"><?= e($req['requester_class']) ?></span>
						<?php endif; ?>
					</dd>

					<dt class="small text-muted">WhatsApp / Nomor HP</dt>
					<dd class="mb-3 d-flex align-items-center justify-content-between">
						<strong class="font-monospace text-dark"><?= e($req['requester_phone']) ?></strong>
						<?php 
						if (!empty($req['requester_phone'])) {
							if ($req['status'] === 'approved') {
								$msg_wa_to_siswa = "Halo *" . $req['requester_name'] . "*,\nPermohonan surat *" . $req['type_name'] . "* Anda di " . $school['school_name'] . " telah *SELESAI DIPROSES* dengan Nomor Surat: *" . $req['letter_number'] . "*.\nSilakan unduh dokumen atau ambil fisik surat di ruang Tata Usaha dengan menyebutkan Kode: *" . $req['tracking_code'] . "*.\nTerima kasih.";
							} elseif ($req['status'] === 'rejected') {
								$msg_wa_to_siswa = "Halo *" . $req['requester_name'] . "*,\nPermohonan surat *" . $req['type_name'] . "* Anda di " . $school['school_name'] . " (Kode: *" . $req['tracking_code'] . "*) belum dapat diproses karena:\n\"" . $req['admin_note'] . "\"\nSilakan hubungi ruang Tata Usaha untuk informasi lebih lanjut.";
							} elseif ($req['status'] === 'processed') {
								$msg_wa_to_siswa = "Halo *" . $req['requester_name'] . "*,\nPermohonan surat *" . $req['type_name'] . "* Anda (Kode: *" . $req['tracking_code'] . "*) saat ini sedang dalam proses penandatanganan Kepala Sekolah & stempel resmi di " . $school['school_name'] . ". Kami akan menginformasikan jika surat sudah siap diambil.";
							} else {
								$msg_wa_to_siswa = "Halo *" . $req['requester_name'] . "*,\nPermohonan surat *" . $req['type_name'] . "* Anda dengan Kode: *" . $req['tracking_code'] . "* sedang diperiksa oleh petugas Tata Usaha " . $school['school_name'] . ".";
							}
							$wa_siswa_href = build_wa_link($req['requester_phone'], $msg_wa_to_siswa);
						?>
							<a class="btn btn-xs btn-outline-success shadow-xs px-2 py-1" href="<?= $wa_siswa_href ?>" target="_blank" rel="noopener">
								<i class="fa-brands fa-whatsapp me-1"></i> Chat WA
							</a>
						<?php } ?>
					</dd>

					<dt class="small text-muted">Email Siswa (Gmail)</dt>
					<dd class="mb-3 d-flex align-items-center justify-content-between">
						<span class="text-truncate small" style="max-width: 180px;"><?= e($req['requester_email'] ?: '-') ?></span>
						<button type="button" class="btn btn-xs btn-outline-primary shadow-xs px-2 py-1" data-bs-toggle="modal" data-bs-target="#modalKirimEmailSiswa">
							<i class="fa-solid fa-paper-plane me-1"></i> Email
						</button>
					</dd>

					<dt class="small text-muted">Catatan dari Siswa</dt>
					<dd class="small text-secondary mb-3"><?= $req['requester_note'] ? nl2br(e($req['requester_note'])) : '<span class="text-muted">-</span>' ?></dd>

					<dt class="small text-muted">Waktu Diajukan</dt>
					<dd class="small text-muted mb-0"><?= e(tgl_indo($req['created_at'], TRUE)) ?> <?= e(substr($req['created_at'], 11, 5)) ?> WIB</dd>
				</dl>
			</div>
		</div>
	</div>
</div>

<!-- =====================================================================
     MODAL 1: PRATINJAU SURAT (Live Document Preview)
     ===================================================================== -->
<div class="modal fade" id="modalPratinjauSurat" tabindex="-1" aria-labelledby="modalPratinjauSuratLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-scrollable">
		<div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
			<div class="modal-header bg-light py-3 border-bottom">
				<div class="d-flex align-items-center gap-2">
					<i class="fa-solid fa-file-invoice text-primary fs-5"></i>
					<div>
						<h5 class="modal-title h6 fw-bold text-navy mb-0" id="modalPratinjauSuratLabel">
							Pratinjau Surat: <?= e($req['type_name']) ?>
						</h5>
						<div class="small text-muted">Perkiraan Nomor: <code class="fw-bold text-primary"><?= e($next_number) ?></code></div>
					</div>
				</div>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
			</div>
			
			<div class="modal-body p-0" style="background: #e2e8f0; min-height: 520px;">
				<iframe src="<?= site_url('permohonan-masuk/pratinjau/'.$id) ?>" style="width: 100%; height: 72vh; border: 0;" title="Pratinjau Dokumen"></iframe>
			</div>

			<div class="modal-footer bg-white border-top py-2 px-3 d-flex justify-content-between align-items-center">
				<a href="<?= site_url('surat/buat/'.(int) $req['letter_type_id'].'?permohonan='.$id) ?>" class="btn btn-sm btn-outline-secondary">
					<i class="fa-solid fa-pen-to-square me-1"></i> Edit di Formulir Penuh
				</a>
				<div class="d-flex gap-2">
					<button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Tutup</button>
					<button type="button" class="btn btn-sm btn-primary fw-bold" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#modalKirimKepsek">
						<i class="fa-solid fa-paper-plane me-1"></i> Lanjut Kirim ke Kepala Sekolah &raquo;
					</button>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- =====================================================================
     MODAL 2: KIRIM KE KEPALA SEKOLAH (Instant Submit to Principal)
     ===================================================================== -->
<div class="modal fade" id="modalKirimKepsek" tabindex="-1" aria-labelledby="modalKirimKepsekLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
			<div class="modal-header bg-primary text-white py-3">
				<h5 class="modal-title h6 fw-bold mb-0" id="modalKirimKepsekLabel">
					<i class="fa-solid fa-paper-plane me-2"></i>Kirim Surat ke Kepala Sekolah
				</h5>
				<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
			</div>

			<?= form_open('permohonan-masuk/kirim-kepsek/'.$id) ?>
				<div class="modal-body p-3 p-sm-4">
					<div class="alert alert-primary bg-primary bg-opacity-10 border-0 text-dark small mb-3 rounded-3">
						<i class="fa-solid fa-circle-info text-primary me-1"></i> Berkas surat akan otomatis diterbitkan dari data siswa ini dan langsung diajukan ke meja <strong>Kepala Sekolah</strong> untuk diverifikasi &amp; ditandatangani.
					</div>

					<div class="mb-3">
						<label class="form-label required fw-bold small text-navy">Nomor Surat:</label>
						<input type="text" name="letter_number" class="form-control font-monospace fw-bold text-navy" value="<?= e($next_number) ?>" required>
						<div class="form-text small">Nomor otomatis dihitung berdasarkan nomor urut agenda sekolah. Anda dapat mengubahnya jika perlu.</div>
					</div>

					<div class="mb-3">
						<label class="form-label required fw-bold small text-navy">Tanggal Surat:</label>
						<input type="date" name="letter_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
					</div>

					<input type="hidden" name="signature_type" value="digital">

					<div class="mb-2">
						<label class="form-label fw-bold small text-navy">Pemberitahuan Otomatis ke Siswa:</label>
						<div class="p-3 bg-light rounded-3 border">
							<div class="form-check">
								<input class="form-check-input" type="checkbox" name="send_wa" id="send_wa_modal_kepsek" value="1" <?= !empty($req['requester_phone']) ? 'checked' : 'disabled' ?>>
								<label class="form-check-label small fw-semibold text-success" for="send_wa_modal_kepsek">
									<i class="fa-brands fa-whatsapp me-1"></i> Buka WhatsApp &amp; kirim info ke nomor siswa (<?= e($req['requester_phone'] ?: '-') ?>)
								</label>
								<div class="form-text small ps-4">Memberitahukan siswa bahwa permohonan telah diverifikasi TU dan sedang diajukan ke Kepala Sekolah.</div>
							</div>
							
							<div class="form-check mt-2">
								<input class="form-check-input" type="checkbox" name="send_email" id="send_email_modal_kepsek" value="1" <?= !empty($req['requester_email']) ? 'checked' : 'disabled' ?>>
								<label class="form-check-label small text-primary fw-semibold" for="send_email_modal_kepsek">
									<i class="fa-solid fa-envelope me-1"></i> Kirim notifikasi email ke Gmail siswa (<?= e($req['requester_email'] ?: '-') ?>)
								</label>
								<div class="form-text small ps-4">Notifikasi otomatis terkirim ke email siswa berisi link status pelacakan online.</div>
							</div>
						</div>
					</div>
				</div>

				<div class="modal-footer bg-light border-top py-2 px-3">
					<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
					<button type="submit" class="btn btn-primary fw-bold shadow-sm">
						<i class="fa-solid fa-paper-plane me-1"></i> Ya, Kirim ke Kepala Sekolah
					</button>
				</div>
			<?= form_close() ?>
		</div>
	</div>
</div>

<!-- =====================================================================
     MODAL 3: TOLAK PERMOHONAN (Clean Modal Rejection)
     ===================================================================== -->
<div class="modal fade" id="modalTolakPermohonan" tabindex="-1" aria-labelledby="modalTolakPermohonanLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
			<div class="modal-header bg-danger text-white py-3">
				<h5 class="modal-title h6 fw-bold mb-0" id="modalTolakPermohonanLabel">
					<i class="fa-solid fa-ban me-2"></i>Tolak Permohonan Siswa
				</h5>
				<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
			</div>

			<?= form_open('permohonan-masuk/tolak/'.$id, array('data-confirm' => 'Tolak permohonan '.$req['tracking_code'].'? Alasan penolakan akan disimpan dan dapat langsung dikirim ke WhatsApp siswa.')) ?>
				<div class="modal-body p-3 p-sm-4">
					<div class="mb-3">
						<label class="form-label required fw-bold small text-navy" for="admin_note_modal">Alasan Penolakan:</label>
						<textarea class="form-control" id="admin_note_modal" name="admin_note" rows="3" maxlength="500" required placeholder="Contoh: NIS tidak sesuai data buku induk sekolah. Silakan ajukan ulang atau hubungi petugas TU."></textarea>
						<div class="form-text small">Alasan ini akan tampil pada status pelacakan siswa &amp; pesan WhatsApp.</div>
					</div>

					<div class="p-3 bg-light rounded-3 border">
						<div class="form-check">
							<input class="form-check-input" type="checkbox" name="send_wa" id="send_wa_modal" value="1" <?= !empty($req['requester_phone']) ? 'checked' : 'disabled' ?>>
							<label class="form-check-label small fw-semibold text-success" for="send_wa_modal">
								<i class="fa-brands fa-whatsapp me-1"></i> Buka WhatsApp &amp; kirim pesan ke nomor siswa (<?= e($req['requester_phone'] ?: '-') ?>)
							</label>
						</div>
						<?php if (!empty($req['requester_email'])): ?>
						<div class="form-check mt-2">
							<input class="form-check-input" type="checkbox" name="send_email" id="send_email_modal" value="1" checked>
							<label class="form-check-label small text-primary fw-semibold" for="send_email_modal">
								<i class="fa-solid fa-envelope me-1"></i> Kirim juga email pemberitahuan ke Gmail siswa
							</label>
						</div>
						<?php endif; ?>
					</div>
				</div>

				<div class="modal-footer bg-light border-top py-2 px-3">
					<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
					<button type="submit" class="btn btn-danger fw-bold shadow-sm">
						<i class="fa-solid fa-ban me-1"></i> Tolak Permohonan
					</button>
				</div>
			<?= form_close() ?>
		</div>
	</div>
</div>

<!-- =====================================================================
     MODAL 4: KIRIM EMAIL KE GMAIL SISWA
     ===================================================================== -->
<div class="modal fade" id="modalKirimEmailSiswa" tabindex="-1" aria-labelledby="modalKirimEmailSiswaLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
			<div class="modal-header bg-light border-bottom py-3">
				<h5 class="modal-title h6 fw-bold text-navy mb-0" id="modalKirimEmailSiswaLabel">
					<i class="fa-solid fa-envelope text-primary me-2"></i>Kirim Notifikasi ke Gmail Siswa
				</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
			</div>
			<?= form_open('permohonan-masuk/kirim-email/'.$req['id']) ?>
				<div class="modal-body p-3 p-sm-4">
					<div class="mb-3">
						<label class="form-label required fw-semibold small" for="recipient_email">Alamat Gmail / Email Siswa:</label>
						<div class="input-group">
							<span class="input-group-text bg-light"><i class="fa-solid fa-envelope text-primary"></i></span>
							<input type="email" class="form-control" id="recipient_email" name="recipient_email" required value="<?= e($req['requester_email']) ?>" placeholder="siswa@gmail.com">
						</div>
					</div>

					<div class="mb-3">
						<label class="form-label required fw-semibold small" for="email_subject">Subjek Pesan:</label>
						<input type="text" class="form-control" id="email_subject" name="email_subject" required 
							value="[<?= e($school['school_name'] ?: 'SMK') ?>] Status Permohonan Surat <?= e($req['type_name']) ?> - <?= e($req['tracking_code']) ?>">
					</div>

					<div class="mb-3">
						<label class="form-label required fw-semibold small" for="email_message">Isi Pesan:</label>
						<?php
						if ($req['status'] === 'approved') {
							$default_msg = "Halo " . $req['requester_name'] . ",\n\nPermohonan surat " . $req['type_name'] . " Anda di " . $school['school_name'] . " telah SELESAI DIPROSES oleh Tata Usaha dengan Nomor Surat: " . ($req['letter_number'] ?: '-') . ".\n\nBerkas surat resmi (PDF) dapat Anda unduh dari lampiran email ini atau diambil langsung fisiknya di ruang TU sekolah pada jam kerja dengan menyebutkan Kode: " . $req['tracking_code'] . ".\n\nTerima kasih.\nTata Usaha " . $school['school_name'];
						} elseif ($req['status'] === 'rejected') {
							$default_msg = "Halo " . $req['requester_name'] . ",\n\nMohon maaf, permohonan surat " . $req['type_name'] . " Anda (Kode: " . $req['tracking_code'] . ") belum dapat diproses karena alasan berikut:\n\"" . $req['admin_note'] . "\"\n\nSilakan lengkapi data dan ajukan ulang atau hubungi petugas Tata Usaha.\n\nTerima kasih.\nTata Usaha " . $school['school_name'];
						} else {
							$default_msg = "Halo " . $req['requester_name'] . ",\n\nPermohonan surat " . $req['type_name'] . " Anda dengan Kode Lacak " . $req['tracking_code'] . " saat ini sedang dalam proses pemeriksaan berkas oleh staf Tata Usaha.\n\nKami akan memberitahukan kembali segera setelah surat disahkan.\n\nTerima kasih.\nTata Usaha " . $school['school_name'];
						}
						?>
						<textarea class="form-control" id="email_message" name="email_message" rows="5" required><?= e($default_msg) ?></textarea>
					</div>

					<?php if (!empty($req['letter_id'])): ?>
					<div class="form-check form-switch p-3 bg-light rounded-3 border">
						<input class="form-check-input ms-0 me-2" type="checkbox" role="switch" id="attach_pdf" name="attach_pdf" value="1" checked>
						<label class="form-check-label fw-semibold small" for="attach_pdf">
							<i class="fa-solid fa-file-pdf text-danger me-1"></i> Lampirkan File PDF Surat Resmi (<?= e($req['letter_number']) ?>)
						</label>
					</div>
					<?php endif; ?>
				</div>
				<div class="modal-footer border-top bg-light py-2 px-3">
					<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
					<button type="submit" class="btn btn-primary fw-semibold">
						<i class="fa-solid fa-paper-plane me-1"></i> Kirim Sekarang
					</button>
				</div>
			<?= form_close() ?>
		</div>
	</div>
</div>

<?php $open_wa_url = $this->session->flashdata('open_wa_url'); ?>
<?php if (!empty($open_wa_url)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
	try {
		var w = window.open('<?= $open_wa_url ?>', '_blank');
		if (w) w.focus();
	} catch (e) {
		console.log('Popup diblokir peramban, tombol manual tersedia di banner atas.');
	}
});
</script>
<?php endif; ?>
