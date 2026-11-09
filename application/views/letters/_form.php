<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Formulir surat (dipakai create.php & edit.php).
 */
$v = function ($key, $default = '') use ($val) {
	return isset($val[$key]) && ! is_array($val[$key]) ? (string) $val[$key] : $default;
};
$err = function ($key) { return form_error($key, '', ''); };
$inv = function ($key) use ($err) { return $err($key) !== '' ? ' is-invalid' : ''; };
$fb  = function ($key) use ($err) { $m = $err($key); return $m !== '' ? '<div class="invalid-feedback d-block">'.e(strip_tags($m)).'</div>' : ''; };

$is_edit = $mode === 'edit';
$action  = $is_edit ? 'surat/update/'.(int) $letter['id'] : 'surat/simpan/'.(int) $type['id'];
$number_mode = $v('number_mode', $is_edit ? 'keep' : 'auto');
$has_errors = (validation_errors() !== '' || ! empty($f_errors));
?>

<?php if ($has_errors): ?>
	<div class="alert alert-danger">
		<i class="fa-solid fa-circle-xmark me-1"></i> <strong>Data belum valid.</strong> Periksa kembali isian yang ditandai merah.
	</div>
<?php endif; ?>

<?php if ( ! empty($request)): ?>
	<div class="alert alert-info d-flex flex-wrap align-items-center gap-2">
		<i class="fa-solid fa-inbox"></i>
		<div class="flex-grow-1">Formulir diisi dari <strong>permohonan siswa <?= e($request['tracking_code']) ?></strong> (<?= e($request['requester_name']) ?>). Periksa dan perbaiki data bila perlu. Setelah disimpan, status permohonan menjadi <strong>Siap Diambil</strong>.</div>
		<a class="btn btn-sm btn-light" href="<?= site_url('permohonan-masuk/'.(int) $request['id']) ?>">Lihat permohonan</a>
	</div>
<?php endif; ?>

<?= form_open($action, array('id' => 'letterForm', 'novalidate' => 'novalidate')) ?>
	<?php if ( ! empty($request)): ?><input type="hidden" name="request_id" value="<?= (int) $request['id'] ?>"><?php endif; ?>
	<input type="hidden" name="letter_type_id" value="<?= (int) $type['id'] ?>">
	<input type="hidden" name="letter_id" value="<?= $is_edit ? (int) $letter['id'] : 0 ?>">

	<!-- 1. Informasi surat -->
	<div class="form-section">
		<div class="form-section-title"><i class="fa-solid fa-circle-info"></i> Informasi Surat
			<span class="badge text-bg-light border ms-auto"><?= e($type['code']) ?> &middot; <?= e($type['name']) ?></span>
		</div>
		<div class="form-section-body">
			<div class="row g-3">
				<div class="col-md-4">
					<label class="form-label required" for="letter_date">Tanggal Surat</label>
					<input type="date" class="form-control<?= $inv('letter_date') ?>" id="letter_date" name="letter_date" value="<?= e($v('letter_date')) ?>" required>
					<?= $fb('letter_date') ?>
				</div>
				<div class="col-md-4">
					<label class="form-label required" for="place">Tempat Surat</label>
					<input type="text" class="form-control<?= $inv('place') ?>" id="place" name="place" maxlength="100" value="<?= e($v('place')) ?>" placeholder="Kota/kabupaten" required>
					<?= $fb('place') ?>
				</div>
				<?php if ($tpl['attachment']): ?>
				<div class="col-md-4">
					<label class="form-label" for="attachment_text">Lampiran</label>
					<input type="text" class="form-control<?= $inv('attachment_text') ?>" id="attachment_text" name="attachment_text" maxlength="100" value="<?= e($v('attachment_text')) ?>" placeholder="Contoh: 1 (satu) berkas atau -">
					<?= $fb('attachment_text') ?>
				</div>
				<?php endif; ?>

				<div class="col-12">
					<label class="form-label required" for="subject">Perihal</label>
					<input type="text" class="form-control<?= $inv('subject') ?>" id="subject" name="subject" maxlength="255" value="<?= e($v('subject')) ?>" required>
					<?= $fb('subject') ?>
				</div>

				<div class="col-12">
					<label class="form-label">Nomor Surat</label>
					<div class="d-flex flex-wrap gap-3 mb-2">
						<?php if ($is_edit): ?>
							<div class="form-check">
								<input class="form-check-input" type="radio" name="number_mode" id="nm_keep" value="keep" <?= $number_mode !== 'manual' ? 'checked' : '' ?>>
								<label class="form-check-label" for="nm_keep">Tetap (tidak berubah)</label>
							</div>
						<?php else: ?>
							<div class="form-check">
								<input class="form-check-input" type="radio" name="number_mode" id="nm_auto" value="auto" <?= $number_mode !== 'manual' ? 'checked' : '' ?>>
								<label class="form-check-label" for="nm_auto">Otomatis</label>
							</div>
						<?php endif; ?>
						<div class="form-check">
							<input class="form-check-input" type="radio" name="number_mode" id="nm_manual" value="manual" <?= $number_mode === 'manual' ? 'checked' : '' ?>>
							<label class="form-check-label" for="nm_manual">Isi manual</label>
						</div>
					</div>
					<div id="autoNumberBox" class="p-2 px-3 rounded bg-light border small">
						<?php if ($is_edit): ?>
							Nomor saat ini: <span class="letter-number"><?= e($letter['letter_number']) ?></span>
						<?php else: ?>
							Perkiraan nomor: <span class="letter-number" id="nextNumber" data-url="<?= site_url('surat/nomor-berikutnya/'.(int) $type['id']) ?>"><?= e($next_number) ?></span>
							<span class="text-muted">&mdash; nomor final ditetapkan saat disimpan.</span>
						<?php endif; ?>
					</div>
					<div id="manualNumberBox" class="d-none">
						<input type="text" class="form-control<?= $inv('letter_number') ?>" name="letter_number" maxlength="100"
							value="<?= e($v('letter_number', $is_edit ? $letter['letter_number'] : '')) ?>" placeholder="Masukkan nomor surat lengkap">
						<?= $fb('letter_number') ?>
						<div class="form-text">Sistem menolak nomor yang sudah dipakai surat lain.</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- 2. Tujuan surat -->
	<?php if ($tpl['recipient'] !== 'hidden'): ?>
	<div class="form-section">
		<div class="form-section-title"><i class="fa-solid fa-location-dot"></i> Tujuan Surat</div>
		<div class="form-section-body">
			<div class="row g-3">
				<div class="col-md-6">
					<label class="form-label <?= $tpl['recipient'] === 'required' ? 'required' : '' ?>" for="recipient">Kepada Yth.</label>
					<input type="text" class="form-control<?= $inv('recipient') ?>" id="recipient" name="recipient" maxlength="255" value="<?= e($v('recipient')) ?>" placeholder="Nama/jabatan penerima">
					<?= $fb('recipient') ?>
				</div>
				<div class="col-md-6">
					<label class="form-label" for="recipient_address">Alamat/Tempat Tujuan</label>
					<input type="text" class="form-control<?= $inv('recipient_address') ?>" id="recipient_address" name="recipient_address" maxlength="255" value="<?= e($v('recipient_address')) ?>" placeholder="Kosongkan untuk mencetak &quot;di Tempat&quot;">
					<?= $fb('recipient_address') ?>
				</div>
			</div>
		</div>
	</div>
	<?php endif; ?>

	<!-- 3. Data khusus jenis surat -->
	<div class="form-section">
		<div class="form-section-title"><i class="fa-solid fa-pen-to-square"></i> Isi <?= e($type['name']) ?></div>
		<div class="form-section-body">
			<div class="row g-3">
				<?php foreach ($tpl['fields'] as $name => $field): ?>
					<?php $this->load->view('letters/_field', array(
						'name'  => $name,
						'field' => $field,
						'value' => isset($f[$name]) ? $f[$name] : '',
						'error' => isset($f_errors[$name]) ? $f_errors[$name] : NULL,
					)); ?>
				<?php endforeach; ?>
			</div>
		</div>
	</div>

	<!-- 4. Penandatangan -->
	<div class="form-section">
		<div class="form-section-title"><i class="fa-solid fa-signature"></i> Penandatangan</div>
		<div class="form-section-body">
			<div class="row g-3">
				<div class="col-md-4">
					<label class="form-label required" for="signer_title">Jabatan</label>
					<input type="text" class="form-control<?= $inv('signer_title') ?>" id="signer_title" name="signer_title" maxlength="150" value="<?= e($v('signer_title')) ?>" required>
					<?= $fb('signer_title') ?>
				</div>
				<div class="col-md-5">
					<label class="form-label required" for="signer_name">Nama</label>
					<input type="text" class="form-control<?= $inv('signer_name') ?>" id="signer_name" name="signer_name" maxlength="150" value="<?= e($v('signer_name')) ?>" required>
					<?= $fb('signer_name') ?>
					<?php if (trim($school['headmaster_name']) === ''): ?>
						<div class="form-text text-warning-emphasis"><i class="fa-solid fa-triangle-exclamation"></i> Nama kepala sekolah belum diatur di <a href="<?= site_url('pengaturan') ?>">Pengaturan Sekolah</a>.</div>
					<?php endif; ?>
				</div>
				<div class="col-md-3">
					<label class="form-label" for="signer_nip">NIP</label>
					<input type="text" class="form-control<?= $inv('signer_nip') ?>" id="signer_nip" name="signer_nip" maxlength="30" value="<?= e($v('signer_nip')) ?>" placeholder="Opsional">
					<?= $fb('signer_nip') ?>
				</div>
				<!-- Pemisahan Wewenang: TTD Kepala Sekolah vs Stempel TU -->
				<div class="col-12">
					<div class="p-3 bg-light rounded-3 border mb-3">
						<div class="d-flex align-items-center gap-2 mb-1">
							<i class="fa-solid fa-signature text-primary fs-5"></i>
							<strong class="text-navy">Wewenang Tanda Tangan: Kepala Sekolah</strong>
						</div>
						<div class="small text-muted ps-4">
							Surat keluar akan diajukan ke Kepala Sekolah untuk diperiksa. <strong>Kepala Sekolah yang berhak memutuskan dan memilih</strong> apakah menandatangani secara Digital (e-Sign) atau Tanda Tangan Basah Fisik saat menyetujui surat.
						</div>
					</div>

					<div class="alert alert-secondary border d-flex align-items-center gap-3 mb-0 py-2 small">
						<i class="fa-solid fa-stamp text-danger fs-4"></i>
						<div>
							<strong class="text-navy">Pengaturan Stempel Resmi Sekolah (Wewenang Tata Usaha):</strong><br>
							Cap stempel resmi sekolah <strong>baru dibubuhkan oleh staf TU setelah surat disahkan/ditandatangani oleh Kepala Sekolah</strong> melalui halaman detail surat.
						</div>
					</div>
					<input type="hidden" name="stamp_type" value="<?= ($letter && isset($letter['stamp_type'])) ? e($letter['stamp_type']) : 'belum_stempel' ?>">
				</div>

				<div class="col-12">
					<label class="form-label" for="notes">Catatan Internal <span class="text-muted fw-normal">(tidak dicetak)</span></label>
					<input type="text" class="form-control<?= $inv('notes') ?>" id="notes" name="notes" maxlength="500" value="<?= e($v('notes')) ?>" placeholder="Misal: Perlu diambil hari Senin oleh wali murid">
					<?= $fb('notes') ?>
				</div>
			</div>
		</div>
		<div class="sticky-actions d-flex flex-wrap gap-2 justify-content-end">
			<a href="<?= $is_edit ? site_url('surat/detail/'.(int) $letter['id']) : site_url('surat/buat') ?>" class="btn btn-light me-auto"><i class="fa-solid fa-arrow-left me-1"></i> Batal</a>
			<button type="button" class="btn btn-outline-primary" data-preview-action="<?= site_url('surat/pratinjau') ?>"><i class="fa-solid fa-eye me-1"></i> Pratinjau</button>
			<button type="submit" name="save_as" value="draft" class="btn btn-outline-secondary"><i class="fa-regular fa-floppy-disk me-1"></i> Simpan Draft</button>
			<?php if (is_kepala_sekolah()): ?>
				<button type="submit" name="save_as" value="final" class="btn btn-primary"><i class="fa-solid fa-signature me-1"></i> Sahkan &amp; Tandatangani (Kepsek)</button>
			<?php else: ?>
				<!-- TU, Admin & Pegawai Wajib Mengajukan ke Kepala Sekolah untuk Ditandatangani -->
				<button type="submit" name="save_as" value="approval" class="btn btn-warning text-dark fw-bold"><i class="fa-solid fa-paper-plane me-1"></i> Ajukan ke Kepala Sekolah (TTD)</button>
			<?php endif; ?>
		</div>
	</div>
<?= form_close() ?>
