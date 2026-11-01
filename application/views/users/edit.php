<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<nav aria-label="breadcrumb">
	<ol class="breadcrumb small mb-2">
		<li class="breadcrumb-item"><a href="<?= site_url('pengguna') ?>">Kelola Pengguna</a></li>
		<li class="breadcrumb-item active" aria-current="page">Edit <?= e($user['username']) ?></li>
	</ol>
</nav>

<div class="row justify-content-center">
	<div class="col-lg-8">
		<div class="card">
			<div class="card-header bg-white py-3">
				<h2 class="h5 card-title mb-0 text-navy"><i class="fa-solid fa-user-pen text-primary me-2"></i>Edit Data Pengguna</h2>
			</div>
			<div class="card-body">
				<?= form_open('pengguna/update/'.$user['id']) ?>
				<div class="row g-3">
					<div class="col-md-6">
						<label class="form-label">Username</label>
						<input type="text" class="form-control bg-light" value="<?= e($user['username']) ?>" disabled readonly>
						<small class="text-muted">Username tidak dapat diubah.</small>
					</div>
					<div class="col-md-6">
						<label class="form-label required" for="role">Peran / Role</label>
						<select class="form-select" id="role" name="role" required>
							<?php foreach ($roles as $rk => $rv): ?>
								<option value="<?= $rk ?>" <?= set_select('role', $rk, $user['role'] === $rk) ?>><?= e($rv) ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="col-md-6">
						<label class="form-label required" for="full_name">Nama Lengkap &amp; Gelar</label>
						<input type="text" class="form-control" id="full_name" name="full_name" value="<?= set_value('full_name', $user['full_name']) ?>" required>
					</div>
					<div class="col-md-6">
						<label class="form-label" for="nip">NIP</label>
						<input type="text" class="form-control" id="nip" name="nip" value="<?= set_value('nip', $user['nip']) ?>">
					</div>

					<div class="col-md-6">
						<label class="form-label" for="position">Jabatan / Posisi</label>
						<input type="text" class="form-control" id="position" name="position" value="<?= set_value('position', $user['position']) ?>">
					</div>
					<div class="col-md-6">
						<label class="form-label" for="phone">Nomor Telepon / WhatsApp</label>
						<input type="text" class="form-control" id="phone" name="phone" value="<?= set_value('phone', $user['phone']) ?>">
					</div>

					<div class="col-12">
						<label class="form-label" for="email">Alamat Email</label>
						<input type="email" class="form-control" id="email" name="email" value="<?= set_value('email', $user['email']) ?>">
					</div>
				</div>

				<div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
					<a href="<?= site_url('pengguna') ?>" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Batal</a>
					<button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan</button>
				</div>
				<?= form_close() ?>
			</div>
		</div>
	</div>
</div>
