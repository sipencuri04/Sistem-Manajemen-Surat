<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<nav aria-label="breadcrumb">
	<ol class="breadcrumb small mb-2">
		<li class="breadcrumb-item"><a href="<?= site_url('pengguna') ?>">Kelola Pengguna</a></li>
		<li class="breadcrumb-item active" aria-current="page">Tambah Pengguna</li>
	</ol>
</nav>

<div class="row justify-content-center">
	<div class="col-lg-8">
		<div class="card">
			<div class="card-header bg-white py-3">
				<h2 class="h5 card-title mb-0 text-navy"><i class="fa-solid fa-user-plus text-primary me-2"></i>Tambah Akun Pengguna Baru</h2>
			</div>
			<div class="card-body">
				<?= form_open('pengguna/simpan') ?>
				<div class="row g-3">
					<div class="col-md-6">
						<label class="form-label required" for="username">Username</label>
						<input type="text" class="form-control" id="username" name="username" value="<?= set_value('username') ?>" placeholder="huruf, angka, strip (tanpa spasi)" required>
					</div>
					<div class="col-md-6">
						<label class="form-label required" for="password">Password</label>
						<input type="password" class="form-control" id="password" name="password" minlength="8" placeholder="Minimal 8 karakter" required>
					</div>

					<div class="col-md-6">
						<label class="form-label required" for="full_name">Nama Lengkap &amp; Gelar</label>
						<input type="text" class="form-control" id="full_name" name="full_name" value="<?= set_value('full_name') ?>" placeholder="Contoh: Drs. Bambang Sutrisno, M.Pd" required>
					</div>
					<div class="col-md-6">
						<label class="form-label required" for="role">Peran / Role</label>
						<select class="form-select" id="role" name="role" required>
							<?php foreach ($roles as $rk => $rv): ?>
								<option value="<?= $rk ?>" <?= set_select('role', $rk, $rk === 'pegawai') ?>><?= e($rv) ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="col-md-6">
						<label class="form-label" for="nip">NIP (Nomor Induk Pegawai)</label>
						<input type="text" class="form-control" id="nip" name="nip" value="<?= set_value('nip') ?>" placeholder="Contoh: 19850320 200902 1 004">
					</div>
					<div class="col-md-6">
						<label class="form-label" for="position">Jabatan / Posisi</label>
						<input type="text" class="form-control" id="position" name="position" value="<?= set_value('position') ?>" placeholder="Contoh: Guru Matematika / Koordinator Sarpras">
					</div>

					<div class="col-md-6">
						<label class="form-label" for="email">Alamat Email</label>
						<input type="email" class="form-control" id="email" name="email" value="<?= set_value('email') ?>" placeholder="nama@sekolah.sch.id">
					</div>
					<div class="col-md-6">
						<label class="form-label" for="phone">Nomor Telepon / WhatsApp</label>
						<input type="text" class="form-control" id="phone" name="phone" value="<?= set_value('phone') ?>" placeholder="Contoh: 081234567890">
					</div>
				</div>

				<div class="alert alert-light border small mt-4 mb-0">
					<i class="fa-solid fa-shield-halved text-primary me-1"></i>
					<strong>Panduan Hak Akses Role:</strong>
					<ul class="mb-0 ps-3 mt-1">
						<li><strong>Superuser / Developer:</strong> Mengelola akun, melihat log audit &amp; pemeliharaan teknis. Tidak menandatangani surat.</li>
						<li><strong>Kepala Sekolah:</strong> Membuat disposisi, memantau tindak lanjut, dan menyetujui surat keluar.</li>
						<li><strong>Tata Usaha (TU):</strong> Mengelola surat masuk &amp; keluar, verifikasi permohonan siswa, template, dan nomor otomatis.</li>
						<li><strong>Pegawai / Guru:</strong> Menerima disposisi, memperbarui progres pelaksanaan tugas, dan melampirkan bukti.</li>
					</ul>
				</div>

				<div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
					<a href="<?= site_url('pengguna') ?>" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Batal</a>
					<button type="submit" class="btn btn-primary"><i class="fa-solid fa-user-plus me-1"></i> Simpan Pengguna</button>
				</div>
				<?= form_close() ?>
			</div>
		</div>
	</div>
</div>
