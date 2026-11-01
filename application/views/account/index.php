<?php defined('BASEPATH') OR exit('No direct script access allowed');
$is_pw = $section === 'password';
$val = function ($k) use ($user, $is_pw) { return $is_pw ? (string) $user[$k] : set_value($k, (string) $user[$k], FALSE); };
$inv = function ($k) { return form_error($k) !== '' ? ' is-invalid' : ''; };
$fb  = function ($k) { $m = strip_tags(form_error($k)); return $m !== '' ? '<div class="invalid-feedback d-block">'.e($m).'</div>' : ''; };
?>
<?php if ($user['username'] === 'admin' && password_verify('admin123', $user['password_hash'])): ?>
	<div class="alert alert-danger"><i class="fa-solid fa-shield-halved me-1"></i> Anda masih memakai password bawaan. Segera ganti password demi keamanan.</div>
<?php endif; ?>
<div class="row g-3">
	<div class="col-lg-6">
		<div class="form-section">
			<div class="form-section-title"><i class="fa-solid fa-id-card"></i> Profil</div>
			<div class="form-section-body">
				<?= form_open('akun', array('novalidate' => 'novalidate')) ?>
					<input type="hidden" name="section" value="profile">
					<div class="mb-3">
						<label class="form-label required" for="full_name">Nama Lengkap</label>
						<input type="text" class="form-control<?= $is_pw ? '' : $inv('full_name') ?>" id="full_name" name="full_name" maxlength="150" value="<?= e($val('full_name')) ?>" required>
						<?= $is_pw ? '' : $fb('full_name') ?>
					</div>
					<div class="mb-3">
						<label class="form-label required" for="username">Username</label>
						<input type="text" class="form-control<?= $is_pw ? '' : $inv('username') ?>" id="username" name="username" maxlength="50" value="<?= e($val('username')) ?>" required>
						<?= $is_pw ? '' : $fb('username') ?>
						<div class="form-text">Huruf, angka, garis bawah, atau tanda minus; minimal 4 karakter.</div>
					</div>
					<div class="mb-3">
						<label class="form-label" for="email">Email</label>
						<input type="email" class="form-control<?= $is_pw ? '' : $inv('email') ?>" id="email" name="email" maxlength="150" value="<?= e($val('email')) ?>">
						<?= $is_pw ? '' : $fb('email') ?>
					</div>
					<button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk me-1"></i> Simpan Profil</button>
				<?= form_close() ?>
			</div>
		</div>
		<p class="small text-muted"><i class="fa-regular fa-clock me-1"></i> Login terakhir: <?= $user['last_login_at'] ? e(tgl_indo($user['last_login_at'])).' '.e(substr($user['last_login_at'], 11, 5)) : '-' ?></p>
	</div>
	<div class="col-lg-6">
		<div class="form-section">
			<div class="form-section-title"><i class="fa-solid fa-key"></i> Ubah Password</div>
			<div class="form-section-body">
				<?= form_open('akun', array('novalidate' => 'novalidate', 'autocomplete' => 'off')) ?>
					<input type="hidden" name="section" value="password">
					<div class="mb-3">
						<label class="form-label required" for="current_password">Password Saat Ini</label>
						<input type="password" class="form-control<?= $is_pw ? $inv('current_password') : '' ?>" id="current_password" name="current_password" required>
						<?= $is_pw ? $fb('current_password') : '' ?>
					</div>
					<div class="mb-3">
						<label class="form-label required" for="new_password">Password Baru</label>
						<input type="password" class="form-control<?= $is_pw ? $inv('new_password') : '' ?>" id="new_password" name="new_password" minlength="8" maxlength="72" required>
						<?= $is_pw ? $fb('new_password') : '' ?>
						<div class="form-text">Minimal 8 karakter, mengandung huruf dan angka.</div>
					</div>
					<div class="mb-3">
						<label class="form-label required" for="confirm_password">Konfirmasi Password Baru</label>
						<input type="password" class="form-control<?= $is_pw ? $inv('confirm_password') : '' ?>" id="confirm_password" name="confirm_password" required>
						<?= $is_pw ? $fb('confirm_password') : '' ?>
					</div>
					<button class="btn btn-navy" type="submit"><i class="fa-solid fa-key me-1"></i> Ubah Password</button>
				<?= form_close() ?>
			</div>
		</div>
	</div>
</div>
