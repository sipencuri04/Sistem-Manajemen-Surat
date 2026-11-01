<?php defined('BASEPATH') OR exit('No direct script access allowed');
$icons = array(
	'siswa_aktif' => 'fa-user-check', 'lulus' => 'fa-graduation-cap', 'pindah' => 'fa-right-left',
	'selesai_pkl' => 'fa-certificate', 'rekomendasi' => 'fa-thumbs-up', 'izin' => 'fa-door-open',
);
?>
<!-- Hero Banner / Greeting -->
<div class="d-flex align-items-center justify-content-between mb-3">
	<div>
		<h1 class="h5 fw-bold text-navy mb-0"><i class="fa-solid fa-file-circle-plus text-primary me-2"></i>Ajukan Surat Sekolah</h1>
		<small class="text-muted">Pilih jenis surat yang Anda butuhkan</small>
	</div>
	<a href="<?= site_url('permohonan/cek') ?>" class="btn-app-track" aria-label="Cek status permohonan">
		<i class="fa-solid fa-magnifying-glass"></i>
		<span>Cek Status</span>
	</a>
</div>



<!-- Card Instansi -->
<div class="d-flex align-items-center gap-3 p-3 rounded-3 border mb-3" style="background:#f8faff;">
	<i class="fa-solid fa-building-columns fa-lg text-primary flex-shrink-0"></i>
	<div class="flex-grow-1 min-w-0">
		<div class="fw-semibold small text-dark">Kirim Surat dari Instansi / Dinas?</div>
		<div class="text-muted" style="font-size:0.8rem;">Upload surat dinas langsung ke Tata Usaha</div>
	</div>
	<a href="<?= site_url('kirim-surat') ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 flex-shrink-0">Kirim &raquo;</a>
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
