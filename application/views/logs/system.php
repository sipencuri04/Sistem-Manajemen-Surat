<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
	<div>
		<h1 class="h4 fw-bold text-navy mb-1"><i class="fa-solid fa-triangle-exclamation me-2 text-danger"></i>Log Sistem &amp; Error</h1>
		<p class="text-muted small mb-0">Pemantauan pesan error runtime, peringatan PHP, dan aktivitas background CodeIgniter.</p>
	</div>
	<?php if (!empty($files) && count($files) > 1): ?>
		<?= form_open('log-sistem/bersihkan', array('data-confirm' => 'Bersihkan file log lama? Log hari ini tetap disimpan.')) ?>
			<button type="submit" class="btn btn-outline-danger btn-sm">
				<i class="fa-solid fa-broom me-1"></i> Bersihkan Log Lama
			</button>
		<?= form_close() ?>
	<?php endif; ?>
</div>

<div class="row g-3">
	<div class="col-md-3">
		<div class="card">
			<div class="card-header bg-white py-3">
				<h2 class="h6 card-title mb-0"><i class="fa-solid fa-folder me-2 text-primary"></i>Berkas Log</h2>
			</div>
			<div class="list-group list-group-flush small" style="max-height: 500px; overflow-y: auto;">
				<?php if (empty($files)): ?>
					<div class="p-3 text-muted text-center">Tidak ada file log.</div>
				<?php else: foreach ($files as $f): ?>
					<a href="<?= site_url('log-sistem?file='.$f) ?>" class="list-group-item list-group-item-action <?= $f === $selected_file ? 'active' : '' ?>">
						<i class="fa-solid fa-file-code me-2"></i><?= e($f) ?>
					</a>
				<?php endforeach; endif; ?>
			</div>
		</div>
	</div>

	<div class="col-md-9">
		<div class="card">
			<div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
				<h2 class="h6 card-title mb-0 text-navy">
					<i class="fa-solid fa-terminal me-2 text-secondary"></i>Isi Berkas: <?= e($selected_file ?: 'Tidak ada berkas dipilih') ?>
				</h2>
			</div>
			<div class="card-body p-0">
				<?php if (empty($content)): ?>
					<div class="p-5 text-center text-muted">
						<i class="fa-solid fa-circle-check fa-3x text-success opacity-50 mb-2 d-block"></i>
						Tidak ada error yang tercatat pada berkas ini (Bersih).
					</div>
				<?php else: ?>
					<pre class="p-3 bg-dark text-light mb-0" style="max-height: 600px; overflow: auto; font-size: 0.82rem; font-family: Consolas, monospace;"><code><?= e($content) ?></code></pre>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
