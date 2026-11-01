<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row justify-content-center py-3">
	<div class="col-lg-8 col-md-10">

		<?php if (!empty($error)): ?>
			<!-- Alert Dokumen Tidak Valid / Palsu -->
			<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 border-top border-danger border-4">
				<div class="card-body p-4 text-center">
					<div class="mb-3">
						<span class="d-inline-flex align-items-center justify-content-center bg-danger bg-opacity-10 text-danger rounded-circle p-3" style="width:72px; height:72px;">
							<i class="fa-solid fa-triangle-exclamation fa-2x"></i>
						</span>
					</div>
					<h2 class="h5 fw-bold text-danger mb-2">Dokumen Tidak Ditemukan / Tidak Valid</h2>
					<p class="text-muted mb-4"><?= e($error) ?></p>

					<div class="alert alert-warning text-start small mb-0">
						<i class="fa-solid fa-circle-exclamation me-1"></i>
						<strong>Perhatian:</strong> Jika Anda memegang surat fisik yang mengatasnamakan sekolah tetapi tidak ditemukan pada sistem ini, harap waspada terhadap kemungkinan dokumen palsu. Silakan konfirmasi langsung ke ruang Tata Usaha.
					</div>
				</div>
			</div>

		<?php elseif (!empty($letter)): ?>
			<!-- Dokumen Sah & Terverifikasi -->
			<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
				<!-- Header Verifikasi -->
				<div class="p-4 text-white text-center" style="background: linear-gradient(135deg, #065f46 0%, #047857 50%, #059669 100%);">
					<div class="mb-2">
						<span class="d-inline-flex align-items-center justify-content-center bg-white bg-opacity-20 rounded-circle p-3" style="width:64px; height:64px;">
							<i class="fa-solid fa-circle-check fa-2x text-white"></i>
						</span>
					</div>
					<span class="badge bg-white text-success fw-bold px-3 py-1 rounded-pill mb-2" style="font-size:0.75rem; letter-spacing:0.05em;">
						<i class="fa-solid fa-shield-halved me-1"></i> RESMI &amp; TERVERIFIKASI
					</span>
					<h1 class="h4 fw-bold mb-1 text-white">Dokumen Sah Terdaftar di Sekolah</h1>
					<p class="small text-white text-opacity-75 mb-0">
						Tercatat secara otentik pada Sistem Manajemen Persuratan <?= e($school['school_name'] ?: 'Sekolah') ?>
					</p>
				</div>

				<div class="card-body p-4">
					<!-- Peringatan Anti-Pemalsuan -->
					<div class="alert alert-success bg-success bg-opacity-10 border-success border-opacity-25 rounded-3 d-flex align-items-start gap-2 mb-4">
						<i class="fa-solid fa-circle-info text-success mt-1 fs-5"></i>
						<div class="small text-dark">
							<strong>Verifikasi Fisik:</strong> Dokumen lembar kertas yang Anda pegang dinyatakan <strong>ASLI &amp; SAH</strong> hanya jika seluruh data (Nomor Surat, Tanggal, Perihal, dan Pejabat Penandatangan) di bawah ini sama persis dengan fisik berkas.
						</div>
					</div>

					<!-- Rincian Data Surat -->
					<div class="table-responsive">
						<table class="table table-bordered align-middle mb-0">
							<tbody>
								<tr>
									<th class="bg-light text-muted small" style="width:35%;">Nomor Surat Resmi</th>
									<td>
										<span class="fw-bold text-primary font-monospace fs-6"><?= e($letter['letter_number']) ?></span>
									</td>
								</tr>
								<tr>
									<th class="bg-light text-muted small">Perihal / Judul</th>
									<td class="fw-semibold text-dark"><?= e($letter['subject']) ?></td>
								</tr>
								<tr>
									<th class="bg-light text-muted small">Tanggal Surat</th>
									<td><?= e(tgl_indo($letter['letter_date'])) ?></td>
								</tr>
								<tr>
									<th class="bg-light text-muted small">Penandatangan</th>
									<td>
										<div class="fw-semibold text-dark"><?= e($letter['signer_name']) ?></div>
										<div class="small text-muted"><?= e($letter['signer_title']) ?><?= !empty($letter['signer_nip']) ? ' &middot; NIP. '.e($letter['signer_nip']) : '' ?></div>
									</td>
								</tr>
								<?php if (!empty($letter['recipient'])): ?>
								<tr>
									<th class="bg-light text-muted small">Ditujukan Kepada / Pemohon</th>
									<td><?= e($letter['recipient']) ?></td>
								</tr>
								<?php endif; ?>
								<tr>
									<th class="bg-light text-muted small">Instansi Penerbit</th>
									<td>
										<div class="fw-semibold"><?= e($school['school_name'] ?: 'Sekolah') ?></div>
										<?php if (!empty($school['npsn'])): ?>
											<div class="small text-muted">NPSN: <?= e($school['npsn']) ?></div>
										<?php endif; ?>
									</td>
								</tr>
								<tr>
									<th class="bg-light text-muted small">Status Dokumen</th>
									<td>
										<span class="badge bg-success px-2 py-1"><i class="fa-solid fa-check-double me-1"></i> Resmi Diterbitkan</span>
									</td>
								</tr>
							</tbody>
						</table>
					</div>

					<!-- Tombol Aksi -->
					<div class="d-flex justify-content-between align-items-center mt-4 pt-2 border-top">
						<a href="<?= site_url('permohonan') ?>" class="btn btn-outline-secondary btn-sm">
							<i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Beranda
						</a>
						<button type="button" onclick="window.print()" class="btn btn-light border btn-sm">
							<i class="fa-solid fa-print me-1"></i> Cetak Bukti Validasi
						</button>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<!-- Form Cek Manual jika belum ada surat -->
		<?php if (empty($letter) && empty($error)): ?>
			<div class="card border-0 shadow-sm rounded-4 mb-4">
				<div class="card-body p-4 text-center">
					<div class="mb-3">
						<span class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle p-3" style="width:68px; height:68px;">
							<i class="fa-solid fa-qrcode fa-2x"></i>
						</span>
					</div>
					<h2 class="h5 fw-bold text-navy mb-2">Verifikasi Keaslian Surat Sekolah</h2>
					<p class="text-muted small mb-4">Scan QR Code pada lembar surat menggunakan kamera HP Anda, atau masukkan Nomor Surat lengkap di bawah ini untuk memeriksa keasliannya.</p>

					<?= form_open('verifikasi', array('method' => 'GET', 'class' => 'text-start')) ?>
						<div class="mb-3">
							<label class="form-label fw-semibold small" for="nomor">Nomor Surat Resmi Sekolah</label>
							<div class="input-group">
								<span class="input-group-text bg-white"><i class="fa-solid fa-file-signature text-muted"></i></span>
								<input type="text" class="form-control" id="nomor" name="nomor" placeholder="Contoh: 421.5/028/SMK-MUH/XI/2026" required value="<?= e($nomor ?? '') ?>">
								<button class="btn btn-primary px-4 fw-semibold" type="submit">
									<i class="fa-solid fa-magnifying-glass me-1"></i> Cek Keaslian
								</button>
							</div>
						</div>
					<?= form_close() ?>
				</div>
			</div>
		<?php endif; ?>

	</div>
</div>
