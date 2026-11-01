<?php defined('BASEPATH') OR exit('No direct script access allowed');
$err = function ($k) { $m = strip_tags(form_error($k)); return $m !== '' ? '<div class="invalid-feedback d-block">'.e($m).'</div>' : ''; };
$inv = function ($k) { return form_error($k) !== '' ? ' is-invalid' : ''; };
$has_errors = validation_errors() !== '' || ! empty($f_errors);
?>
<div class="row justify-content-center">
	<div class="col-lg-9 col-xl-8">
		<!-- Top Navigation Pill -->
		<div class="d-flex align-items-center justify-content-between mb-3">
			<a href="<?= site_url('permohonan') ?>" class="btn btn-sm btn-light border rounded-pill px-3 py-1 text-muted text-decoration-none">
				<i class="fa-solid fa-arrow-left me-1"></i> Kembali
			</a>
			<span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1">
				<i class="fa-solid fa-file-pen me-1"></i> Formulir Online
			</span>
		</div>

		<div class="mb-3">
			<h1 class="h4 fw-bold text-navy mb-1"><?= e($type['name']) ?></h1>
			<?php if ($tpl['guide']['purpose'] !== ''): ?>
				<p class="text-muted small mb-0"><?= e($tpl['guide']['purpose']) ?></p>
			<?php endif; ?>
		</div>

		<?php if ($error): ?>
			<div class="alert alert-warning border-0 shadow-sm rounded-4 mb-3">
				<div class="d-flex align-items-center gap-2">
					<i class="fa-solid fa-triangle-exclamation text-warning fs-5"></i>
					<div><?= e($error) ?></div>
				</div>
			</div>
		<?php elseif ($has_errors): ?>
			<div class="alert alert-danger border-0 shadow-sm rounded-4 mb-3">
				<div class="d-flex align-items-center gap-2">
					<i class="fa-solid fa-circle-xmark text-danger fs-5"></i>
					<div>Data belum lengkap atau belum benar. Silakan periksa kolom yang ditandai merah.</div>
				</div>
			</div>
		<?php endif; ?>

		<?= form_open('permohonan/kirim/'.(int) $type['id'], array('novalidate' => 'novalidate', 'autocomplete' => 'off')) ?>
			<!-- Kolom jebakan robot: biarkan kosong -->
			<div class="hp-field" aria-hidden="true">
				<label for="website">Website</label>
				<input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
			</div>

			<!-- Section 1: Data Surat -->
			<div class="card border-0 shadow-sm rounded-4 mb-3 overflow-hidden">
				<div class="card-header bg-white border-bottom py-3 px-3 px-sm-4">
					<div class="d-flex align-items-center gap-2">
						<span class="badge bg-primary rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width:28px;height:28px;">
							<i class="fa-solid fa-pen fa-xs text-white"></i>
						</span>
						<h2 class="h6 fw-bold text-navy mb-0">Data untuk Surat</h2>
					</div>
				</div>
				<div class="card-body p-3 p-sm-4">
					<?php if ($tpl['guide']['tip'] !== ''): ?>
						<div class="alert alert-info py-2 px-3 small rounded-3 mb-3 border-0 bg-info-subtle text-info-emphasis">
							<i class="fa-solid fa-lightbulb me-1"></i> <?= e($tpl['guide']['tip']) ?>
						</div>
					<?php endif; ?>

					<div class="row g-3">
						<?php if ($need_name): ?>
							<div class="col-12 col-md-6">
								<label class="form-label fw-semibold small required" for="requester_name">Nama Pemohon (Siswa)</label>
								<input type="text" class="form-control rounded-3<?= $inv('requester_name') ?>" id="requester_name" name="requester_name" maxlength="150" placeholder="Nama lengkap siswa" value="<?= e(set_value('requester_name', '', FALSE)) ?>">
								<?= $err('requester_name') ?>
							</div>
						<?php endif; ?>
						<?php foreach ($tpl['fields'] as $name => $field): ?>
							<?php $this->load->view('letters/_field', array(
								'name'  => $name,
								'field' => $field,
								'value' => isset($f[$name]) ? $f[$name] : '',
								'error' => isset($f_errors[$name]) ? $f_errors[$name] : NULL,
							)); ?>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

			<!-- Section 2: Kontak & Verifikasi -->
			<div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
				<div class="card-header bg-white border-bottom py-3 px-3 px-sm-4">
					<div class="d-flex align-items-center gap-2">
						<span class="badge bg-success rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width:28px;height:28px;">
							<i class="fa-brands fa-whatsapp fa-xs text-white"></i>
						</span>
						<h2 class="h6 fw-bold text-navy mb-0">Kontak &amp; Konfirmasi</h2>
					</div>
				</div>
				<div class="card-body p-3 p-sm-4">
					<div class="row g-3">
						<div class="col-12 col-md-6">
							<label class="form-label fw-semibold small required" for="requester_phone">Nomor WhatsApp Aktif</label>
							<div class="input-group">
								<span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="fa-solid fa-phone text-muted"></i></span>
								<input type="tel" class="form-control rounded-end-3<?= $inv('requester_phone') ?>" id="requester_phone" name="requester_phone" maxlength="20" inputmode="tel" placeholder="Contoh: 081234567890" value="<?= e(set_value('requester_phone', '', FALSE)) ?>">
							</div>
							<?= $err('requester_phone') ?>
							<div class="form-text small">Petugas TU akan menghubungi nomor ini jika ada data yang perlu diverifikasi.</div>
						</div>

						<div class="col-12 col-md-6">
							<label class="form-label fw-semibold small" for="requester_note">Catatan Tambahan <span class="text-muted fw-normal">(opsional)</span></label>
							<input type="text" class="form-control rounded-3<?= $inv('requester_note') ?>" id="requester_note" name="requester_note" maxlength="500" value="<?= e(set_value('requester_note', '', FALSE)) ?>" placeholder="Misal: Perlu segera untuk beasiswa">
							<?= $err('requester_note') ?>
						</div>

						<div class="col-12 col-md-6">
							<label class="form-label fw-semibold small required" for="captcha">Hitung: <strong><?= e($captcha_q) ?> = ?</strong></label>
							<div class="input-group" style="max-width: 180px;">
								<span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="fa-solid fa-calculator text-muted"></i></span>
								<input type="text" class="form-control rounded-end-3 text-center fw-bold<?= $inv('captcha') ?>" id="captcha" name="captcha" maxlength="3" inputmode="numeric" placeholder="Jawaban">
							</div>
							<?= $err('captcha') ?>
						</div>

						<div class="col-12">
							<div class="p-3 bg-light rounded-3 border">
								<div class="form-check mb-0">
									<input class="form-check-input<?= $inv('agree') ?>" type="checkbox" id="agree" name="agree" value="1" <?= set_checkbox('agree', '1') ?>>
									<label class="form-check-label small" for="agree">
										Saya menyatakan bahwa semua data yang saya masukkan adalah <strong>benar dan sesuai dokumen asli</strong> sekolah.
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
						<i class="fa-solid fa-paper-plane me-1"></i> Kirim Permohonan
					</button>
				</div>
			</div>
		<?= form_close() ?>
	</div>
</div>
