<?php defined('BASEPATH') OR exit('No direct script access allowed');
$icons = array(
	'siswa_aktif' => 'fa-user-check', 'lulus' => 'fa-graduation-cap', 'pindah' => 'fa-right-left',
	'selesai_pkl' => 'fa-certificate', 'rekomendasi' => 'fa-thumbs-up', 'izin' => 'fa-door-open',
);
?>
<!-- Card Instansi -->
<div class="instansi-card mb-4">
	<div class="instansi-card-badge">
		<i class="fa-solid fa-shield-halved me-1"></i> KHUSUS INSTANSI / LEMBAGA LAIN
	</div>
	<span class="instansi-card-chip">Bukan Siswa</span>
	<h2 class="instansi-card-title">Kirim Surat Masuk Kedinasan<br><span style="font-weight:700;">(Format PDF)</span></h2>
	<p class="instansi-card-desc">Bagi <strong>Dinas Pendidikan, Instansi Pemerintah, Dunia Usaha (DUDI), Puskesmas, Kepolisian,</strong> atau <strong>Sekolah Mitra</strong> yang ingin mengirimkan surat resmi, undangan kedinasan, atau permohonan kerjasama secara online langsung ke Bagian Tata Usaha.</p>
	<a href="<?= site_url('kirim-surat') ?>" class="instansi-card-btn-primary">
		<i class="fa-solid fa-file-arrow-up me-1"></i> Kirim Surat Sekarang &raquo;
	</a>
	<a href="<?= site_url('permohonan/cek') ?>" class="instansi-card-btn-outline">
		<i class="fa-solid fa-receipt me-1"></i> Cek Resi Surat
	</a>
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
