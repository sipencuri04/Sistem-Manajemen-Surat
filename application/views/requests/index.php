<?php defined('BASEPATH') OR exit('No direct script access allowed');
$labels = Letter_request_model::$statuses;
$colors = array('pending' => 'warning', 'approved' => 'success', 'taken' => 'secondary', 'rejected' => 'danger');
$tabs = array('pending' => 'Menunggu', 'approved' => 'Siap Diambil', 'taken' => 'Sudah Diambil', 'rejected' => 'Ditolak', 'semua' => 'Semua');
?>
<div class="alert alert-light border d-flex flex-wrap align-items-center gap-2 share-box">
	<i class="fa-solid fa-link text-primary"></i>
	<span>Bagikan alamat formulir permohonan kepada siswa:</span>
	<code class="user-select-all" id="publicLink"><?= e(site_url('permohonan')) ?></code>
	<button type="button" class="btn btn-sm btn-outline-primary" data-copy="#publicLink"><i class="fa-regular fa-copy me-1"></i> Salin</button>
	<a href="<?= site_url('permohonan') ?>" target="_blank" rel="noopener" class="btn btn-sm btn-light">Buka <i class="fa-solid fa-up-right-from-square ms-1"></i></a>
</div>

<div class="card">
	<div class="card-header d-flex flex-wrap gap-2 align-items-center">
		<ul class="nav nav-pills small me-auto status-tabs">
			<?php foreach ($tabs as $key => $label): ?>
				<li class="nav-item">
					<a class="nav-link py-1 px-3 <?= $status === $key ? 'active' : '' ?>" href="<?= site_url('permohonan-masuk?status='.$key) ?>">
						<?= e($label) ?><?php if (isset($counts[$key]) && $counts[$key] > 0): ?> <span class="badge rounded-pill <?= $status === $key ? 'text-bg-light' : 'text-bg-'.$colors[$key] ?>"><?= (int) $counts[$key] ?></span><?php endif; ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
		<form method="get" action="<?= site_url('permohonan-masuk') ?>" class="d-flex gap-2 req-search">
			<input type="hidden" name="status" value="<?= e($status) ?>">
			<input type="search" name="q" class="form-control form-control-sm" value="<?= e($filters['q']) ?>" placeholder="Nama, kode, atau no. HP" maxlength="100" aria-label="Cari permohonan">
			<button class="btn btn-sm btn-navy" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
		</form>
	</div>

	<?php if (empty($requests)): ?>
		<div class="empty-state"><i class="fa-regular fa-envelope-open d-block"></i>
			<p class="mb-0"><?= $status === 'pending' ? 'Tidak ada permohonan yang menunggu. Semua sudah diproses.' : 'Tidak ada permohonan.' ?></p></div>
	<?php else: ?>
		<div class="table-responsive d-none d-md-block">
			<table class="table table-hover align-middle mb-0">
				<thead><tr><th>No</th><th>Kode</th><th>Diajukan</th><th>Nama</th><th>Kelas</th><th>Jenis</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
				<tbody>
				<?php foreach ($requests as $i => $r): ?>
					<tr>
						<td class="text-muted"><?= $offset + $i + 1 ?></td>
						<td class="letter-number"><?= e($r['tracking_code']) ?></td>
						<td class="text-nowrap small"><?= e(tgl_indo($r['created_at'])) ?><br><span class="text-muted"><?= e(substr($r['created_at'], 11, 5)) ?></span></td>
						<td>
							<?= e($r['requester_name']) ?><br>
							<a class="small text-muted text-decoration-none me-2" href="https://wa.me/62<?= e(substr($r['requester_phone'], 1)) ?>" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp text-success"></i> <?= e($r['requester_phone']) ?></a>
							<?php if (!empty($r['requester_email'])): ?>
								<span class="small text-muted" title="<?= e($r['requester_email']) ?>"><i class="fa-solid fa-envelope text-primary"></i> <?= e($r['requester_email']) ?></span>
							<?php endif; ?>
						</td>
						<td><?= e($r['requester_class'] ?: '-') ?></td>
						<td><span class="badge text-bg-light border" title="<?= e($r['type_name']) ?>"><?= e($r['type_code']) ?></span></td>
						<td><span class="badge rounded-pill text-bg-<?= $colors[$r['status']] ?>"><?= e($labels[$r['status']]) ?></span>
							<?php if ($r['letter_number']): ?><div class="small letter-number mt-1"><?= e($r['letter_number']) ?></div><?php endif; ?></td>
						<td class="text-end text-nowrap">
							<a class="btn btn-sm <?= $r['status'] === 'pending' ? 'btn-primary' : 'btn-light' ?>" href="<?= site_url('permohonan-masuk/'.(int) $r['id']) ?>">
								<?= $r['status'] === 'pending' ? '<i class="fa-solid fa-list-check me-1"></i> Periksa' : '<i class="fa-solid fa-eye"></i>' ?>
							</a>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<!-- Tampilan kartu untuk ponsel -->
		<div class="list-mobile d-md-none">
		<?php foreach ($requests as $r): ?>
			<a class="list-mobile-item" href="<?= site_url('permohonan-masuk/'.(int) $r['id']) ?>">
				<div class="d-flex justify-content-between align-items-start gap-2">
					<div class="min-w-0">
						<div class="fw-semibold text-truncate"><?= e($r['requester_name']) ?></div>
						<div class="small text-muted">
							<?= e($r['requester_class'] ?: '-') ?> &middot; <span class="badge text-bg-light border"><?= e($r['type_code']) ?></span>
							<?php if (!empty($r['requester_email'])): ?> &middot; <i class="fa-solid fa-envelope text-primary"></i> <?= e($r['requester_email']) ?><?php endif; ?>
						</div>
					</div>
					<span class="badge rounded-pill text-bg-<?= $colors[$r['status']] ?> flex-shrink-0"><?= e($labels[$r['status']]) ?></span>
				</div>
				<div class="d-flex justify-content-between align-items-center mt-2 small">
					<span class="letter-number"><?= e($r['tracking_code']) ?></span>
					<span class="text-muted"><?= e(tgl_indo($r['created_at'])) ?> <?= e(substr($r['created_at'], 11, 5)) ?></span>
				</div>
				<?php if ($r['letter_number']): ?><div class="small letter-number mt-1"><i class="fa-solid fa-envelope-open-text me-1"></i><?= e($r['letter_number']) ?></div><?php endif; ?>
			</a>
		<?php endforeach; ?>
		</div>
		<div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2 border-top">
			<small class="text-muted">Menampilkan <?= $offset + 1 ?>&ndash;<?= $offset + count($requests) ?> dari <?= (int) $total ?> permohonan</small>
			<?= $pagination ?>
		</div>
	<?php endif; ?>
</div>
