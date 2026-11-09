<?php defined('BASEPATH') OR exit('No direct script access allowed');

// Konfigurasi status & tema visual
$badges = array(
	'pending' => array(
		'bg'        => 'linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%)',
		'border'    => '#fde68a',
		'badge_bg'  => '#f59e0b',
		'badge_txt' => '#ffffff',
		'label'     => 'PENDING',
		'icon'      => 'fa-hourglass-half',
		'title'     => 'Menunggu Pemeriksaan',
		'desc'      => 'Permohonan sudah diterima dan menunggu diverifikasi oleh petugas Tata Usaha.',
		'step_active' => 2, // Verifikasi TU aktif
	),
	'processed' => array(
		'bg'        => 'linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%)',
		'border'    => '#bfdbfe',
		'badge_bg'  => '#2563eb',
		'badge_txt' => '#ffffff',
		'label'     => 'DIPROSES',
		'icon'      => 'fa-arrows-rotate fa-spin',
		'title'     => 'Sedang Diproses & Disahkan',
		'desc'      => 'Surat sedang dibuat oleh Tata Usaha, proses penandatanganan Kepala Sekolah, dan pembubuhan stempel resmi.',
		'step_active' => 4, // Penandatanganan Kepala Sekolah aktif
	),
	'approved' => array(
		'bg'        => 'linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%)',
		'border'    => '#a7f3d0',
		'badge_bg'  => '#10b981',
		'badge_txt' => '#ffffff',
		'label'     => 'SIAP DIAMBIL / UNDUH',
		'icon'      => 'fa-circle-check',
		'title'     => 'Surat Selesai Disahkan',
		'desc'      => 'Surat resmi telah disahkan oleh Kepala Sekolah dan distempel Tata Usaha. Dokumen siap diambil di ruang TU atau diunduh.',
		'step_active' => 5, // Siap unduh / selesai
	),
	'taken' => array(
		'bg'        => 'linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%)',
		'border'    => '#e2e8f0',
		'badge_bg'  => '#64748b',
		'badge_txt' => '#ffffff',
		'label'     => 'SUDAH DIAMBIL',
		'icon'      => 'fa-box-archive',
		'title'     => 'Surat Sudah Diserahkan',
		'desc'      => 'Fisik surat resmi telah diserahkan dan diambil oleh pemohon di ruang Tata Usaha.',
		'step_active' => 5, // Selesai tuntas
	),
	'rejected' => array(
		'bg'        => 'linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%)',
		'border'    => '#fecdd3',
		'badge_bg'  => '#ef4444',
		'badge_txt' => '#ffffff',
		'label'     => 'DITOLAK',
		'icon'      => 'fa-circle-xmark',
		'title'     => 'Permohonan Belum Dapat Diproses',
		'desc'      => 'Permohonan tidak dapat diproses. Silakan baca catatan keterangan dari petugas Tata Usaha di bawah.',
		'step_active' => -1, // Ditolak
	),
);
?>
<div class="row justify-content-center">
	<div class="col-lg-8 col-xl-7">
		<!-- Top Action Bar -->
		<div class="d-flex align-items-center justify-content-between mb-3">
			<a href="<?= site_url('permohonan') ?>" class="btn btn-sm btn-light border rounded-pill px-3 py-1 text-muted text-decoration-none">
				<i class="fa-solid fa-arrow-left me-1"></i> Beranda
			</a>
			<span class="badge bg-secondary-subtle text-secondary border rounded-pill px-3 py-1">
				<i class="fa-solid fa-magnifying-glass me-1"></i> Pelacakan Surat
			</span>
		</div>

		<?php if ($is_new && $request): ?>
			<!-- Success Alert Box (First Submission) -->
			<div class="card border-0 shadow-sm rounded-4 mb-3 text-center overflow-hidden" style="background: linear-gradient(180deg, #ecfdf5 0%, #ffffff 100%);">
				<div class="card-body py-4 px-3 px-sm-4">
					<div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success text-white mb-3" style="width: 58px; height: 58px; box-shadow: 0 6px 16px rgba(16, 185, 129, 0.35);">
						<i class="fa-solid fa-check fa-2x"></i>
					</div>
					<h1 class="h4 fw-bold text-navy mb-1">Permohonan Berhasil Dikirim!</h1>
					<p class="text-muted small mb-3">Simpan atau screenshot kode pelacakan di bawah ini untuk mengecek status secara berkala:</p>
					
					<div class="d-inline-block p-2 rounded-4 bg-white border mb-3 shadow-sm">
						<div class="tracking-code" id="trackingCode"><?= e($request['tracking_code']) ?></div>
					</div>
					<div class="d-flex flex-wrap gap-2 justify-content-center">
						<button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" data-copy="#trackingCode">
							<i class="fa-regular fa-copy me-1"></i> Salin Kode
						</button>
						<?php 
						$school_phone = !empty($school['phone']) ? $school['phone'] : '';
						if ($school_phone !== ''): 
							$wa_msg_siswa = "Halo Petugas TU " . $school['school_name'] . ", saya *" . $request['requester_name'] . "* telah mengajukan *" . $request['type_name'] . "* secara online dengan Kode: *" . $request['tracking_code'] . "*. Mohon bantuannya untuk diverifikasi. Terima kasih.";
							$wa_url_siswa = build_wa_link($school_phone, $wa_msg_siswa);
						?>
							<a href="<?= $wa_url_siswa ?>" target="_blank" rel="noopener" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm fw-semibold">
								<i class="fa-brands fa-whatsapp me-1"></i> Konfirmasi ke WhatsApp TU
							</a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<!-- Search / Hero Card (Matches Image Design) -->
		<div class="card border-0 shadow-sm rounded-4 mb-3 overflow-hidden text-white" style="background: linear-gradient(135deg, #1d6fe0 0%, #0d47a1 100%);">
			<div class="card-body p-3 p-sm-4">
				<div class="d-flex align-items-center gap-3 mb-3">
					<div class="d-flex align-items-center justify-content-center rounded-3 bg-white bg-opacity-20 text-white" style="width: 48px; height: 48px; flex-shrink: 0;">
						<i class="fa-solid fa-file-circle-check fs-4"></i>
					</div>
					<div>
						<h2 class="h5 fw-bold mb-0 text-white">Pelacakan Surat &amp; Permohonan</h2>
						<div class="small text-white-50">Lacak status pengajuan surat dengan kode lacak atau nomor agenda.</div>
					</div>
				</div>
				
				<form method="get" action="<?= site_url('permohonan/cek') ?>" class="d-flex gap-2 bg-white p-2 rounded-4 shadow-sm">
					<input type="text" name="kode" class="form-control border-0 rounded-3 text-uppercase fw-bold letter-number px-3" maxlength="30" value="<?= e($code) ?>" placeholder="REQ-PREF82 atau SM-005/2026" aria-label="Kode permohonan" required style="letter-spacing: 0.5px;">
					<button class="btn btn-primary rounded-3 px-3 px-sm-4 fw-semibold d-flex align-items-center gap-2" type="submit">
						<i class="fa-solid fa-magnifying-glass"></i>
						<span>Lacak</span>
					</button>
				</form>
			</div>
		</div>

		<?php if ($searched && ! $request): ?>
			<!-- Not Found Card -->
			<div class="card border-0 shadow-sm rounded-4 text-center py-5 bg-white mb-4">
				<i class="fa-solid fa-triangle-exclamation fa-3x text-warning mb-2"></i>
				<h5 class="fw-bold text-navy">Kode Tidak Ditemukan</h5>
				<p class="text-muted small mb-0 px-3">Kode permohonan <code><?= e($code) ?></code> tidak terdaftar di sistem. Mohon periksa kembali pengetikan kode Anda.</p>
			</div>
		<?php elseif ($request): 
			$b = isset($badges[$request['status']]) ? $badges[$request['status']] : $badges['pending'];
			$st_act = $b['step_active'];
			$is_rejected = ($request['status'] === 'rejected');
		?>
			<!-- Horizontal Stepper Tracker Card (Matches Image Design) -->
			<div class="tracking-stepper-card">
				<div class="tracking-stepper">
					<!-- Step 1: Pengajuan -->
					<div class="tracking-step completed">
						<div class="tracking-step-circle">
							<i class="fa-solid fa-check"></i>
						</div>
						<div class="tracking-step-label">Pengajuan Surat</div>
					</div>

					<!-- Step 2: Verifikasi TU -->
					<div class="tracking-step <?= $is_rejected ? 'rejected' : ($st_act > 2 ? 'completed' : ($st_act == 2 ? 'active' : '')) ?>">
						<div class="tracking-step-circle">
							<?php if ($is_rejected): ?>
								<i class="fa-solid fa-xmark"></i>
							<?php elseif ($st_act > 2): ?>
								<i class="fa-solid fa-check"></i>
							<?php elseif ($st_act == 2): ?>
								<i class="fa-solid fa-hourglass-half"></i>
							<?php else: ?>
								<i class="fa-regular fa-clock"></i>
							<?php endif; ?>
						</div>
						<div class="tracking-step-label">Verifikasi TU</div>
					</div>

					<!-- Step 3: Pemeriksaan Sekolah / Draft -->
					<div class="tracking-step <?= ($st_act > 3 && !$is_rejected) ? 'completed' : ($st_act == 3 ? 'active' : '') ?>">
						<div class="tracking-step-circle">
							<?php if ($st_act > 3 && !$is_rejected): ?>
								<i class="fa-solid fa-check"></i>
							<?php elseif ($st_act == 3): ?>
								<i class="fa-regular fa-file-lines"></i>
							<?php else: ?>
								<i class="fa-regular fa-file"></i>
							<?php endif; ?>
						</div>
						<div class="tracking-step-label">Pemeriksaan Sekolah</div>
					</div>

					<!-- Step 4: Penandatanganan Kepala Sekolah -->
					<div class="tracking-step <?= ($st_act > 4 && !$is_rejected) ? 'completed' : ($st_act == 4 ? 'active' : '') ?>">
						<div class="tracking-step-circle">
							<?php if ($st_act > 4 && !$is_rejected): ?>
								<i class="fa-solid fa-check"></i>
							<?php elseif ($st_act == 4): ?>
								<i class="fa-solid fa-pen-fancy"></i>
							<?php else: ?>
								<i class="fa-solid fa-pen"></i>
							<?php endif; ?>
						</div>
						<div class="tracking-step-label">Penandatangan Kepala Sekolah</div>
					</div>

					<!-- Step 5: Selesai (Unduh / Ambil) -->
					<div class="tracking-step <?= ($st_act >= 5 && !$is_rejected) ? 'completed' : '' ?>">
						<div class="tracking-step-circle">
							<?php if ($st_act >= 5 && !$is_rejected): ?>
								<i class="fa-solid fa-check"></i>
							<?php else: ?>
								<i class="fa-solid fa-download"></i>
							<?php endif; ?>
						</div>
						<div class="tracking-step-label">Selesai (Unduh)</div>
					</div>
				</div>
			</div>

			<!-- Status Summary Card (Warm Yellow/Colored Card Matching Image) -->
			<div class="card border-0 shadow-sm rounded-4 mb-3 overflow-hidden" style="background: <?= $b['bg'] ?>; border: 1px solid <?= $b['border'] ?> !important;">
				<div class="card-body p-3 p-sm-4 d-flex align-items-center gap-3">
					<!-- Icon Box Squircle -->
					<div class="d-flex align-items-center justify-content-center rounded-4 text-white shadow-xs flex-shrink-0" style="width: 52px; height: 52px; background: <?= $b['badge_bg'] ?>;">
						<i class="fa-solid <?= $b['icon'] ?> fs-4"></i>
					</div>

					<!-- Main Status Content -->
					<div class="flex-grow-1">
						<span class="badge rounded-pill px-2 py-1 text-uppercase mb-1" style="font-size: 0.68rem; background: <?= $b['badge_bg'] ?>; color: <?= $b['badge_txt'] ?>;">
							<?= e($b['label']) ?>
						</span>
						<h3 class="h5 fw-bold text-dark mb-1"><?= e($b['title']) ?></h3>
						<p class="small text-secondary mb-0"><?= e($b['desc']) ?></p>
					</div>

					<!-- 3D Folder Icon Right Side (Matches Image) -->
					<div class="ms-auto d-none d-sm-flex align-items-center justify-content-center text-warning" style="font-size: 3rem; opacity: 0.9;">
						<i class="fa-solid fa-folder-open"></i>
					</div>
				</div>
			</div>

			<!-- Details Card (Clean Rows with Icons Matching Image) -->
			<div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden bg-white">
				<div class="card-body p-3 p-sm-4">
					<div class="d-flex flex-column">
						<!-- Row 1: Kode Lacak -->
						<div class="d-flex align-items-center py-2 border-bottom">
							<div class="d-flex align-items-center gap-2 text-muted" style="width: 145px; flex-shrink: 0;">
								<i class="fa-solid fa-barcode text-primary" style="width: 18px;"></i>
								<span class="small">Kode Lacak</span>
							</div>
							<div class="me-2 text-muted">:</div>
							<div class="flex-grow-1">
								<span class="badge bg-light text-dark border font-monospace fs-6 px-2 py-1"><?= e($request['tracking_code']) ?></span>
							</div>
						</div>
						
						<!-- Row 2: Jenis Surat -->
						<div class="d-flex align-items-center py-2 border-bottom">
							<div class="d-flex align-items-center gap-2 text-muted" style="width: 145px; flex-shrink: 0;">
								<i class="fa-solid fa-file-lines text-primary" style="width: 18px;"></i>
								<span class="small">Jenis Surat</span>
							</div>
							<div class="me-2 text-muted">:</div>
							<div class="flex-grow-1 fw-bold text-navy">
								<?= e($request['type_name']) ?>
							</div>
						</div>

						<!-- Row 3: Nama Siswa -->
						<div class="d-flex align-items-center py-2 border-bottom">
							<div class="d-flex align-items-center gap-2 text-muted" style="width: 145px; flex-shrink: 0;">
								<i class="fa-solid fa-user text-primary" style="width: 18px;"></i>
								<span class="small">Nama Siswa</span>
							</div>
							<div class="me-2 text-muted">:</div>
							<div class="flex-grow-1 fw-bold text-dark">
								<?= e($request['requester_name']) ?>
							</div>
						</div>

						<!-- Row 4: Email Siswa -->
						<div class="d-flex align-items-center py-2 border-bottom">
							<div class="d-flex align-items-center gap-2 text-muted" style="width: 145px; flex-shrink: 0;">
								<i class="fa-solid fa-envelope text-primary" style="width: 18px;"></i>
								<span class="small">Email Siswa</span>
							</div>
							<div class="me-2 text-muted">:</div>
							<div class="flex-grow-1 text-dark small">
								<?= e($request['requester_email'] ?: '-') ?>
							</div>
						</div>

						<!-- Row 5: Waktu Pengajuan -->
						<div class="d-flex align-items-center py-2">
							<div class="d-flex align-items-center gap-2 text-muted" style="width: 145px; flex-shrink: 0;">
								<i class="fa-solid fa-calendar-days text-primary" style="width: 18px;"></i>
								<span class="small">Waktu Pengajuan</span>
							</div>
							<div class="me-2 text-muted">:</div>
							<div class="flex-grow-1 text-dark small">
								<?= e(tgl_indo($request['created_at'])) ?> &bull; <?= e(substr($request['created_at'], 11, 5)) ?> WIB
							</div>
						</div>
						
						<!-- Row 6: Nomor Surat (Jika sudah terbit) -->
						<?php if (!empty($request['letter_number'])): ?>
							<div class="d-flex align-items-center py-2 border-top">
								<div class="d-flex align-items-center gap-2 text-muted" style="width: 145px; flex-shrink: 0;">
									<i class="fa-solid fa-certificate text-primary" style="width: 18px;"></i>
									<span class="small">Nomor Surat</span>
								</div>
								<div class="me-2 text-muted">:</div>
								<div class="flex-grow-1 fw-bold font-monospace text-primary">
									<?= e($request['letter_number']) ?>
								</div>
							</div>
						<?php endif; ?>

						<!-- Row 7: Catatan Petugas (Jika ada revisi/penolakan) -->
						<?php if (!empty($request['admin_note'])): ?>
							<div class="d-flex align-items-start py-2 border-top">
								<div class="d-flex align-items-center gap-2 text-danger" style="width: 145px; flex-shrink: 0;">
									<i class="fa-solid fa-triangle-exclamation" style="width: 18px;"></i>
									<span class="small fw-semibold">Catatan Petugas</span>
								</div>
								<div class="me-2 text-danger">:</div>
								<div class="flex-grow-1 text-danger small fw-semibold">
									<?= nl2br(e($request['admin_note'])) ?>
								</div>
							</div>
						<?php endif; ?>
					</div>

					<!-- Tombol Cetak / Unduh Dokumen jika sudah disetujui -->
					<?php if (in_array($request['status'], array('approved', 'taken'), TRUE) && !empty($request['letter_id'])): ?>
						<div class="mt-4 pt-3 border-top text-center">
							<?php if (isset($request['signature_type']) && $request['signature_type'] === 'basah'): ?>
								<div class="card border-0 rounded-4 p-3 mb-3 text-start shadow-sm" style="background: #fffbeb; border-left: 4px solid #f59e0b !important;">
									<div class="d-flex align-items-start gap-2">
										<i class="fa-solid fa-pen-fancy text-warning fs-4 mt-1 flex-shrink-0"></i>
										<div>
											<div class="fw-bold text-dark">Wajib Mengambil Berkas Fisik di Ruang Tata Usaha (TU)</div>
											<div class="small text-muted mt-1">
												Surat ini ditetapkan <strong>WAJIB TANDA TANGAN BASAH &amp; CAP STEMPEL ASLI</strong>. Silakan datang langsung ke ruang Tata Usaha pada jam kerja dengan menyebutkan Kode: <code><?= e($request['tracking_code']) ?></code>.
											</div>
										</div>
									</div>
								</div>
								<a href="<?= site_url('permohonan/dokumen/'.e($request['tracking_code']).'/cetak') ?>" target="_blank" rel="noopener" class="btn btn-outline-secondary rounded-pill px-4 btn-sm">
									<i class="fa-solid fa-print me-1"></i> Cetak Pratinjau Blanko
								</a>
							<?php else: ?>
								<div class="alert alert-success border-0 small mb-3 rounded-4">
									<i class="fa-solid fa-circle-check me-1"></i> Surat telah ditandatangani secara digital &amp; distempel resmi. Anda dapat langsung mengunduh file PDF resmi di bawah ini.
								</div>
								<div class="d-flex flex-wrap gap-2 justify-content-center">
									<a href="<?= site_url('permohonan/dokumen/'.e($request['tracking_code']).'/cetak') ?>" target="_blank" rel="noopener" class="btn btn-primary rounded-pill px-4 shadow-sm fw-semibold">
										<i class="fa-solid fa-print me-1"></i> Cetak Dokumen Resmi
									</a>
									<a href="<?= site_url('permohonan/dokumen/'.e($request['tracking_code']).'/unduh') ?>" class="btn btn-outline-primary rounded-pill px-4 fw-semibold">
										<i class="fa-solid fa-download me-1"></i> Unduh File PDF
									</a>
								</div>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<!-- Bottom Action Button: Buat Permohonan Lain (Matches Image) -->
			<div class="text-center mb-5">
				<a href="<?= site_url('permohonan') ?>" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-semibold shadow-xs">
					<i class="fa-solid fa-plus me-1"></i> Buat Permohonan Lain
				</a>
			</div>
		<?php endif; ?>
	</div>
</div>
