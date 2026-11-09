<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="card">
	<div class="card-header">
		<form method="get" action="<?= site_url('surat') ?>" class="row g-2 align-items-center">
			<div class="col-md-5">
				<div class="input-group">
					<span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
					<input type="search" name="q" class="form-control" value="<?= e($filters['q']) ?>" placeholder="Cari nomor surat, perihal, atau tujuan" maxlength="100">
				</div>
			</div>
			<div class="col-6 col-md-3">
				<select name="type" class="form-select" data-autosubmit aria-label="Jenis surat">
					<option value="">Semua jenis</option>
					<?php foreach ($types as $t): ?>
						<option value="<?= (int) $t['id'] ?>" <?= $filters['type'] == $t['id'] ? 'selected' : '' ?>><?= e($t['name']) ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="col-6 col-md-2">
				<select name="status" class="form-select" data-autosubmit aria-label="Status">
					<option value="">Semua status</option>
					<option value="draft" <?= $filters['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
					<option value="final" <?= $filters['status'] === 'final' ? 'selected' : '' ?>>Final</option>
				</select>
			</div>
			<div class="col-md-2 d-flex gap-2">
				<button class="btn btn-navy flex-fill" type="submit">Cari</button>
				<a class="btn btn-light border" href="<?= site_url('surat') ?>" title="Reset"><i class="fa-solid fa-rotate-left"></i></a>
			</div>
		</form>
	</div>

	<?php if (empty($letters)): ?>
		<div class="empty-state">
			<i class="fa-regular fa-folder-open d-block"></i>
			<p class="mb-3"><?= ($filters['q'] !== '' OR $filters['type'] OR $filters['status'] !== '') ? 'Tidak ada surat yang cocok dengan pencarian.' : 'Belum ada surat.' ?></p>
			<a href="<?= site_url('surat/buat') ?>" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus me-1"></i> Buat Surat</a>
		</div>
	<?php else: ?>
		<?php $this->load->view('letters/_table', array('back' => 'surat')); ?>
		<div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2 border-top">
			<small class="text-muted">Menampilkan <?= $offset + 1 ?>&ndash;<?= $offset + count($letters) ?> dari <?= (int) $total ?> surat</small>
			<?= $pagination ?>
		</div>
	<?php endif; ?>
</div>
