<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<nav aria-label="breadcrumb">
	<ol class="breadcrumb small mb-2">
		<li class="breadcrumb-item"><a href="<?= site_url('surat-masuk') ?>">Surat Masuk</a></li>
		<li class="breadcrumb-item active" aria-current="page">Catat Surat Masuk</li>
	</ol>
</nav>

<div class="row justify-content-center">
	<div class="col-lg-9">
		<div class="card">
			<div class="card-header bg-white py-3">
				<h2 class="h5 card-title mb-0 text-navy"><i class="fa-solid fa-file-circle-plus text-primary me-2"></i>Catat Surat Masuk Baru</h2>
			</div>
			<div class="card-body">
				<?= form_open_multipart('surat-masuk/simpan') ?>
				<div class="row g-3">
					<div class="col-md-6">
						<label class="form-label required" for="agenda_number">Nomor Agenda</label>
						<input type="text" class="form-control letter-number fw-bold" id="agenda_number" name="agenda_number" value="<?= set_value('agenda_number', $agenda_number) ?>" required>
						<small class="text-muted">Nomor urut registrasi surat masuk di sekolah.</small>
					</div>
					<div class="col-md-6">
						<label class="form-label required" for="received_date">Tanggal Diterima</label>
						<input type="date" class="form-control" id="received_date" name="received_date" value="<?= set_value('received_date', date('Y-m-d')) ?>" required>
					</div>

					<div class="col-md-6">
						<label class="form-label required" for="letter_number">Nomor Surat Asal</label>
						<input type="text" class="form-control" id="letter_number" name="letter_number" value="<?= set_value('letter_number') ?>" placeholder="Contoh: 421/108/Disdik/2026" required>
						<small class="text-muted">Nomor surat yang tertera dari instansi pengirim.</small>
					</div>
					<div class="col-md-6">
						<label class="form-label required" for="letter_date">Tanggal Surat</label>
						<input type="date" class="form-control" id="letter_date" name="letter_date" value="<?= set_value('letter_date', date('Y-m-d')) ?>" required>
					</div>

					<div class="col-md-6">
						<label class="form-label required" for="sender">Pengirim / Asal Surat</label>
						<input type="text" class="form-control" id="sender" name="sender" value="<?= set_value('sender') ?>" placeholder="Contoh: Dinas Pendidikan Provinsi Jawa Barat" required>
					</div>
					<div class="col-md-6">
						<label class="form-label" for="recipient">Ditujukan Kepada</label>
						<input type="text" class="form-control" id="recipient" name="recipient" value="<?= set_value('recipient', 'Kepala Sekolah') ?>" placeholder="Default: Kepala Sekolah">
					</div>

					<div class="col-12">
						<label class="form-label required" for="subject">Perihal Surat</label>
						<input type="text" class="form-control" id="subject" name="subject" value="<?= set_value('subject') ?>" placeholder="Contoh: Undangan Rapat Koordinasi Kepala SMK Se-Wilayah" required>
					</div>

					<div class="col-12">
						<label class="form-label" for="description">Ringkasan Isi / Catatan Tambahan</label>
						<textarea class="form-control" id="description" name="description" rows="3" placeholder="Tuliskan poin penting surat atau catatan khusus..."><?= set_value('description') ?></textarea>
					</div>

					<div class="col-12">
						<label class="form-label" for="letter_file">Unggah Berkas Scan / PDF Surat Masuk (Maksimal 2 MB)</label>
						<input type="file" class="form-control" id="letter_file" name="letter_file" accept="application/pdf,image/png,image/jpeg">
						<small class="text-muted">Format file yang diperbolehkan: PDF, JPG, PNG (maksimal 2 MB). File tersimpan terlindungi dari akses publik.</small>
					</div>
				</div>

				<div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
					<a href="<?= site_url('surat-masuk') ?>" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Kembali</a>
					<button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i> Simpan Surat Masuk</button>
				</div>
				<?= form_close() ?>
			</div>
		</div>
	</div>
</div>
