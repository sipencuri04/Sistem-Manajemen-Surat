<?php defined('BASEPATH') OR exit('No direct script access allowed');
$is_overdue = $disp['deadline_date'] && strtotime($disp['deadline_date']) < time() && $disp['status'] !== 'selesai';
?>
<nav aria-label="breadcrumb">
	<ol class="breadcrumb small mb-2">
		<li class="breadcrumb-item"><a href="<?= site_url('disposisi') ?>">Disposisi Surat</a></li>
		<li class="breadcrumb-item active" aria-current="page"><?= e($disp['disposition_number']) ?></li>
	</ol>
</nav>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
	<div>
		<div class="d-flex align-items-center gap-2">
			<h1 class="h4 fw-bold text-navy mb-0"><?= e($disp['disposition_number']) ?></h1>
			<?= disposition_priority_badge($disp['priority']) ?>
			<?= disposition_status_badge($disp['status'], $is_overdue) ?>
		</div>
		<small class="text-muted">Dibuat oleh: <?= e($disp['creator_name']) ?> (<?= e($disp['creator_position'] ?: 'Kepala Sekolah') ?>) &middot; <?= e(tgl_indo($disp['disposition_date'])) ?></small>
	</div>
	<div>
		<?php if ($disp['incoming_letter_id']): ?>
			<a href="<?= site_url('surat-masuk/detail/'.$disp['incoming_letter_id']) ?>" class="btn btn-outline-primary btn-sm">
				<i class="fa-solid fa-inbox me-1"></i> Buka Surat Masuk
			</a>
		<?php endif; ?>
	</div>
</div>

<div class="row g-3">
	<div class="col-lg-8">
		<!-- Kartu Instruksi Kepala Sekolah -->
		<div class="card mb-3">
			<div class="card-header bg-white py-3">
				<h2 class="h6 card-title mb-0 text-navy"><i class="fa-solid fa-quote-left text-warning me-2"></i>Instruksi Kepala Sekolah</h2>
			</div>
			<div class="card-body">
				<div class="p-3 bg-light border-start border-warning border-4 rounded mb-3">
					<div class="fs-6 fw-semibold text-dark mb-1"><?= nl2br(e($disp['instruction'])) ?></div>
					<?php if ($disp['notes']): ?>
						<div class="small text-muted mt-2 border-top pt-2"><i class="fa-solid fa-circle-info me-1"></i>Catatan: <?= nl2br(e($disp['notes'])) ?></div>
					<?php endif; ?>
				</div>

				<div class="row g-2 small">
					<div class="col-sm-6">
						<span class="text-muted">Tanggal Disposisi:</span>
						<strong><?= e(tgl_indo($disp['disposition_date'], TRUE)) ?></strong>
					</div>
					<div class="col-sm-6">
						<span class="text-muted">Tenggat Waktu:</span>
						<?php if ($disp['deadline_date']): ?>
							<strong class="<?= $is_overdue ? 'text-danger' : '' ?>"><?= e(tgl_indo($disp['deadline_date'], TRUE)) ?></strong>
							<?php if ($is_overdue): ?><span class="badge text-bg-danger ms-1">Terlewati</span><?php endif; ?>
						<?php else: ?>
							<span class="text-muted">Tidak ada batas waktu khusus</span>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>

		<!-- Daftar Penerima Disposisi & Progres Tindak Lanjut -->
		<div class="card mb-3">
			<div class="card-header bg-white py-3">
				<h2 class="h6 card-title mb-0 text-navy"><i class="fa-solid fa-users text-primary me-2"></i>Status Tindak Lanjut Pegawai</h2>
			</div>
			<div class="card-body p-0">
				<div class="table-responsive">
					<table class="table table-hover align-middle mb-0">
						<thead class="table-light">
							<tr>
								<th>Pegawai Penerima</th>
								<th>Status</th>
								<th>Progres</th>
								<th>Catatan &amp; Bukti</th>
								<th style="width:130px" class="text-end">Aksi</th>
							</tr>
						</thead>
						<tbody>
						<?php foreach ($disp['recipients'] as $r):
							$u_curr = current_user();
							$is_me = $u_curr && ((int) $r['user_id'] === (int) $u_curr['id']);
							$can_edit = $is_me || has_role(array('kepala_sekolah', 'superuser', 'admin'));
						?>
							<tr class="<?= $is_me ? 'table-primary-subtle' : '' ?>">
								<td>
									<div class="fw-bold d-flex align-items-center gap-2">
										<span><?= e($r['full_name']) ?></span>
										<?= $is_me ? '<span class="badge text-bg-primary">Saya</span>' : '' ?>
										<?php if (!empty($r['phone'])): 
											$emp_wa_msg = "Assalamu'alaikum Bpk/Ibu *" . $r['full_name'] . "*,\nAnda menerima Lembar Disposisi dari Kepala Sekolah:\n- No. Disposisi: *" . $disp['disposition_number'] . "*\n- Sifat: *" . strtoupper($disp['priority']) . "*\n- Instruksi: \"" . $disp['instruction'] . "\"\n- Tenggat: " . ($disp['deadline_date'] ? tgl_indo($disp['deadline_date']) : 'Segera') . "\nBuka sistem untuk melihat berkas: " . site_url('disposisi/detail/'.$disp['id']) . "\nTerima kasih.";
											$emp_wa_link = build_wa_link($r['phone'], $emp_wa_msg);
										?>
											<a href="<?= $emp_wa_link ?>" target="_blank" rel="noopener" class="btn btn-xs btn-outline-success" title="Kirim Instruksi Disposisi via WhatsApp ke <?= e($r['full_name']) ?>">
												<i class="fa-brands fa-whatsapp me-1"></i> WA
											</a>
										<?php endif; ?>
									</div>
									<small class="text-muted"><?= e($r['position'] ?: 'Pegawai') ?> <?= $r['nip'] ? '&middot; NIP: '.e($r['nip']) : '' ?></small>
									<?php if ($r['delegated_to']): ?>
										<div class="small text-danger mt-1">
											<i class="fa-solid fa-arrow-turn-down me-1"></i> Didelegasikan kepada: <strong><?= e($r['delegated_to_name']) ?></strong>
											<div class="text-muted fst-italic">"<?= e($r['delegation_reason']) ?>"</div>
										</div>
									<?php endif; ?>
								</td>
								<td>
									<?= disposition_status_badge($r['status'], $is_overdue) ?>
									<?php if ($r['completed_at']): ?>
										<div class="small text-muted mt-1"><?= e(tgl_indo($r['completed_at'])) ?></div>
									<?php endif; ?>
								</td>
								<td>
									<div class="d-flex align-items-center gap-2">
										<div class="progress flex-fill" style="height: 8px;">
											<div class="progress-bar bg-<?= $r['status'] === 'selesai' ? 'success' : 'primary' ?>" role="progressbar" style="width: <?= (int) $r['progress_percent'] ?>%"></div>
										</div>
										<span class="small fw-bold"><?= (int) $r['progress_percent'] ?>%</span>
									</div>
								</td>
								<td>
									<div class="small"><?= $r['progress_notes'] ? nl2br(e($r['progress_notes'])) : '<span class="text-muted">-</span>' ?></div>
									<?php if ($r['evidence_file']): ?>
										<a href="<?= site_url('disposisi/bukti/'.$r['id']) ?>" target="_blank" class="badge text-bg-light border mt-1 text-decoration-none">
											<i class="fa-solid fa-paperclip me-1 text-primary"></i> <?= e($r['evidence_name'] ?: 'File Bukti') ?>
										</a>
									<?php endif; ?>
								</td>
								<td class="text-end">
									<?php if ($can_edit && ! $r['delegated_to']): ?>
										<button type="button" class="btn btn-xs btn-primary me-1" data-bs-toggle="modal" data-bs-target="#progressModal_<?= $r['id'] ?>" title="Update Progres">
											<i class="fa-solid fa-pen-to-square"></i> Update
										</button>
										<button type="button" class="btn btn-xs btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#delegateModal_<?= $r['id'] ?>" title="Delegasikan Ulang">
											<i class="fa-solid fa-share"></i>
										</button>
									<?php endif; ?>
								</td>
							</tr>

							<!-- Modal Update Progres -->
							<?php if ($can_edit && ! $r['delegated_to']): ?>
							<div class="modal fade" id="progressModal_<?= $r['id'] ?>" tabindex="-1" aria-hidden="true">
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header">
											<h5 class="modal-title h6"><i class="fa-solid fa-pen-to-square me-2 text-primary"></i>Update Tindak Lanjut: <?= e($r['full_name']) ?></h5>
											<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
										</div>
										<?= form_open_multipart('disposisi/update-progres/'.$r['id']) ?>
										<div class="modal-body">
											<div class="mb-3">
												<label class="form-label required">Status Tindak Lanjut</label>
												<select name="status" class="form-select" required>
													<?php foreach (Disposition_model::$recipient_statuses as $rk => $rv): ?>
														<option value="<?= $rk ?>" <?= $r['status'] === $rk ? 'selected' : '' ?>><?= e($rv[1]) ?></option>
													<?php endforeach; ?>
												</select>
											</div>
											<div class="mb-3">
												<label class="form-label required">Persentase Progres (0 - 100%)</label>
												<input type="number" name="progress_percent" class="form-control" min="0" max="100" value="<?= (int) $r['progress_percent'] ?>" required>
												<small class="text-muted">Jika status Selesai dipilih, progres otomatis menjadi 100%.</small>
											</div>
											<div class="mb-3">
												<label class="form-label required">Catatan Tindak Lanjut</label>
												<textarea name="progress_notes" class="form-control" rows="3" placeholder="Tuliskan uraian hasil pelaksanaan tugas atau kendala..." required><?= e($r['progress_notes']) ?></textarea>
											</div>
											<div class="mb-3">
												<label class="form-label">Unggah Bukti Hasil (PDF/Foto/Dokumen, maks 2 MB)</label>
												<?php if ($r['evidence_file']): ?>
													<div class="small text-muted mb-1">File saat ini: <?= e($r['evidence_name']) ?></div>
												<?php endif; ?>
												<input type="file" name="evidence_file" class="form-control">
											</div>
										</div>
										<div class="modal-footer">
											<button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Batal</button>
											<button type="submit" class="btn btn-sm btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i> Simpan Progres</button>
										</div>
										<?= form_close() ?>
									</div>
								</div>
							</div>

							<!-- Modal Delegasikan Ulang -->
							<div class="modal fade" id="delegateModal_<?= $r['id'] ?>" tabindex="-1" aria-hidden="true">
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header">
											<h5 class="modal-title h6"><i class="fa-solid fa-share me-2 text-warning"></i>Delegasikan Ulang Tugas</h5>
											<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
										</div>
										<?= form_open('disposisi/delegasikan/'.$r['id']) ?>
										<div class="modal-body">
											<div class="alert alert-light border small mb-3">
												Anda akan mengalihkan pelaksanaan instruksi disposisi dari <strong><?= e($r['full_name']) ?></strong> kepada pegawai lain. Riwayat pengalihan akan tercatat permanen.
											</div>
											<div class="mb-3">
												<label class="form-label required">Pegawai Tujuan Delegasi</label>
												<select name="to_user_id" class="form-select" required>
													<option value="">-- Pilih Pegawai --</option>
													<?php foreach ($employees as $emp): if ((int) $emp['id'] !== (int) $r['user_id']): ?>
														<option value="<?= (int) $emp['id'] ?>"><?= e($emp['full_name']) ?> (<?= e($emp['position'] ?: 'Staf') ?>)</option>
													<?php endif; endforeach; ?>
												</select>
											</div>
											<div class="mb-3">
												<label class="form-label required">Alasan Pendelegasian</label>
												<textarea name="delegation_reason" class="form-control" rows="2" placeholder="Contoh: Tugas teknis laboratorium jaringan dialihkan ke laboran..." required></textarea>
											</div>
										</div>
										<div class="modal-footer">
											<button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Batal</button>
											<button type="submit" class="btn btn-sm btn-warning"><i class="fa-solid fa-share me-1"></i> Delegasikan</button>
										</div>
										<?= form_close() ?>
									</div>
								</div>
							</div>
							<?php endif; ?>

						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>

		<!-- Timeline Riwayat Aktivitas Disposisi -->
		<div class="card">
			<div class="card-header bg-white py-3">
				<h2 class="h6 card-title mb-0 text-navy"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Riwayat &amp; Jejak Aktivitas Disposisi</h2>
			</div>
			<div class="card-body">
				<?php if (empty($disp['activities'])): ?>
					<div class="text-muted small">Belum ada catatan aktivitas.</div>
				<?php else: ?>
					<div class="timeline">
						<?php foreach ($disp['activities'] as $act): ?>
							<div class="d-flex gap-3 mb-3">
								<div class="text-primary mt-1"><i class="fa-solid fa-circle-dot"></i></div>
								<div class="flex-fill border-bottom pb-2">
									<div class="d-flex justify-content-between align-items-center mb-1">
										<strong class="text-navy small"><?= e($act['full_name'] ?: 'Sistem') ?></strong>
										<span class="text-muted" style="font-size:0.75rem;"><?= e(tgl_indo($act['created_at'])) ?> <?= e(substr($act['created_at'], 11, 5)) ?></span>
									</div>
									<div class="small text-secondary"><?= nl2br(e($act['description'])) ?></div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<!-- Kolom Kanan: Rujukan Surat Masuk -->
	<div class="col-lg-4">
		<?php if ($disp['incoming_letter_id']): ?>
			<div class="card mb-3">
				<div class="card-header bg-white py-3">
					<h2 class="h6 card-title mb-0 text-navy"><i class="fa-solid fa-inbox text-primary me-2"></i>Surat Masuk Rujukan</h2>
				</div>
				<div class="card-body">
					<dl class="dl-meta row mb-0 small">
						<dt class="col-5">No. Agenda</dt>
						<dd class="col-7 letter-number fw-bold"><?= e($disp['agenda_number']) ?></dd>

						<dt class="col-5">No. Asal</dt>
						<dd class="col-7"><?= e($disp['incoming_letter_number']) ?></dd>

						<dt class="col-5">Pengirim</dt>
						<dd class="col-7 fw-semibold"><?= e($disp['incoming_sender']) ?></dd>

						<dt class="col-5">Perihal</dt>
						<dd class="col-7"><?= e($disp['incoming_subject']) ?></dd>
					</dl>

					<?php if ($disp['incoming_file_path']): ?>
						<div class="mt-3 pt-3 border-top text-center">
							<a href="<?= site_url('surat-masuk/berkas/'.$disp['incoming_letter_id'].'?lihat=1') ?>" target="_blank" class="btn btn-sm btn-outline-danger w-100">
								<i class="fa-solid fa-file-pdf me-1"></i> Buka Scan Surat Masuk
							</a>
						</div>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>

		<div class="card">
			<div class="card-header bg-white py-3">
				<h2 class="h6 card-title mb-0 text-navy"><i class="fa-solid fa-shield-halved text-primary me-2"></i>Kewenangan &amp; Catatan</h2>
			</div>
			<div class="card-body small text-muted">
				<p class="mb-2"><i class="fa-solid fa-circle-check text-success me-1"></i> <strong>Disposisi:</strong> Merupakan instruksi operasional tindak lanjut dan tidak otomatis memindahkan kewenangan tanda tangan surat.</p>
				<p class="mb-2"><i class="fa-solid fa-user-shield text-primary me-1"></i> <strong>Akses:</strong> Hanya pegawai penerima dan pihak berwenang yang dapat melihat rincian disposisi rahasia.</p>
				<p class="mb-0"><i class="fa-solid fa-bell text-warning me-1"></i> <strong>Notifikasi:</strong> Setiap perubahan status dan penyelesaian tugas otomatis dilaporkan kepada Kepala Sekolah.</p>
			</div>
		</div>
	</div>
</div>
