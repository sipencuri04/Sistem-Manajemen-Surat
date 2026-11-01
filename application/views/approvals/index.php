<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
	<div>
		<h1 class="h4 fw-bold text-navy mb-1"><i class="fa-solid fa-signature me-2 text-primary"></i>Persetujuan Surat Keluar</h1>
		<p class="text-muted small mb-0">Daftar surat resmi yang diajukan oleh staf Tata Usaha dan membutuhkan persetujuan Kepala Sekolah.</p>
	</div>
</div>

<div class="card mb-4">
	<div class="card-header bg-white py-3">
		<h2 class="h6 card-title mb-0 text-navy">
			<i class="fa-solid fa-hourglass-half text-warning me-2"></i>Menunggu Persetujuan
			<?php if ( ! empty($letters)): ?>
				<span class="badge text-bg-warning ms-1"><?= count($letters) ?> Surat</span>
			<?php endif; ?>
		</h2>
	</div>
	<div class="card-body p-0">
		<div class="table-responsive">
			<table class="table table-hover align-middle mb-0">
				<thead class="table-light">
					<tr>
						<th>Nomor Surat &amp; Jenis</th>
						<th>Tanggal</th>
						<th>Tujuan / Penerima</th>
						<th>Perihal</th>
						<th>Dibuat Oleh</th>
						<th style="width:200px" class="text-end">Aksi Persetujuan</th>
					</tr>
				</thead>
				<tbody>
				<?php if (empty($letters)): ?>
					<tr>
						<td colspan="6" class="text-center py-4 text-muted">
							<i class="fa-solid fa-circle-check fa-3x text-success opacity-50 mb-2 d-block"></i>
							Tidak ada surat yang sedang menunggu persetujuan.
						</td>
					</tr>
				<?php else: foreach ($letters as $l): ?>
					<tr>
						<td>
							<div class="fw-bold letter-number text-navy"><?= e($l['letter_number']) ?></div>
							<div class="d-flex align-items-center gap-1 mt-1">
								<small class="text-muted"><?= e($l['type_name']) ?></small>
								<?php if (isset($l['signature_type']) && $l['signature_type'] === 'basah'): ?>
									<span class="badge bg-warning-subtle text-warning border border-warning-subtle" title="Surat ini disiapkan TU untuk ditandatangani basah secara fisik"><i class="fa-solid fa-pen-nib me-1"></i>TTD Basah</span>
								<?php else: ?>
									<span class="badge bg-info-subtle text-info border border-info-subtle" title="Surat ini menggunakan TTD & Stempel digital otomatis"><i class="fa-solid fa-qrcode me-1"></i>TTD Digital</span>
								<?php endif; ?>
							</div>
						</td>
						<td class="small"><?= e(tgl_indo($l['letter_date'])) ?></td>
						<td><?= e($l['recipient'] ?: '-') ?></td>
						<td><div class="fw-semibold text-truncate" style="max-width:260px;" title="<?= e($l['subject']) ?>"><?= e($l['subject']) ?></div></td>
						<td class="small text-muted"><?= e($l['created_by_name'] ?: 'TU') ?></td>
						<td class="text-end">
							<div class="btn-group btn-group-sm">
								<a href="<?= site_url('surat/pdf/'.$l['id'].'/lihat') ?>" target="_blank" class="btn btn-outline-primary" title="Lihat Pratinjau PDF">
									<i class="fa-solid fa-eye"></i>
								</a>
								<?= form_open('persetujuan-surat/setujui/'.$l['id'], array('class' => 'd-inline', 'data-confirm' => 'Setujui surat No. '.$l['letter_number'].'? Surat akan otomatis berstatus Final dan PDF arsip diterbitkan.')) ?>
									<button type="submit" class="btn btn-success" title="Setujui Surat"><i class="fa-solid fa-check"></i></button>
								<?= form_close() ?>
								<button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal_<?= $l['id'] ?>" title="Tolak / Minta Revisi">
									<i class="fa-solid fa-xmark"></i>
								</button>
							</div>

							<!-- Modal Tolak Surat -->
							<div class="modal fade text-start" id="rejectModal_<?= $l['id'] ?>" tabindex="-1" aria-hidden="true">
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header">
											<h5 class="modal-title h6 text-danger"><i class="fa-solid fa-ban me-2"></i>Tolak / Minta Revisi Surat</h5>
											<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
										</div>
										<?= form_open('persetujuan-surat/tolak/'.$l['id']) ?>
										<div class="modal-body">
											<div class="mb-3">
												<label class="form-label required">Alasan Penolakan / Catatan Revisi untuk TU</label>
												<textarea name="rejection_reason" class="form-control" rows="3" placeholder="Contoh: Perbaiki bagian tujuan surat atau periksa kembali nama siswa..." required></textarea>
											</div>
										</div>
										<div class="modal-footer">
											<button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Batal</button>
											<button type="submit" class="btn btn-sm btn-danger"><i class="fa-solid fa-ban me-1"></i> Tolak Surat</button>
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
</div>

<!-- Riwayat Persetujuan Terakhir -->
<div class="card">
	<div class="card-header bg-white py-3">
		<h2 class="h6 card-title mb-0 text-navy"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Riwayat Persetujuan Terakhir</h2>
	</div>
	<div class="card-body p-0">
		<div class="table-responsive">
			<table class="table table-hover align-middle mb-0 small">
				<thead class="table-light">
					<tr>
						<th>Nomor Surat</th>
						<th>Perihal</th>
						<th>Diproses Pada</th>
						<th>Keputusan</th>
						<th>Catatan / Alasan</th>
					</tr>
				</thead>
				<tbody>
				<?php if (empty($history)): ?>
					<tr><td colspan="5" class="text-center py-3 text-muted">Belum ada riwayat persetujuan.</td></tr>
				<?php else: foreach ($history as $h): ?>
					<tr>
						<td><a href="<?= site_url('surat/detail/'.$h['id']) ?>" class="fw-bold letter-number"><?= e($h['letter_number']) ?></a></td>
						<td><?= e($h['subject']) ?></td>
						<td><?= $h['approved_at'] ? e(tgl_indo($h['approved_at'])) : '-' ?></td>
						<td>
							<?php if ($h['approval_status'] === 'disetujui'): ?>
								<span class="badge text-bg-success"><i class="fa-solid fa-check me-1"></i>Disetujui</span>
							<?php else: ?>
								<span class="badge text-bg-danger"><i class="fa-solid fa-xmark me-1"></i>Ditolak</span>
							<?php endif; ?>
						</td>
						<td><?= $h['rejection_reason'] ? e($h['rejection_reason']) : '<span class="text-muted">-</span>' ?></td>
					</tr>
				<?php endforeach; endif; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
