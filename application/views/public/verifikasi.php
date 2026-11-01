<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row justify-content-center py-3">
	<div class="col-lg-8 col-md-10">

		<?php if (!empty($error)): ?>
			<!-- Alert Dokumen Tidak Valid / Palsu -->
			<div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4 border-top border-danger border-5">
				<div class="card-body p-4 text-center">
					<div class="mb-3">
						<span class="d-inline-flex align-items-center justify-content-center bg-danger bg-opacity-10 text-danger rounded-circle p-3" style="width:80px; height:80px;">
							<i class="fa-solid fa-triangle-exclamation fa-3x"></i>
						</span>
					</div>
					<h2 class="h4 fw-bold text-danger mb-2">PERINGATAN: DOKUMEN TIDAK VALID / PALSU</h2>
					<p class="text-muted mb-4"><?= e($error) ?></p>

					<div class="alert alert-danger text-start small mb-0 rounded-3">
						<i class="fa-solid fa-shield-xmark me-2 fs-5 text-danger align-middle"></i>
						<strong>Hati-hati terhadap Pemalsuan:</strong> Surat fisik yang Anda scan <strong>TIDAK TERCATAT</strong> dalam pangkalan data resmi <?= e($school['school_name'] ?: 'Sekolah') ?>. Berkas tersebut berkemungkinan besar adalah hasil editan/rekayasa ilegal. Harap konfirmasi langsung ke ruang Tata Usaha.
					</div>
				</div>
			</div>

		<?php elseif (!empty($letter)): ?>
			<!-- NOTIFIKASI UTAMA: SURAT INI ASLI -->
			<div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4">
				
				<!-- Banner Hijau Besar: SURAT INI ASLI -->
				<div class="p-4 p-md-5 text-white text-center position-relative" style="background: linear-gradient(135deg, #047857 0%, #059669 50%, #10b981 100%);">
					
					<!-- Lingkaran Animasi Centang -->
					<div class="mb-3 d-flex justify-content-center">
						<div class="d-inline-flex align-items-center justify-content-center bg-white rounded-circle shadow" style="width:84px; height:84px;">
							<i class="fa-solid fa-circle-check text-success fa-3x"></i>
						</div>
					</div>

					<!-- Badge & Judul Utama -->
					<div class="mb-2">
						<span class="badge bg-white text-success fw-bolder px-3 py-2 rounded-pill shadow-sm" style="font-size:0.85rem; letter-spacing:0.06em;">
							<i class="fa-solid fa-shield-halved me-1"></i> VERIFIKASI DIGITAL BERHASIL
						</span>
					</div>
					
					<h1 class="h2 fw-bold text-white mb-2" style="letter-spacing:-0.02em;">
						SURAT INI ASLI &amp; RESMI
					</h1>
					
					<p class="text-white text-opacity-90 mb-0 mx-auto" style="max-width:540px; font-size:0.95rem;">
						Dokumen ini <strong>100% Otentik</strong>, terdaftar sah, dan diterbitkan secara resmi oleh pangkalan data <strong><?= e($school['school_name'] ?: 'Sekolah') ?></strong>.
					</p>
				</div>

				<div class="card-body p-4 p-md-4">
					
					<!-- Notifikasi Banner Hasil Scan -->
					<div class="alert alert-success border-0 shadow-sm rounded-3 d-flex align-items-center gap-3 p-3 mb-4" style="background: #ecfdf5; border-left: 5px solid #10b981 !important;">
						<i class="fa-solid fa-certificate text-success fa-2x flex-shrink-0"></i>
						<div class="small text-dark">
							<strong class="d-block text-success" style="font-size:0.95rem;">Status Validasi: TERCATAT DAN SAH</strong>
							Barcode QR pada lembar surat fisik cocok persis dengan arsip induk di server sekolah. Dokumen sah digunakan untuk keperluan kedinasan, akademik, maupun instansi.
						</div>
					</div>

					<!-- Rincian Data Surat yang Terverifikasi -->
					<h3 class="h6 fw-bold text-navy mb-3"><i class="fa-solid fa-file-invoice text-primary me-2"></i>Rincian Berkas Resmi</h3>
					<div class="table-responsive rounded-3 border">
						<table class="table table-hover align-middle mb-0">
							<tbody>
								<tr>
									<th class="bg-light text-muted small" style="width:38%;">Nomor Surat Resmi</th>
									<td>
										<span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 font-monospace fs-6 fw-bold">
											<?= e($letter['letter_number']) ?>
										</span>
									</td>
								</tr>
								<tr>
									<th class="bg-light text-muted small">Perihal / Keterangan</th>
									<td class="fw-bold text-dark"><?= e($letter['subject']) ?></td>
								</tr>
								<tr>
									<th class="bg-light text-muted small">Tanggal Surat</th>
									<td class="fw-semibold text-secondary"><?= e(tgl_indo($letter['letter_date'])) ?></td>
								</tr>
								<tr>
									<th class="bg-light text-muted small">Penandatangan Resmi</th>
									<td>
										<div class="fw-bold text-dark"><?= e($letter['signer_name']) ?></div>
										<div class="small text-muted"><?= e($letter['signer_title']) ?><?= !empty($letter['signer_nip']) ? ' &middot; NIP. '.e($letter['signer_nip']) : '' ?></div>
									</td>
								</tr>
								<?php if (!empty($letter['recipient'])): ?>
								<tr>
									<th class="bg-light text-muted small">Ditujukan Kepada / Pemohon</th>
									<td class="fw-semibold text-dark"><?= e($letter['recipient']) ?></td>
								</tr>
								<?php endif; ?>
								<tr>
									<th class="bg-light text-muted small">Instansi Penerbit</th>
									<td>
										<div class="fw-bold text-navy"><?= e($school['school_name'] ?: 'Sekolah') ?></div>
										<?php if (!empty($school['npsn'])): ?>
											<div class="small text-muted">NPSN: <?= e($school['npsn']) ?> &middot; Akreditasi Resmi</div>
										<?php endif; ?>
									</td>
								</tr>
								<tr>
									<th class="bg-light text-muted small">Status Legalitas Dokumen</th>
									<td>
										<span class="badge bg-success px-3 py-2 fw-bold" style="font-size:0.8rem;">
											<i class="fa-solid fa-circle-check me-1"></i> SURAT INI ASLI &amp; BERLAKU
										</span>
									</td>
								</tr>
							</tbody>
						</table>
					</div>

					<!-- Panduan Pengecekan Fisik -->
					<div class="mt-4 p-3 bg-light rounded-3 border small text-muted">
						<div class="d-flex gap-2">
							<i class="fa-solid fa-triangle-exclamation text-warning fs-5 flex-shrink-0 mt-1"></i>
							<div>
								<strong class="text-dark">Tips Keamanan Bagi Penerima Dokumen:</strong>
								<br>Pastikan informasi (Nomor Surat, Tanggal, dan Nama yang bersangkutan) pada lembar kertas fisik yang Anda terima sama persis dengan rincian di atas. Jika berbeda, kertas fisik tersebut telah dipalsukan.
							</div>
						</div>
					</div>

					<!-- Tombol Aksi -->
					<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-4 pt-3 border-top">
						<a href="<?= site_url('permohonan') ?>" class="btn btn-outline-secondary btn-sm">
							<i class="fa-solid fa-house me-1"></i> Kembali ke Beranda
						</a>
						<button type="button" onclick="window.print()" class="btn btn-primary btn-sm">
							<i class="fa-solid fa-print me-1"></i> Cetak Bukti Verifikasi Asli
						</button>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<!-- Form Cek Manual jika belum scan -->
		<?php if (empty($letter) && empty($error)): ?>
			<div class="card border-0 shadow-sm rounded-4 mb-4">
				<div class="card-body p-4 text-center">
					<div class="mb-3">
						<span class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success rounded-circle p-3" style="width:72px; height:72px;">
							<i class="fa-solid fa-shield-halved fa-2x"></i>
						</span>
					</div>
					<h2 class="h5 fw-bold text-navy mb-2">Cek Keaslian Surat Sekolah</h2>
					<p class="text-muted small mb-4">Arahkan kamera ponsel Anda ke Barcode / QR Code pada lembar surat, atau masukkan Nomor Surat lengkap di bawah ini untuk membuktikan keasliannya.</p>

					<?= form_open('verifikasi', array('method' => 'GET', 'class' => 'text-start')) ?>
						<div class="mb-3">
							<label class="form-label fw-semibold small" for="nomor">Nomor Surat Resmi Sekolah</label>
							<div class="input-group">
								<span class="input-group-text bg-white"><i class="fa-solid fa-file-signature text-muted"></i></span>
								<input type="text" class="form-control" id="nomor" name="nomor" placeholder="Contoh: 421.5/028/SMK-MUH/XI/2026" required value="<?= e($nomor ?? '') ?>">
								<button class="btn btn-success px-4 fw-bold" type="submit">
									<i class="fa-solid fa-magnifying-glass me-1"></i> Periksa Keaslian
								</button>
							</div>
						</div>
					<?= form_close() ?>
				</div>
			</div>
		<?php endif; ?>

	</div>
</div>
