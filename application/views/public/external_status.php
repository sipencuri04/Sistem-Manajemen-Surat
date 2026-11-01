<?php defined('BASEPATH') OR exit('No direct script access allowed');
$statuses = array(
	'baru'        => array('warning', 'fa-hourglass-half', 'Diterima di Antrean TU', 'Surat telah masuk ke sistem dan sedang menunggu verifikasi awal oleh petugas Tata Usaha.'),
	'diteruskan'  => array('primary', 'fa-paper-plane', 'Diteruskan ke Kepala Sekolah', 'Surat sudah diverifikasi TU dan telah diteruskan ke meja Kepala Sekolah untuk disposisi.'),
	'didisposisi' => array('info', 'fa-share-nodes', 'Sudah Didisposisikan', 'Kepala Sekolah telah memberikan instruksi disposisi kepada pejabat/pegawai/guru terkait untuk ditindaklanjuti.'),
	'selesai'     => array('success', 'fa-circle-check', 'Selesai Ditindaklanjuti', 'Instruksi dan urusan terkait surat ini telah selesai dilaksanakan oleh pihak sekolah.'),
	'diarsipkan'  => array('secondary', 'fa-box-archive', 'Telah Diarsipkan', 'Surat dan berkas telah masuk ke dalam buku arsip sekolah.'),
);
?>
<div class="row justify-content-center">
	<div class="col-lg-8 col-xl-7">
		<!-- Top Action Bar -->
		<div class="d-flex align-items-center justify-content-between mb-3">
			<a href="<?= site_url('permohonan') ?>" class="btn btn-sm btn-light border rounded-pill px-3 py-1 text-muted text-decoration-none">
				<i class="fa-solid fa-arrow-left me-1"></i> Beranda Portal
			</a>
			<a href="<?= site_url('kirim-surat') ?>" class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-semibold">
				<i class="fa-solid fa-plus me-1"></i> Kirim Surat Lain
			</a>
		</div>

		<?php if ($is_new && $letter): ?>
			<!-- Success Box (Saat baru berhasil mengirim) -->
			<div class="card border-0 shadow-sm rounded-4 mb-4 text-center overflow-hidden" style="background: linear-gradient(180deg, #eff6ff 0%, #ffffff 100%);">
				<div class="card-body py-4 px-3 px-sm-4">
					<div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white mb-3" style="width: 60px; height: 60px; box-shadow: 0 6px 16px rgba(29, 111, 224, 0.35);">
						<i class="fa-solid fa-check fa-2x"></i>
					</div>
					<h1 class="h4 fw-bold text-navy mb-1">Surat Berhasil Dikirim ke Sekolah!</h1>
					<p class="text-muted small mb-3">Terima kasih. Berkas surat dinas Anda telah berhasil masuk ke antrean Bagian Tata Usaha. Simpan atau catat <strong>Nomor Resi / Tanda Terima</strong> di bawah ini:</p>
					
					<div class="d-inline-block p-2 rounded-4 bg-white border mb-3 shadow-sm">
						<div class="tracking-code text-primary" id="trackingCode"><?= e($letter['tracking_code']) ?></div>
					</div>
					<div class="d-flex flex-wrap gap-2 justify-content-center">
						<button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" data-copy="#trackingCode">
							<i class="fa-regular fa-copy me-1"></i> Salin Nomor Resi
						</button>
						<?php 
						$school_phone = !empty($school['phone']) ? $school['phone'] : '';
						if ($school_phone !== ''): 
							$wa_msg_ext = "Yth. Bagian Tata Usaha " . $school['school_name'] . ", kami dari *" . $letter['sender'] . "* telah mengirimkan Surat Kedinasan secara online:\n- No. Surat: *" . $letter['letter_number'] . "*\n- Perihal: *" . $letter['subject'] . "*\n- No. Resi: *" . $letter['tracking_code'] . "*\nMohon dapat segera diproses. Terima kasih.";
							$wa_url_ext = build_wa_link($school_phone, $wa_msg_ext);
						?>
							<a href="<?= $wa_url_ext ?>" target="_blank" rel="noopener" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm fw-semibold">
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
				<h2 class="h6 fw-bold text-navy mb-2"><i class="fa-solid fa-magnifying-glass text-primary me-1"></i> Cek Tanda Terima Surat Kedinasan</h2>
				<p class="small text-muted mb-3">Masukkan kode resi (misal: <code><?= e($code ?: 'SM-XXXXXX') ?></code>) untuk melihat status penanganan surat oleh sekolah.</p>
				<form method="get" action="<?= site_url('kirim-surat/status') ?>" class="d-flex gap-2">
					<input type="text" name="kode" class="form-control rounded-3 text-uppercase fw-bold letter-number" maxlength="15" value="<?= e($code) ?>" placeholder="Contoh: SM-7K9X2M" aria-label="Nomor Resi" required>
					<button class="btn btn-primary rounded-3 px-3 px-sm-4" type="submit">
						<i class="fa-solid fa-magnifying-glass me-1"></i> Lacak
					</button>
				</form>
			</div>
		</div>

		<?php if ($searched && ! $letter): ?>
			<div class="card border-0 shadow-sm rounded-4 text-center py-5">
				<i class="fa-solid fa-triangle-exclamation fa-3x text-warning mb-2"></i>
				<h5 class="fw-bold text-navy">Nomor Resi Tidak Ditemukan</h5>
				<p class="text-muted small mb-0 px-3">Kode <code><?= e($code) ?></code> tidak terdaftar di sistem persuratan. Pastikan penulisan kode resi sudah benar.</p>
			</div>
		<?php elseif ($letter): 
			$st = isset($statuses[$letter['status']]) ? $statuses[$letter['status']] : array('secondary', 'fa-circle-info', ucfirst($letter['status']), '');
		?>
			<!-- Result Details Card -->
			<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
				<div class="p-3 p-sm-4 border-bottom bg-<?= $st[0] ?>-subtle d-flex align-items-center gap-3">
					<div class="rounded-circle p-3 bg-<?= $st[0] ?> text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width:48px;height:48px;">
						<i class="fa-solid <?= $st[1] ?> fs-5"></i>
					</div>
					<div>
						<span class="badge bg-<?= $st[0] ?> text-white text-uppercase px-2 py-1 mb-1" style="font-size:0.68rem;"><?= e($letter['status']) ?></span>
						<h3 class="h5 fw-bold text-<?= $st[0] ?>-emphasis mb-0"><?= e($st[2]) ?></h3>
					</div>
				</div>

				<div class="card-body p-3 p-sm-4">
					<p class="text-muted small mb-4"><?= e($st[3]) ?></p>

					<div class="table-responsive">
						<table class="table table-borderless table-sm mb-0">
							<tbody>
								<tr>
									<td class="text-muted small py-2" style="width: 35%;">Nomor Resi</td>
									<td class="py-2"><span class="badge bg-light text-dark border px-2 py-1 font-monospace fs-6"><?= e($letter['tracking_code']) ?></span></td>
								</tr>
								<tr>
									<td class="text-muted small py-2">Nomor Agenda Sekolah</td>
									<td class="py-2 font-monospace fw-bold text-navy"><?= e($letter['agenda_number']) ?></td>
								</tr>
								<tr>
									<td class="text-muted small py-2">Nomor Surat Asal</td>
									<td class="py-2 font-monospace"><?= e($letter['letter_number']) ?></td>
								</tr>
								<tr>
									<td class="text-muted small py-2">Instansi Pengirim</td>
									<td class="fw-semibold text-navy py-2"><?= e($letter['sender']) ?></td>
								</tr>
								<tr>
									<td class="text-muted small py-2">Perihal</td>
									<td class="py-2"><?= e($letter['subject']) ?></td>
								</tr>
								<tr>
									<td class="text-muted small py-2">Waktu Diterima</td>
									<td class="py-2 small text-muted"><?= e(tgl_indo($letter['received_date'])) ?> &bull; <?= e(substr($letter['created_at'], 11, 5)) ?> WIB</td>
								</tr>
								<tr>
									<td class="text-muted small py-2">Ditujukan Kepada</td>
									<td class="py-2"><?= e($letter['recipient'] ?: 'Kepala Sekolah') ?></td>
								</tr>
							</tbody>
						</table>
					</div>

					<div class="mt-4 pt-3 border-top d-flex flex-wrap gap-2 justify-content-between align-items-center">
						<span class="small text-muted"><i class="fa-solid fa-shield-halved text-success me-1"></i> Bukti tanda terima digital resmi</span>
						<button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="window.print();">
							<i class="fa-solid fa-print me-1"></i> Cetak Tanda Terima
						</button>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>
