<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
	<div>
		<h1 class="h4 fw-bold text-navy mb-1"><i class="fa-solid fa-clipboard-check me-2 text-primary"></i>Disposisi Saya</h1>
		<p class="text-muted small mb-0">Daftar tugas dan instruksi disposisi dari Kepala Sekolah yang ditugaskan kepada Anda.</p>
	</div>
</div>

<div class="card mb-3">
	<div class="card-body py-2">
		<form method="get" action="<?= site_url('disposisi-saya') ?>" class="row g-2 align-items-center">
			<div class="col-md-5">
				<div class="input-group input-group-sm">
					<span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
					<input type="text" name="q" class="form-control" placeholder="Cari instruksi, perihal, asal surat..." value="<?= e($filters['q']) ?>">
				</div>
			</div>
			<div class="col-md-3">
				<select name="status" class="form-select form-select-sm">
					<option value="">-- Semua Status --</option>
					<?php foreach (Disposition_model::$recipient_statuses as $k => $v): ?>
						<option value="<?= $k ?>" <?= $filters['status'] === $k ? 'selected' : '' ?>><?= e($v[1]) ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="col-md-2">
				<select name="priority" class="form-select form-select-sm">
					<option value="">-- Semua Prioritas --</option>
					<?php foreach (Disposition_model::$priorities as $k => $v): ?>
						<option value="<?= $k ?>" <?= $filters['priority'] === $k ? 'selected' : '' ?>><?= e($v[1]) ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="col-md-2 d-flex gap-1">
				<button type="submit" class="btn btn-sm btn-navy flex-fill"><i class="fa-solid fa-filter me-1"></i> Filter</button>
				<?php if ( ! empty($filters['q']) || ! empty($filters['status']) || ! empty($filters['priority'])): ?>
					<a href="<?= site_url('disposisi-saya') ?>" class="btn btn-sm btn-outline-secondary" title="Reset filter"><i class="fa-solid fa-rotate-left"></i></a>
				<?php endif; ?>
			</div>
		</form>
	</div>
</div>

<div class="row g-3">
<?php if (empty($dispositions)): ?>
	<div class="col-12">
		<div class="card py-5 text-center text-muted">
			<i class="fa-solid fa-clipboard-check fa-3x mb-3 text-secondary opacity-50 d-block"></i>
			<h5 class="fw-semibold">Tidak ada tugas disposisi</h5>
			<p class="small mb-0">Saat ini Anda tidak memiliki instruksi disposisi aktif yang perlu dikerjakan.</p>
		</div>
	</div>
<?php else: foreach ($dispositions as $d):
	$is_overdue = $d['deadline_date'] && strtotime($d['deadline_date']) < time() && $d['status'] !== 'selesai';
?>
	<div class="col-md-6 col-xl-4">
		<div class="card h-100 border <?= $is_overdue ? 'border-danger-subtle' : ($d['status'] === 'selesai' ? 'border-success-subtle' : '') ?>">
			<div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
				<span class="letter-number fw-bold text-navy small"><?= e($d['disposition_number']) ?></span>
				<div>
					<?= disposition_priority_badge($d['priority']) ?>
					<?= disposition_status_badge($d['status'], $is_overdue) ?>
				</div>
			</div>
			<div class="card-body">
				<?php if (!empty($d['incoming_letter_id'])): ?>
					<div class="small text-muted mb-1"><i class="fa-solid fa-inbox me-1"></i> Dari: <strong><?= e(isset($d['incoming_sender']) ? $d['incoming_sender'] : '-') ?></strong></div>
					<h6 class="fw-bold text-navy mb-2 text-truncate" title="<?= e(isset($d['incoming_subject']) ? $d['incoming_subject'] : '') ?>"><?= e(isset($d['incoming_subject']) ? $d['incoming_subject'] : '-') ?></h6>
				<?php else: ?>
					<h6 class="fw-bold text-navy mb-2">Disposisi Internal</h6>
				<?php endif; ?>

				<div class="p-2 bg-light rounded small mb-3 border-start border-3 border-warning">
					<strong>Instruksi Kepala Sekolah:</strong>
					<div class="text-secondary"><?= nl2br(e($d['instruction'])) ?></div>
				</div>

				<div class="mb-3">
					<div class="d-flex justify-content-between small mb-1">
						<span class="text-muted">Progres Tindak Lanjut:</span>
						<strong class="text-navy"><?= (int) $d['progress_percent'] ?>%</strong>
					</div>
					<div class="progress" style="height: 6px;">
						<div class="progress-bar bg-<?= $d['status'] === 'selesai' ? 'success' : 'primary' ?>" role="progressbar" style="width: <?= (int) $d['progress_percent'] ?>%"></div>
					</div>
				</div>

				<div class="small text-muted d-flex justify-content-between">
					<span><i class="fa-solid fa-calendar me-1"></i> <?= e(tgl_indo($d['disposition_date'])) ?></span>
					<span>
						<i class="fa-solid fa-clock me-1"></i>
						Tenggat: <strong class="<?= $is_overdue ? 'text-danger' : '' ?>"><?= $d['deadline_date'] ? e(tgl_indo($d['deadline_date'])) : 'Tidak ditentukan' ?></strong>
					</span>
				</div>
			</div>
			<div class="card-footer bg-white border-top d-flex justify-content-between align-items-center py-2">
				<a href="<?= site_url('disposisi/detail/'.$d['disposition_id']) ?>" class="btn btn-xs btn-outline-secondary">
					<i class="fa-solid fa-circle-info me-1"></i> Rincian
				</a>
				<button type="button" class="btn btn-xs btn-primary" data-bs-toggle="modal" data-bs-target="#myProgressModal_<?= $d['id'] ?>">
					<i class="fa-solid fa-pen-to-square me-1"></i> Lapor Tindak Lanjut
				</button>
			</div>
		</div>
	</div>

	<!-- Modal Lapor Progres -->
	<div class="modal fade" id="myProgressModal_<?= $d['id'] ?>" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title h6"><i class="fa-solid fa-pen-to-square me-2 text-primary"></i>Lapor Progres Tindak Lanjut</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
				</div>
				<?= form_open_multipart('disposisi/update-progres/'.$d['id']) ?>
				<div class="modal-body">
					<div class="mb-3">
						<label class="form-label required">Status Tindak Lanjut</label>
						<select name="status" class="form-select" required>
							<?php foreach (Disposition_model::$recipient_statuses as $rk => $rv): ?>
								<option value="<?= $rk ?>" <?= $d['status'] === $rk ? 'selected' : '' ?>><?= e($rv[1]) ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="mb-3">
						<label class="form-label required">Persentase Progres (0 - 100%)</label>
						<input type="number" name="progress_percent" class="form-control" min="0" max="100" value="<?= (int) $d['progress_percent'] ?>" required>
						<small class="text-muted">Pilih 100% jika seluruh tugas telah selesai dikerjakan.</small>
					</div>
					<div class="mb-3">
						<label class="form-label required">Catatan Hasil / Keterangan</label>
						<textarea name="progress_notes" class="form-control" rows="3" placeholder="Tuliskan uraian hasil pelaksanaan tugas..." required><?= e($d['progress_notes']) ?></textarea>
					</div>
					<div class="mb-3">
						<label class="form-label">Unggah Bukti Hasil (PDF/Foto/Dokumen, maks 5 MB)</label>
						<?php if ($d['evidence_file']): ?>
							<div class="small text-muted mb-1">File saat ini: <?= e($d['evidence_name']) ?></div>
						<?php endif; ?>
						<input type="file" name="evidence_file" class="form-control">
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Batal</button>
					<button type="submit" class="btn btn-sm btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i> Simpan Laporan</button>
				</div>
				<?= form_close() ?>
			</div>
		</div>
	</div>
<?php endforeach; endif; ?>
</div>

<?php if ($pagination): ?>
	<div class="d-flex justify-content-between align-items-center mt-3">
		<div class="small text-muted">Menampilkan <?= count($dispositions) ?> dari total <?= number_format($total) ?> disposisi</div>
		<?= $pagination ?>
	</div>
<?php endif; ?>
