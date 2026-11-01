<?php defined('BASEPATH') OR exit('No direct script access allowed');
$val = function ($k) use ($type) { return set_value($k, isset($type[$k]) ? (string) $type[$k] : '', FALSE); };
$inv = function ($k) { return form_error($k) !== '' ? ' is-invalid' : ''; };
$fb  = function ($k) { $m = strip_tags(form_error($k)); return $m !== '' ? '<div class="invalid-feedback d-block">'.e($m).'</div>' : ''; };
$action = $type['id'] ? 'jenis-surat/edit/'.(int) $type['id'] : 'jenis-surat/tambah';
$active = $this->input->method(TRUE) === 'POST' ? (bool) $this->input->post('is_active') : (bool) $type['is_active'];
$public = $this->input->method(TRUE) === 'POST' ? (bool) $this->input->post('allow_public') : ! empty($type['allow_public']);
$current_tpl = $val('template_key');
?>
<?= form_open($action, array('novalidate' => 'novalidate')) ?>
<div class="row g-3">
	<div class="col-lg-5">
		<div class="form-section">
			<div class="form-section-title"><i class="fa-solid fa-tag"></i> Data Jenis Surat</div>
			<div class="form-section-body">
				<div class="mb-3">
					<label class="form-label required" for="name">Nama Jenis Surat</label>
					<input type="text" class="form-control<?= $inv('name') ?>" id="name" name="name" maxlength="150" value="<?= e($val('name')) ?>" required>
					<?= $fb('name') ?>
				</div>
				<div class="row g-3 mb-3">
					<div class="col-7">
						<label class="form-label required" for="code">Kode</label>
						<input type="text" class="form-control text-uppercase<?= $inv('code') ?>" id="code" name="code" maxlength="20" value="<?= e($val('code')) ?>" required>
						<?= $fb('code') ?>
						<div class="form-text">Dipakai pada token <span class="token">{KODE}</span> nomor surat.</div>
					</div>
					<div class="col-5">
						<label class="form-label" for="sort_order">Urutan</label>
						<input type="number" class="form-control<?= $inv('sort_order') ?>" id="sort_order" name="sort_order" min="0" max="9999" value="<?= e($val('sort_order')) ?>">
						<?= $fb('sort_order') ?>
					</div>
				</div>
				<div class="mb-3">
					<label class="form-label required" for="template_key">Template Formulir &amp; Dokumen</label>
					<select class="form-select<?= $inv('template_key') ?>" id="template_key" name="template_key" required <?= $used ? 'disabled' : '' ?>>
						<option value="">&mdash; Pilih template &mdash;</option>
						<?php foreach ($templates as $key => $tp): ?>
							<option value="<?= e($key) ?>" <?= $current_tpl === $key ? 'selected' : '' ?>><?= e($tp['label']) ?> (<?= $tp['layout'] === 'dinas' ? 'surat dinas' : 'surat keterangan' ?>)</option>
						<?php endforeach; ?>
					</select>
					<?php if ($used): ?>
						<input type="hidden" name="template_key" value="<?= e($type['template_key']) ?>">
						<div class="form-text">Template tidak dapat diubah karena sudah dipakai <?= (int) $used ?> surat.</div>
					<?php endif; ?>
					<?= $fb('template_key') ?>
				</div>
				<div class="mb-3">
					<label class="form-label" for="description">Keterangan</label>
					<input type="text" class="form-control<?= $inv('description') ?>" id="description" name="description" maxlength="255" value="<?= e($val('description')) ?>">
					<?= $fb('description') ?>
				</div>
				<div class="form-check form-switch">
					<input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" <?= $active ? 'checked' : '' ?>>
					<label class="form-check-label" for="is_active">Aktif (tampil di menu Buat Surat)</label>
				</div>
				<div class="form-check form-switch mt-2">
					<input class="form-check-input" type="checkbox" role="switch" id="allow_public" name="allow_public" value="1" <?= $public ? 'checked' : '' ?>>
					<label class="form-check-label" for="allow_public">Siswa dapat mengajukan sendiri lewat formulir permohonan online</label>
				</div>
				<div class="form-text">Aktifkan hanya untuk surat yang isinya tentang diri siswa, misalnya surat keterangan aktif, lulus, atau selesai PKL.</div>
			</div>
		</div>

		<?php if ($current_tpl && isset($templates[$current_tpl])): ?>
		<div class="form-section">
			<div class="form-section-title"><i class="fa-solid fa-code"></i> Placeholder Tersedia</div>
			<div class="form-section-body small">
				<p class="text-muted mb-2">Gunakan placeholder berikut pada teks surat; akan diganti otomatis dengan data.</p>
				<?php foreach (array('nama_sekolah', 'alamat_sekolah', 'kota', 'jabatan_penandatangan', 'nama_penandatangan', 'perihal', 'tujuan', 'nomor_surat', 'tanggal_surat') as $ph): ?>
					<span class="token d-inline-block mb-1">{<?= $ph ?>}</span>
				<?php endforeach; ?>
				<hr>
				<?php foreach ($templates[$current_tpl]['fields'] as $k => $f): if (isset($f['type']) && $f['type'] === 'rows') continue; ?>
					<div><span class="token">{<?= e($k) ?>}</span> <span class="text-muted"><?= e($f['label']) ?></span></div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php endif; ?>
	</div>

	<div class="col-lg-7">
		<div class="form-section">
			<div class="form-section-title"><i class="fa-solid fa-paragraph"></i> Teks Surat</div>
			<div class="form-section-body">
				<div class="mb-3">
					<label class="form-label" for="opening_text">Teks Pembuka</label>
					<textarea class="form-control<?= $inv('opening_text') ?>" id="opening_text" name="opening_text" rows="4" maxlength="3000"><?= e($val('opening_text')) ?></textarea>
					<?= $fb('opening_text') ?>
				</div>
				<div class="mb-3">
					<label class="form-label" for="body_text">Teks Isi (setelah data utama)</label>
					<textarea class="form-control<?= $inv('body_text') ?>" id="body_text" name="body_text" rows="4" maxlength="3000"><?= e($val('body_text')) ?></textarea>
					<?= $fb('body_text') ?>
				</div>
				<div class="mb-1">
					<label class="form-label" for="closing_text">Teks Penutup</label>
					<textarea class="form-control<?= $inv('closing_text') ?>" id="closing_text" name="closing_text" rows="3" maxlength="3000"><?= e($val('closing_text')) ?></textarea>
					<?= $fb('closing_text') ?>
				</div>
				<div class="form-text">Perubahan teks berlaku untuk semua surat jenis ini, termasuk saat PDF surat lama dibuat ulang.</div>
			</div>
			<div class="sticky-actions d-flex gap-2 justify-content-end">
				<a href="<?= site_url('jenis-surat') ?>" class="btn btn-light me-auto">Batal</a>
				<button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i> Simpan</button>
			</div>
		</div>
	</div>
</div>
<?= form_close() ?>
