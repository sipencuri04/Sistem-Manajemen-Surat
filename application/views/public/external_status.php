<?php defined('BASEPATH') OR exit('No direct script access allowed');

// Konfigurasi status & tema visual alur surat masuk
$statuses = array(
	'baru' => array(
		'bg'        => 'linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%)',
		'border'    => '#fde68a',
		'badge_bg'  => '#f59e0b',
		'badge_txt' => '#ffffff',
		'label'     => 'DITERIMA DI TU',
		'icon'      => 'fa-hourglass-half',
		'title'     => 'Diterima di Tata Usaha',
		'desc'      => 'Surat telah dicatat di Buku Agenda TU dan sedang dalam antrean pemeriksaan berkas sebelum diserahkan ke Kepala Sekolah.',
		'step_active' => 2, // Verifikasi TU aktif
	),
	'diteruskan' => array(
		'bg'        => 'linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%)',
		'border'    => '#bfdbfe',
		'badge_bg'  => '#2563eb',
		'badge_txt' => '#ffffff',
		'label'     => 'DITERUSKAN KE KEPSEK',
		'icon'      => 'fa-paper-plane',
		'title'     => 'Diteruskan ke Kepala Sekolah',
		'desc'      => 'Surat telah diverifikasi TU dan sudah diserahkan ke meja Kepala Sekolah untuk ditelaah dan diberikan disposisi instruksi dinas.',
		'step_active' => 3, // Meja Kepsek aktif
	),
	'didisposisi' => array(
		'bg'        => 'linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%)',
		'border'    => '#99f6e4',
		'badge_bg'  => '#0d9488',
		'badge_txt' => '#ffffff',
		'label'     => 'SUDAH DIDISPOSISIKAN',
		'icon'      => 'fa-share-nodes',
		'title'     => 'Didisposisikan Kepala Sekolah',
		'desc'      => 'Kepala Sekolah telah menerbitkan lembar disposisi dan menugaskan pegawai/guru penanggung jawab untuk menindaklanjuti isi surat ini.',
		'step_active' => 5, // Tindak lanjut pegawai aktif
	),
	'selesai' => array(
		'bg'        => 'linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%)',
		'border'    => '#a7f3d0',
		'badge_bg'  => '#10b981',
		'badge_txt' => '#ffffff',
		'label'     => 'SELESAI DITINDAKLANJUTI',
		'icon'      => 'fa-circle-check',
		'title'     => 'Selesai Ditindaklanjuti',
		'desc'      => 'Seluruh instruksi, urusan, atau kegiatan terkait surat kedinasan ini telah tuntas dilaksanakan oleh pihak sekolah.',
		'step_active' => 5, // Selesai
	),
	'diarsipkan' => array(
		'bg'        => 'linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%)',
		'border'    => '#e2e8f0',
		'badge_bg'  => '#64748b',
		'badge_txt' => '#ffffff',
		'label'     => 'TELAH DIARSIPKAN',
		'icon'      => 'fa-box-archive',
		'title'     => 'Telah Diarsipkan',
		'desc'      => 'Rangkaian penanganan surat telah selesai dan berkas fisik/digital telah disimpan dalam buku arsip sekolah.',
		'step_active' => 5, // Diarsipkan
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
				<i class="fa-solid fa-magnifying-glass me-1"></i> Pelacakan Surat Masuk
			</span>
		</div>

		<?php if ($is_new && $letter): ?>
			<!-- Success Box (Saat baru berhasil mengirim) -->
			<div class="card border-0 shadow-sm rounded-4 mb-3 text-center overflow-hidden" style="background: linear-gradient(180deg, #eff6ff 0%, #ffffff 100%);">
				<div class="card-body py-4 px-3 px-sm-4">
					<div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white mb-3" style="width: 58px; height: 58px; box-shadow: 0 6px 16px rgba(29, 111, 224, 0.35);">
						<i class="fa-solid fa-check fa-2x"></i>
					</div>
					<h1 class="h4 fw-bold text-navy mb-1">Surat Berhasil Dikirim ke Sekolah!</h1>
					<p class="text-muted small mb-3">Terima kasih. Berkas surat dinas Anda telah berhasil masuk ke antrean Tata Usaha. Catat <strong>Nomor Agenda / Resi</strong> di bawah ini:</p>
					
					<div class="d-inline-block p-2 rounded-4 bg-white border mb-3 shadow-sm">
						<div class="tracking-code text-primary" id="trackingCode"><?= e($letter['agenda_number'] ?: $letter['tracking_code']) ?></div>
					</div>
					<div class="d-flex flex-wrap gap-2 justify-content-center">
						<button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" data-copy="#trackingCode">
							<i class="fa-regular fa-copy me-1"></i> Salin Nomor Lacak
						</button>
						<?php 
						$school_phone = !empty($school['phone']) ? $school['phone'] : '';
						if ($school_phone !== ''): 
							$wa_msg_ext = "Yth. Bagian Tata Usaha " . $school['school_name'] . ", kami dari *" . $letter['sender'] . "* telah mengirimkan Surat Kedinasan secara online:\n- No. Surat: *" . $letter['letter_number'] . "*\n- Perihal: *" . $letter['subject'] . "*\n- No. Agenda: *" . $letter['agenda_number'] . "*\nMohon dapat segera diproses. Terima kasih.";
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

		<!-- Search / Hero Card (Matches Image Design) -->
		<div class="card border-0 shadow-sm rounded-4 mb-3 overflow-hidden text-white" style="background: linear-gradient(135deg, #1d6fe0 0%, #0d47a1 100%);">
			<div class="card-body p-3 p-sm-4">
				<div class="d-flex align-items-center gap-3 mb-3">
					<div class="d-flex align-items-center justify-content-center rounded-3 bg-white bg-opacity-20 text-white" style="width: 48px; height: 48px; flex-shrink: 0;">
						<i class="fa-solid fa-envelope-open-text fs-4"></i>
					</div>
					<div>
						<h2 class="h5 fw-bold mb-0 text-white">Pelacakan Surat Kedinasan</h2>
						<div class="small text-white-50">Lacak alur penanganan surat masuk dengan Nomor Agenda atau Resi.</div>
					</div>
				</div>
				
				<form method="get" action="<?= site_url('kirim-surat/status') ?>" class="tracking-search-form d-flex gap-2 bg-white p-2 rounded-4 shadow-sm align-items-center">
					<input type="text" name="kode" class="form-control border-0 rounded-3 text-uppercase fw-bold letter-number px-3 flex-grow-1" maxlength="30" value="<?= e($code) ?>" placeholder="Contoh: SM-005/2026 atau SM-XXXXXX" aria-label="Nomor Agenda atau Resi" required style="letter-spacing: 0.5px; min-width: 0;">
					<button class="btn btn-primary rounded-3 px-3 py-2 fw-semibold d-flex align-items-center justify-content-center flex-shrink-0" style="min-width: 48px; height: 44px;" type="submit" aria-label="Lacak" title="Lacak">
						<i class="fa-solid fa-magnifying-glass fs-5"></i>
					</button>
				</form>
			</div>
		</div>

		<?php if ($searched && ! $letter): ?>
			<!-- Not Found Card -->
			<div class="card border-0 shadow-sm rounded-4 text-center py-5 bg-white mb-4">
				<i class="fa-solid fa-triangle-exclamation fa-3x text-warning mb-2"></i>
				<h5 class="fw-bold text-navy">Surat Masuk Tidak Ditemukan</h5>
				<p class="text-muted small mb-0 px-3">Nomor Agenda atau Kode <code><?= e($code) ?></code> tidak terdaftar di sistem persuratan. Pastikan penulisan nomor agenda (misal <strong>SM-005/2026</strong>) sudah sesuai.</p>
			</div>
		<?php elseif ($letter): 
			$st = isset($statuses[$letter['status']]) ? $statuses[$letter['status']] : $statuses['baru'];
			$st_act = $st['step_active'];
		?>
			<!-- Horizontal Stepper Tracker Card (5 Steps Surat Masuk Matches Image) -->
			<div class="tracking-stepper-card">
				<div class="tracking-stepper">
					<!-- Step 1: Penerimaan TU -->
					<div class="tracking-step completed">
						<div class="tracking-step-circle">
							<i class="fa-solid fa-check"></i>
						</div>
						<div class="tracking-step-label">Penerimaan TU</div>
					</div>

					<!-- Step 2: Verifikasi TU -->
					<div class="tracking-step <?= $st_act > 2 ? 'completed' : ($st_act == 2 ? 'active' : '') ?>">
						<div class="tracking-step-circle">
							<?php if ($st_act > 2): ?>
								<i class="fa-solid fa-check"></i>
							<?php elseif ($st_act == 2): ?>
								<i class="fa-solid fa-hourglass-half"></i>
							<?php else: ?>
								<i class="fa-regular fa-clock"></i>
							<?php endif; ?>
						</div>
						<div class="tracking-step-label">Verifikasi TU</div>
					</div>

					<!-- Step 3: Meja Kepala Sekolah -->
					<div class="tracking-step <?= $st_act > 3 ? 'completed' : ($st_act == 3 ? 'active' : '') ?>">
						<div class="tracking-step-circle">
							<?php if ($st_act > 3): ?>
								<i class="fa-solid fa-check"></i>
							<?php elseif ($st_act == 3): ?>
								<i class="fa-solid fa-paper-plane"></i>
							<?php else: ?>
								<i class="fa-regular fa-paper-plane"></i>
							<?php endif; ?>
						</div>
						<div class="tracking-step-label">Meja Kepsek</div>
					</div>

					<!-- Step 4: Disposisi Kepala Sekolah -->
					<div class="tracking-step <?= ($st_act >= 4 && in_array($letter['status'], array('didisposisi', 'selesai', 'diarsipkan'))) ? 'completed' : '' ?>">
						<div class="tracking-step-circle">
							<?php if (in_array($letter['status'], array('didisposisi', 'selesai', 'diarsipkan'))): ?>
								<i class="fa-solid fa-check"></i>
							<?php else: ?>
								<i class="fa-solid fa-share-nodes"></i>
							<?php endif; ?>
						</div>
						<div class="tracking-step-label">Disposisi Kepsek</div>
					</div>

					<!-- Step 5: Tindak Lanjut Pegawai (Akhir) -->
					<div class="tracking-step <?= in_array($letter['status'], array('selesai', 'diarsipkan')) ? 'completed' : ($letter['status'] === 'didisposisi' ? 'active' : '') ?>">
						<div class="tracking-step-circle">
							<?php if (in_array($letter['status'], array('selesai', 'diarsipkan'))): ?>
								<i class="fa-solid fa-check"></i>
							<?php elseif ($letter['status'] === 'didisposisi'): ?>
								<i class="fa-solid fa-arrows-rotate fa-spin"></i>
							<?php else: ?>
								<i class="fa-solid fa-user-check"></i>
							<?php endif; ?>
						</div>
						<div class="tracking-step-label">Tindak Lanjut Pegawai</div>
					</div>
				</div>
			</div>

			<!-- Status Summary Card (Matches Image Warm Style) -->
			<div class="card border-0 shadow-sm rounded-4 mb-3 overflow-hidden" style="background: <?= $st['bg'] ?>; border: 1px solid <?= $st['border'] ?> !important;">
				<div class="card-body p-3 p-sm-4 d-flex align-items-center gap-3">
					<!-- Icon Squircle -->
					<div class="d-flex align-items-center justify-content-center rounded-4 text-white shadow-xs flex-shrink-0" style="width: 52px; height: 52px; background: <?= $st['badge_bg'] ?>;">
						<i class="fa-solid <?= $st['icon'] ?> fs-4"></i>
					</div>

					<!-- Content -->
					<div class="flex-grow-1">
						<span class="badge rounded-pill px-2 py-1 text-uppercase mb-1" style="font-size: 0.68rem; background: <?= $st['badge_bg'] ?>; color: <?= $st['badge_txt'] ?>;">
							<?= e($st['label']) ?>
						</span>
						<h3 class="h5 fw-bold text-dark mb-1"><?= e($st['title']) ?></h3>
						<p class="small text-secondary mb-0"><?= e($st['desc']) ?></p>
					</div>

					<!-- 3D Folder Icon Right Side -->
					<div class="ms-auto d-none d-sm-flex align-items-center justify-content-center text-warning" style="font-size: 3rem; opacity: 0.9;">
						<i class="fa-solid fa-folder-open"></i>
					</div>
				</div>
			</div>

			<!-- Details Card (Clean Rows with Icons Matching Image) -->
			<div class="card border-0 shadow-sm rounded-4 mb-3 overflow-hidden bg-white">
				<div class="card-body p-3 p-sm-4">
					<div class="d-flex flex-column">
						<!-- Row 1: Nomor Agenda -->
						<div class="d-flex align-items-center py-2 border-bottom">
							<div class="d-flex align-items-center gap-2 text-muted" style="width: 145px; flex-shrink: 0;">
								<i class="fa-solid fa-barcode text-primary" style="width: 18px;"></i>
								<span class="small">Nomor Agenda</span>
							</div>
							<div class="me-2 text-muted">:</div>
							<div class="flex-grow-1">
								<span class="badge bg-light text-dark border font-monospace fs-6 px-2 py-1"><?= e($letter['agenda_number']) ?></span>
							</div>
						</div>

						<!-- Row 2: Nomor Surat Asal -->
						<div class="d-flex align-items-center py-2 border-bottom">
							<div class="d-flex align-items-center gap-2 text-muted" style="width: 145px; flex-shrink: 0;">
								<i class="fa-solid fa-file-lines text-primary" style="width: 18px;"></i>
								<span class="small">No. Surat Asal</span>
							</div>
							<div class="me-2 text-muted">:</div>
							<div class="flex-grow-1 font-monospace fw-semibold text-dark">
								<?= e($letter['letter_number']) ?>
							</div>
						</div>

						<!-- Row 3: Instansi Pengirim -->
						<div class="d-flex align-items-center py-2 border-bottom">
							<div class="d-flex align-items-center gap-2 text-muted" style="width: 145px; flex-shrink: 0;">
								<i class="fa-solid fa-building text-primary" style="width: 18px;"></i>
								<span class="small">Instansi Pengirim</span>
							</div>
							<div class="me-2 text-muted">:</div>
							<div class="flex-grow-1 fw-bold text-navy">
								<?= e($letter['sender']) ?>
							</div>
						</div>

						<!-- Row 4: Ditujukan Kepada -->
						<div class="d-flex align-items-center py-2 border-bottom">
							<div class="d-flex align-items-center gap-2 text-muted" style="width: 145px; flex-shrink: 0;">
								<i class="fa-solid fa-user-tie text-primary" style="width: 18px;"></i>
								<span class="small">Ditujukan Ke</span>
							</div>
							<div class="me-2 text-muted">:</div>
							<div class="flex-grow-1 text-dark small fw-semibold">
								<?= e($letter['recipient'] ?: 'Kepala Sekolah') ?>
							</div>
						</div>

						<!-- Row 5: Perihal -->
						<div class="d-flex align-items-center py-2 border-bottom">
							<div class="d-flex align-items-center gap-2 text-muted" style="width: 145px; flex-shrink: 0;">
								<i class="fa-solid fa-quote-left text-primary" style="width: 18px;"></i>
								<span class="small">Perihal Surat</span>
							</div>
							<div class="me-2 text-muted">:</div>
							<div class="flex-grow-1 text-dark fw-semibold">
								<?= e($letter['subject']) ?>
							</div>
						</div>

						<!-- Row 6: Waktu Diterima TU -->
						<div class="d-flex align-items-center py-2 <?= (!empty($letter['tracking_code']) || !empty($letter['description'])) ? 'border-bottom' : '' ?>">
							<div class="d-flex align-items-center gap-2 text-muted" style="width: 145px; flex-shrink: 0;">
								<i class="fa-solid fa-calendar-check text-primary" style="width: 18px;"></i>
								<span class="small">Waktu Diterima TU</span>
							</div>
							<div class="me-2 text-muted">:</div>
							<div class="flex-grow-1 text-dark small">
								<?= e(tgl_indo($letter['received_date'], TRUE)) ?> &bull; <?= e(substr($letter['created_at'], 11, 5)) ?> WIB
							</div>
						</div>

						<!-- Row 7: Kode Resi Pengirim (Jika ada) -->
						<?php if (!empty($letter['tracking_code'])): ?>
							<div class="d-flex align-items-center py-2 <?= !empty($letter['description']) ? 'border-bottom' : '' ?>">
								<div class="d-flex align-items-center gap-2 text-muted" style="width: 145px; flex-shrink: 0;">
									<i class="fa-solid fa-receipt text-primary" style="width: 18px;"></i>
									<span class="small">Nomor Resi</span>
								</div>
								<div class="me-2 text-muted">:</div>
								<div class="flex-grow-1 font-monospace text-primary fw-bold">
									<?= e($letter['tracking_code']) ?>
								</div>
							</div>
						<?php endif; ?>

						<!-- Row 8: Ringkasan Catatan -->
						<?php if (!empty($letter['description'])): ?>
							<div class="d-flex align-items-start py-2">
								<div class="d-flex align-items-center gap-2 text-muted" style="width: 145px; flex-shrink: 0;">
									<i class="fa-solid fa-circle-info text-primary" style="width: 18px;"></i>
									<span class="small">Keterangan</span>
								</div>
								<div class="me-2 text-muted">:</div>
								<div class="flex-grow-1 text-muted small">
									<?= nl2br(e($letter['description'])) ?>
								</div>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<!-- Kartu Khusus: Penugasan Pegawai & Disposisi (Diberikan Kepada Siapa di Akhir) -->
			<div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden bg-white">
				<div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
					<h4 class="h6 fw-bold text-navy mb-0">
						<i class="fa-solid fa-users text-primary me-2"></i>Penugasan Akhir: Diberikan Kepada Siapa?
					</h4>
					<span class="badge bg-primary-subtle text-primary border border-primary-subtle">
						<?= !empty($dispositions) ? count($dispositions).' Disposisi Pimpinan' : 'Menunggu Arahan' ?>
					</span>
				</div>
				<div class="card-body p-3 p-sm-4">
					<?php if (empty($dispositions)): ?>
						<div class="p-3 text-center text-muted bg-light rounded-3">
							<i class="fa-solid fa-user-clock fa-2x text-warning mb-2 d-block"></i>
							<div class="fw-semibold text-dark">Belum Ditugaskan ke Pegawai</div>
							<div class="small text-muted mt-1">
								Surat saat ini masih berada di tahap 
								<strong><?= $letter['status'] === 'baru' ? 'Pemeriksaan TU' : 'Meja Kepala Sekolah' ?></strong>.
								Segera setelah Kepala Sekolah memberikan disposisi, nama guru/pegawai pelaksana dan progres tindak lanjut akan otomatis tampil di sini.
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

								<!-- Kotak Instruksi Kepala Sekolah -->
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
								<div class="small fw-bold text-navy text-uppercase mb-2" style="font-size:0.75rem;">
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

											<!-- Progres Bar -->
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

				<div class="card-footer bg-light p-3 d-flex flex-wrap gap-2 justify-content-between align-items-center">
					<span class="small text-muted"><i class="fa-solid fa-shield-halved text-success me-1"></i> Bukti tanda terima digital resmi SMK Muhammadiyah Bandongan</span>
					<button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="window.print();">
						<i class="fa-solid fa-print me-1"></i> Cetak Tanda Terima
					</button>
				</div>
			</div>

			<!-- Bottom Action Button -->
			<div class="text-center mb-5">
				<a href="<?= site_url('kirim-surat') ?>" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-semibold shadow-xs">
					<i class="fa-solid fa-plus me-1"></i> Kirim Surat Kedinasan Lain
				</a>
			</div>
		<?php endif; ?>
	</div>
</div>
