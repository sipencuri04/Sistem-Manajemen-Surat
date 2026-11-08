<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
	<div>
		<h1 class="h4 fw-bold text-navy mb-1"><i class="fa-solid fa-share-nodes me-2 text-warning"></i>Disposisi Surat</h1>
		<p class="text-muted small mb-0">Instruksi tindak lanjut surat dari Kepala Sekolah kepada pegawai.</p>
	</div>
	<?php if (is_kepala_sekolah()): ?>
		<a href="<?= site_url('disposisi/buat') ?>" class="btn btn-warning"><i class="fa-solid fa-plus me-1"></i> Buat Disposisi</a>
	<?php endif; ?>
</div>

<div class="card mb-3">
	<div class="card-body py-2">
		<form method="get" action="<?= site_url('disposisi') ?>" class="row g-2 align-items-center">
			<div class="col-md-4">
				<div class="input-group input-group-sm">
					<span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
					<input type="text" name="q" class="form-control" placeholder="Cari no disposisi, instruksi, perihal, asal..." value="<?= e($filters['q']) ?>">
				</div>
			</div>
			<div class="col-md-3">
				<select name="status" class="form-select form-select-sm">
					<option value="">-- Semua Status --</option>
					<?php foreach (Disposition_model::$statuses as $k => $v): ?>
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
			<div class="col-md-3 d-flex gap-1">
				<select name="overdue" class="form-select form-select-sm">
					<option value="">-- Semua Waktu --</option>
					<option value="yes" <?= $filters['overdue'] === 'yes' ? 'selected' : '' ?>>Hanya Terlambat</option>
				</select>
				<button type="submit" class="btn btn-sm btn-navy"><i class="fa-solid fa-filter me-1"></i> Filter</button>
				<?php if ( ! empty($filters['q']) || ! empty($filters['status']) || ! empty($filters['priority']) || ! empty($filters['overdue'])): ?>
					<a href="<?= site_url('disposisi') ?>" class="btn btn-sm btn-outline-secondary" title="Reset filter"><i class="fa-solid fa-rotate-left"></i></a>
				<?php endif; ?>
			</div>
		</form>
	</div>
</div>

<div class="card">
	<div class="card-body p-0">
		<div class="table-responsive d-none d-md-block">
			<table class="table table-hover align-middle mb-0">
				<thead class="table-light">
					<tr>
						<th style="width:50px" class="text-center">No</th>
						<th>No. Disposisi &amp; Tanggal</th>
						<th>Surat Rujukan</th>
						<th>Instruksi Kepala Sekolah</th>
						<th>Prioritas</th>
						<th>Tenggat Waktu</th>
						<th>Progres Pegawai</th>
						<th>Status</th>
						<th style="width:80px" class="text-end">Aksi</th>
					</tr>
				</thead>
				<tbody>
				<?php if (empty($dispositions)): ?>
					<tr>
						<td colspan="9" class="text-center py-4 text-muted">
							<i class="fa-solid fa-clipboard-list fa-3x mb-3 text-secondary opacity-50 d-block"></i>
							Belum ada lembar disposisi yang sesuai.
						</td>
					</tr>
				<?php else: foreach ($dispositions as $idx => $d):
					$is_overdue = $d['deadline_date'] && strtotime($d['deadline_date']) < time() && $d['status'] !== 'selesai';
				?>
					<tr>
						<td class="text-center text-muted small"><?= $offset + $idx + 1 ?></td>
						<td>
							<div class="fw-bold letter-number text-navy"><?= e($d['disposition_number']) ?></div>
							<small class="text-muted"><?= e(tgl_indo($d['disposition_date'])) ?></small>
						</td>
						<td>
							<?php if ($d['incoming_letter_id']): ?>
								<div class="fw-semibold text-truncate" style="max-width:200px;" title="<?= e($d['incoming_subject']) ?>"><?= e($d['incoming_subject']) ?></div>
								<small class="text-muted">Agenda: <?= e($d['agenda_number']) ?> (<?= e($d['incoming_sender']) ?>)</small>
							<?php else: ?>
								<span class="text-muted small">Disposisi Mandiri</span>
							<?php endif; ?>
						</td>
						<td>
							<div class="text-truncate" style="max-width:260px;" title="<?= e($d['instruction']) ?>">
								<?= e($d['instruction']) ?>
							</div>
						</td>
						<td><?= disposition_priority_badge($d['priority']) ?></td>
						<td class="small">
							<?php if ($d['deadline_date']): ?>
								<span class="<?= $is_overdue ? 'text-danger fw-bold' : '' ?>"><?= e(tgl_indo($d['deadline_date'])) ?></span>
								<?php if ($is_overdue): ?>
									<div class="badge text-bg-danger mt-1">Terlambat</div>
								<?php endif; ?>
							<?php else: ?>
								<span class="text-muted">-</span>
							<?php endif; ?>
						</td>
						<td>
							<div class="d-flex align-items-center gap-1">
								<span class="badge text-bg-light border"><?= (int) $d['completed_recipients'] ?>/<?= (int) $d['total_recipients'] ?> Selesai</span>
							</div>
						</td>
						<td><?= disposition_status_badge($d['status'], $is_overdue) ?></td>
						<td class="text-end">
							<a href="<?= site_url('disposisi/detail/'.$d['id']) ?>" class="btn btn-sm btn-outline-primary" title="Buka Detail">
								<i class="fa-solid fa-eye"></i>
							</a>
						</td>
					</tr>
				<?php endforeach; endif; ?>
				</tbody>
			</table>
		</div>

		<!-- Tampilan Kartu Responsif Khusus Mobile (< 768px) -->
		<div class="list-mobile d-md-none">
		<?php if (empty($dispositions)): ?>
			<div class="text-center py-4 text-muted">
				<i class="fa-solid fa-clipboard-list fa-3x mb-3 text-secondary opacity-50 d-block"></i>
				Belum ada lembar disposisi yang sesuai.
			</div>
		<?php else: foreach ($dispositions as $idx => $d):
			$is_overdue = $d['deadline_date'] && strtotime($d['deadline_date']) < time() && $d['status'] !== 'selesai';
		?>
			<div class="list-mobile-item p-3">
				<div class="d-flex justify-content-between align-items-start gap-2 mb-2">
					<div class="min-w-0">
						<div class="fw-bold letter-number text-navy text-truncate"><?= e($d['disposition_number']) ?></div>
						<small class="text-muted"><?= e(tgl_indo($d['disposition_date'])) ?></small>
					</div>
					<div class="text-end flex-shrink-0">
						<?= disposition_status_badge($d['status'], $is_overdue) ?>
						<div class="mt-1"><?= disposition_priority_badge($d['priority']) ?></div>
					</div>
				</div>

				<div class="fw-semibold text-dark mb-1">
					<i class="fa-solid fa-comment-dots text-warning me-1"></i> <?= e($d['instruction']) ?>
				</div>

				<?php if ($d['incoming_letter_id']): ?>
					<div class="small text-muted mb-2">
						<i class="fa-solid fa-link text-primary me-1"></i> Rujukan: <?= e($d['incoming_subject']) ?>
						<div class="text-muted ms-3">Agenda: <?= e($d['agenda_number']) ?> &middot; <?= e($d['incoming_sender']) ?></div>
					</div>
				<?php endif; ?>

				<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 text-muted small mb-3">
					<div>
						<?php if ($d['deadline_date']): ?>
							<span class="<?= $is_overdue ? 'text-danger fw-bold' : '' ?>">
								<i class="fa-regular fa-clock me-1"></i> Tenggat: <?= e(tgl_indo($d['deadline_date'])) ?>
							</span>
							<?php if ($is_overdue): ?>
								<span class="badge text-bg-danger ms-1">Terlambat</span>
							<?php endif; ?>
						<?php endif; ?>
					</div>
					<div>
						<span class="badge text-bg-light border"><?= (int) $d['completed_recipients'] ?>/<?= (int) $d['total_recipients'] ?> Selesai</span>
					</div>
				</div>

				<div class="pt-2 border-top">
					<a href="<?= site_url('disposisi/detail/'.$d['id']) ?>" class="btn btn-sm btn-outline-primary w-100">
						<i class="fa-solid fa-eye me-1"></i> Buka Detail Disposisi
					</a>
				</div>
			</div>
		<?php endforeach; endif; ?>
		</div>
	</div>
	<?php if ($pagination): ?>
		<div class="card-footer bg-white d-flex align-items-center justify-content-between py-2">
			<div class="small text-muted">Menampilkan <?= count($dispositions) ?> dari total <?= number_format($total) ?> disposisi</div>
			<?= $pagination ?>
		</div>
	<?php endif; ?>
</div>
