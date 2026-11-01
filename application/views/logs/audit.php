<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
	<div>
		<h1 class="h4 fw-bold text-navy mb-1"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Audit Log Aktivitas</h1>
		<p class="text-muted small mb-0">Perekaman seluruh aktivitas login, perubahan data penting, mutasi role, dan tindakan administratif.</p>
	</div>
</div>

<div class="card mb-3">
	<div class="card-body py-2">
		<form method="get" action="<?= site_url('log-aktivitas') ?>" class="row g-2 align-items-center">
			<div class="col-md-7">
				<div class="input-group input-group-sm">
					<span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
					<input type="text" name="q" class="form-control" placeholder="Cari aksi, username, rincian aktivitas..." value="<?= e($filters['q']) ?>">
				</div>
			</div>
			<div class="col-md-3">
				<input type="text" name="action" class="form-control form-control-sm" placeholder="Filter kode aksi..." value="<?= e($filters['action']) ?>">
			</div>
			<div class="col-md-2 d-flex gap-1">
				<button type="submit" class="btn btn-sm btn-navy flex-fill"><i class="fa-solid fa-filter me-1"></i> Filter</button>
				<?php if ( ! empty($filters['q']) || ! empty($filters['action'])): ?>
					<a href="<?= site_url('log-aktivitas') ?>" class="btn btn-sm btn-outline-secondary" title="Reset filter"><i class="fa-solid fa-rotate-left"></i></a>
				<?php endif; ?>
			</div>
		</form>
	</div>
</div>

<div class="card">
	<div class="card-body p-0">
		<div class="table-responsive">
			<table class="table table-hover align-middle mb-0 small">
				<thead class="table-light">
					<tr>
						<th style="width:40px" class="text-center">No</th>
						<th>Waktu</th>
						<th>Pengguna</th>
						<th>Aksi</th>
						<th>Rincian Keterangan</th>
						<th>IP &amp; User Agent</th>
					</tr>
				</thead>
				<tbody>
				<?php if (empty($logs)): ?>
					<tr><td colspan="6" class="text-center py-4 text-muted">Belum ada catatan log aktivitas.</td></tr>
				<?php else: foreach ($logs as $idx => $l): ?>
					<tr>
						<td class="text-center text-muted"><?= $offset + $idx + 1 ?></td>
						<td style="white-space:nowrap;">
							<div class="fw-semibold"><?= e(tgl_indo($l['created_at'])) ?></div>
							<span class="text-muted"><?= e(substr($l['created_at'], 11, 8)) ?></span>
						</td>
						<td><span class="fw-bold text-navy"><?= e($l['username']) ?></span></td>
						<td><code class="fw-semibold text-primary"><?= e($l['action']) ?></code></td>
						<td><?= nl2br(e($l['details'])) ?></td>
						<td class="text-muted text-truncate" style="max-width:200px;" title="<?= e($l['user_agent']) ?>">
							<div><?= e($l['ip_address']) ?></div>
							<small><?= e(substr($l['user_agent'], 0, 40)) ?>...</small>
						</td>
					</tr>
				<?php endforeach; endif; ?>
				</tbody>
			</table>
		</div>
	</div>
	<?php if ($pagination): ?>
		<div class="card-footer bg-white d-flex align-items-center justify-content-between py-2">
			<div class="small text-muted">Menampilkan <?= count($logs) ?> dari total <?= number_format($total) ?> entri log</div>
			<?= $pagination ?>
		</div>
	<?php endif; ?>
</div>
