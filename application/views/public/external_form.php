<?php defined('BASEPATH') OR exit('No direct script access allowed');
$err = function ($k) use ($custom_errors) {
	$m = strip_tags(form_error($k));
	if ($m === '' && isset($custom_errors[$k])) $m = $custom_errors[$k];
	return $m !== '' ? '<div class="invalid-feedback d-block">'.e($m).'</div>' : '';
};
$inv = function ($k) use ($custom_errors) {
	return (form_error($k) !== '' || isset($custom_errors[$k])) ? ' is-invalid' : '';
};
?>
<div class="row justify-content-center">
	<div class="col-lg-9 col-xl-8">


		<!-- Top Navigation Pill -->
		<div class="d-flex align-items-center justify-content-between mb-3">
			<a href="<?= site_url('permohonan') ?>" class="btn btn-sm btn-light border rounded-pill px-3 py-1 text-muted text-decoration-none">
				<i class="fa-solid fa-arrow-left me-1"></i> Beranda Portal
			</a>
			<a href="<?= site_url('kirim-surat/status') ?>" class="badge bg-secondary-subtle text-secondary border rounded-pill px-3 py-1 text-decoration-none">
				<i class="fa-solid fa-receipt me-1"></i> Cek Tanda Terima Resi
			</a>
		</div>

		<!-- Header Banner -->
		<div class="d-flex align-items-center gap-3 mb-4">
			<div class="bg-primary bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:48px;height:48px;">
				<i class="fa-solid fa-building-columns text-primary fs-5"></i>
			</div>
			<div>
				<h1 class="h5 fw-bold text-navy mb-0">Kirim Surat Kedinasan</h1>
				<p class="text-muted small mb-0">Upload surat dinas resmi langsung ke Tata Usaha sekolah</p>
			</div>
		</div>

		<?php if (isset($custom_errors['general'])): ?>
			<div class="alert alert-danger border-0 shadow-sm rounded-4 mb-3">
				<i class="fa-solid fa-circle-xmark me-1"></i> <?= e($custom_errors['general']) ?>
			</div>
		<?php endif; ?>

		<?= form_open_multipart('kirim-surat/simpan', array('novalidate' => 'novalidate', 'autocomplete' => 'off')) ?>
			<!-- Honeypot robot trap -->
			<div class="hp-field" aria-hidden="true">
				<label for="website">Website</label>
				<input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
			</div>

			<!-- Card Section 1: Data Surat Resmi -->
			<div class="card border-0 shadow-sm rounded-4 mb-3 overflow-hidden">
				<div class="card-header bg-white border-bottom py-3 px-3 px-sm-4">
					<div class="d-flex align-items-center gap-2">
						<span class="badge bg-primary rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width:28px;height:28px;">
							<i class="fa-solid fa-envelope fa-xs text-white"></i>
						</span>
						<h2 class="h6 fw-bold text-navy mb-0">Identitas Surat Dinas</h2>
					</div>
				</div>
				<div class="card-body p-3 p-sm-4">
					<div class="row g-3">
						<div class="col-12 col-md-6">
							<label class="form-label fw-semibold small required" for="sender">Nama Lembaga / Instansi Pengirim</label>
							<input type="text" class="form-control rounded-3<?= $inv('sender') ?>" id="sender" name="sender" maxlength="200" placeholder="Contoh: Dinas Pendidikan Wilayah VII" value="<?= e(set_value('sender', '', FALSE)) ?>" required>
							<?= $err('sender') ?>
						</div>

						<div class="col-12 col-md-6">
							<label class="form-label fw-semibold small required" for="letter_number">Nomor Surat Asal</label>
							<input type="text" class="form-control font-monospace rounded-3<?= $inv('letter_number') ?>" id="letter_number" name="letter_number" maxlength="100" placeholder="Contoh: 421.3/085/DISDIK/2026" value="<?= e(set_value('letter_number', '', FALSE)) ?>" required>
							<?= $err('letter_number') ?>
						</div>

						<div class="col-12 col-md-6">
							<label class="form-label fw-semibold small required" for="letter_date">Tanggal yang Tertera di Surat</label>
							<input type="date" class="form-control rounded-3<?= $inv('letter_date') ?>" id="letter_date" name="letter_date" value="<?= e(set_value('letter_date', date('Y-m-d'), FALSE)) ?>" required>
							<?= $err('letter_date') ?>
						</div>

						<div class="col-12 col-md-6">
							<label class="form-label fw-semibold small" for="recipient">Ditujukan Kepada</label>
							<input type="text" class="form-control rounded-3<?= $inv('recipient') ?>" id="recipient" name="recipient" maxlength="200" placeholder="Kepala Sekolah / Waka Kurikulum / TU" value="<?= e(set_value('recipient', 'Kepala Sekolah', FALSE)) ?>">
							<?= $err('recipient') ?>
						</div>

						<div class="col-12">
							<label class="form-label fw-semibold small required" for="subject">Perihal / Pokok Isi Surat</label>
							<input type="text" class="form-control rounded-3<?= $inv('subject') ?>" id="subject" name="subject" maxlength="255" placeholder="Contoh: Undangan Rapat Koordinasi Kurikulum SMK PK" value="<?= e(set_value('subject', '', FALSE)) ?>" required>
							<?= $err('subject') ?>
						</div>

						<div class="col-12">
							<label class="form-label fw-semibold small" for="description">Ringkasan / Keterangan Isi Surat <span class="text-muted fw-normal">(opsional)</span></label>
							<textarea class="form-control rounded-3<?= $inv('description') ?>" id="description" name="description" rows="3" placeholder="Tuliskan keterangan singkat, waktu acara, tempat, atau catatan penting..."><?= e(set_value('description', '', FALSE)) ?></textarea>
							<?= $err('description') ?>
						</div>
					</div>
				</div>
			</div>

			<!-- Card Section 2: Upload Berkas PDF / Gambar -->
			<div class="card border-0 shadow-sm rounded-4 mb-3 overflow-hidden">
				<div class="card-header bg-white border-bottom py-3 px-3 px-sm-4">
					<div class="d-flex align-items-center gap-2">
						<span class="badge bg-danger rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width:28px;height:28px;">
							<i class="fa-solid fa-file-pdf fa-xs text-white"></i>
						</span>
						<h2 class="h6 fw-bold text-navy mb-0">Lampiran Berkas Surat (PDF/Gambar)</h2>
					</div>
				</div>
				<div class="card-body p-3 p-sm-4">
					<div class="p-3 bg-light rounded-3 border mb-3">
						<label class="form-label fw-semibold small required" for="letter_file">Pilih Dokumen Scan Surat Resmi</label>
						<input type="file" class="form-control rounded-3<?= $inv('letter_file') ?>" id="letter_file" name="letter_file" accept=".pdf,.jpg,.jpeg,.png" required>
						<?= $err('letter_file') ?>
						<div class="form-text small mt-2">
							<i class="fa-solid fa-circle-info text-primary me-1"></i> Format yang didukung: <strong>PDF, JPG, PNG</strong>. Maksimal ukuran file <strong>2 MB</strong>. Pastikan surat telah ditandatangani dan dibubuhi stempel resmi instansi pengirim.
						</div>
					</div>
				</div>
			</div>

			<!-- Card Section 3: Kontak Narahubung & Captcha -->
			<div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
				<div class="card-header bg-white border-bottom py-3 px-3 px-sm-4">
					<div class="d-flex align-items-center gap-2">
						<span class="badge bg-success rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width:28px;height:28px;">
							<i class="fa-brands fa-whatsapp fa-xs text-white"></i>
						</span>
						<h2 class="h6 fw-bold text-navy mb-0">Narahubung (PIC) Pengirim &amp; Verifikasi</h2>
					</div>
				</div>
				<div class="card-body p-3 p-sm-4">
					<div class="row g-3">
						<div class="col-12 col-md-6">
							<label class="form-label fw-semibold small required" for="sender_phone">Nomor WhatsApp / HP Narahubung</label>
							<div class="input-group">
								<span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="fa-solid fa-phone text-muted"></i></span>
								<input type="tel" class="form-control rounded-end-3<?= $inv('sender_phone') ?>" id="sender_phone" name="sender_phone" maxlength="30" placeholder="Contoh: 081234567890" value="<?= e(set_value('sender_phone', '', FALSE)) ?>" required>
							</div>
							<?= $err('sender_phone') ?>
							<div class="form-text small">Digunakan TU sekolah untuk konfirmasi atau balasan tindak lanjut.</div>
						</div>

						<div class="col-12 col-md-6">
							<label class="form-label fw-semibold small" for="sender_email">Alamat Email Narahubung <span class="text-muted fw-normal">(opsional)</span></label>
							<div class="input-group">
								<span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="fa-solid fa-envelope text-muted"></i></span>
								<input type="email" class="form-control rounded-end-3<?= $inv('sender_email') ?>" id="sender_email" name="sender_email" maxlength="100" placeholder="nama@instansi.go.id" value="<?= e(set_value('sender_email', '', FALSE)) ?>">
							</div>
							<?= $err('sender_email') ?>
						</div>

						<div class="col-12 col-md-6">
							<label class="form-label fw-semibold small required" for="captcha">Verifikasi Keamanan: <strong><?= e($captcha_q) ?> = ?</strong></label>
							<div class="input-group" style="max-width: 180px;">
								<span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="fa-solid fa-shield-halved text-muted"></i></span>
								<input type="text" class="form-control rounded-end-3 text-center fw-bold<?= $inv('captcha') ?>" id="captcha" name="captcha" maxlength="3" inputmode="numeric" placeholder="Jawaban" required>
							</div>
							<?= $err('captcha') ?>
						</div>

						<div class="col-12">
							<div class="p-3 bg-light rounded-3 border">
								<div class="form-check mb-0">
									<input class="form-check-input<?= $inv('agree') ?>" type="checkbox" id="agree" name="agree" value="1" <?= set_checkbox('agree', '1') ?> required>
									<label class="form-check-label small" for="agree">
										Saya mewakili lembaga/instansi pengirim menyatakan bahwa surat dan lampiran yang kami kirimkan adalah <strong>asli, sah, dan dapat dipertanggungjawabkan</strong>.
									</label>
								</div>
								<?= $err('agree') ?>
							</div>
						</div>
					</div>
				</div>

				<div class="card-footer bg-light px-3 px-sm-4 py-3 d-flex gap-2 justify-content-between align-items-center form-submit-bar">
					<a href="<?= site_url('permohonan') ?>" class="btn btn-outline-secondary rounded-pill px-3">
						<i class="fa-solid fa-arrow-left me-1"></i> Batal
					</a>
					<button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm fw-semibold">
						<i class="fa-solid fa-paper-plane me-1"></i> Kirim Surat ke Sekolah
					</button>
				</div>
			</div>
		<?= form_close() ?>
	</div>
</div>
