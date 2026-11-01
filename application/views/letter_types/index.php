<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="card">
	<div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
		<h3 class="card-title"><i class="fa-solid fa-layer-group me-1"></i> Master Jenis Surat</h3>
		<a href="<?= site_url('jenis-surat/tambah') ?>" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus me-1"></i> Tambah Jenis Surat</a>
	</div>
	<div class="table-responsive">
		<table class="table table-hover mb-0">
			<thead><tr><th style="width:60px">Urut</th><th>Kode</th><th>Nama Jenis Surat</th><th>Template</th><th class="text-center">Jumlah Surat</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
			<tbody>
			<?php foreach ($types as $t): ?>
				<tr>
					<td class="text-muted"><?= (int) $t['sort_order'] ?></td>
					<td><span class="badge text-bg-light border"><?= e($t['code']) ?></span></td>
					<td><div class="fw-semibold"><?= e($t['name']) ?></div><?php if ($t['description']): ?><small class="text-muted"><?= e($t['description']) ?></small><?php endif; ?></td>
					<td class="small"><?= isset($templates[$t['template_key']]) ? e($templates[$t['template_key']]) : '<span class="text-danger">Tidak ditemukan ('.e($t['template_key']).')</span>' ?></td>
					<td class="text-center"><?= (int) $t['letter_count'] ?></td>
					<td><?= (int) $t['is_active'] ? '<span class="badge rounded-pill text-bg-success">Aktif</span>' : '<span class="badge rounded-pill text-bg-secondary">Nonaktif</span>' ?>
						<?php if ( ! empty($t['allow_public'])): ?><span class="badge rounded-pill text-bg-info" title="Siswa dapat mengajukan lewat formulir online"><i class="fa-solid fa-user-graduate"></i> Siswa</span><?php endif; ?></td>
					<td class="text-end text-nowrap">
						<a class="btn btn-sm btn-light" href="<?= site_url('jenis-surat/edit/'.(int) $t['id']) ?>" title="Edit"><i class="fa-solid fa-pen"></i></a>
						<?php if ((int) $t['letter_count'] === 0): ?>
						<?= form_open('jenis-surat/hapus/'.(int) $t['id'], array('class' => 'd-inline', 'data-confirm' => 'Hapus jenis surat "'.$t['name'].'"?')) ?>
							<button class="btn btn-sm btn-light text-danger" type="submit" title="Hapus"><i class="fa-solid fa-trash"></i></button>
						<?= form_close() ?>
						<?php else: ?>
							<button class="btn btn-sm btn-light text-muted" type="button" disabled title="Sudah dipakai surat"><i class="fa-solid fa-trash"></i></button>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<div class="card-body border-top small text-muted">
		<i class="fa-solid fa-code me-1"></i> Developer dapat menambah template baru melalui <code>application/config/letter_templates.php</code> dan
		<code>application/views/letters/templates/</code>. Lihat README bagian "Menambah template surat".
	</div>
</div>
