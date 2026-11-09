<?php defined('BASEPATH') OR exit('No direct script access allowed');
$statuses = array(
	'baru'        => array('warning', 'fa-hourglass-half', 'Diterima di Bagian Tata Usaha (TU)', 'Surat masuk telah tercatat di buku agenda TU dan sedang dalam antrean pemeriksaan awal sebelum diserahkan ke Kepala Sekolah.'),
	'diteruskan'  => array('primary', 'fa-paper-plane', 'Diteruskan ke Kepala Sekolah', 'Surat telah diverifikasi oleh Tata Usaha dan saat ini berada di meja Kepala Sekolah untuk ditelaah dan diberikan disposisi dinas.'),
	'didisposisi' => array('info', 'fa-share-nodes', 'Sudah Didisposisikan oleh Kepala Sekolah', 'Kepala Sekolah telah menerbitkan lembar disposisi dan menugaskan pegawai/guru penanggung jawab untuk menindaklanjuti isi surat ini.'),
	'selesai'     => array('success', 'fa-circle-check', 'Selesai Ditindaklanjuti', 'Seluruh instruksi, urusan, atau kegiatan terkait surat kedinasan ini telah tuntas dilaksanakan oleh pihak sekolah.'),
	'diarsipkan'  => array('secondary', 'fa-box-archive', 'Telah Diarsipkan', 'Seluruh rangkaian penanganan surat telah selesai dan berkas fisik/digital telah disimpan dalam buku arsip sekolah.'),
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
					<p class="text-muted small mb-3">Terima kasih. Berkas surat dinas Anda telah berhasil masuk ke antrean Bagian Tata Usaha. Simpan atau catat <strong>Nomor Resi / Agenda</strong> di bawah ini untuk melacak status:</p>
					
					<div class="d-inline-block p-2 rounded-4 bg-white border mb-3 shadow-sm">
						<div class="tracking-code text-primary" id="trackingCode"><?= e($letter['tracking_code'] ?: $letter['agenda_number']) ?></div>
					</div>
					<div class="d-flex flex-wrap gap-2 justify-content-center">
						<button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" data-copy="#trackingCode">
							<i class="fa-regular fa-copy me-1"></i> Salin Nomor Lacak
						</button>
						<?php 
						$school_phone = !empty($school['phone']) ? $school['phone'] : '';
						if ($school_phone !== ''): 
							$wa_msg_ext = "Yth. Bagian Tata Usaha " . $school['school_name'] . ", kami dari *" . $letter['sender'] . "* telah mengirimkan Surat Kedinasan secara online:\n- No. Surat: *" . $letter['letter_number'] . "*\n- Perihal: *" . $letter['subject'] . "*\n- No. Agenda/Resi: *" . ($letter['tracking_code'] ?: $letter['agenda_number']) . "*\nMohon dapat segera diproses. Terima kasih.";
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
				<h2 class="h6 fw-bold text-navy mb-2"><i class="fa-solid fa-magnifying-glass text-primary me-1"></i> Lacak Status Surat Kedinasan / Surat Masuk</h2>
				<p class="small text-muted mb-3">Masukkan Nomor Agenda (misal: <code>SM-005/2026</code>) atau Kode Resi (misal: <code><?= e($code ?: 'SM-7K9X2M') ?></code>) untuk melihat alur penanganan surat:</p>
				<form method="get" action="<?= site_url('kirim-surat/status') ?>" class="d-flex gap-2">
					<input type="text" name="kode" class="form-control rounded-3 text-uppercase fw-bold letter-number" maxlength="30" value="<?= e($code) ?>" placeholder="Contoh: SM-005/2026 atau SM-7K9X2M" aria-label="Nomor Agenda / Resi" required>
					<button class="btn btn-primary rounded-3 px-3 px-sm-4 fw-semibold" type="submit">
						<i class="fa-solid fa-magnifying-glass me-1"></i> Lacak
					</button>
				</form>
			</div>
		</div>

		<?php if ($searched && ! $letter): ?>
			<div class="card border-0 shadow-sm rounded-4 text-center py-5">
				<i class="fa-solid fa-triangle-exclamation fa-3x text-warning mb-2"></i>
				<h5 class="fw-bold text-navy">Surat Tidak Ditemukan</h5>
				<p class="text-muted small mb-0 px-3">Kode / Nomor <code><?= e($code) ?></code> tidak terdaftar di sistem surat masuk. Pastikan penulisan Nomor Agenda (misal <strong>SM-005/2026</strong>) atau Kode Resi sudah tepat.</p>
			</div>
		<?php elseif ($letter): 
			$st = isset($statuses[$letter['status']]) ? $statuses[$letter['status']] : array('secondary', 'fa-circle-info', ucfirst($letter['status']), '');
			$is_forwarded   = in_array($letter['status'], array('diteruskan', 'didisposisi', 'selesai', 'diarsipkan'), TRUE);
			$is_dispositioned = in_array($letter['status'], array('didisposisi', 'selesai', 'diarsipkan'), TRUE);
			$is_finished    = in_array($letter['status'], array('selesai', 'diarsipkan'), TRUE);
		?>
			<!-- Result Details Card: Header -->
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

					<!-- Visual Timeline Alur Penanganan Surat -->
					<div class="p-3 bg-light rounded-4 mb-4 border">
						<div class="small fw-bold text-uppercase text-navy mb-3">
							<i class="fa-solid fa-timeline text-primary me-1"></i> Alur Perjalanan Surat:
						</div>

						<div class="position-relative ps-2">
							<!-- Tahap 1: Diterima TU -->
							<div class="d-flex gap-3 mb-3">
								<div class="text-success mt-1 fs-5"><i class="fa-solid fa-circle-check"></i></div>
								<div class="flex-fill">
									<div class="fw-bold text-navy small">1. Diterima &amp; Diagendakan Tata Usaha (TU)</div>
									<div class="small text-muted">
										Surat dicatat dengan No. Agenda: <strong class="text-dark font-monospace"><?= e($letter['agenda_number']) ?></strong> pada <?= e(tgl_indo($letter['received_date'], TRUE)) ?>.
									</div>
								</div>
							</div>

							<!-- Tahap 2: Diteruskan ke Kepala Sekolah -->
							<div class="d-flex gap-3 mb-3">
								<div class="<?= $is_forwarded ? 'text-success' : 'text-warning' ?> mt-1 fs-5">
									<i class="fa-solid <?= $is_forwarded ? 'fa-circle-check' : 'fa-hourglass-half' ?>"></i>
								</div>
								<div class="flex-fill">
									<div class="fw-bold <?= $is_forwarded ? 'text-navy' : 'text-secondary' ?> small">2. Diteruskan ke Meja Kepala Sekolah</div>
									<div class="small text-muted">
										<?php if ($is_forwarded): ?>
											Surat telah diverifikasi TU dan diserahkan ke Kepala Sekolah untuk dipelajari &amp; didisposisi.
										<?php else: ?>
											Sedang antre verifikasi staf Tata Usaha sebelum diteruskan ke Kepala Sekolah.
										<?php endif; ?>
									</div>
								</div>
							</div>

							<!-- Tahap 3: Disposisi Kepala Sekolah -->
							<div class="d-flex gap-3 mb-3">
								<div class="<?= $is_dispositioned ? 'text-success' : ($is_forwarded ? 'text-warning' : 'text-muted opacity-50') ?> mt-1 fs-5">
									<i class="fa-solid <?= $is_dispositioned ? 'fa-circle-check' : ($is_forwarded ? 'fa-hourglass-half' : 'fa-circle-notch') ?>"></i>
								</div>
								<div class="flex-fill">
									<div class="fw-bold <?= $is_dispositioned ? 'text-navy' : 'text-secondary' ?> small">3. Disposisi Arahan &amp; Instruksi Kepala Sekolah</div>
									<div class="small text-muted">
										<?php if ($is_dispositioned): ?>
											Kepala Sekolah telah menerbitkan lembar disposisi dan menunjuk penanggung jawab pelaksana.
										<?php elseif ($is_forwarded): ?>
											Menunggu Kepala Sekolah memeriksa berkas dan menentukan arahan disposisi.
										<?php else: ?>
											Belum sampai di tahap telaah Kepala Sekolah.
										<?php endif; ?>
									</div>
								</div>
							</div>

							<!-- Tahap 4: Diberikan Kepada Pegawai & Tindak Lanjut -->
							<div class="d-flex gap-3 mb-3">
								<div class="<?= $is_finished ? 'text-success' : (!empty($dispositions) ? 'text-primary' : 'text-muted opacity-50') ?> mt-1 fs-5">
									<i class="fa-solid <?= $is_finished ? 'fa-circle-check' : (!empty($dispositions) ? 'fa-arrows-rotate' : 'fa-circle-notch') ?>"></i>
								</div>
								<div class="flex-fill">
									<div class="fw-bold <?= !empty($dispositions) ? 'text-navy' : 'text-secondary' ?> small">4. Penugasan &amp; Tindak Lanjut Pegawai</div>
									<div class="small text-muted">
										<?php if (!empty($dispositions)): ?>
											Surat dan instruksi telah diserahkan kepada pegawai/guru penanggung jawab untuk ditindaklanjuti.
										<?php else: ?>
											Menunggu penetapan nama pegawai penerima dari Kepala Sekolah.
										<?php endif; ?>
									</div>
								</div>
							</div>

							<!-- Tahap 5: Selesai / Arsip -->
							<div class="d-flex gap-3">
								<div class="<?= $is_finished ? 'text-success' : 'text-muted opacity-50' ?> mt-1 fs-5">
									<i class="fa-solid <?= $is_finished ? 'fa-circle-check' : 'fa-flag-checkered' ?>"></i>
								</div>
								<div class="flex-fill">
									<div class="fw-bold <?= $is_finished ? 'text-navy' : 'text-secondary' ?> small">5. Selesai Ditindaklanjuti &amp; Arsip</div>
									<div class="small text-muted">
										<?php if ($is_finished): ?>
											Semua urusan kedinasan terkait surat ini telah tuntas dilaksanakan dan berkas telah diarsipkan.
										<?php else: ?>
											Tahap akhir setelah seluruh pegawai pelaksana menyelesaikan tugasnya.
										<?php endif; ?>
									</div>
								</div>
							</div>
						</div>
					</div>

					<!-- Bagian Spesifik: Diberikan Kepada Siapa di Akhir (Penerima Disposisi) -->
					<div class="card border border-primary-subtle rounded-4 mb-4 overflow-hidden shadow-xs">
						<div class="card-header bg-primary-subtle py-3 border-bottom border-primary-subtle d-flex align-items-center justify-content-between">
							<h4 class="h6 fw-bold text-navy mb-0">
								<i class="fa-solid fa-users text-primary me-2"></i>Penugasan Akhir: Diberikan Kepada Siapa?
							</h4>
							<span class="badge bg-primary text-white">
								<?= !empty($dispositions) ? count($dispositions).' Disposisi' : 'Belum Ditugaskan' ?>
							</span>
						</div>
						<div class="card-body p-3 p-sm-4 bg-white">
							<?php if (empty($dispositions)): ?>
								<div class="p-3 text-center text-muted bg-light rounded-3">
									<i class="fa-solid fa-user-clock fa-2x text-warning mb-2 d-block"></i>
									<div class="fw-semibold text-dark">Surat Belum Didisposisikan ke Pegawai</div>
									<div class="small text-muted mt-1">
										Surat saat ini masih berada di tahap 
										<strong><?= $letter['status'] === 'baru' ? 'Pemeriksaan TU' : 'Meja Kepala Sekolah' ?></strong>.
										Segera setelah Kepala Sekolah menentukan nama guru/staf penerima, rincian pegawai dan progres tindak lanjut akan otomatis tampil di sini.
									</div>
								</div>
							<?php else: ?>
								<?php foreach ($dispositions as $idx => $d): ?>
									<div class="<?= $idx > 0 ? 'pt-3 border-top mt-3' : '' ?>">
										<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
											<div>
												<span class="badge bg-secondary-subtle text-secondary border font-monospace me-1"><?= e($d['disposition_number'] ?: 'DISP-'.$d['id']) ?></span>
												<span class="small text-muted">Pemberi Disposisi: <strong><?= e($d['creator_name']) ?></strong> (<?= e($d['creator_position'] ?: 'Kepala Sekolah') ?>)</span>
											</div>
											<div class="d-flex gap-1">
												<?= disposition_priority_badge($d['priority']) ?>
												<?= disposition_status_badge($d['status']) ?>
											</div>
										</div>

										<div class="p-3 bg-light rounded-3 border-start border-warning border-4 mb-3">
											<div class="small fw-bold text-navy mb-1"><i class="fa-solid fa-quote-left me-1 text-warning"></i> Instruksi Kepala Sekolah:</div>
											<div class="small text-dark fw-semibold"><?= nl2br(e($d['instruction'])) ?></div>
											<?php if (!empty($d['notes'])): ?>
												<div class="small text-muted mt-2 border-top pt-2"><i class="fa-solid fa-circle-info me-1"></i> Catatan tambahan: <?= nl2br(e($d['notes'])) ?></div>
											<?php endif; ?>
											<?php if (!empty($d['deadline_date'])): ?>
												<div class="small text-danger fw-semibold mt-1"><i class="fa-solid fa-calendar-xmark me-1"></i> Batas Waktu Penyelesaian: <?= e(tgl_indo($d['deadline_date'], TRUE)) ?></div>
											<?php endif; ?>
										</div>

										<!-- Daftar Pegawai Penerima Disposisi -->
										<div class="small fw-bold text-navy text-uppercase mb-2" style="font-size:0.78rem;">
											<i class="fa-solid fa-user-check me-1 text-primary"></i> Pegawai Yang Diberikan Tugas (Penerima):
										</div>

										<?php if (empty($d['recipients'])): ?>
											<p class="small text-muted italic">Belum ada rincian penerima pegawai.</p>
										<?php else: ?>
											<div class="d-flex flex-column gap-2">
												<?php foreach ($d['recipients'] as $r): 
													$r_status = $r['status'];
													$r_badge = 'warning';
													$r_label = 'Menunggu Tindak Lanjut';
													$r_icon  = 'fa-clock';
													if ($r_status === 'diproses') {
														$r_badge = 'info';
														$r_label = 'Sedang Dikerjakan';
														$r_icon  = 'fa-spinner fa-spin';
													} elseif ($r_status === 'selesai') {
														$r_badge = 'success';
														$r_label = 'Selesai';
														$r_icon  = 'fa-check';
													} elseif ($r_status === 'menunggu_informasi') {
														$r_badge = 'secondary';
														$r_label = 'Menunggu Info Tambahan';
														$r_icon  = 'fa-circle-question';
													}
												?>
												<div class="p-3 border rounded-3 bg-white shadow-xs">
													<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
														<div>
															<div class="fw-bold text-navy fs-6"><i class="fa-solid fa-user-tie me-1 text-primary"></i> <?= e($r['full_name']) ?></div>
															<div class="small text-muted"><?= e($r['position'] ?: 'Guru / Tenaga Pendidik') ?> <?= !empty($r['nip']) ? '&bull; NIP: '.e($r['nip']) : '' ?></div>
														</div>
														<div>
															<span class="badge bg-<?= $r_badge ?>-subtle text-<?= $r_badge ?> border border-<?= $r_badge ?>-subtle px-2 py-1">
																<i class="fa-solid <?= $r_icon ?> me-1"></i> <?= $r_label ?>
															</span>
														</div>
													</div>

													<!-- Progress Bar -->
													<div class="mb-2">
														<div class="d-flex justify-content-between align-items-center small text-muted mb-1">
															<span>Progres Tindak Lanjut</span>
															<strong class="text-dark"><?= (int)$r['progress_percent'] ?>%</strong>
														</div>
														<div class="progress" style="height: 6px;">
															<div class="progress-bar <?= $r['progress_percent'] >= 100 ? 'bg-success' : 'bg-primary' ?>" role="progressbar" style="width: <?= (int)$r['progress_percent'] ?>%"></div>
														</div>
													</div>

													<?php if (!empty($r['progress_notes'])): ?>
														<div class="p-2 rounded bg-light border small text-dark mt-2">
															<strong><i class="fa-regular fa-comment-dots text-primary me-1"></i> Laporan / Catatan Bukti:</strong><br>
															<?= nl2br(e($r['progress_notes'])) ?>
														</div>
													<?php endif; ?>
												</div>
												<?php endforeach; ?>
											</div>
										<?php endif; ?>
									</div>
								<?php endforeach; ?>
							<?php endif; ?>
						</div>
					</div>

					<!-- Ringkasan Data Surat Kedinasan -->
					<div class="table-responsive">
						<table class="table table-borderless table-sm mb-0">
							<tbody>
								<?php if (!empty($letter['tracking_code'])): ?>
									<tr>
										<td class="text-muted small py-2" style="width: 35%;">Nomor Resi / Tanda Terima</td>
										<td class="py-2"><span class="badge bg-light text-dark border px-2 py-1 font-monospace fs-6"><?= e($letter['tracking_code']) ?></span></td>
									</tr>
								<?php endif; ?>
								<tr>
									<td class="text-muted small py-2">Nomor Agenda Sekolah</td>
									<td class="py-2 font-monospace fw-bold text-navy fs-6"><?= e($letter['agenda_number']) ?></td>
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
									<td class="py-2 fw-semibold"><?= e($letter['subject']) ?></td>
								</tr>
								<tr>
									<td class="text-muted small py-2">Waktu Diterima TU</td>
									<td class="py-2 small text-muted"><?= e(tgl_indo($letter['received_date'], TRUE)) ?> &bull; <?= e(substr($letter['created_at'], 11, 5)) ?> WIB</td>
								</tr>
								<tr>
									<td class="text-muted small py-2">Ditujukan Kepada</td>
									<td class="py-2"><?= e($letter['recipient'] ?: 'Kepala Sekolah') ?></td>
								</tr>
								<?php if (!empty($letter['description'])): ?>
									<tr>
										<td class="text-muted small py-2">Keterangan / Ringkasan</td>
										<td class="py-2 small text-muted"><?= nl2br(e($letter['description'])) ?></td>
									</tr>
								<?php endif; ?>
							</tbody>
						</table>
					</div>

					<div class="mt-4 pt-3 border-top d-flex flex-wrap gap-2 justify-content-between align-items-center">
						<span class="small text-muted"><i class="fa-solid fa-shield-halved text-success me-1"></i> Bukti tanda terima digital resmi SMK Muhammadiyah Bandongan</span>
						<button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="window.print();">
							<i class="fa-solid fa-print me-1"></i> Cetak Tanda Terima
						</button>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>
