<?php defined('BASEPATH') OR exit('No direct script access allowed');
$has_filter = $filters['q'] !== '' || $filters['type'] || $filters['year'] || $filters['month'] || $filters['status'] !== '';
?>
<div class="card mb-3">
	<div class="card-body">
		<form method="get" action="<?= site_url('arsip') ?>" class="row g-2 align-items-end">
			<div class="col-lg-3">
				<label class="form-label small text-muted mb-1" for="q">Cari</label>
				<div class="input-group">
					<span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
					<input type="search" id="q" name="q" class="form-control" value="<?= e($filters['q']) ?>" placeholder="Nomor surat atau perihal" maxlength="100">
				</div>
			</div>
			<div class="col-sm-6 col-lg-3">
				<label class="form-label small text-muted mb-1" for="type">Jenis Surat</label>
				<select id="type" name="type" class="form-select">
					<option value="">Semua jenis</option>
					<?php foreach ($types as $t): ?>
						<option value="<?= (int) $t['id'] ?>" <?= $filters['type'] == $t['id'] ? 'selected' : '' ?>><?= e($t['name']) ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="col-6 col-sm-3 col-lg-2">
				<label class="form-label small text-muted mb-1" for="month">Bulan</label>
				<select id="month" name="month" class="form-select">
					<option value="">Semua</option>
					<?php for ($m = 1; $m <= 12; $m++): ?>
						<option value="<?= $m ?>" <?= $filters['month'] === $m ? 'selected' : '' ?>><?= e(bulan_indo($m)) ?></option>
					<?php endfor; ?>
				</select>
			</div>
			<div class="col-6 col-sm-3 col-lg-2">
				<label class="form-label small text-muted mb-1" for="year">Tahun</label>
				<select id="year" name="year" class="form-select">
					<option value="">Semua</option>
					<?php foreach ($years as $y): ?>
						<option value="<?= (int) $y ?>" <?= $filters['year'] === (int) $y ? 'selected' : '' ?>><?= (int) $y ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="col-lg-2 d-flex gap-2">
				<button class="btn btn-navy flex-fill" type="submit"><i class="fa-solid fa-filter me-1"></i> Filter</button>
				<a class="btn btn-light" href="<?= site_url('arsip') ?>" title="Reset filter"><i class="fa-solid fa-rotate-left"></i></a>
			</div>
			<div class="col-12 d-flex flex-wrap gap-3 small">
				<div class="form-check form-check-inline mb-0">
					<input class="form-check-input" type="radio" name="status" id="st_all" value="" <?= $filters['status'] === '' ? 'checked' : '' ?>>
					<label class="form-check-label" for="st_all">Semua status</label>
				</div>
				<div class="form-check form-check-inline mb-0">
					<input class="form-check-input" type="radio" name="status" id="st_final" value="final" <?= $filters['status'] === 'final' ? 'checked' : '' ?>>
					<label class="form-check-label" for="st_final">Final</label>
				</div>
				<div class="form-check form-check-inline mb-0">
					<input class="form-check-input" type="radio" name="status" id="st_draft" value="draft" <?= $filters['status'] === 'draft' ? 'checked' : '' ?>>
					<label class="form-check-label" for="st_draft">Draft</label>
				</div>
				<div class="ms-auto">
					<select name="sort" class="form-select form-select-sm" aria-label="Urutan">
						<option value="newest" <?= $filters['sort'] === 'newest' ? 'selected' : '' ?>>Terbaru dahulu</option>
						<option value="oldest" <?= $filters['sort'] === 'oldest' ? 'selected' : '' ?>>Terlama dahulu</option>
					</select>
				</div>
			</div>
		</form>
	</div>
</div>

<div class="card">
	<div class="card-header d-flex justify-content-between align-items-center">
		<h3 class="card-title"><i class="fa-solid fa-box-archive me-1"></i> Hasil Arsip</h3>
		<span class="badge text-bg-light border"><?= (int) $total ?> surat</span>
	</div>
	<?php if (empty($letters)): ?>
		<div class="empty-state">
			<i class="fa-regular fa-folder-open d-block"></i>
			<p class="mb-0"><?= $has_filter ? 'Tidak ada surat yang cocok dengan filter.' : 'Arsip masih kosong.' ?></p>
		</div>
	<?php else: ?>
		<?php $this->load->view('letters/_table', array('back' => 'arsip')); ?>
		<div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2 border-top">
			<small class="text-muted">Menampilkan <?= $offset + 1 ?>&ndash;<?= $offset + count($letters) ?> dari <?= (int) $total ?> surat</small>
			<?= $pagination ?>
		</div>
	<?php endif; ?>
</div>
