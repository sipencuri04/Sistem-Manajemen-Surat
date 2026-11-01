<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php if ( ! $settings_ready && in_array($role, array('tu', 'superuser', 'admin'), TRUE)): ?>
<div class="alert alert-warning d-flex align-items-center gap-3">
	<i class="fa-solid fa-school-circle-exclamation fa-lg"></i>
	<div class="flex-grow-1">Identitas sekolah (nama sekolah, kota, dan nama kepala sekolah) belum lengkap. Data ini dipakai pada kop dan tanda tangan surat.</div>
	<a href="<?= site_url('pengaturan') ?>" class="btn btn-sm btn-warning">Lengkapi</a>
</div>
<?php endif; ?>

<!-- Notifikasi & Peringatan Tindakan Berdasarkan Role -->
<?php if ($role === 'kepala_sekolah' || in_array($role, array('superuser', 'admin'), TRUE)): ?>
	<?php if ( ! empty($incoming_need_disposition)): ?>
		<div class="alert alert-warning d-flex align-items-center gap-3">
			<i class="fa-solid fa-inbox fa-lg text-warning"></i>
			<div class="flex-grow-1">Ada <strong><?= (int) $incoming_need_disposition ?> surat masuk</strong> yang telah diteruskan oleh TU dan siap untuk Anda disposisikan.</div>
			<a href="<?= site_url('surat-masuk?status=diteruskan') ?>" class="btn btn-sm btn-warning">Periksa &amp; Disposisikan</a>
		</div>
	<?php endif; ?>

	<?php if ( ! empty($pending_approvals_total)): ?>
		<div class="alert alert-info d-flex align-items-center gap-3">
			<i class="fa-solid fa-signature fa-lg text-primary"></i>
			<div class="flex-grow-1">Ada <strong><?= (int) $pending_approvals_total ?> surat keluar</strong> yang menunggu persetujuan dan pengesahan Anda.</div>
			<a href="<?= site_url('persetujuan-surat') ?>" class="btn btn-sm btn-primary">Lihat Persetujuan</a>
		</div>
	<?php endif; ?>
<?php endif; ?>

<?php if ( ! empty($pending_requests) && in_array($role, array('tu', 'superuser', 'admin'), TRUE)): ?>
<div class="alert alert-info d-flex align-items-center gap-3">
	<i class="fa-solid fa-inbox fa-lg"></i>
	<div class="flex-grow-1">Ada <strong><?= (int) $pending_requests ?> permohonan surat dari siswa</strong> yang menunggu diperiksa.</div>
	<a href="<?= site_url('permohonan-masuk') ?>" class="btn btn-sm btn-primary">Periksa</a>
</div>
<?php endif; ?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
	<div>
		<div class="d-flex align-items-center gap-2">
			<h2 class="h4 fw-bold text-navy mb-0">Selamat datang, <?= e($current_user['full_name']) ?></h2>
			<?= role_badge($current_user['role']) ?>
		</div>
		<p class="text-muted mb-0 small"><?= e($current_user['position'] ?: role_title($current_user['role'])) ?> &middot; <?= tgl_indo(date('Y-m-d'), TRUE) ?></p>
	</div>
	<div class="d-flex gap-2">
		<?php if ($role === 'pegawai'): ?>
			<a href="<?= site_url('disposisi-saya') ?>" class="btn btn-primary"><i class="fa-solid fa-clipboard-check me-1"></i> Buka Disposisi Saya</a>
		<?php elseif ($role === 'kepala_sekolah'): ?>
			<a href="<?= site_url('disposisi/buat') ?>" class="btn btn-warning"><i class="fa-solid fa-share-nodes me-1"></i> Buat Disposisi</a>
		<?php elseif (in_array($role, array('tu', 'superuser', 'admin'), TRUE)): ?>
			<a href="<?= site_url('surat/buat') ?>" class="btn btn-primary"><i class="fa-solid fa-file-circle-plus me-1"></i> Buat Surat Baru</a>
			<a href="<?= site_url('surat-masuk/catat') ?>" class="btn btn-outline-primary"><i class="fa-solid fa-inbox me-1"></i> Catat Surat Masuk</a>
		<?php endif; ?>
	</div>
</div>

<!-- ========================================================================= -->
<!-- DASHBOARD KHUSUS PEGAWAI / GURU -->
<!-- ========================================================================= -->
<?php if ($role === 'pegawai'): ?>
	<div class="row g-3 mb-4">
		<div class="col-sm-4">
			<div class="card stat-card">
				<div class="stat-icon blue"><i class="fa-solid fa-clipboard-list"></i></div>
				<div>
					<div class="stat-value"><?= number_format($my_dispositions_count) ?></div>
					<div class="stat-label">Total Disposisi Diterima</div>
				</div>
			</div>
		</div>
		<div class="col-sm-4">
			<div class="card stat-card">
				<div class="stat-icon amber"><i class="fa-solid fa-clock"></i></div>
				<div>
					<div class="stat-value"><?= number_format($my_pending_count) ?></div>
					<div class="stat-label">Perlu Tindak Lanjut</div>
				</div>
			</div>
		</div>
		<div class="col-sm-4">
			<div class="card stat-card">
				<div class="stat-icon green"><i class="fa-solid fa-circle-check"></i></div>
				<div>
					<div class="stat-value"><?= number_format($my_completed_count) ?></div>
					<div class="stat-label">Tugas Selesai</div>
				</div>
			</div>
		</div>
	</div>

	<div class="card mb-4">
		<div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
			<h3 class="card-title h6 mb-0 text-navy"><i class="fa-solid fa-clipboard-check me-2 text-primary"></i>Instruksi Disposisi Terbaru untuk Anda</h3>
			<a href="<?= site_url('disposisi-saya') ?>" class="btn btn-sm btn-outline-primary">Buka Semua</a>
		</div>
		<div class="card-body p-0">
			<?php if (empty($my_recent_dispositions)): ?>
				<div class="p-5 text-center text-muted">
					<i class="fa-solid fa-clipboard-check fa-3x text-secondary opacity-50 mb-2 d-block"></i>
					Tidak ada instruksi disposisi aktif saat ini.
				</div>
			<?php else: ?>
				<div class="table-responsive">
					<table class="table table-hover align-middle mb-0">
						<thead class="table-light">
							<tr>
								<th>No. Disposisi</th>
								<th>Surat Rujukan</th>
								<th>Instruksi Kepala Sekolah</th>
								<th>Prioritas</th>
								<th>Tenggat</th>
								<th>Progres</th>
								<th style="width:100px" class="text-end">Aksi</th>
							</tr>
						</thead>
						<tbody>
						<?php foreach ($my_recent_dispositions as $md):
							$over = $md['deadline_date'] && strtotime($md['deadline_date']) < time() && $md['status'] !== 'selesai';
						?>
							<tr>
								<td class="letter-number fw-bold"><?= e($md['disposition_number']) ?></td>
								<td><?= e($md['incoming_subject'] ?: 'Disposisi Internal') ?></td>
								<td><div class="text-truncate" style="max-width:240px;" title="<?= e($md['instruction']) ?>"><?= e($md['instruction']) ?></div></td>
								<td><?= disposition_priority_badge($md['priority']) ?></td>
								<td class="small <?= $over ? 'text-danger fw-bold' : '' ?>">
									<?= $md['deadline_date'] ? e(tgl_indo($md['deadline_date'])) : '-' ?>
									<?php if ($over): ?><div class="badge text-bg-danger">Terlambat</div><?php endif; ?>
								</td>
								<td>
									<div class="d-flex align-items-center gap-2">
										<div class="progress flex-fill" style="width:60px; height:6px;">
											<div class="progress-bar bg-<?= $md['status'] === 'selesai' ? 'success' : 'primary' ?>" style="width:<?= (int) $md['progress_percent'] ?>%"></div>
										</div>
										<span class="small fw-bold"><?= (int) $md['progress_percent'] ?>%</span>
									</div>
								</td>
								<td class="text-end">
									<a href="<?= site_url('disposisi/detail/'.$md['disposition_id']) ?>" class="btn btn-xs btn-outline-primary">
										<i class="fa-solid fa-eye me-1"></i> Rincian
									</a>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
		</div>
	</div>

<!-- ========================================================================= -->
<!-- DASHBOARD KEPALA SEKOLAH, TU, SUPERUSER -->
<!-- ========================================================================= -->
<?php else: ?>
	<div class="row g-3 mb-3">
		<div class="col-6 col-xl-3">
			<div class="card stat-card">
				<div class="stat-icon navy"><i class="fa-solid fa-envelopes-bulk"></i></div>
				<div><div class="stat-value"><?= number_format($total, 0, ',', '.') ?></div><div class="stat-label">Surat keluar</div></div>
			</div>
		</div>
		<div class="col-6 col-xl-3">
			<div class="card stat-card">
				<div class="stat-icon blue"><i class="fa-solid fa-inbox"></i></div>
				<div><div class="stat-value"><?= number_format($total_incoming, 0, ',', '.') ?></div><div class="stat-label">Surat masuk</div></div>
			</div>
		</div>
		<div class="col-6 col-xl-3">
			<div class="card stat-card">
				<div class="stat-icon green"><i class="fa-solid fa-share-nodes"></i></div>
				<div><div class="stat-value"><?= number_format($total_disposition, 0, ',', '.') ?></div><div class="stat-label">Lembar disposisi</div></div>
			</div>
		</div>
		<div class="col-6 col-xl-3">
			<div class="card stat-card">
				<div class="stat-icon amber"><i class="fa-solid fa-calendar-day"></i></div>
				<div><div class="stat-value"><?= number_format($this_month, 0, ',', '.') ?></div><div class="stat-label">Surat bulan ini</div></div>
			</div>
		</div>
	</div>

	<div class="row g-3 mb-3">
		<div class="col-lg-8">
			<div class="card h-100">
				<div class="card-header d-flex align-items-center justify-content-between">
					<h3 class="card-title h6 mb-0"><i class="fa-solid fa-chart-column me-1 text-primary"></i> Jumlah Surat per Bulan</h3>
					<form method="get" action="<?= site_url('dashboard') ?>">
						<select name="tahun" class="form-select form-select-sm" data-autosubmit aria-label="Tahun">
							<?php foreach ($years as $y): ?>
								<option value="<?= (int) $y ?>" <?= $y == $chart_year ? 'selected' : '' ?>><?= (int) $y ?></option>
							<?php endforeach; ?>
						</select>
					</form>
				</div>
				<div class="card-body">
					<div style="height:280px"><canvas id="monthlyChart" data-chart="<?= e(json_encode($chart)) ?>"></canvas></div>
				</div>
			</div>
		</div>

		<div class="col-lg-4">
			<div class="card h-100">
				<div class="card-header"><h3 class="card-title h6 mb-0"><i class="fa-solid fa-layer-group me-1 text-primary"></i> Surat per Jenis</h3></div>
				<div class="card-body p-0" style="max-height:330px; overflow:auto">
					<?php $max = 0; foreach ($by_type as $t) { $max = max($max, (int) $t['total']); } ?>
					<ul class="list-group list-group-flush">
						<?php foreach ($by_type as $t): ?>
							<li class="list-group-item">
								<div class="d-flex justify-content-between small mb-1">
									<a class="text-decoration-none text-body text-truncate me-2" href="<?= site_url('arsip?type='.(int) $t['id']) ?>"><?= e($t['name']) ?></a>
									<strong><?= (int) $t['total'] ?></strong>
								</div>
								<div class="progress" style="height:5px">
									<div class="progress-bar" style="width:<?= $max ? round($t['total'] / $max * 100) : 0 ?>%"></div>
								</div>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
		</div>
	</div>

	<div class="card">
		<div class="card-header d-flex align-items-center justify-content-between">
			<h3 class="card-title h6 mb-0"><i class="fa-solid fa-clock-rotate-left me-1 text-primary"></i> Surat Keluar Terbaru</h3>
			<a href="<?= site_url('surat') ?>" class="btn btn-sm btn-outline-primary">Lihat semua</a>
		</div>
		<?php if (empty($recent)): ?>
			<div class="empty-state">
				<i class="fa-regular fa-envelope d-block"></i>
				<p class="mb-3">Belum ada surat yang dibuat.</p>
				<a href="<?= site_url('surat/buat') ?>" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus me-1"></i> Buat surat pertama</a>
			</div>
		<?php else: ?>
			<div class="table-responsive">
				<table class="table table-hover align-middle mb-0">
					<thead class="table-light"><tr><th>Nomor Surat</th><th>Jenis</th><th>Tanggal</th><th>Perihal</th><th>Status</th><th style="width:80px" class="text-end">Aksi</th></tr></thead>
					<tbody>
					<?php foreach ($recent as $r): ?>
						<tr>
							<td class="letter-number fw-bold"><?= e($r['letter_number']) ?></td>
							<td><?= e($r['type_name']) ?></td>
							<td class="text-nowrap small"><?= e(tgl_indo($r['letter_date'])) ?></td>
							<td><div class="text-truncate" style="max-width:260px;" title="<?= e($r['subject']) ?>"><?= e($r['subject']) ?></div></td>
							<td><?= status_badge($r['status']) ?></td>
							<td class="text-end"><a class="btn btn-sm btn-light" href="<?= site_url('surat/detail/'.(int) $r['id']) ?>" title="Detail"><i class="fa-solid fa-eye"></i></a></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	</div>
<?php endif; ?>
