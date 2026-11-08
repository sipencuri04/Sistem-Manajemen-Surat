<?php defined('BASEPATH') OR exit('No direct script access allowed');
/** Tabel surat (dipakai Data Surat & Arsip). @var array $letters @var int $offset @var string $back */
?>
<div class="table-responsive d-none d-md-block">
	<table class="table table-hover align-middle mb-0">
		<thead>
			<tr>
				<th style="width:56px">No</th>
				<th>Nomor Surat</th>
				<th>Jenis</th>
				<th>Tanggal</th>
				<th>Perihal</th>
				<th>Penerima/Tujuan</th>
				<th>Status</th>
				<th class="text-end">Aksi</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($letters as $i => $r): ?>
			<tr>
				<td class="text-muted"><?= $offset + $i + 1 ?></td>
				<td class="letter-number text-nowrap"><?= e($r['letter_number']) ?></td>
				<td><span class="badge text-bg-light border me-1" title="<?= e($r['type_name']) ?>"><?= e($r['type_code']) ?></span><span class="d-none d-xxl-inline"><?= e($r['type_name']) ?></span></td>
				<td class="text-nowrap"><?= e(tgl_indo($r['letter_date'])) ?></td>
				<td><?= e($r['subject']) ?></td>
				<td><?= e($r['recipient'] !== NULL && $r['recipient'] !== '' ? $r['recipient'] : '-') ?></td>
				<td class="text-nowrap"><?= status_badge($r['status']) ?>
					<?php if (isset($r['signature_type']) && $r['signature_type'] === 'basah'): ?>
						<span class="badge bg-warning-subtle text-warning border border-warning-subtle ms-1" title="Mode Tanda Tangan Basah (Kepsek)"><i class="fa-solid fa-pen-nib"></i> Basah</span>
					<?php elseif (isset($r['approval_status']) && $r['approval_status'] === 'disetujui'): ?>
						<span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1" title="Disetujui Kepsek (e-Sign Digital)"><i class="fa-solid fa-signature"></i> e-Sign</span>
					<?php endif; ?>
					<?php if (isset($r['approval_status']) && $r['approval_status'] === 'disetujui' && isset($r['stamp_type']) && $r['stamp_type'] === 'digital' && ! empty($r['stamped_at'])): ?>
						<span class="badge bg-danger-subtle text-danger border border-danger-subtle ms-1" title="Stempel Digital TU"><i class="fa-solid fa-stamp"></i> Stempel</span>
					<?php endif; ?>
					<?php if ((int) $r['pdf_count'] > 0): ?><i class="fa-solid fa-file-pdf text-danger ms-1" title="PDF arsip tersimpan"></i><?php endif; ?></td>
				<td class="text-end text-nowrap">
					<a class="btn btn-sm btn-light" href="<?= site_url('surat/detail/'.(int) $r['id']) ?>" title="Detail"><i class="fa-solid fa-eye"></i></a>
					<?php if ( ! is_pegawai()): ?>
						<a class="btn btn-sm btn-light" href="<?= site_url('surat/edit/'.(int) $r['id']) ?>" title="Edit"><i class="fa-solid fa-pen"></i></a>
					<?php endif; ?>
					<a class="btn btn-sm btn-light" href="<?= site_url('surat/pdf/'.(int) $r['id']) ?>" title="Unduh PDF"><i class="fa-solid fa-download"></i></a>
					<?php if ( ! is_pegawai()): ?>
						<?= form_open('surat/hapus/'.(int) $r['id'], array('class' => 'd-inline', 'data-confirm' => 'Hapus surat nomor '.$r['letter_number'].'? Data dan file PDF arsipnya akan dihapus permanen.')) ?>
							<input type="hidden" name="back" value="<?= e($back) ?>">
							<button type="submit" class="btn btn-sm btn-light text-danger" title="Hapus"><i class="fa-solid fa-trash"></i></button>
						<?= form_close() ?>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>

<!-- Tampilan Kartu Responsif Khusus Mobile (< 768px) -->
<div class="list-mobile d-md-none">
	<?php foreach ($letters as $i => $r): ?>
		<div class="list-mobile-item p-3">
			<div class="d-flex justify-content-between align-items-start gap-2 mb-2">
				<div class="min-w-0">
					<div class="letter-number fw-bold text-navy text-truncate"><?= e($r['letter_number']) ?></div>
					<div class="small text-muted mt-1">
						<span class="badge text-bg-light border me-1"><?= e($r['type_code']) ?></span>
						<span><?= e($r['type_name']) ?></span>
					</div>
				</div>
				<div class="text-end flex-shrink-0">
					<?= status_badge($r['status']) ?>
					<?php if (isset($r['signature_type']) && $r['signature_type'] === 'basah'): ?>
						<span class="badge bg-warning-subtle text-warning border border-warning-subtle d-block mt-1" style="font-size:0.68rem;"><i class="fa-solid fa-pen-nib"></i> Basah</span>
					<?php elseif (isset($r['approval_status']) && $r['approval_status'] === 'disetujui'): ?>
						<span class="badge bg-primary-subtle text-primary border border-primary-subtle d-block mt-1" style="font-size:0.68rem;"><i class="fa-solid fa-signature"></i> e-Sign</span>
					<?php endif; ?>
				</div>
			</div>

			<div class="fw-semibold text-dark mb-2"><?= e($r['subject']) ?></div>

			<div class="d-flex flex-wrap gap-2 text-muted small mb-3">
				<div><i class="fa-regular fa-calendar me-1"></i><?= e(tgl_indo($r['letter_date'])) ?></div>
				<?php if ($r['recipient'] !== NULL && $r['recipient'] !== ''): ?>
					<div>&middot; <i class="fa-solid fa-paper-plane me-1"></i><?= e($r['recipient']) ?></div>
				<?php endif; ?>
				<?php if ((int) $r['pdf_count'] > 0): ?>
					<div>&middot; <span class="text-danger fw-semibold"><i class="fa-solid fa-file-pdf me-1"></i>PDF Tersedia</span></div>
				<?php endif; ?>
			</div>

			<div class="d-flex align-items-center justify-content-between gap-2 pt-2 border-top">
				<a class="btn btn-sm btn-outline-primary flex-fill" href="<?= site_url('surat/detail/'.(int) $r['id']) ?>">
					<i class="fa-solid fa-eye me-1"></i> Detail
				</a>
				<?php if ( ! is_pegawai()): ?>
					<a class="btn btn-sm btn-outline-secondary flex-fill" href="<?= site_url('surat/edit/'.(int) $r['id']) ?>">
						<i class="fa-solid fa-pen me-1"></i> Edit
					</a>
				<?php endif; ?>
				<a class="btn btn-sm btn-light border flex-fill text-center" href="<?= site_url('surat/pdf/'.(int) $r['id']) ?>" title="Unduh PDF">
					<i class="fa-solid fa-download me-1"></i> PDF
				</a>
				<?php if ( ! is_pegawai()): ?>
					<?= form_open('surat/hapus/'.(int) $r['id'], array('class' => 'd-inline', 'data-confirm' => 'Hapus surat nomor '.$r['letter_number'].'? Data dan file PDF arsipnya akan dihapus permanen.')) ?>
						<input type="hidden" name="back" value="<?= e($back) ?>">
						<button type="submit" class="btn btn-sm btn-light border text-danger" title="Hapus"><i class="fa-solid fa-trash"></i></button>
					<?= form_close() ?>
				<?php endif; ?>
			</div>
		</div>
	<?php endforeach; ?>
</div>
