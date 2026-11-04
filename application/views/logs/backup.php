<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
	<div>
		<h1 class="h4 fw-bold text-navy mb-1"><i class="fa-solid fa-database me-2 text-primary"></i>Backup Database &amp; Pemeliharaan</h1>
		<p class="text-muted small mb-0">Cadangan basis data aplikasi untuk pencegahan kehilangan data dan pemeliharaan server.</p>
	</div>
	<div class="d-flex flex-wrap gap-2">
		<?= form_open('backup/buat-surat', array('data-confirm' => 'Mulai proses backup khusus data surat & persuratan (SQL)?')) ?>
			<button type="submit" class="btn btn-success fw-semibold"><i class="fa-solid fa-envelope-open-text me-1"></i> Backup Khusus Surat (SQL)</button>
		<?= form_close() ?>
		<?= form_open('backup/buat', array('data-confirm' => 'Mulai proses backup seluruh database saat ini?')) ?>
			<button type="submit" class="btn btn-primary"><i class="fa-solid fa-database me-1"></i> Backup Seluruh Database</button>
		<?= form_close() ?>
	</div>
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
		<div class="card mb-3 border-success-subtle bg-light">
			<div class="card-header bg-white py-3">
				<h2 class="h6 card-title mb-0 text-success"><i class="fa-solid fa-calendar-check me-2"></i>Jadwal Backup Surat Mingguan (cPanel)</h2>
			</div>
			<div class="card-body small text-muted">
				<p class="mb-2">Sistem dapat mencadangkan tabel surat secara otomatis setiap minggu via menu <strong>Cron Jobs</strong> di cPanel hosting:</p>
				<div class="mb-2">
					<label class="form-label fw-bold small text-navy mb-1">Perintah Cron cPanel (Minggu pk 02:00):</label>
					<div class="input-group input-group-sm">
						<input type="text" class="form-control font-monospace small bg-white" id="cronCmd" value="curl -s &quot;<?= site_url('backup/cron?token=smk_weekly_backup_secret') ?>&quot; > /dev/null 2>&1" readonly>
						<button class="btn btn-outline-success" type="button" onclick="navigator.clipboard.writeText(document.getElementById('cronCmd').value); this.innerText = 'Tersalin!'; setTimeout(() => this.innerText = 'Salin', 2000);">Salin</button>
					</div>
				</div>
				<small class="text-muted d-block"><i class="fa-solid fa-circle-info me-1"></i> Jadwal: <code>0 2 * * 0</code>. Berkas tersimpan di folder <code>storage/backups/</code> dan dirotasi otomatis.</small>
			</div>
		</div>

		<div class="card mb-3">
			<div class="card-header bg-white py-3">
				<h2 class="h6 card-title mb-0 text-navy"><i class="fa-solid fa-hard-drive text-primary me-2"></i>Kapasitas Penyimpanan</h2>
			</div>
			<div class="card-body">
				<div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
					<span class="small text-muted"><i class="fa-solid fa-file-pdf text-danger me-2"></i>Arsip Surat Keluar</span>
					<span class="badge text-bg-light border fw-semibold"><?= e($storage_stats['letters']) ?></span>
				</div>
				<div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
					<span class="small text-muted"><i class="fa-solid fa-file-import text-info me-2"></i>Berkas Surat Masuk</span>
					<span class="badge text-bg-light border fw-semibold"><?= e($storage_stats['incoming']) ?></span>
				</div>
				<div class="d-flex justify-content-between align-items-center">
					<span class="small text-muted"><i class="fa-solid fa-database text-warning me-2"></i>Cadangan Database</span>
					<span class="badge text-bg-light border fw-semibold"><?= e($storage_stats['backups']) ?></span>
				</div>
			</div>
		</div>

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
