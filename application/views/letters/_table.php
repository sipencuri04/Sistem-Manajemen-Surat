<?php defined('BASEPATH') OR exit('No direct script access allowed');
/** Tabel surat (dipakai Data Surat & Arsip). @var array $letters @var int $offset @var string $back */
?>
<div class="table-responsive">
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
					<a class="btn btn-sm btn-light" href="<?= site_url('surat/edit/'.(int) $r['id']) ?>" title="Edit"><i class="fa-solid fa-pen"></i></a>
					<a class="btn btn-sm btn-light" href="<?= site_url('surat/pdf/'.(int) $r['id']) ?>" title="Unduh PDF"><i class="fa-solid fa-download"></i></a>
					<?= form_open('surat/hapus/'.(int) $r['id'], array('class' => 'd-inline', 'data-confirm' => 'Hapus surat nomor '.$r['letter_number'].'? Data dan file PDF arsipnya akan dihapus permanen.')) ?>
						<input type="hidden" name="back" value="<?= e($back) ?>">
						<button type="submit" class="btn btn-sm btn-light text-danger" title="Hapus"><i class="fa-solid fa-trash"></i></button>
					<?= form_close() ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>
