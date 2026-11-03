<?php defined('BASEPATH') OR exit('No direct script access allowed');
$id = (int) $letter['id'];
$curr_user = current_user();
$u_role = $curr_user ? strtolower($curr_user['role']) : (isset($current_user['role']) ? strtolower($current_user['role']) : '');
$can_tu = in_array($u_role, array('tu', 'superuser', 'admin'), TRUE) || is_tu() || is_superuser() || is_admin();
$can_kepsek = in_array($u_role, array('kepala_sekolah', 'superuser', 'admin'), TRUE) || is_kepala_sekolah();
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
	<?php if ($can_kepsek && isset($letter['approval_status']) && $letter['approval_status'] === 'menunggu_persetujuan'): ?>
		<button type="button" class="btn btn-success fw-semibold" data-bs-toggle="modal" data-bs-target="#kepsekApproveModal">
			<i class="fa-solid fa-signature me-1"></i> Tanda Tangani Surat
		</button>
		<button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
			<i class="fa-solid fa-ban me-1"></i> Tolak / Minta Revisi
		</button>
	<?php endif; ?>
	<?php if ($can_tu): ?>
		<?php if (isset($letter['approval_status']) && $letter['approval_status'] === 'disetujui'): ?>
			<button type="button" class="btn btn-danger fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#tuStempelModal">
				<i class="fa-solid fa-stamp me-1"></i> Beri Stempel TU
			</button>
		<?php else: ?>
			<button type="button" class="btn btn-outline-secondary" disabled title="Surat harus ditandatangani Kepala Sekolah terlebih dahulu sebelum distempel TU">
				<i class="fa-solid fa-stamp me-1"></i> Stempel TU (Menunggu TTD Kepsek)
			</button>
		<?php endif; ?>
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
					<dt>Tanda Tangan (Kepala Sekolah)</dt>
					<dd>
						<?php if ($letter['approval_status'] === 'menunggu_persetujuan'): ?>
							<span class="badge bg-warning-subtle text-warning border border-warning-subtle fw-semibold">
								<i class="fa-solid fa-clock me-1"></i> Menunggu Tanda Tangan Kepala Sekolah
							</span>
						<?php elseif ($letter['approval_status'] === 'disetujui'): ?>
							<?php if (isset($letter['signature_type']) && $letter['signature_type'] === 'basah'): ?>
								<span class="badge bg-warning-subtle text-warning border border-warning-subtle fw-semibold">
									<i class="fa-solid fa-pen-fancy me-1"></i> Disetujui untuk TTD Basah Fisik
								</span>
							<?php else: ?>
								<span class="badge bg-success-subtle text-success border border-success-subtle fw-semibold">
									<i class="fa-solid fa-signature me-1"></i> Ditandatangani Digital (e-Sign)
								</span>
							<?php endif; ?>
						<?php elseif ($letter['approval_status'] === 'ditolak'): ?>
							<span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-semibold">
								<i class="fa-solid fa-circle-xmark me-1"></i> Ditolak Kepala Sekolah
							</span>
						<?php else: ?>
							<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Draft</span>
						<?php endif; ?>
					</dd>
					<dt>Stempel Resmi (Wewenang TU)</dt>
					<dd>
						<?php 
						$is_approved_letter = isset($letter['approval_status']) && $letter['approval_status'] === 'disetujui';
						$st_type = isset($letter['stamp_type']) ? $letter['stamp_type'] : 'belum_stempel'; 
						?>
						<?php if ( ! $is_approved_letter): ?>
							<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle fw-semibold d-inline-block mb-1">
								<i class="fa-solid fa-clock me-1"></i> Menunggu TTD Kepala Sekolah
							</span>
							<div class="small text-muted mt-1"><i class="fa-solid fa-lock me-1"></i> Tombol stempel TU otomatis aktif setelah Kepala Sekolah menyetujui surat ini.</div>
						<?php elseif ($st_type === 'digital' && ! empty($letter['stamped_at'])): ?>
							<span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-semibold d-inline-block mb-1">
								<i class="fa-solid fa-stamp me-1"></i> Telah Dibubuhi Stempel Digital TU
							</span>
							<?php if (!empty($letter['stamped_by_name'])): ?><small class="text-muted d-block">(oleh <?= e($letter['stamped_by_name']) ?>)</small><?php endif; ?>
							<?php if ($can_tu): ?>
								<div class="mt-2">
									<button type="button" class="btn btn-sm btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#tuStempelModal">
										<i class="fa-solid fa-stamp me-1"></i> Ubah / Cabut Stempel TU
									</button>
								</div>
							<?php endif; ?>
						<?php elseif ($st_type === 'basah'): ?>
							<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle fw-semibold d-inline-block mb-1">
								<i class="fa-solid fa-hand-holding-hand me-1"></i> Mode Stempel Basah Fisik
							</span>
							<?php if ($can_tu): ?>
								<div class="mt-2">
									<button type="button" class="btn btn-sm btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#tuStempelModal">
										<i class="fa-solid fa-stamp me-1"></i> Ubah Status Stempel TU
									</button>
								</div>
							<?php endif; ?>
						<?php else: ?>
							<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle fw-semibold d-inline-block mb-1">
								<i class="fa-solid fa-triangle-exclamation me-1"></i> Belum Distempel oleh TU
							</span>
							<?php if ($can_tu): ?>
								<div class="mt-2">
									<button type="button" class="btn btn-sm btn-danger fw-bold shadow-sm w-100" data-bs-toggle="modal" data-bs-target="#tuStempelModal">
										<i class="fa-solid fa-stamp me-1"></i> Beri Stempel TU Sekarang
									</button>
								</div>
							<?php endif; ?>
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

<!-- Modal Persetujuan & Keputusan TTD Kepala Sekolah -->
<div class="modal fade" id="kepsekApproveModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content shadow border-0">
			<div class="modal-header border-bottom">
				<h5 class="modal-title h6 fw-bold text-navy"><i class="fa-solid fa-signature text-primary me-2"></i>Persetujuan &amp; Tanda Tangan Kepala Sekolah</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
			</div>
			<?= form_open('persetujuan-surat/setujui/'.$id) ?>
			<div class="modal-body p-4">
				<p class="small text-muted mb-3">Sebagai Kepala Sekolah, silakan tentukan cara pengesahan tanda tangan surat ini:</p>
				<div class="mb-3">
					<div class="form-check p-3 border rounded-3 bg-light mb-2">
						<input class="form-check-input ms-0 me-2" type="radio" name="signature_type" id="dtl_sig_digital" value="digital" checked>
						<label class="form-check-label fw-bold text-primary" for="dtl_sig_digital">
							<i class="fa-solid fa-file-signature me-1"></i> Tanda Tangani Secara Digital (e-Sign)
						</label>
						<div class="small text-muted ps-4">Scan tanda tangan resmi Kepala Sekolah langsung tercantum pada dokumen PDF surat.</div>
					</div>
					<div class="form-check p-3 border rounded-3 bg-light">
						<input class="form-check-input ms-0 me-2" type="radio" name="signature_type" id="dtl_sig_basah" value="basah">
						<label class="form-check-label fw-bold text-secondary" for="dtl_sig_basah">
							<i class="fa-solid fa-pen-fancy me-1"></i> Setujui untuk Tanda Tangan Basah Fisik
						</label>
						<div class="small text-muted ps-4">Surat disetujui untuk diprint fisik dan ditandatangani basah manual menggunakan bolpoin di meja Kepala Sekolah.</div>
					</div>
				</div>
				<div class="alert alert-info py-2 small mb-0">
					<i class="fa-solid fa-info-circle me-1"></i> <strong>Catatan Stempel:</strong> Cap stempel sekolah adalah wewenang bagian Tata Usaha (TU) dan akan dibubuhkan oleh petugas TU.
				</div>
			</div>
			<div class="modal-footer border-top bg-light">
				<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
				<button type="submit" class="btn btn-success fw-semibold"><i class="fa-solid fa-signature me-1"></i> Sahkan &amp; Tandatangani</button>
			</div>
			<?= form_close() ?>
		</div>
	</div>
</div>

<!-- Modal Kelola Stempel Resmi TU -->
<div class="modal fade" id="tuStempelModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content shadow border-0">
			<div class="modal-header border-bottom">
				<h5 class="modal-title h6 fw-bold text-danger"><i class="fa-solid fa-stamp text-danger me-2"></i>Pengelolaan Stempel Resmi (Wewenang Tata Usaha)</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
			</div>
			<?= form_open('surat/stempel/'.$id) ?>
			<div class="modal-body p-4">
				<p class="small text-muted mb-3">Sebagai petugas Tata Usaha, tentukan status cap stempel resmi sekolah untuk surat ini:</p>
				<div class="mb-3">
					<div class="form-check p-3 border rounded-3 bg-light mb-2">
						<input class="form-check-input ms-0 me-2" type="radio" name="stamp_action" id="tu_stamp_digital" value="digital" <?= (!isset($letter['stamp_type']) || $letter['stamp_type'] === 'digital') ? 'checked' : '' ?>>
						<label class="form-check-label fw-bold text-primary" for="tu_stamp_digital">
							<i class="fa-solid fa-stamp me-1"></i> Bubuhkan Stempel Digital TU
						</label>
						<div class="small text-muted ps-4">Cap stempel resmi sekolah dibubuhkan secara digital pada berkas PDF.</div>
					</div>
					<div class="form-check p-3 border rounded-3 bg-light mb-2">
						<input class="form-check-input ms-0 me-2" type="radio" name="stamp_action" id="tu_stamp_basah" value="basah" <?= (isset($letter['stamp_type']) && $letter['stamp_type'] === 'basah') ? 'checked' : '' ?>>
						<label class="form-check-label fw-bold text-secondary" for="tu_stamp_basah">
							<i class="fa-solid fa-hand-holding-hand me-1"></i> Mode Stempel Basah Fisik
						</label>
						<div class="small text-muted ps-4">Surat dicetak fisik lalu dicap basah menggunakan stempel asli di ruang TU.</div>
					</div>
					<div class="form-check p-3 border rounded-3 bg-light">
						<input class="form-check-input ms-0 me-2" type="radio" name="stamp_action" id="tu_stamp_cabut" value="cabut" <?= (isset($letter['stamp_type']) && $letter['stamp_type'] === 'belum_stempel') ? 'checked' : '' ?>>
						<label class="form-check-label fw-bold text-danger" for="tu_stamp_cabut">
							<i class="fa-solid fa-ban me-1"></i> Cabut Stempel (Belum Distempel)
						</label>
						<div class="small text-muted ps-4">Menghilangkan stempel dari surat ini sampai TU membubuhkannya kembali.</div>
					</div>
				</div>
			</div>
			<div class="modal-footer border-top bg-light">
				<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
				<button type="submit" class="btn btn-danger fw-semibold"><i class="fa-solid fa-stamp me-1"></i> Simpan Status Stempel TU</button>
			</div>
			<?= form_close() ?>
		</div>
	</div>
</div>
