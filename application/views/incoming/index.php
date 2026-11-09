<?php defined('BASEPATH') OR exit('No direct script access allowed');
$labels = Incoming_letter_model::$statuses;
$colors = array(
	'baru'        => 'warning',
	'diteruskan'  => 'info',
	'didisposisi' => 'primary',
	'selesai'     => 'success',
	'diarsipkan'  => 'secondary',
	'semua'       => 'secondary',
);
$tabs = array(
	'baru'        => 'Baru',
	'diteruskan'  => 'Diteruskan ke Kepsek',
	'didisposisi' => 'Didisposisi',
	'selesai'     => 'Selesai',
	'diarsipkan'  => 'Arsip',
	'semua'       => 'Semua',
);
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
	<div>
		<h1 class="h4 fw-bold text-navy mb-1"><i class="fa-solid fa-inbox me-2 text-primary"></i>Surat Masuk</h1>
		<p class="text-muted small mb-0">Pencatatan surat dinas masuk, penerusan ke Kepala Sekolah, dan lembar disposisi.</p>
	</div>
	<?php if (has_role(array('tu', 'superuser', 'admin'))): ?>
		<a href="<?= site_url('surat-masuk/catat') ?>" class="btn btn-primary shadow-sm"><i class="fa-solid fa-plus me-1"></i> Catat Surat Masuk</a>
	<?php endif; ?>
</div>

<div class="alert alert-light border d-flex flex-wrap align-items-center gap-2 share-box mb-3">
	<i class="fa-solid fa-link text-primary"></i>
	<span>Alamat portal kirim surat resmi untuk instansi luar (Dinas/Perusahaan/Tamu):</span>
	<code class="user-select-all" id="publicLinkInstansi"><?= e(site_url('kirim-surat')) ?></code>
	<button type="button" class="btn btn-sm btn-outline-primary" data-copy="#publicLinkInstansi"><i class="fa-regular fa-copy me-1"></i> Salin</button>
	<a href="<?= site_url('kirim-surat') ?>" target="_blank" rel="noopener" class="btn btn-sm btn-light">Buka <i class="fa-solid fa-up-right-from-square ms-1"></i></a>
</div>

<div class="row g-3 mb-3">
	<div class="col-6 col-md-3">
		<a href="<?= site_url('surat-masuk?status=semua') ?>" class="text-decoration-none">
			<div class="card stat-card shadow-sm h-100 <?= $status === 'semua' ? 'border-primary' : '' ?>">
				<div class="stat-icon bg-primary-subtle text-primary"><i class="fa-solid fa-inbox"></i></div>
				<div>
					<div class="stat-label">Total Surat Masuk</div>
					<div class="stat-value text-navy"><?= number_format($counts['semua']) ?></div>
				</div>
			</div>
		</a>
	</div>
	<div class="col-6 col-md-3">
		<a href="<?= site_url('surat-masuk?status=baru') ?>" class="text-decoration-none">
			<div class="card stat-card shadow-sm h-100 <?= $status === 'baru' ? 'border-warning' : '' ?>">
				<div class="stat-icon bg-warning-subtle text-warning"><i class="fa-solid fa-envelope"></i></div>
				<div>
					<div class="stat-label">Baru Diterima</div>
					<div class="stat-value text-warning"><?= number_format($counts['baru']) ?></div>
				</div>
			</div>
		</a>
	</div>
	<div class="col-6 col-md-3">
		<a href="<?= site_url('surat-masuk?status=diteruskan') ?>" class="text-decoration-none">
			<div class="card stat-card shadow-sm h-100 <?= $status === 'diteruskan' ? 'border-info' : '' ?>">
				<div class="stat-icon bg-info-subtle text-info"><i class="fa-solid fa-paper-plane"></i></div>
				<div>
					<div class="stat-label">Diteruskan ke Kepsek</div>
					<div class="stat-value text-info"><?= number_format($counts['diteruskan']) ?></div>
				</div>
			</div>
		</a>
	</div>
	<div class="col-6 col-md-3">
		<a href="<?= site_url('surat-masuk?status=didisposisi') ?>" class="text-decoration-none">
			<div class="card stat-card shadow-sm h-100 <?= $status === 'didisposisi' ? 'border-success' : '' ?>">
				<div class="stat-icon bg-success-subtle text-success"><i class="fa-solid fa-share-nodes"></i></div>
				<div>
					<div class="stat-label">Sudah Didisposisi</div>
					<div class="stat-value text-success"><?= number_format($counts['didisposisi']) ?></div>
				</div>
			</div>
		</a>
	</div>
</div>

<div class="card shadow-sm">
	<div class="card-header d-flex flex-wrap gap-2 align-items-center">
		<ul class="nav nav-pills small me-auto status-tabs">
			<?php foreach ($tabs as $key => $label): ?>
				<li class="nav-item">
					<a class="nav-link py-1 px-3 <?= $status === $key ? 'active' : '' ?>" href="<?= site_url('surat-masuk?status='.$key.(!empty($filters['from']) ? '&from='.urlencode($filters['from']) : '').(!empty($filters['to']) ? '&to='.urlencode($filters['to']) : '').(!empty($filters['q']) ? '&q='.urlencode($filters['q']) : '')) ?>">
						<?= e($label) ?><?php if (isset($counts[$key]) && $counts[$key] > 0): ?> <span class="badge rounded-pill <?= $status === $key ? 'text-bg-light' : 'text-bg-'.$colors[$key] ?>"><?= (int) $counts[$key] ?></span><?php endif; ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
		<form method="get" action="<?= site_url('surat-masuk') ?>" class="d-flex gap-2 req-search align-items-center">
			<input type="hidden" name="status" value="<?= e($status) ?>">
			<?php if (!empty($filters['from'])): ?><input type="hidden" name="from" value="<?= e($filters['from']) ?>"><?php endif; ?>
			<?php if (!empty($filters['to'])): ?><input type="hidden" name="to" value="<?= e($filters['to']) ?>"><?php endif; ?>
			<input type="search" name="q" class="form-control form-control-sm" value="<?= e($filters['q']) ?>" placeholder="No. agenda, asal, perihal..." maxlength="100" aria-label="Cari surat masuk">
			<button class="btn btn-sm btn-navy" type="submit" title="Cari"><i class="fa-solid fa-magnifying-glass"></i></button>
			<button type="button" class="btn btn-sm <?= (!empty($filters['from']) || !empty($filters['to'])) ? 'btn-primary' : 'btn-outline-secondary' ?>" data-bs-toggle="collapse" data-bs-target="#dateFilterCollapse" title="Filter Rentang Tanggal"><i class="fa-regular fa-calendar-days"></i></button>
		</form>
	</div>

	<!-- Filter Rentang Tanggal (Collapsible) -->
	<div class="collapse <?= (!empty($filters['from']) || !empty($filters['to'])) ? 'show' : '' ?> border-bottom bg-light px-3 py-2" id="dateFilterCollapse">
		<form method="get" action="<?= site_url('surat-masuk') ?>" class="row g-2 align-items-center">
			<input type="hidden" name="status" value="<?= e($status) ?>">
			<input type="hidden" name="q" value="<?= e($filters['q']) ?>">
			<div class="col-auto small text-muted fw-semibold">
				<i class="fa-regular fa-calendar me-1 text-primary"></i> Rentang Tanggal Terima:
			</div>
			<div class="col-auto">
				<input type="date" name="from" class="form-control form-control-sm" value="<?= e($filters['from']) ?>" title="Dari tanggal">
			</div>
			<div class="col-auto text-muted">&ndash;</div>
			<div class="col-auto">
				<input type="date" name="to" class="form-control form-control-sm" value="<?= e($filters['to']) ?>" title="Sampai tanggal">
			</div>
			<div class="col-auto">
				<button type="submit" class="btn btn-sm btn-primary"><i class="fa-solid fa-filter me-1"></i> Terapkan</button>
				<?php if (!empty($filters['from']) || !empty($filters['to'])): ?>
					<a href="<?= site_url('surat-masuk?status='.$status.(!empty($filters['q']) ? '&q='.urlencode($filters['q']) : '')) ?>" class="btn btn-sm btn-outline-secondary" title="Reset filter tanggal"><i class="fa-solid fa-rotate-left"></i></a>
				<?php endif; ?>
			</div>
		</form>
	</div>

	<div class="card-body p-0">
		<div class="table-responsive d-none d-md-block">
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
						<td colspan="8" class="text-center py-5 text-muted">
							<i class="fa-solid fa-inbox fa-3x mb-3 text-secondary opacity-50 d-block"></i>
							<div>Belum ada surat masuk pada kategori ini.</div>
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

		<!-- Tampilan Kartu Responsif Khusus Mobile (< 768px) -->
		<div class="list-mobile d-md-none">
		<?php if (empty($letters)): ?>
			<div class="text-center py-4 text-muted">
				<i class="fa-solid fa-inbox fa-3x mb-3 text-secondary opacity-50 d-block"></i>
				Belum ada surat masuk pada kategori ini.
			</div>
		<?php else: foreach ($letters as $idx => $l): ?>
			<div class="list-mobile-item p-3">
				<div class="d-flex justify-content-between align-items-start gap-2 mb-2">
					<div class="min-w-0">
						<div class="fw-bold letter-number text-navy text-truncate"><?= e($l['agenda_number']) ?></div>
						<small class="text-muted">Asal: <?= e($l['letter_number']) ?></small>
					</div>
					<div class="text-end flex-shrink-0">
						<?= incoming_status_badge($l['status']) ?>
					</div>
				</div>

				<div class="fw-semibold text-dark mb-1"><?= e($l['subject']) ?></div>

				<div class="small text-muted mb-2">
					<i class="fa-solid fa-building me-1"></i> Asal: <strong class="text-dark"><?= e($l['sender']) ?></strong>
					<?php if ( ! empty($l['is_external'])): ?>
						<span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1" style="font-size:0.68rem;">
							<i class="fa-solid fa-globe me-1"></i>Online
						</span>
					<?php endif; ?>
				</div>

				<div class="d-flex flex-wrap align-items-center column-gap-3 row-gap-1 text-muted small mb-3">
					<span class="d-inline-flex align-items-center"><i class="fa-regular fa-calendar-check me-1"></i>Terima: <?= e(tgl_indo($l['received_date'])) ?></span>
					<span class="d-inline-flex align-items-center"><i class="fa-regular fa-calendar me-1"></i>Surat: <?= e(tgl_indo($l['letter_date'])) ?></span>
					<?php if ($l['disposition_count'] > 0): ?>
						<span class="badge text-bg-info text-white"><i class="fa-solid fa-share-nodes me-1"></i><?= (int) $l['disposition_count'] ?> Disposisi</span>
					<?php endif; ?>
				</div>

				<div class="d-flex align-items-center gap-2 pt-2 border-top">
					<a href="<?= site_url('surat-masuk/detail/'.$l['id']) ?>" class="btn btn-sm btn-outline-primary flex-fill">
						<i class="fa-solid fa-eye me-1"></i> Detail
					</a>
					<?php if ($l['file_path']): ?>
						<a href="<?= site_url('surat-masuk/berkas/'.$l['id']) ?>" target="_blank" class="btn btn-sm btn-outline-danger" title="Buka Scan PDF">
							<i class="fa-solid fa-file-pdf me-1"></i> PDF
						</a>
					<?php endif; ?>
					<?php if (is_kepala_sekolah()): ?>
						<a href="<?= site_url('disposisi/buat/'.$l['id']) ?>" class="btn btn-sm btn-warning" title="Buat Lembar Disposisi">
							<i class="fa-solid fa-share-nodes me-1"></i> Disposisi
						</a>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; endif; ?>
		</div>
	</div>
	<?php if ($pagination): ?>
		<div class="card-footer bg-white d-flex flex-wrap align-items-center justify-content-between gap-2 py-2 border-top">
			<div class="small text-muted">Menampilkan <?= count($letters) ?> dari total <?= number_format($total) ?> surat masuk</div>
			<?= $pagination ?>
		</div>
	<?php endif; ?>
</div>
