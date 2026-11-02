<?php defined('BASEPATH') OR exit('No direct script access allowed');
$val = function ($k) use ($s) { return set_value($k, isset($s[$k]) && $s[$k] !== NULL ? (string) $s[$k] : '', FALSE); };
$inv = function ($k) { return form_error($k) !== '' ? ' is-invalid' : ''; };
$fb  = function ($k) { $m = strip_tags(form_error($k)); return $m !== '' ? '<div class="invalid-feedback d-block">'.e($m).'</div>' : ''; };
$sel = function ($k, $opt) use ($val) { return $val($k) === (string) $opt ? 'selected' : ''; };
$text = function ($k, $label, $opts = array()) use ($val, $inv, $fb) {
	$req = ! empty($opts['required']);
	$h  = '<label class="form-label'.($req ? ' required' : '').'" for="'.$k.'">'.e($label).'</label>';
	$h .= '<input type="'.(isset($opts['type']) ? $opts['type'] : 'text').'" class="form-control'.$inv($k).'" id="'.$k.'" name="'.$k.'" value="'.e($val($k)).'"'
		.(isset($opts['max']) ? ' maxlength="'.(int) $opts['max'].'"' : '')
		.(isset($opts['ph']) ? ' placeholder="'.e($opts['ph']).'"' : '').($req ? ' required' : '').'>';
	$h .= $fb($k);
	if (isset($opts['help'])) $h .= '<div class="form-text">'.$opts['help'].'</div>';
	return $h;
};
?>
<?= form_open_multipart('pengaturan', array('novalidate' => 'novalidate')) ?>
<div class="row g-3">
	<div class="col-xl-7">
		<div class="form-section">
			<div class="form-section-title"><i class="fa-solid fa-school"></i> Identitas Sekolah</div>
			<div class="form-section-body">
				<div class="row g-3">
					<div class="col-12"><?= $text('school_name', 'Nama Sekolah', array('required' => TRUE, 'max' => 200)) ?></div>
					<div class="col-md-6"><?= $text('kop_line1', 'Baris Kop 1 (instansi induk)', array('max' => 200, 'help' => 'Opsional, tampil di atas nama sekolah.')) ?></div>
					<div class="col-md-6"><?= $text('kop_line2', 'Baris Kop 2', array('max' => 200, 'help' => 'Opsional.')) ?></div>
					<div class="col-md-4"><?= $text('npsn', 'NPSN', array('max' => 20)) ?></div>
					<div class="col-md-4"><?= $text('school_code', 'Kode Singkat Sekolah', array('max' => 30, 'help' => 'Untuk token <span class="token">{SEKOLAH}</span>.')) ?></div>
					<div class="col-md-4"><?= $text('city', 'Kota/Kabupaten', array('required' => TRUE, 'max' => 100, 'help' => 'Tempat penerbitan surat.')) ?></div>
					<div class="col-12">
						<label class="form-label" for="address">Alamat</label>
						<textarea class="form-control<?= $inv('address') ?>" id="address" name="address" rows="2" maxlength="500"><?= e($val('address')) ?></textarea>
						<?= $fb('address') ?>
					</div>
					<div class="col-md-3"><?= $text('postal_code', 'Kode Pos', array('max' => 10)) ?></div>
					<div class="col-md-4"><?= $text('phone', 'No. WhatsApp / Telepon TU', array('max' => 50, 'help' => 'Nomor resmi TU untuk menerima konfirmasi WA dari siswa/instansi luar.')) ?></div>
					<div class="col-md-5"><?= $text('email', 'Email', array('max' => 150, 'type' => 'email')) ?></div>
					<div class="col-12"><?= $text('website', 'Website', array('max' => 150)) ?></div>
				</div>
			</div>
		</div>

		<div class="form-section">
			<div class="form-section-title"><i class="fa-solid fa-signature"></i> Penandatangan</div>
			<div class="form-section-body">
				<div class="row g-3">
					<div class="col-md-6"><?= $text('headmaster_name', 'Nama Kepala Sekolah / Pejabat', array('required' => TRUE, 'max' => 150)) ?></div>
					<div class="col-md-6"><?= $text('headmaster_nip', 'NIP', array('max' => 30, 'help' => 'Kosongkan bila tidak ada.')) ?></div>
					<div class="col-md-6"><?= $text('signer_title', 'Jabatan Penandatangan', array('required' => TRUE, 'max' => 150)) ?></div>
					<div class="col-md-6 d-flex align-items-end">
						<div class="form-check form-switch mb-2">
							<?php $show_nip = $this->input->method(TRUE) === 'POST' ? (bool) $this->input->post('show_nip') : (bool) $s['show_nip']; ?>
							<input class="form-check-input" type="checkbox" role="switch" id="show_nip" name="show_nip" value="1" <?= $show_nip ? 'checked' : '' ?>>
							<label class="form-check-label" for="show_nip">Tampilkan NIP di bawah nama</label>
						</div>
					</div>
				</div>
				<p class="small text-muted mt-3 mb-0"><i class="fa-solid fa-circle-info me-1"></i> Nilai ini menjadi isian awal setiap surat baru dan tetap dapat diubah per surat.</p>
			</div>
		</div>

		<div class="form-section">
			<div class="form-section-title"><i class="fa-solid fa-hashtag"></i> Format Penomoran Surat</div>
			<div class="form-section-body">
				<div class="mb-3">
					<label class="form-label required" for="number_format">Format Nomor</label>
					<input type="text" class="form-control font-monospace<?= $inv('number_format') ?>" id="number_format" name="number_format" maxlength="150" value="<?= e($val('number_format')) ?>" required>
					<?= $fb('number_format') ?>
					<div class="form-text">Klik token untuk menyisipkan:
						<?php foreach (array('{NO}' => 'nomor urut', '{NO_RAW}' => 'nomor tanpa nol', '{KODE}' => 'kode jenis surat', '{SEKOLAH}' => 'kode sekolah', '{NPSN}' => 'NPSN', '{BULAN}' => 'bulan 01-12', '{BULAN_ROMAWI}' => 'bulan I-XII', '{TAHUN}' => 'tahun') as $tok => $desc): ?>
							<span class="token d-inline-block mb-1" data-token="<?= e($tok) ?>" title="<?= e($desc) ?>"><?= e($tok) ?></span>
						<?php endforeach; ?>
					</div>
					<?php if ($example): ?>
						<div class="small mt-2">Contoh (tersimpan) untuk <?= e($example_type['name']) ?>: <span class="letter-number"><?= e($example) ?></span></div>
					<?php endif; ?>
				</div>
				<div class="row g-3">
					<div class="col-md-3">
						<label class="form-label required" for="number_padding">Digit No. Urut</label>
						<select class="form-select<?= $inv('number_padding') ?>" id="number_padding" name="number_padding">
							<?php for ($i = 1; $i <= 6; $i++): ?><option value="<?= $i ?>" <?= $sel('number_padding', $i) ?>><?= $i ?> (<?= str_pad('1', $i, '0', STR_PAD_LEFT) ?>)</option><?php endfor; ?>
						</select>
					</div>
					<div class="col-md-3">
						<label class="form-label required" for="number_reset">Nomor Urut Diulang</label>
						<select class="form-select<?= $inv('number_reset') ?>" id="number_reset" name="number_reset">
							<option value="yearly" <?= $sel('number_reset', 'yearly') ?>>Setiap tahun</option>
							<option value="monthly" <?= $sel('number_reset', 'monthly') ?>>Setiap bulan</option>
							<option value="never" <?= $sel('number_reset', 'never') ?>>Tidak pernah</option>
						</select>
					</div>
					<div class="col-md-3">
						<label class="form-label required" for="number_scope">Urutan Nomor</label>
						<select class="form-select<?= $inv('number_scope') ?>" id="number_scope" name="number_scope">
							<option value="global" <?= $sel('number_scope', 'global') ?>>Satu urutan untuk semua jenis</option>
							<option value="per_type" <?= $sel('number_scope', 'per_type') ?>>Terpisah per jenis surat</option>
						</select>
					</div>
					<div class="col-md-3">
						<?= $text('letter_year', 'Tahun Surat', array('type' => 'number', 'ph' => 'Ikuti tanggal', 'help' => 'Kosongkan agar mengikuti tanggal surat.')) ?>
					</div>
				</div>
				<div class="alert alert-light border small mt-3 mb-0">
					<i class="fa-solid fa-shield-halved me-1 text-primary"></i> Nomor urut tersimpan di database dan tidak pernah mundur, sehingga nomor yang sudah terpakai (termasuk dari surat yang dihapus) tidak dipakai ulang secara otomatis. Mengubah format tidak mengubah nomor surat yang sudah ada.
				</div>
			</div>
		</div>

		<div class="form-section" id="email_section">
			<div class="form-section-title d-flex align-items-center justify-content-between">
				<span><i class="fa-solid fa-envelope text-primary"></i> Email Notifikasi (Gmail TU / SMTP)</span>
				<?php if (!empty($s['smtp_enabled']) && !empty($s['smtp_user']) && !empty($s['smtp_pass'])): ?>
					<span class="badge bg-success"><i class="fa-solid fa-circle-check me-1"></i> Terhubung &amp; Aktif</span>
				<?php elseif (!empty($s['smtp_user']) && empty($s['smtp_pass'])): ?>
					<span class="badge bg-warning text-dark"><i class="fa-solid fa-key me-1"></i> Sandi Belum Tersimpan</span>
				<?php elseif (empty($s['smtp_enabled']) && !empty($s['smtp_user'])): ?>
					<span class="badge bg-secondary"><i class="fa-solid fa-power-off me-1"></i> Dinonaktifkan</span>
				<?php else: ?>
					<span class="badge bg-warning text-dark"><i class="fa-solid fa-triangle-exclamation me-1"></i> Belum Dikonfigurasi</span>
				<?php endif; ?>
			</div>
			<div class="form-section-body">
				<div class="alert alert-info border-0 shadow-sm small mb-3">
					<div class="d-flex gap-2">
						<i class="fa-solid fa-circle-info fs-5 mt-1 text-primary"></i>
						<div>
							<strong>Integrasi Gmail TU:</strong> Mengirim notifikasi surat masuk, disposisi, &amp; persetujuan surat secara otomatis ke akun Gmail guru, pegawai, &amp; Kepala Sekolah.
							<br>Gunakan <strong>Sandi Aplikasi (App Password)</strong> 16 karakter dari Google Account, bukan password login biasa.
						</div>
					</div>
				</div>

				<div class="form-check form-switch mb-3">
					<?php $smtp_enabled = $this->input->method(TRUE) === 'POST' ? (bool) $this->input->post('smtp_enabled') : (isset($s['smtp_enabled']) ? (bool) $s['smtp_enabled'] : TRUE); ?>
					<input class="form-check-input" type="checkbox" role="switch" id="smtp_enabled" name="smtp_enabled" value="1" <?= $smtp_enabled ? 'checked' : '' ?>>
					<label class="form-check-label fw-semibold" for="smtp_enabled">Aktifkan Pengiriman Email Notifikasi</label>
				</div>

				<div class="row g-3">
					<div class="col-md-6">
						<?= $text('smtp_user', 'Alamat Gmail TU (Pengirim)', array('type' => 'email', 'ph' => 'persuratan.smk@gmail.com', 'help' => 'Alamat Gmail resmi sekolah/TU.')) ?>
					</div>
					<div class="col-md-6">
						<label class="form-label" for="smtp_pass">Sandi Aplikasi Google (App Password)</label>
						<div class="input-group">
							<input type="password" class="form-control<?= $inv('smtp_pass') ?>" id="smtp_pass" name="smtp_pass" value="<?= e($val('smtp_pass')) ?>" placeholder="<?= !empty($s['smtp_pass']) ? '•••••••••••••••• (Tersimpan)' : '16 huruf App Password' ?>" autocomplete="new-password">
							<button class="btn btn-outline-secondary" type="button" id="toggle_smtp_pass" title="Tampilkan / Sembunyikan Password"><i class="fa-solid fa-eye"></i></button>
						</div>
						<?= $fb('smtp_pass') ?>
						<div class="form-text">Dibuat dari <em>Akun Google &rarr; Keamanan &rarr; Sandi Aplikasi</em>. Kosongkan bila tidak diubah.</div>
					</div>
					<div class="col-12">
						<?= $text('smtp_from_name', 'Nama Pengirim yang Ditampilkan', array('ph' => 'SMK Muhammadiyah Bandongan', 'help' => 'Nama instansi/sekolah yang akan muncul di kotak masuk email penerima.')) ?>
					</div>
				</div>

				<div class="accordion mt-3" id="smtpAdvancedAccordion">
					<div class="accordion-item border-0 bg-light rounded-3">
						<h2 class="accordion-header">
							<button class="accordion-button collapsed bg-light py-2 text-muted small fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#smtpAdvancedCollapse">
								<i class="fa-solid fa-sliders me-2"></i> Pengaturan SMTP Lanjutan (Opsional)
							</button>
						</h2>
						<div id="smtpAdvancedCollapse" class="accordion-collapse collapse" data-bs-parent="#smtpAdvancedAccordion">
							<div class="accordion-body pt-2">
								<div class="row g-2">
									<div class="col-md-5">
										<?= $text('smtp_host', 'Host SMTP', array('ph' => 'smtp.gmail.com', 'help' => 'Default Gmail: smtp.gmail.com')) ?>
									</div>
									<div class="col-md-3">
										<?= $text('smtp_port', 'Port SMTP', array('type' => 'number', 'ph' => '587', 'help' => 'Default: 587 (TLS) atau 465 (SSL)')) ?>
									</div>
									<div class="col-md-4">
										<label class="form-label" for="smtp_crypto">Protokol Enkripsi</label>
										<select class="form-select" id="smtp_crypto" name="smtp_crypto">
											<option value="tls" <?= $sel('smtp_crypto', 'tls') ?>>TLS (Port 587)</option>
											<option value="ssl" <?= $sel('smtp_crypto', 'ssl') ?>>SSL (Port 465)</option>
											<option value="none" <?= $sel('smtp_crypto', 'none') ?>>None (Tanpa Enkripsi)</option>
										</select>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mt-3 pt-3 border-top">
					<div class="small text-muted">
						<i class="fa-solid fa-shield-halved text-success me-1"></i> Sandi disimpan aman di database aplikasi.
					</div>
					<div class="d-flex gap-2">
						<button type="submit" name="save_email_config" value="1" class="btn btn-primary fw-bold shadow-sm">
							<i class="fa-solid fa-floppy-disk me-1"></i> Simpan Konfigurasi Email
						</button>
						<button type="button" class="btn btn-outline-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#testEmailModal">
							<i class="fa-solid fa-paper-plane me-1"></i> Uji Coba Kirim Email
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-xl-5">
		<div class="form-section">
			<div class="form-section-title"><i class="fa-solid fa-heading"></i> Template Kop Surat</div>
			<div class="form-section-body">
				<select class="form-select<?= $inv('kop_template') ?>" id="kop_template" name="kop_template" aria-label="Template kop">
					<option value="logo_kiri" <?= $sel('kop_template', 'logo_kiri') ?>>Logo di kiri</option>
					<option value="logo_kiri_kanan" <?= $sel('kop_template', 'logo_kiri_kanan') ?>>Logo kiri &amp; kanan</option>
					<option value="tanpa_logo" <?= $sel('kop_template', 'tanpa_logo') ?>>Tanpa logo (teks di tengah)</option>
				</select>
				<?= $fb('kop_template') ?>
			</div>
		</div>

		<div class="form-section">
			<div class="form-section-title"><i class="fa-solid fa-image"></i> Logo, Tanda Tangan &amp; Stempel</div>
			<div class="form-section-body">
				<p class="small text-muted">Format PNG/JPG, maksimal 5 MB. Gunakan hanya gambar resmi dari pihak sekolah. Jika tanda tangan/stempel tidak diunggah, PDF menyediakan ruang kosong untuk ditandatangani basah.</p>
				<?php foreach ($images as $field => $meta): ?>
					<div class="border rounded-3 p-3 mb-3">
						<label class="form-label" for="<?= e($field) ?>"><?= e($meta[0]) ?></label>
						<div class="d-flex align-items-center gap-3 mb-2">
							<?php if ( ! empty($s[$field])): ?>
								<img class="img-setting" id="prev_<?= e($field) ?>" src="<?= site_url('media/'.$meta[1]) ?>?v=<?= e(substr(md5($s[$field]), 0, 8)) ?>" alt="<?= e($meta[0]) ?>">
								<button type="submit" class="btn btn-sm btn-outline-danger" form="rm_<?= e($field) ?>"><i class="fa-solid fa-trash me-1"></i> Hapus</button>
							<?php else: ?>
								<img class="img-setting d-none" id="prev_<?= e($field) ?>" alt="">
								<span class="small text-muted">Belum ada gambar.</span>
							<?php endif; ?>
						</div>
						<input type="file" class="form-control form-control-sm<?= isset($upload_errors[$field]) ? ' is-invalid' : '' ?>" id="<?= e($field) ?>" name="<?= e($field) ?>" accept="image/png,image/jpeg" data-preview="#prev_<?= e($field) ?>">
						<?php if (isset($upload_errors[$field])): ?><div class="invalid-feedback d-block"><?= e($upload_errors[$field]) ?></div><?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="d-grid">
			<button type="submit" class="btn btn-primary btn-lg"><i class="fa-solid fa-floppy-disk me-1"></i> Simpan Pengaturan</button>
		</div>
	</div>
</div>
<?= form_close() ?>

<?php foreach ($images as $field => $meta): if (empty($s[$field])) continue; ?>
	<?= form_open('pengaturan/hapus-gambar/'.$field, array('id' => 'rm_'.$field, 'data-confirm' => 'Hapus '.$meta[0].'?')) ?><?= form_close() ?>
<?php endforeach; ?>

<!-- Modal Uji Coba Kirim Email -->
<div class="modal fade" id="testEmailModal" tabindex="-1" aria-labelledby="testEmailModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content shadow border-0">
			<div class="modal-header border-bottom">
				<h5 class="modal-title fs-6 fw-bold text-navy" id="testEmailModalLabel"><i class="fa-solid fa-paper-plane text-primary me-2"></i>Uji Coba Kirim Email</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
			</div>
			<?= form_open('pengaturan/tes-email', array('id' => 'formTestEmail')) ?>
				<input type="hidden" name="test_smtp_user" id="test_smtp_user" value="">
				<input type="hidden" name="test_smtp_pass" id="test_smtp_pass" value="">
				<input type="hidden" name="test_smtp_host" id="test_smtp_host" value="">
				<input type="hidden" name="test_smtp_port" id="test_smtp_port" value="">
				<input type="hidden" name="test_smtp_crypto" id="test_smtp_crypto" value="">
				<div class="modal-body p-4">
					<p class="small text-muted mb-3">Sistem akan melakukan koneksi ke Gmail TU dan mengirim email uji coba untuk memverifikasi apakah akun dan sandi aplikasi sudah bekerja.</p>
					
					<div class="p-2 mb-3 rounded bg-light border text-secondary small" id="test_sender_preview">
						<i class="fa-solid fa-circle-user text-primary me-1"></i> Akun Pengirim: <strong class="text-dark" id="preview_smtp_user"><?= !empty($s['smtp_user']) ? e($s['smtp_user']) : '(Belum diisi)' ?></strong>
					</div>

					<div class="mb-3">
						<label for="test_recipient" class="form-label required fw-semibold small">Kirim Email Percobaan Ke:</label>
						<input type="email" class="form-control" id="test_recipient" name="test_recipient" required placeholder="nama@gmail.com" value="<?= !empty($current_user['email']) ? e($current_user['email']) : '' ?>">
						<div class="form-text">Masukkan alamat email tujuan (misal email pribadi Anda) untuk memeriksa apakah pesan masuk.</div>
					</div>
					
					<div class="alert alert-info py-2 small mb-0">
						<i class="fa-solid fa-circle-info me-1"></i> Jika uji coba berhasil, notifikasi sukses hijau akan muncul dan pesan tes akan masuk ke Inbox Gmail Anda.
					</div>
				</div>
				<div class="modal-footer border-top bg-light">
					<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
					<button type="submit" class="btn btn-primary fw-semibold"><i class="fa-solid fa-paper-plane me-1"></i> Kirim Email Tes</button>
				</div>
			<?= form_close() ?>
		</div>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
	var toggleBtn = document.getElementById('toggle_smtp_pass');
	var passInput = document.getElementById('smtp_pass');
	if (toggleBtn && passInput) {
		toggleBtn.addEventListener('click', function() {
			var isPass = passInput.type === 'password';
			passInput.type = isPass ? 'text' : 'password';
			toggleBtn.innerHTML = isPass ? '<i class="fa-solid fa-eye-slash"></i>' : '<i class="fa-solid fa-eye"></i>';
		});
	}

	var testModal = document.getElementById('testEmailModal');
	if (testModal) {
		testModal.addEventListener('show.bs.modal', function() {
			var smtpUserEl = document.getElementById('smtp_user');
			var smtpPassEl = document.getElementById('smtp_pass');
			var smtpHostEl = document.getElementById('smtp_host');
			var smtpPortEl = document.getElementById('smtp_port');
			var smtpCryptoEl = document.getElementById('smtp_crypto');

			var uVal = smtpUserEl ? smtpUserEl.value.trim() : '';
			var pVal = smtpPassEl ? smtpPassEl.value.trim() : '';
			var hVal = smtpHostEl ? smtpHostEl.value.trim() : '';
			var portVal = smtpPortEl ? smtpPortEl.value.trim() : '';
			var cVal = smtpCryptoEl ? smtpCryptoEl.value.trim() : '';

			if (document.getElementById('test_smtp_user')) document.getElementById('test_smtp_user').value = uVal;
			if (document.getElementById('test_smtp_pass')) document.getElementById('test_smtp_pass').value = pVal;
			if (document.getElementById('test_smtp_host')) document.getElementById('test_smtp_host').value = hVal;
			if (document.getElementById('test_smtp_port')) document.getElementById('test_smtp_port').value = portVal;
			if (document.getElementById('test_smtp_crypto')) document.getElementById('test_smtp_crypto').value = cVal;

			var previewEl = document.getElementById('preview_smtp_user');
			if (previewEl && uVal) {
				previewEl.textContent = uVal;
			}
		});
	}
});
</script>
