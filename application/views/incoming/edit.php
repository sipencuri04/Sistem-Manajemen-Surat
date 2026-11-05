<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<nav aria-label="breadcrumb">
	<ol class="breadcrumb small mb-2">
		<li class="breadcrumb-item"><a href="<?= site_url('surat-masuk') ?>">Surat Masuk</a></li>
		<li class="breadcrumb-item"><a href="<?= site_url('surat-masuk/detail/'.$letter['id']) ?>"><?= e($letter['agenda_number']) ?></a></li>
		<li class="breadcrumb-item active" aria-current="page">Edit</li>
	</ol>
</nav>

<div class="row justify-content-center">
	<div class="col-lg-9">
		<div class="card">
			<div class="card-header bg-white py-3">
				<h2 class="h5 card-title mb-0 text-navy"><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Edit Surat Masuk</h2>
			</div>
			<div class="card-body">
				<?= form_open_multipart('surat-masuk/update/'.$letter['id']) ?>
				<div class="row g-3">
					<div class="col-md-6">
						<label class="form-label required" for="agenda_number">Nomor Agenda</label>
						<input type="text" class="form-control letter-number fw-bold" id="agenda_number" name="agenda_number" value="<?= set_value('agenda_number', $letter['agenda_number']) ?>" required>
					</div>
					<div class="col-md-6">
						<label class="form-label required" for="received_date">Tanggal Diterima</label>
						<input type="date" class="form-control" id="received_date" name="received_date" value="<?= set_value('received_date', $letter['received_date']) ?>" required>
					</div>

					<div class="col-md-6">
						<label class="form-label required" for="letter_number">Nomor Surat Asal</label>
						<input type="text" class="form-control" id="letter_number" name="letter_number" value="<?= set_value('letter_number', $letter['letter_number']) ?>" required>
					</div>
					<div class="col-md-6">
						<label class="form-label required" for="letter_date">Tanggal Surat</label>
						<input type="date" class="form-control" id="letter_date" name="letter_date" value="<?= set_value('letter_date', $letter['letter_date']) ?>" required>
					</div>

					<div class="col-md-6">
						<label class="form-label required" for="sender">Pengirim / Asal Surat</label>
						<input type="text" class="form-control" id="sender" name="sender" value="<?= set_value('sender', $letter['sender']) ?>" required>
					</div>
					<div class="col-md-6">
						<label class="form-label" for="recipient">Ditujukan Kepada</label>
						<input type="text" class="form-control" id="recipient" name="recipient" value="<?= set_value('recipient', $letter['recipient']) ?>">
					</div>

					<div class="col-12">
						<label class="form-label required" for="subject">Perihal Surat</label>
						<input type="text" class="form-control" id="subject" name="subject" value="<?= set_value('subject', $letter['subject']) ?>" required>
					</div>

					<div class="col-12">
						<label class="form-label" for="description">Ringkasan Isi / Catatan Tambahan</label>
						<textarea class="form-control" id="description" name="description" rows="3"><?= set_value('description', $letter['description']) ?></textarea>
					</div>

					<div class="col-12">
						<label class="form-label" for="letter_file">Ganti Berkas Scan (Kosongkan jika tidak diubah, Maksimal 2 MB)</label>
						<?php if ($letter['file_path']): ?>
							<div class="mb-2 small text-muted"><i class="fa-solid fa-file me-1"></i> File saat ini: <?= e($letter['file_name']) ?></div>
						<?php endif; ?>
						<input type="file" class="form-control" id="letter_file" name="letter_file" accept="application/pdf,image/png,image/jpeg">
					</div>
				</div>

				<div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
					<a href="<?= site_url('surat-masuk/detail/'.$letter['id']) ?>" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Batal</a>
					<button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i> Perbarui Surat Masuk</button>
				</div>
				<?= form_close() ?>
			</div>
		</div>
	</div>
</div>
