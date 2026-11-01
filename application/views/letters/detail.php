<?php defined('BASEPATH') OR exit('No direct script access allowed');
$id = (int) $letter['id'];
?>
<nav aria-label="breadcrumb">
	<ol class="breadcrumb small mb-2">
		<li class="breadcrumb-item"><a href="<?= site_url('surat') ?>">Data Surat</a></li>
		<li class="breadcrumb-item active" aria-current="page"><?= e($letter['letter_number']) ?></li>
	</ol>
</nav>

<div class="d-flex flex-wrap align-items-center gap-2 mb-3">
	<div class="me-auto">
		<h2 class="h5 fw-bold text-navy mb-0"><?= e($letter['subject']) ?></h2>
		<div class="small text-muted">
			<span class="letter-number"><?= e($letter['letter_number']) ?></span> &middot; <?= e($letter['type_name']) ?> &middot; <?= status_badge($letter['status']) ?>
			<?php if (!empty($letter['approval_status'])): ?>
				&middot; 
				<?php if ($letter['approval_status'] === 'menunggu_persetujuan'): ?>
					<span class="badge bg-warning text-dark"><i class="fa-solid fa-clock me-1"></i> Menunggu Persetujuan Kepsek</span>
				<?php elseif ($letter['approval_status'] === 'disetujui'): ?>
					<span class="badge bg-success"><i class="fa-solid fa-check-circle me-1"></i> Disetujui Kepsek</span>
				<?php elseif ($letter['approval_status'] === 'ditolak'): ?>
					<span class="badge bg-danger"><i class="fa-solid fa-times-circle me-1"></i> Ditolak Kepsek</span>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</div>
	<?php if (is_kepala_sekolah() && isset($letter['approval_status']) && $letter['approval_status'] === 'menunggu_persetujuan'): ?>
		<?= form_open('persetujuan-surat/setujui/'.$id, array('class' => 'd-inline', 'data-confirm' => 'Setujui dan terbitkan surat ini?')) ?>
			<button type="submit" class="btn btn-success"><i class="fa-solid fa-check me-1"></i> Setujui Surat</button>
		<?= form_close() ?>
		<button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal"><i class="fa-solid fa-ban me-1"></i> Tolak / Minta Revisi</button>
	<?php endif; ?>
	<a href="<?= site_url('surat/edit/'.$id) ?>" class="btn btn-outline-primary"><i class="fa-solid fa-pen me-1"></i> Edit</a>
	<div class="btn-group">
		<a href="<?= site_url('surat/pdf/'.$id.'/lihat') ?>" target="_blank" rel="noopener" class="btn btn-outline-secondary">
			<i class="fa-solid fa-print me-1"></i> Cetak Surat
		</a>
		<button type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
			<span class="visually-hidden">Pilihan Cetak</span>
		</button>
		<ul class="dropdown-menu dropdown-menu-end shadow">
			<li><h6 class="dropdown-header">Pilihan Mode Cetak / Unduh</h6></li>
			<li>
				<a class="dropdown-item" href="<?= site_url('surat/pdf/'.$id.'/lihat') ?>" target="_blank" rel="noopener">
					<i class="fa-solid fa-stamp text-primary me-2"></i> Cetak Dokumen (Sesuai Mode Surat)
				</a>
			</li>
			<li>
				<a class="dropdown-item" href="<?= site_url('surat/pdf/'.$id.'/lihat?mode_ttd=basah') ?>" target="_blank" rel="noopener">
					<i class="fa-solid fa-pen-fancy text-danger me-2"></i> Cetak Blanko TTD Basah (Kosongkan TTD &amp; Cap)
				</a>
			</li>
			<li><hr class="dropdown-divider"></li>
			<li>
				<a class="dropdown-item" href="<?= site_url('surat/pdf/'.$id) ?>">
					<i class="fa-solid fa-download text-secondary me-2"></i> Unduh File PDF
				</a>
			</li>
		</ul>
	</div>
	<a href="<?= site_url('surat/pdf/'.$id) ?>" class="btn btn-primary"><i class="fa-solid fa-download me-1"></i> Unduh PDF</a>
</div>

<?php if (isset($letter['approval_status']) && $letter['approval_status'] === 'menunggu_persetujuan'): ?>
	<div class="alert alert-warning d-flex align-items-center mb-3">
		<i class="fa-solid fa-triangle-exclamation fa-lg me-3"></i>
		<div>
			<strong>Menunggu Persetujuan Kepala Sekolah</strong>
			<div class="small">Surat ini telah diajukan ke Kepala Sekolah dan saat ini belum berstatus final.</div>
		</div>
	</div>
<?php elseif (isset($letter['approval_status']) && $letter['approval_status'] === 'ditolak'): ?>
	<div class="alert alert-danger mb-3">
		<div class="d-flex align-items-start">
			<i class="fa-solid fa-circle-xmark fa-lg me-3 mt-1"></i>
			<div>
				<strong>Surat Ditolak / Membutuhkan Revisi oleh Kepala Sekolah</strong>
				<?php if (!empty($letter['rejection_reason'])): ?>
					<div class="mt-1 small bg-white p-2 rounded border border-danger-subtle text-dark"><strong>Alasan Penolakan:</strong> <?= nl2br(e($letter['rejection_reason'])) ?></div>
				<?php endif; ?>
				<div class="mt-2 small">Silakan klik tombol <strong>Edit</strong> untuk memperbaiki data dan mengajukan kembali.</div>
			</div>
		</div>
	</div>
<?php endif; ?>

<div class="row g-3">
	<div class="col-xl-4">
		<div class="card mb-3">
			<div class="card-header"><h3 class="card-title">Informasi Surat</h3></div>
			<div class="card-body">
				<dl class="dl-meta mb-0">
					<dt>Nomor Surat</dt><dd class="letter-number"><?= e($letter['letter_number']) ?>
						<span class="badge text-bg-light border ms-1"><?= $letter['number_mode'] === 'manual' ? 'manual' : 'otomatis' ?></span></dd>
					<dt>Jenis Surat</dt><dd><?= e($letter['type_name']) ?></dd>
					<dt>Status Persetujuan</dt>
					<dd>
						<?php if (empty($letter['approval_status']) || $letter['approval_status'] === 'draft'): ?>
							<span class="badge text-bg-secondary">Draft</span>
						<?php elseif ($letter['approval_status'] === 'menunggu_persetujuan'): ?>
							<span class="badge bg-warning text-dark">Menunggu Persetujuan</span>
						<?php elseif ($letter['approval_status'] === 'disetujui'): ?>
							<span class="badge bg-success">Disetujui</span>
						<?php elseif ($letter['approval_status'] === 'ditolak'): ?>
							<span class="badge bg-danger">Ditolak</span>
						<?php endif; ?>
					<dt>Mode Pengesahan</dt>
					<dd>
						<?php if (isset($letter['signature_type']) && $letter['signature_type'] === 'basah'): ?>
							<span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-semibold">
								<i class="fa-solid fa-pen-fancy me-1"></i> Wajib Tanda Tangan Basah (Fisik)
							</span>
						<?php else: ?>
							<span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-semibold">
								<i class="fa-solid fa-stamp me-1"></i> Tanda Tangan &amp; Stempel Digital
							</span>
						<?php endif; ?>
					</dd>
					<dt>Tempat, Tanggal</dt><dd><?= e($letter['place']) ?>, <?= e(tgl_indo($letter['letter_date'])) ?></dd>
					<dt>Perihal</dt><dd><?= e($letter['subject']) ?></dd>
					<?php if ($tpl['attachment']): ?><dt>Lampiran</dt><dd><?= e($letter['attachment_text'] ?: '-') ?></dd><?php endif; ?>
					<dt>Penerima/Tujuan</dt><dd><?= e($letter['recipient'] ?: '-') ?><?= $letter['recipient_address'] ? '<br><small class="text-muted">'.e($letter['recipient_address']).'</small>' : '' ?></dd>
					<dt>Penandatangan</dt><dd><?= e($letter['signer_name']) ?><br><small class="text-muted"><?= e($letter['signer_title']) ?><?= $letter['signer_nip'] ? ' &middot; NIP '.e($letter['signer_nip']) : '' ?></small></dd>
					<?php if ($letter['notes']): ?><dt>Catatan Internal</dt><dd><?= e($letter['notes']) ?></dd><?php endif; ?>
					<dt>Dibuat</dt><dd class="small"><?= e(tgl_indo($letter['created_at'])) ?> <?= e(substr($letter['created_at'], 11, 5)) ?> oleh <?= e($letter['created_by_name'] ?: '-') ?></dd>
					<?php if ($letter['updated_at']): ?><dt>Diperbarui</dt><dd class="small"><?= e(tgl_indo($letter['updated_at'])) ?> <?= e(substr($letter['updated_at'], 11, 5)) ?> oleh <?= e($letter['updated_by_name'] ?: '-') ?></dd><?php endif; ?>
				</dl>
			</div>
		</div>

		<div class="card mb-3">
			<div class="card-header"><h3 class="card-title">Data <?= e($letter['type_name']) ?></h3></div>
			<div class="card-body">
				<dl class="dl-meta mb-0">
				<?php foreach ($tpl['fields'] as $name => $field): ?>
					<dt><?= e($field['label']) ?></dt>
					<dd>
					<?php if ($field['type'] === 'rows'): ?>
						<?php if (empty($values[$name])): ?>-<?php else: ?>
							<ol class="ps-3 mb-0 small">
							<?php foreach ($values[$name] as $row): ?>
								<li><?= e(implode(' · ', array_filter(array_map('strval', $row), 'strlen'))) ?></li>
							<?php endforeach; ?>
							</ol>
						<?php endif; ?>
					<?php else: ?>
						<?= $values[$name] !== '' ? nl2br(e($values[$name])) : '-' ?>
					<?php endif; ?>
					</dd>
				<?php endforeach; ?>
				</dl>
			</div>
		</div>

		<div class="card mb-3">
			<div class="card-header d-flex align-items-center justify-content-between">
				<h3 class="card-title">PDF Arsip</h3>
				<?= form_open('surat/arsipkan-pdf/'.$id, array('class' => 'd-inline')) ?>
					<button class="btn btn-sm btn-outline-primary" type="submit"><i class="fa-solid fa-arrows-rotate me-1"></i> <?= empty($files) ? 'Simpan PDF Arsip' : 'Buat Ulang PDF' ?></button>
				<?= form_close() ?>
			</div>
			<div class="card-body">
				<?php if (empty($files)): ?>
					<p class="small text-muted mb-0">Belum ada PDF arsip. PDF tetap dapat diunduh kapan saja (dibuat otomatis dari data surat). PDF arsip dibuat otomatis saat surat difinalkan.</p>
				<?php else: ?>
					<?php foreach ($files as $file): ?>
						<div class="d-flex align-items-center gap-2">
							<i class="fa-solid fa-file-pdf fa-2x text-danger"></i>
							<div class="flex-grow-1 small">
								<a href="<?= site_url('arsip/file/'.(int) $file['id']) ?>" class="fw-semibold text-decoration-none"><?= e($file['original_name']) ?></a><br>
								<span class="text-muted"><?= e(format_bytes($file['file_size'])) ?> &middot; <?= e(tgl_indo($file['created_at'])) ?> <?= e(substr($file['created_at'], 11, 5)) ?></span>
							</div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</div>

		<?= form_open('surat/hapus/'.$id, array('data-confirm' => 'Hapus surat nomor '.$letter['letter_number'].'? Data dan file PDF arsipnya akan dihapus permanen.')) ?>
			<button type="submit" class="btn btn-outline-danger w-100"><i class="fa-solid fa-trash me-1"></i> Hapus Surat</button>
		<?= form_close() ?>
	</div>

	<div class="col-xl-8">
		<div class="card">
			<div class="card-header d-flex align-items-center justify-content-between">
				<h3 class="card-title">Pratinjau Dokumen (PDF A4)</h3>
				<a href="<?= site_url('surat/pdf/'.$id.'/lihat') ?>" target="_blank" rel="noopener" class="small">Buka di tab baru <i class="fa-solid fa-up-right-from-square"></i></a>
			</div>
			<div class="card-body p-2">
				<iframe class="preview-frame" src="<?= site_url('surat/pdf/'.$id.'/lihat') ?>" title="Pratinjau PDF"></iframe>
			</div>
		</div>
	</div>
</div>

<?php if (is_kepala_sekolah()): ?>
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog">
		<?= form_open('persetujuan-surat/tolak/'.$id) ?>
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title text-danger"><i class="fa-solid fa-ban me-1"></i> Tolak / Minta Revisi Surat</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
				</div>
				<div class="modal-body">
					<p class="small text-muted mb-2">Tuliskan alasan penolakan atau instruksi perbaikan yang harus dilakukan oleh staf TU:</p>
					<div class="mb-3">
						<label class="form-label required" for="rejection_reason">Alasan / Catatan Revisi</label>
						<textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="4" required placeholder="Contoh: Format penulisan tanggal salah, perbaiki nama instansi tujuan."></textarea>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
					<button type="submit" class="btn btn-danger"><i class="fa-solid fa-paper-plane me-1"></i> Kirim Penolakan</button>
				</div>
			</div>
		<?= form_close() ?>
	</div>
</div>
<?php endif; ?>
