<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
	<div>
		<h1 class="h4 fw-bold text-navy mb-1"><i class="fa-solid fa-users-gear me-2 text-primary"></i>Kelola Akun &amp; Penetapan Role</h1>
		<p class="text-muted small mb-0">Manajemen akun staf sekolah, aktivasi akun, dan hak akses 5 peran sistem.</p>
	</div>
	<a href="<?= site_url('pengguna/tambah') ?>" class="btn btn-primary"><i class="fa-solid fa-user-plus me-1"></i> Tambah Pengguna</a>
</div>

<div class="card mb-3">
	<div class="card-body py-2">
		<form method="get" action="<?= site_url('pengguna') ?>" class="row g-2 align-items-center">
			<div class="col-md-5">
				<div class="input-group input-group-sm">
					<span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
					<input type="text" name="q" class="form-control" placeholder="Cari nama, username, NIP, jabatan..." value="<?= e($filters['q']) ?>">
				</div>
			</div>
			<div class="col-md-3">
				<select name="role" class="form-select form-select-sm">
					<option value="">-- Semua Role --</option>
					<?php foreach ($roles as $rk => $rv): ?>
						<option value="<?= $rk ?>" <?= $filters['role'] === $rk ? 'selected' : '' ?>><?= e($rv) ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="col-md-2">
				<select name="is_active" class="form-select form-select-sm">
					<option value="">-- Semua Status --</option>
					<option value="1" <?= $filters['is_active'] === 1 ? 'selected' : '' ?>>Aktif</option>
					<option value="0" <?= $filters['is_active'] === 0 ? 'selected' : '' ?>>Nonaktif</option>
				</select>
			</div>
			<div class="col-md-2 d-flex gap-1">
				<button type="submit" class="btn btn-sm btn-navy flex-fill"><i class="fa-solid fa-filter me-1"></i> Filter</button>
				<?php if ( ! empty($filters['q']) || ! empty($filters['role']) || $filters['is_active'] !== ''): ?>
					<a href="<?= site_url('pengguna') ?>" class="btn btn-sm btn-outline-secondary" title="Reset filter"><i class="fa-solid fa-rotate-left"></i></a>
				<?php endif; ?>
			</div>
		</form>
	</div>
</div>

<div class="card">
	<div class="card-body p-0">
		<div class="table-responsive">
			<table class="table table-hover align-middle mb-0">
				<thead class="table-light">
					<tr>
						<th style="width:50px" class="text-center">No</th>
						<th>Nama &amp; NIP</th>
						<th>Username</th>
						<th>Peran / Role</th>
						<th>Jabatan / Posisi</th>
						<th>Status</th>
						<th style="width:140px" class="text-end">Aksi</th>
					</tr>
				</thead>
				<tbody>
				<?php if (empty($users)): ?>
					<tr><td colspan="7" class="text-center py-4 text-muted">Tidak ada pengguna yang cocok.</td></tr>
				<?php else: 
					$u_curr = current_user();
					foreach ($users as $idx => $u):
					$is_self = $u_curr && ((int) $u['id'] === (int) $u_curr['id']);
				?>
					<tr>
						<td class="text-center text-muted small"><?= $offset + $idx + 1 ?></td>
						<td>
							<div class="fw-bold text-navy"><?= e($u['full_name']) ?> <?= $is_self ? '<span class="badge text-bg-primary">Saya</span>' : '' ?></div>
							<small class="text-muted"><?= $u['nip'] ? 'NIP: '.e($u['nip']) : ($u['email'] ? e($u['email']) : '-') ?></small>
						</td>
						<td><code class="text-dark fw-bold"><?= e($u['username']) ?></code></td>
						<td><?= role_badge($u['role']) ?></td>
						<td><?= e($u['position'] ?: '-') ?></td>
						<td>
							<?php if ($u['is_active']): ?>
								<span class="badge text-bg-success">Aktif</span>
							<?php else: ?>
								<span class="badge text-bg-danger">Nonaktif</span>
							<?php endif; ?>
						</td>
						<td class="text-end">
							<div class="btn-group btn-group-sm">
								<a href="<?= site_url('pengguna/edit/'.$u['id']) ?>" class="btn btn-outline-primary" title="Edit Pengguna">
									<i class="fa-solid fa-pen-to-square"></i>
								</a>
								<button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#resetPassModal_<?= $u['id'] ?>" title="Reset Password">
									<i class="fa-solid fa-key"></i>
								</button>
								<?php if ( ! $is_self): ?>
									<?= form_open('pengguna/status/'.$u['id'], array('class' => 'd-inline', 'data-confirm' => ($u['is_active'] ? 'Nonaktifkan' : 'Aktifkan').' akun '.$u['username'].'?')) ?>
										<button type="submit" class="btn btn-outline-<?= $u['is_active'] ? 'danger' : 'success' ?>" title="<?= $u['is_active'] ? 'Nonaktifkan Akun' : 'Aktifkan Akun' ?>">
											<i class="fa-solid fa-<?= $u['is_active'] ? 'user-slash' : 'user-check' ?>"></i>
										</button>
									<?= form_close() ?>
								<?php endif; ?>
							</div>

							<!-- Modal Reset Password -->
							<div class="modal fade text-start" id="resetPassModal_<?= $u['id'] ?>" tabindex="-1" aria-hidden="true">
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header">
											<h5 class="modal-title h6"><i class="fa-solid fa-key me-2 text-warning"></i>Reset Password: <?= e($u['username']) ?></h5>
											<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
										</div>
										<?= form_open('pengguna/reset-password/'.$u['id']) ?>
										<div class="modal-body">
											<p class="small text-muted mb-3">Masukkan password baru untuk <strong><?= e($u['full_name']) ?></strong> (minimal 8 karakter).</p>
											<div class="mb-3">
												<label class="form-label required">Password Baru</label>
												<input type="password" name="password" class="form-control" minlength="8" required placeholder="Minimal 8 karakter">
											</div>
										</div>
										<div class="modal-footer">
											<button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Batal</button>
											<button type="submit" class="btn btn-sm btn-warning"><i class="fa-solid fa-key me-1"></i> Simpan Password Baru</button>
										</div>
										<?= form_close() ?>
									</div>
								</div>
							</div>
						</td>
					</tr>
				<?php endforeach; endif; ?>
				</tbody>
			</table>
		</div>
	</div>
	<?php if ($pagination): ?>
		<div class="card-footer bg-white d-flex align-items-center justify-content-between py-2">
			<div class="small text-muted">Menampilkan <?= count($users) ?> dari total <?= number_format($total) ?> akun</div>
			<?= $pagination ?>
		</div>
	<?php endif; ?>
</div>
