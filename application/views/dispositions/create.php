<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<nav aria-label="breadcrumb">
	<ol class="breadcrumb small mb-2">
		<li class="breadcrumb-item"><a href="<?= site_url('disposisi') ?>">Disposisi Surat</a></li>
		<li class="breadcrumb-item active" aria-current="page">Buat Disposisi</li>
	</ol>
</nav>

<div class="row justify-content-center">
	<div class="col-lg-9">
		<div class="card">
			<div class="card-header bg-white py-3">
				<h2 class="h5 card-title mb-0 text-navy"><i class="fa-solid fa-share-nodes text-warning me-2"></i>Lembar Disposisi Kepala Sekolah</h2>
			</div>
			<div class="card-body">
				<?php if ($incoming): ?>
					<div class="alert alert-light border mb-4">
						<div class="d-flex align-items-center gap-2 mb-1">
							<span class="badge text-bg-primary">Surat Rujukan</span>
							<strong class="letter-number text-navy"><?= e($incoming['agenda_number']) ?></strong> &middot;
							<span class="text-muted">No. Asal: <?= e($incoming['letter_number']) ?></span>
						</div>
						<div class="fw-bold text-navy"><?= e($incoming['subject']) ?></div>
						<small class="text-muted">Dari: <?= e($incoming['sender']) ?> &middot; Diterima: <?= e(tgl_indo($incoming['received_date'])) ?></small>
						<?php if ($incoming['file_path']): ?>
							<div class="mt-2">
								<a href="<?= site_url('surat-masuk/berkas/'.$incoming['id'].'?lihat=1') ?>" target="_blank" class="btn btn-xs btn-outline-danger">
									<i class="fa-solid fa-file-pdf me-1"></i> Buka Scan Surat Masuk
								</a>
							</div>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?= form_open('disposisi/simpan') ?>
				<?php if ($incoming): ?>
					<input type="hidden" name="incoming_letter_id" value="<?= (int) $incoming['id'] ?>">
				<?php endif; ?>

				<div class="row g-3">
					<div class="col-md-6">
						<label class="form-label required" for="disposition_number">Nomor Lembar Disposisi</label>
						<input type="text" class="form-control letter-number fw-bold" id="disposition_number" name="disposition_number" value="<?= set_value('disposition_number', $disposition_number) ?>" required>
					</div>
					<div class="col-md-6">
						<label class="form-label required" for="disposition_date">Tanggal Disposisi</label>
						<input type="date" class="form-control" id="disposition_date" name="disposition_date" value="<?= set_value('disposition_date', date('Y-m-d')) ?>" required>
					</div>

					<div class="col-md-6">
						<label class="form-label required" for="priority">Sifat / Prioritas</label>
						<select class="form-select" id="priority" name="priority" required>
							<?php foreach (Disposition_model::$priorities as $pk => $pv): ?>
								<option value="<?= $pk ?>" <?= set_select('priority', $pk, $pk === 'biasa') ?>><?= e($pv[1]) ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-md-6">
						<label class="form-label" for="deadline_date">Tenggat Waktu Penyelesaian (Batas Akhir)</label>
						<input type="date" class="form-control" id="deadline_date" name="deadline_date" value="<?= set_value('deadline_date') ?>">
						<small class="text-muted">Kosongkan jika tidak ada batas waktu khusus.</small>
					</div>

					<div class="col-12">
						<label class="form-label required" for="instruction">Instruksi Tindak Lanjut Kepala Sekolah</label>
						<textarea class="form-control" id="instruction" name="instruction" rows="3" placeholder="Contoh: Koordinasikan dengan waka kurikulum untuk menyiapkan jadwal pendampingan, lalu laporkan hasilnya." required><?= set_value('instruction') ?></textarea>
					</div>

					<div class="col-12">
						<div class="d-flex justify-content-between align-items-center mb-1">
							<label class="form-label required mb-0">Tujukan Kepada Pegawai / Tim Penerima Disposisi</label>
							<div class="d-flex gap-2">
								<button type="button" class="btn btn-xs btn-outline-primary" id="btnSelectAllEmployees">
									<i class="fa-solid fa-check-double me-1"></i> Pilih Semua Pegawai
								</button>
								<button type="button" class="btn btn-xs btn-outline-secondary" id="btnDeselectAllEmployees">
									<i class="fa-solid fa-xmark me-1"></i> Batal Pilih
								</button>
							</div>
						</div>
						<div class="border rounded p-3 bg-light" style="max-height: 260px; overflow-y: auto;">
							<?php if (empty($employees)): ?>
								<div class="text-muted small">Belum ada akun pegawai aktif. Tambahkan melalui menu Pengguna.</div>
							<?php else: foreach ($employees as $emp): ?>
								<div class="form-check mb-2">
									<input class="form-check-input emp-checkbox" type="checkbox" name="recipient_ids[]" value="<?= (int) $emp['id'] ?>" id="emp_<?= (int) $emp['id'] ?>">
									<label class="form-check-label d-flex justify-content-between align-items-center cursor-pointer" for="emp_<?= (int) $emp['id'] ?>">
										<div>
											<strong><?= e($emp['full_name']) ?></strong>
											<?= role_badge($emp['role']) ?>
											<div class="small text-muted"><?= e($emp['position'] ?: 'Staf') ?> <?= $emp['nip'] ? ' &middot; NIP: '.e($emp['nip']) : '' ?></div>
										</div>
									</label>
								</div>
							<?php endforeach; endif; ?>
						</div>
						<div class="d-flex justify-content-between align-items-center mt-1">
							<small class="text-muted">Centang satu, beberapa, atau gunakan tombol <strong>"Pilih Semua Pegawai"</strong> di atas.</small>
							<small class="fw-semibold text-primary" id="selectedCountText">0 pegawai terpilih</small>
						</div>
					</div>

					<div class="col-12">
						<label class="form-label" for="notes">Catatan Tambahan (Opsional)</label>
						<textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Catatan internal tambahan..."><?= set_value('notes') ?></textarea>
					</div>
				</div>

				<div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
					<a href="<?= $incoming ? site_url('surat-masuk/detail/'.$incoming['id']) : site_url('disposisi') ?>" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Batal</a>
					<button type="submit" class="btn btn-warning"><i class="fa-solid fa-paper-plane me-1"></i> Simpan &amp; Kirim Disposisi</button>
				</div>
				<?= form_close() ?>
			</div>
		</div>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
	var checkboxes = document.querySelectorAll('.emp-checkbox');
	var btnSelectAll = document.getElementById('btnSelectAllEmployees');
	var btnDeselectAll = document.getElementById('btnDeselectAllEmployees');
	var countText = document.getElementById('selectedCountText');

	function updateCount() {
		var checkedCount = 0;
		checkboxes.forEach(function(cb) {
			if (cb.checked) checkedCount++;
		});
		if (countText) {
			countText.textContent = checkedCount + ' pegawai terpilih';
		}
	}

	if (btnSelectAll) {
		btnSelectAll.addEventListener('click', function() {
			checkboxes.forEach(function(cb) {
				cb.checked = true;
			});
			updateCount();
		});
	}

	if (btnDeselectAll) {
		btnDeselectAll.addEventListener('click', function() {
			checkboxes.forEach(function(cb) {
				cb.checked = false;
			});
			updateCount();
		});
	}

	checkboxes.forEach(function(cb) {
		cb.addEventListener('change', updateCount);
	});

	updateCount();
});
</script>
