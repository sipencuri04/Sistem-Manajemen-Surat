<?php defined('BASEPATH') OR exit('No direct script access allowed');
$icons = array(
	'siswa_aktif' => 'fa-user-check', 'lulus' => 'fa-graduation-cap', 'pindah' => 'fa-right-left',
	'selesai_pkl' => 'fa-certificate', 'rekomendasi' => 'fa-thumbs-up', 'izin' => 'fa-door-open',
);
?>
<!-- Hero Banner / Greeting -->
<div class="app-hero-banner">
	<div class="app-hero-pill">
		<i class="fa-solid fa-bolt"></i> Layanan Mandiri Siswa
	</div>
	<h1 class="app-hero-title">Ajukan Surat Sekolah Cepat &amp; Praktis</h1>
	<p class="app-hero-desc">Pilih permohonan surat keterangan yang Anda butuhkan tanpa perlu antre di ruang TU. Pantau status dan cetak langsung dari smartphone Anda.</p>
	
</div>



<!-- Card Khusus: Pengiriman Surat dari Instansi Lain / Kedinasan (Sangat Jelas & Terpisah) -->
<div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden position-relative" style="background: linear-gradient(135deg, #091e42 0%, #0c2e68 55%, #174291 100%);">
	<div class="card-body p-3 p-md-4 text-white">
		<div class="row align-items-center g-3">
			<div class="col-lg-8">
				<div class="d-flex flex-wrap align-items-center gap-2 mb-2">
					<span class="badge bg-warning text-dark fw-bold px-3 py-1 rounded-pill" style="font-size: 0.75rem; letter-spacing: 0.05em;">
						<i class="fa-solid fa-shield-halved me-1"></i> KHUSUS INSTANSI / LEMBAGA LAIN
					</span>
					<span class="badge bg-white bg-opacity-25 text-white rounded-pill px-2 py-1 small">Bukan Siswa</span>
				</div>
				<h2 class="h5 fw-bold mb-1 text-white">Kirim Surat Masuk Kedinasan (Format PDF)</h2>
				<p class="small text-white-50 mb-0">
					Bagi <strong>Dinas Pendidikan, Instansi Pemerintah, Dunia Usaha (DUDI), Puskesmas, Kepolisian, atau Sekolah Mitra</strong> yang ingin mengirimkan surat resmi, undangan kedinasan, atau permohonan kerjasama secara online langsung ke Bagian Tata Usaha.
				</p>
			</div>
			<div class="col-lg-4 text-lg-end">
				<div class="d-flex flex-wrap gap-2 justify-content-lg-end">
					<a href="<?= site_url('kirim-surat') ?>" class="btn btn-warning rounded-pill px-3 py-2 fw-bold shadow-sm text-dark">
						<i class="fa-solid fa-file-arrow-up me-1"></i> Kirim Surat Sekarang &raquo;
					</a>
					<a href="<?= site_url('kirim-surat/status') ?>" class="btn btn-outline-light rounded-pill px-3 py-2 btn-sm">
						<i class="fa-solid fa-receipt me-1"></i> Cek Resi Surat
					</a>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Process Step Cards (Mobile Carousel / Grid) -->
<div class="d-flex align-items-center justify-content-between mb-2">
	<h2 class="h6 fw-bold text-navy mb-0"><i class="fa-solid fa-route text-primary me-1"></i> Alur Permohonan</h2>
	<a href="#modalAlur" data-bs-toggle="modal" class="small text-primary text-decoration-none fw-semibold">Detail &raquo;</a>
</div>
<div class="app-steps-grid">
	<div class="app-step-card">
		<div class="step-number-circle step-1">1</div>
		<div class="step-label">Isi Form</div>
		<div class="step-desc">Pilih surat &amp; data diri</div>
	</div>
	<div class="app-step-card">
		<div class="step-number-circle step-2">2</div>
		<div class="step-label">Kode Unik</div>
		<div class="step-desc">Simpan kode lacak</div>
	</div>
	<div class="app-step-card">
		<div class="step-number-circle step-3">3</div>
		<div class="step-label">Verifikasi</div>
		<div class="step-desc">TU proses di jam kerja</div>
	</div>
	<div class="app-step-card">
		<div class="step-number-circle step-4">4</div>
		<div class="step-label">Selesai</div>
		<div class="step-desc">Cetak / ambil fisik</div>
	</div>
</div>

<!-- Section: Letter Types -->
<div id="daftarSurat" class="d-flex align-items-center justify-content-between mb-3 pt-2">
	<div>
		<h2 class="h5 fw-bold text-navy mb-0">Pilihan Jenis Surat</h2>
		<small class="text-muted">Ketuk pada jenis surat yang ingin Anda ajukan</small>
	</div>
	<span class="badge bg-light text-secondary border px-2 py-1 small"><?= count($types) ?> Jenis</span>
</div>

<?php if (empty($types)): ?>
	<div class="card border-0 shadow-sm rounded-4"><div class="empty-state py-5"><i class="fa-regular fa-envelope-open d-block fa-3x text-muted mb-2"></i>
		<p class="mb-0 text-muted">Saat ini belum ada surat yang dapat diajukan secara online. Silakan datang langsung ke ruang Tata Usaha.</p></div></div>
<?php else: ?>
	<?php
	$colors = array(
		'siswa_aktif' => 'icon-blue',
		'lulus'       => 'icon-purple',
		'pindah'      => 'icon-amber',
		'selesai_pkl' => 'icon-emerald',
		'rekomendasi' => 'icon-rose',
		'izin'        => 'icon-sky',
	);
	$icons = array(
		'siswa_aktif' => 'fa-user-check',
		'lulus'       => 'fa-graduation-cap',
		'pindah'      => 'fa-arrow-right-arrow-left',
		'selesai_pkl' => 'fa-award',
		'rekomendasi' => 'fa-thumbs-up',
		'izin'        => 'fa-door-open',
	);
	?>
	<div class="row g-3" id="letterGrid">
		<?php foreach ($types as $t): 
			$t_key = isset($t['template_key']) ? $t['template_key'] : '';
			$c_class = isset($colors[$t_key]) ? $colors[$t_key] : 'icon-blue';
			$i_class = isset($icons[$t_key]) ? $icons[$t_key] : 'fa-file-lines';
			$purp = !empty($t['guide']['purpose']) ? $t['guide']['purpose'] : (string) $t['description'];
		?>
			<div class="col-md-6 letter-item" data-search="<?= strtolower(e($t['name'].' '.$purp.' '.(!empty($t['guide']['when']) ? implode(' ', $t['guide']['when']) : ''))) ?>">
				<div class="app-letter-card">
					<div>
						<div class="letter-card-header">
							<div class="letter-icon-box <?= $c_class ?>">
								<i class="fa-solid <?= $i_class ?>"></i>
							</div>
							<div class="min-w-0 flex-grow-1">
								<h3 class="letter-card-title"><?= e($t['name']) ?></h3>
								<div class="letter-card-desc"><?= e($purp) ?></div>
							</div>
						</div>

						<?php if ( ! empty($t['guide']['when'])): ?>
							<div class="letter-card-chips">
								<?php foreach (array_slice($t['guide']['when'], 0, 3) as $w): ?>
									<span class="app-chip"><i class="fa-solid fa-check text-success me-1"></i><?= e($w) ?></span>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>

					<a href="<?= site_url('permohonan/ajukan/'.(int) $t['id']) ?>" class="btn-app-action">
						<span>Ajukan Surat Ini</span>
						<i class="fa-solid fa-arrow-right"></i>
					</a>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	
	<!-- Empty search result state -->
	<div id="noResult" class="card border-0 shadow-sm rounded-4 text-center py-5 d-none">
		<i class="fa-solid fa-magnifying-glass fa-2x text-muted mb-2"></i>
		<h6 class="fw-bold text-navy">Surat tidak ditemukan</h6>
		<p class="text-muted small mb-0">Coba gunakan kata kunci pencarian yang lain.</p>
	</div>
<?php endif; ?>
