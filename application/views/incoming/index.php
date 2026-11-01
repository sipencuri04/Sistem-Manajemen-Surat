<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
	<div>
		<h1 class="h4 fw-bold text-navy mb-1"><i class="fa-solid fa-inbox me-2 text-primary"></i>Surat Masuk</h1>
		<p class="text-muted small mb-0">Pencatatan surat masuk, penerusan ke Kepala Sekolah, dan disposisi.</p>
	</div>
	<?php if (has_role(array('tu', 'superuser', 'admin'))): ?>
		<a href="<?= site_url('surat-masuk/catat') ?>" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i> Catat Surat Masuk</a>
	<?php endif; ?>
</div>

<div class="row g-2 mb-3">
	<div class="col-sm-6 col-md-3">
		<div class="stat-card">
			<div class="stat-icon bg-primary-subtle text-primary"><i class="fa-solid fa-inbox"></i></div>
			<div>
				<div class="stat-label">Total Surat Masuk</div>
				<div class="stat-value"><?= number_format($counts['semua']) ?></div>
			</div>
		</div>
	</div>
	<div class="col-sm-6 col-md-3">
		<div class="stat-card">
			<div class="stat-icon bg-warning-subtle text-warning"><i class="fa-solid fa-envelope"></i></div>
			<div>
				<div class="stat-label">Baru / Diteruskan</div>
				<div class="stat-value"><?= number_format($counts['baru'] + $counts['diteruskan']) ?></div>
			</div>
		</div>
	</div>
	<div class="col-sm-6 col-md-3">
		<div class="stat-card">
			<div class="stat-icon bg-info-subtle text-info"><i class="fa-solid fa-share-nodes"></i></div>
			<div>
				<div class="stat-label">Sudah Didisposisi</div>
				<div class="stat-value"><?= number_format($counts['didisposisi']) ?></div>
			</div>
		</div>
	</div>
	<div class="col-sm-6 col-md-3">
		<div class="stat-card">
			<div class="stat-icon bg-success-subtle text-success"><i class="fa-solid fa-check-double"></i></div>
			<div>
				<div class="stat-label">Selesai Tindak Lanjut</div>
				<div class="stat-value"><?= number_format($counts['selesai']) ?></div>
			</div>
		</div>
	</div>
</div>

<div class="card mb-3">
	<div class="card-body py-2">
		<form method="get" action="<?= site_url('surat-masuk') ?>" class="row g-2 align-items-center">
			<div class="col-md-4">
				<div class="input-group input-group-sm">
					<span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
					<input type="text" name="q" class="form-control" placeholder="Cari no agenda, no surat, asal, perihal..." value="<?= e($filters['q']) ?>">
				</div>
			</div>
			<div class="col-md-3">
				<select name="status" class="form-select form-select-sm">
					<option value="">-- Semua Status --</option>
					<?php foreach (Incoming_letter_model::$statuses as $k => $v): ?>
						<option value="<?= $k ?>" <?= $filters['status'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="col-md-3">
				<div class="input-group input-group-sm">
					<input type="date" name="from" class="form-control" value="<?= e($filters['from']) ?>" title="Dari tanggal terima">
					<span class="input-group-text">-</span>
					<input type="date" name="to" class="form-control" value="<?= e($filters['to']) ?>" title="Sampai tanggal">
				</div>
			</div>
			<div class="col-md-2 d-flex gap-1">
				<button type="submit" class="btn btn-sm btn-navy flex-fill"><i class="fa-solid fa-filter me-1"></i> Filter</button>
				<?php if ( ! empty($filters['q']) || ! empty($filters['status']) || ! empty($filters['from']) || ! empty($filters['to'])): ?>
					<a href="<?= site_url('surat-masuk') ?>" class="btn btn-sm btn-outline-secondary" title="Reset filter"><i class="fa-solid fa-rotate-left"></i></a>
				<?php endif; ?>
			</div>
		</form>
	</div>
</div>

<div class="card">
	<div class="card-body p-0">
		<div class="table-responsive">
			<table class="table table-hover align-middle mb-0">
				<thead class="table-light">
					<tr>
						<th style="width:50px" class="text-center">No</th>
						<th>No. Agenda &amp; Surat</th>
						<th>Tanggal Terima</th>
						<th>Pengirim / Asal</th>
						<th>Perihal</th>
						<th>Berkas</th>
						<th>Status</th>
						<th style="width:130px" class="text-end">Aksi</th>
					</tr>
				</thead>
				<tbody>
				<?php if (empty($letters)): ?>
					<tr>
						<td colspan="8" class="text-center py-4 text-muted">
							<i class="fa-solid fa-inbox fa-3x mb-3 text-secondary opacity-50 d-block"></i>
							Belum ada surat masuk yang cocok dengan filter pencarian.
						</td>
					</tr>
				<?php else: foreach ($letters as $idx => $l): ?>
					<tr>
						<td class="text-center text-muted small"><?= $offset + $idx + 1 ?></td>
						<td>
							<div class="fw-bold letter-number text-navy"><?= e($l['agenda_number']) ?></div>
							<small class="text-muted">Asal: <?= e($l['letter_number']) ?></small>
						</td>
						<td class="small">
							<div><?= e(tgl_indo($l['received_date'])) ?></div>
							<small class="text-muted">Surat: <?= e(tgl_indo($l['letter_date'])) ?></small>
						</td>
						<td>
							<span class="fw-semibold"><?= e($l['sender']) ?></span>
							<?php if ( ! empty($l['is_external'])): ?>
								<div class="mt-1">
									<span class="badge bg-primary-subtle text-primary border border-primary-subtle small" title="Dikirim online dari portal instansi luar">
										<i class="fa-solid fa-globe me-1"></i>Kirim Online
									</span>
									<?php if ( ! empty($l['tracking_code'])): ?>
										<small class="text-muted d-block font-monospace">Resi: <?= e($l['tracking_code']) ?></small>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						</td>
						<td>
							<div class="text-truncate" style="max-width:280px;" title="<?= e($l['subject']) ?>">
								<?= e($l['subject']) ?>
							</div>
							<?php if ($l['disposition_count'] > 0): ?>
								<span class="badge text-bg-info small mt-1"><i class="fa-solid fa-share-nodes me-1"></i><?= (int) $l['disposition_count'] ?> Disposisi</span>
							<?php endif; ?>
						</td>
						<td>
							<?php if ($l['file_path']): ?>
								<a href="<?= site_url('surat-masuk/berkas/'.$l['id']) ?>" target="_blank" class="btn btn-xs btn-outline-danger" title="Buka berkas scan">
									<i class="fa-solid fa-file-pdf"></i> PDF
								</a>
							<?php else: ?>
								<span class="text-muted small">-</span>
							<?php endif; ?>
						</td>
						<td><?= incoming_status_badge($l['status']) ?></td>
						<td class="text-end">
							<div class="btn-group btn-group-sm">
								<a href="<?= site_url('surat-masuk/detail/'.$l['id']) ?>" class="btn btn-outline-primary" title="Lihat detail">
									<i class="fa-solid fa-eye"></i>
								</a>
								<?php if (is_kepala_sekolah()): ?>
									<a href="<?= site_url('disposisi/buat/'.$l['id']) ?>" class="btn btn-warning" title="Buat Lembar Disposisi">
										<i class="fa-solid fa-share-nodes"></i>
									</a>
								<?php endif; ?>
							</div>
						</td>
					</tr>
				<?php endforeach; endif; ?>
				</tbody>
			</table>
		</div>
	</div>
	<?php if ($pagination): ?>
		<div class="card-footer bg-white d-flex align-items-center justify-content-between py-2">
			<div class="small text-muted">Menampilkan <?= count($letters) ?> dari total <?= number_format($total) ?> surat masuk</div>
			<?= $pagination ?>
		</div>
	<?php endif; ?>
</div>
