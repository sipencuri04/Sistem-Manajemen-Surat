<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
	<div>
		<h1 class="h4 fw-bold text-navy mb-1"><i class="fa-solid fa-database me-2 text-primary"></i>Backup Database &amp; Pemeliharaan</h1>
		<p class="text-muted small mb-0">Cadangan basis data aplikasi untuk pencegahan kehilangan data dan pemeliharaan server.</p>
	</div>
	<?= form_open('backup/buat', array('data-confirm' => 'Mulai proses backup seluruh database saat ini?')) ?>
		<button type="submit" class="btn btn-primary"><i class="fa-solid fa-download me-1"></i> Buat Cadangan Database Baru</button>
	<?= form_close() ?>
</div>

<div class="row g-3">
	<div class="col-lg-8">
		<div class="card">
			<div class="card-header bg-white py-3">
				<h2 class="h6 card-title mb-0 text-navy"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Daftar Berkas Cadangan Tersimpan</h2>
			</div>
			<div class="card-body p-0">
				<div class="table-responsive">
					<table class="table table-hover align-middle mb-0">
						<thead class="table-light">
							<tr>
								<th>Nama Berkas Cadangan</th>
								<th>Ukuran</th>
								<th>Waktu Pembuatan</th>
								<th style="width:120px" class="text-end">Aksi</th>
							</tr>
						</thead>
						<tbody>
						<?php if (empty($files)): ?>
							<tr><td colspan="4" class="text-center py-4 text-muted">Belum ada file backup yang dibuat. Klik tombol di atas untuk membuat backup pertama.</td></tr>
						<?php else: foreach ($files as $b): ?>
							<tr>
								<td>
									<div class="fw-bold text-navy"><i class="fa-solid fa-file-code me-2 text-secondary"></i><?= e($b['name']) ?></div>
								</td>
								<td><span class="badge text-bg-light border"><?= e($b['size']) ?></span></td>
								<td class="small text-muted"><?= e(tgl_indo($b['date'])) ?> <?= e(substr($b['date'], 11, 5)) ?></td>
								<td class="text-end">
									<a href="<?= site_url('backup/unduh/'.preg_replace('/\.sql$/', '', $b['name'])) ?>" class="btn btn-sm btn-outline-primary" title="Unduh File SQL">
										<i class="fa-solid fa-download me-1"></i> Unduh
									</a>
								</td>
							</tr>
						<?php endforeach; endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>

	<div class="col-lg-4">
		<div class="card">
			<div class="card-header bg-white py-3">
				<h2 class="h6 card-title mb-0 text-navy"><i class="fa-solid fa-shield-halved text-success me-2"></i>Keamanan Cadangan</h2>
			</div>
			<div class="card-body small text-muted">
				<p class="mb-2"><strong>Lokasi Penyimpanan:</strong> Seluruh berkas cadangan disimpan di dalam folder <code>storage/backups/</code> yang terlindungi oleh konfigurasi Apache (tidak dapat diakses langsung oleh publik).</p>
				<p class="mb-2"><strong>Format File:</strong> Berupa berkas skrip SQL standar MySQL yang berisi struktur tabel dan data utuh.</p>
				<p class="mb-0"><strong>Pemulihan (Restore):</strong> File backup dapat diimpor kembali sewaktu-waktu melalui aplikasi phpMyAdmin jika terjadi kerusakan sistem atau perpindahan server.</p>
			</div>
		</div>
	</div>
</div>
