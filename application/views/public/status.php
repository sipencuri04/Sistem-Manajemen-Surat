<?php defined('BASEPATH') OR exit('No direct script access allowed');
/** Samarkan nama: "Siswa Contoh" -> "Si*** Co****" */
$mask = function ($name) {
	$out = array();
	foreach (preg_split('/\s+/', trim($name)) as $w)
	{
		$len = mb_strlen($w);
		$out[] = $len <= 2 ? $w : mb_substr($w, 0, 2).str_repeat('*', $len - 2);
	}
	return implode(' ', $out);
};
$badges = array(
	'pending'   => array('warning', 'fa-hourglass-half', 'Menunggu Pemeriksaan', 'Permohonan sudah diterima dan menunggu diverifikasi oleh petugas Tata Usaha.'),
	'processed' => array('info', 'fa-arrows-rotate', 'Sedang Diproses', 'Surat sedang dibuat oleh Tata Usaha, proses penandatanganan Kepala Sekolah, dan pembubuhan stempel resmi.'),
	'approved'  => array('success', 'fa-circle-check', 'Siap Dicetak / Diambil', 'Surat resmi telah disahkan oleh Kepala Sekolah dan distempel Tata Usaha. Dokumen siap diambil di ruang TU atau diunduh.'),
	'taken'     => array('secondary', 'fa-box-archive', 'Sudah Diambil', 'Fisik surat resmi sudah diserahkan / diambil pemohon di ruang Tata Usaha.'),
	'rejected'  => array('danger', 'fa-circle-xmark', 'Ditolak', 'Permohonan tidak dapat diproses. Lihat keterangan dari petugas TU di bawah.'),
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
			<div class="card border-0 shadow-sm rounded-4 mb-4 text-center overflow-hidden" style="background: linear-gradient(180deg, #ecfdf5 0%, #ffffff 100%);">
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

		<!-- Search / Query Form Card -->
		<div class="card border-0 shadow-sm rounded-4 mb-4">
			<div class="card-body p-3 p-sm-4">
				<h2 class="h6 fw-bold text-navy mb-2"><i class="fa-solid fa-ticket-simple text-primary me-1"></i> Cek Status Permohonan Lain</h2>
				<form method="get" action="<?= site_url('permohonan/cek') ?>" class="d-flex gap-2">
					<input type="text" name="kode" class="form-control rounded-3 text-uppercase fw-bold letter-number" maxlength="12" value="<?= e($code) ?>" placeholder="Masukkan kode permohonan" aria-label="Kode permohonan" required>
					<button class="btn btn-primary rounded-3 px-3 px-sm-4" type="submit">
						<i class="fa-solid fa-magnifying-glass me-1"></i> Cari
					</button>
				</form>
			</div>
		</div>

		<?php if ($searched && ! $request): ?>
			<div class="card border-0 shadow-sm rounded-4 text-center py-5">
				<i class="fa-solid fa-triangle-exclamation fa-3x text-warning mb-2"></i>
				<h5 class="fw-bold text-navy">Kode Tidak Ditemukan</h5>
				<p class="text-muted small mb-0 px-3">Kode permohonan <code><?= e($code) ?></code> tidak terdaftar di sistem. Mohon periksa kembali pengetikan kode Anda.</p>
			</div>
		<?php elseif ($request): $b = $badges[$request['status']]; ?>
			<!-- Result Details Card -->
			<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
				<div class="p-3 p-sm-4 border-bottom bg-<?= $b[0] ?>-subtle d-flex align-items-center gap-3">
					<div class="rounded-circle p-3 bg-<?= $b[0] ?> text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width:48px;height:48px;">
						<i class="fa-solid <?= $b[1] ?> fs-5"></i>
					</div>
					<div>
						<div class="badge bg-<?= $b[0] ?> text-white text-uppercase px-2 py-1 mb-1" style="font-size:0.68rem;"><?= e($request['status']) ?></div>
						<h3 class="h5 fw-bold text-<?= $b[0] ?>-emphasis mb-0"><?= e($b[2]) ?></h3>
					</div>
				</div>

				<div class="card-body p-3 p-sm-4">
					<p class="text-muted small mb-4"><?= e($b[3]) ?></p>

					<div class="table-responsive">
						<table class="table table-borderless table-sm mb-0">
							<tbody>
								<tr>
									<td class="text-muted small py-2" style="width: 35%;">Kode Lacak</td>
									<td class="py-2"><span class="badge bg-light text-dark border px-2 py-1 font-monospace fs-6"><?= e($request['tracking_code']) ?></span></td>
								</tr>
								<tr>
									<td class="text-muted small py-2">Jenis Surat</td>
									<td class="fw-semibold text-navy py-2"><?= e($request['type_name']) ?></td>
								</tr>
								<tr>
									<td class="text-muted small py-2">Nama Siswa</td>
									<td class="py-2"><?= e($mask($request['requester_name'])) ?></td>
								</tr>
								<?php if (!empty($request['requester_email'])): ?>
									<tr>
										<td class="text-muted small py-2">Gmail Siswa</td>
										<td class="py-2 small text-muted"><i class="fa-solid fa-envelope text-primary me-1"></i><?= e($request['requester_email']) ?></td>
									</tr>
								<?php endif; ?>
								<tr>
									<td class="text-muted small py-2">Waktu Pengajuan</td>
									<td class="py-2 small text-muted"><?= e(tgl_indo($request['created_at'])) ?> &bull; <?= e(substr($request['created_at'], 11, 5)) ?> WIB</td>
								</tr>
								<?php if ( ! empty($request['letter_number'])): ?>
									<tr>
										<td class="text-muted small py-2">Nomor Surat</td>
										<td class="py-2 font-monospace fw-bold text-navy"><?= e($request['letter_number']) ?></td>
									</tr>
								<?php endif; ?>
								<?php if ($request['processed_at']): ?>
									<tr>
										<td class="text-muted small py-2">Waktu Selesai</td>
										<td class="py-2 small text-muted"><?= e(tgl_indo($request['processed_at'])) ?> &bull; <?= e(substr($request['processed_at'], 11, 5)) ?> WIB</td>
									</tr>
								<?php endif; ?>
								<?php if ($request['admin_note']): ?>
									<tr>
										<td class="text-muted small py-2">Catatan Petugas</td>
										<td class="py-2 text-danger small"><?= nl2br(e($request['admin_note'])) ?></td>
									</tr>
								<?php endif; ?>
							</tbody>
						</table>
					</div>

					<?php if (in_array($request['status'], array('approved', 'taken'), TRUE) && ! empty($request['letter_id'])): ?>
						<div class="mt-4 pt-3 border-top text-center">
							<?php if (isset($request['signature_type']) && $request['signature_type'] === 'basah'): ?>
								<div class="card border-0 rounded-4 p-3 mb-3 text-start shadow-sm" style="background: #fffbeb; border-left: 4px solid #f59e0b !important;">
									<div class="d-flex align-items-start gap-2">
										<i class="fa-solid fa-pen-fancy text-warning fs-4 mt-1 flex-shrink-0"></i>
										<div>
											<div class="fw-bold text-dark">Wajib Mengambil Berkas Fisik di Ruang Tata Usaha (TU)</div>
											<div class="small text-muted mt-1">
												Surat permohonan Anda telah selesai diproses dengan <strong>Nomor Surat: <?= e($request['letter_number']) ?></strong>.
												Oleh petugas TU, surat ini ditetapkan <strong>WAJIB TANDA TANGAN BASAH &amp; CAP STEMPEL ASLI</strong> dari Kepala Sekolah.
											</div>
											<div class="small text-dark mt-2 fw-semibold">
												<i class="fa-solid fa-clock me-1 text-primary"></i> Silakan datang langsung ke ruang Tata Usaha pada jam kerja sekolah dengan menyebutkan Kode Permohonan: <code><?= e($request['tracking_code']) ?></code>.
											</div>
										</div>
									</div>
								</div>
								<div class="d-flex flex-wrap gap-2 justify-content-center">
									<a href="<?= site_url('permohonan/dokumen/'.e($request['tracking_code']).'/cetak') ?>" target="_blank" rel="noopener" class="btn btn-outline-secondary rounded-pill px-4 btn-sm">
										<i class="fa-solid fa-print me-1"></i> Cetak Pratinjau Blanko
									</a>
								</div>
							<?php else: ?>
								<div class="alert alert-success border-0 small mb-3">
									<i class="fa-solid fa-circle-check me-1"></i> Surat resmi telah disahkan dengan <strong>Tanda Tangan &amp; Stempel Digital</strong>. Anda dapat mengunduh atau mencetak mandiri langsung di bawah ini:
								</div>
								<div class="d-flex flex-wrap gap-2 justify-content-center">
									<a href="<?= site_url('permohonan/dokumen/'.e($request['tracking_code']).'/cetak') ?>" target="_blank" rel="noopener" class="btn btn-primary rounded-pill px-4 shadow-sm fw-semibold">
										<i class="fa-solid fa-print me-1"></i> Cetak Surat Resmi
									</a>
									<a href="<?= site_url('permohonan/dokumen/'.e($request['tracking_code']).'/unduh') ?>" class="btn btn-outline-primary rounded-pill px-4 fw-semibold">
										<i class="fa-solid fa-file-arrow-down me-1"></i> Unduh PDF
									</a>
								</div>
							<?php endif; ?>
						</div>
					<?php elseif ($request['status'] === 'processed'): ?>
						<div class="mt-4 pt-3 border-top text-center">
							<div class="alert alert-info border-0 rounded-4 text-start p-3 mb-0 small">
								<div class="d-flex gap-2">
									<i class="fa-solid fa-circle-info fs-5 text-info mt-1 flex-shrink-0"></i>
									<div>
										<div class="fw-bold text-dark">Tahap Pemrosesan Dokumen Surat</div>
										<div class="text-muted mt-1">
											<?php if (empty($request['approval_status']) || $request['approval_status'] === 'menunggu_persetujuan'): ?>
												Surat permohonan telah dibuat drafnya dan saat ini sedang menunggu persetujuan / tanda tangan Kepala Sekolah.
											<?php elseif ($request['approval_status'] === 'disetujui' && (empty($request['stamp_type']) || $request['stamp_type'] === 'belum_stempel')): ?>
												Surat telah ditandatangani Kepala Sekolah dan sedang dalam proses pembubuhan cap stempel resmi oleh Tata Usaha.
											<?php else: ?>
												Surat sedang dalam proses penyelesaian administrasi dan legalisasi sekolah.
											<?php endif; ?>
										</div>
										<div class="mt-2 text-primary fw-semibold">
											<i class="fa-solid fa-arrows-rotate me-1"></i> Halaman unduh/cetak akan otomatis aktif setelah surat selesai disahkan &amp; distempel. Silakan cek berkala.
										</div>
									</div>
								</div>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>

		<div class="text-center mt-3">
			<a href="<?= site_url('permohonan') ?>" class="btn btn-sm btn-light border rounded-pill px-3 text-muted text-decoration-none">
				<i class="fa-solid fa-plus me-1"></i> Buat Permohonan Lain
			</a>
		</div>
	</div>
</div>
