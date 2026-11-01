<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<nav aria-label="breadcrumb">
	<ol class="breadcrumb small mb-2">
		<li class="breadcrumb-item"><a href="<?= site_url('surat-masuk') ?>">Surat Masuk</a></li>
		<li class="breadcrumb-item active" aria-current="page"><?= e($letter['agenda_number']) ?></li>
	</ol>
</nav>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
	<div>
		<div class="d-flex align-items-center gap-2">
			<h1 class="h4 fw-bold text-navy mb-0"><?= e($letter['agenda_number']) ?></h1>
			<?= incoming_status_badge($letter['status']) ?>
		</div>
		<small class="text-muted">Perihal: <?= e($letter['subject']) ?></small>
	</div>
	<div class="d-flex gap-2">
		<?php if (is_kepala_sekolah()): ?>
			<a href="<?= site_url('disposisi/buat/'.$letter['id']) ?>" class="btn btn-warning">
				<i class="fa-solid fa-share-nodes me-1"></i> Buat Disposisi
			</a>
		<?php endif; ?>

		<?php if (has_role(array('tu', 'superuser', 'admin')) && $letter['status'] === 'baru'): ?>
			<?= form_open('surat-masuk/teruskan/'.$letter['id'], array('class' => 'd-inline', 'data-confirm' => 'Teruskan surat masuk ini kepada Kepala Sekolah untuk didisposisikan?')) ?>
				<button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane me-1"></i> Teruskan ke Kepala Sekolah</button>
			<?= form_close() ?>
		<?php endif; ?>

		<?php if (!empty($kepsek['phone'])): 
			$msg_ks = "Assalamu'alaikum Bapak/Ibu Kepala Sekolah, ada surat masuk baru di sistem persuratan:\n- No. Agenda: *" . $letter['agenda_number'] . "*\n- Dari: *" . $letter['sender'] . "*\n- Perihal: *" . $letter['subject'] . "*\nLink Surat: " . site_url('surat-masuk/detail/'.$letter['id']) . "\nMohon berkenan untuk memeriksa dan memberikan lembar disposisi. Terima kasih.";
			$wa_ks_link = build_wa_link($kepsek['phone'], $msg_ks);
		?>
			<a href="<?= $wa_ks_link ?>" target="_blank" rel="noopener" class="btn btn-success" title="Kirim Notifikasi via WhatsApp ke Kepala Sekolah">
				<i class="fa-brands fa-whatsapp me-1"></i> Beritahu Kepsek
			</a>
		<?php endif; ?>

		<?php if (has_role(array('tu', 'superuser', 'admin'))): ?>
			<a href="<?= site_url('surat-masuk/edit/'.$letter['id']) ?>" class="btn btn-outline-secondary">
				<i class="fa-solid fa-pen-to-square me-1"></i> Edit
			</a>
			<?= form_open('surat-masuk/hapus/'.$letter['id'], array('class' => 'd-inline', 'data-confirm' => 'Hapus surat masuk '.$letter['agenda_number'].'?')) ?>
				<button type="submit" class="btn btn-outline-danger"><i class="fa-solid fa-trash me-1"></i> Hapus</button>
			<?= form_close() ?>
		<?php endif; ?>
	</div>
</div>

<div class="row g-3">
	<div class="col-lg-7">
		<div class="card mb-3">
			<div class="card-header bg-white py-3">
				<h2 class="h6 card-title mb-0"><i class="fa-solid fa-circle-info text-primary me-2"></i>Informasi Surat Masuk</h2>
			</div>
			<div class="card-body">
				<dl class="dl-meta row mb-0">
					<dt class="col-sm-4">No. Agenda</dt>
					<dd class="col-sm-8 letter-number fw-bold text-navy"><?= e($letter['agenda_number']) ?></dd>

					<dt class="col-sm-4">No. Surat Asal</dt>
					<dd class="col-sm-8"><?= e($letter['letter_number']) ?></dd>

					<dt class="col-sm-4">Tanggal Surat</dt>
					<dd class="col-sm-8"><?= e(tgl_indo($letter['letter_date'], TRUE)) ?></dd>

					<dt class="col-sm-4">Tanggal Diterima</dt>
					<dd class="col-sm-8"><?= e(tgl_indo($letter['received_date'], TRUE)) ?></dd>

					<dt class="col-sm-4">Pengirim / Asal</dt>
					<dd class="col-sm-8 fw-semibold">
						<?= e($letter['sender']) ?>
						<?php if ( ! empty($letter['is_external'])): ?>
							<span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1">Kirim Online / Eksternal</span>
						<?php endif; ?>
					</dd>

					<?php if ( ! empty($letter['is_external'])): ?>
						<?php if ( ! empty($letter['tracking_code'])): ?>
							<dt class="col-sm-4">No. Resi Pengirim</dt>
							<dd class="col-sm-8 font-monospace fw-bold text-primary"><?= e($letter['tracking_code']) ?></dd>
						<?php endif; ?>
						<?php if ( ! empty($letter['sender_phone'])): 
							$msg_wa_to_agency = "Yth. Narahubung *" . $letter['sender'] . "*,\nSurat Kedinasan Anda dengan No. Resi: *" . $letter['tracking_code'] . "* (Perihal: *" . $letter['subject'] . "*)\ntelah kami terima di Bagian Tata Usaha " . $school['school_name'] . " dengan Nomor Agenda: *" . $letter['agenda_number'] . "*.\nStatus saat ini: *" . ucfirst($letter['status']) . "*.\nTerima kasih.";
							$wa_agency_link = build_wa_link($letter['sender_phone'], $msg_wa_to_agency);
						?>
							<dt class="col-sm-4">Kontak PIC Pengirim</dt>
							<dd class="col-sm-8 d-flex align-items-center gap-2">
								<span class="fw-semibold"><?= e($letter['sender_phone']) ?></span>
								<a href="<?= $wa_agency_link ?>" target="_blank" rel="noopener" class="btn btn-xs btn-outline-success shadow-sm">
									<i class="fa-brands fa-whatsapp me-1"></i> Balas WhatsApp
								</a>
							</dd>
						<?php endif; ?>
						<?php if ( ! empty($letter['sender_email'])): ?>
							<dt class="col-sm-4">Email PIC Pengirim</dt>
							<dd class="col-sm-8"><i class="fa-regular fa-envelope me-1 text-muted"></i><?= e($letter['sender_email']) ?></dd>
						<?php endif; ?>
					<?php endif; ?>

					<dt class="col-sm-4">Ditujukan Kepada</dt>
					<dd class="col-sm-8"><?= e($letter['recipient'] ?: 'Kepala Sekolah') ?></dd>

					<dt class="col-sm-4">Perihal</dt>
					<dd class="col-sm-8 fw-semibold text-navy"><?= e($letter['subject']) ?></dd>

					<dt class="col-sm-4">Ringkasan / Catatan</dt>
					<dd class="col-sm-8"><?= $letter['description'] ? nl2br(e($letter['description'])) : '<span class="text-muted">-</span>' ?></dd>

					<dt class="col-sm-4">Dicatat Oleh</dt>
					<dd class="col-sm-8 small text-muted"><?= !empty($letter['is_external']) ? 'Portal Publik Eksternal' : e($letter['created_by_name'] ?: 'TU') ?> pada <?= e(tgl_indo($letter['created_at'])) ?> <?= e(substr($letter['created_at'], 11, 5)) ?></dd>
				</dl>
			</div>
		</div>

		<!-- Riwayat Disposisi Terkait Surat Ini -->
		<div class="card">
			<div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
				<h2 class="h6 card-title mb-0"><i class="fa-solid fa-share-nodes text-primary me-2"></i>Disposisi dari Kepala Sekolah</h2>
				<?php if (is_kepala_sekolah()): ?>
					<a href="<?= site_url('disposisi/buat/'.$letter['id']) ?>" class="btn btn-sm btn-outline-warning">
						<i class="fa-solid fa-plus me-1"></i> Tambah Disposisi
					</a>
				<?php endif; ?>
			</div>
			<div class="card-body p-0">
				<?php if (empty($dispositions)): ?>
					<div class="p-4 text-center text-muted">
						<i class="fa-solid fa-clipboard-question fa-2x mb-2 text-secondary opacity-50 d-block"></i>
						Surat ini belum memiliki lembar disposisi.
						<?php if ($letter['status'] === 'baru' && has_role(array('tu', 'superuser', 'admin'))): ?>
							<div class="mt-2">
								<span class="small">Klik tombol <strong>"Teruskan ke Kepala Sekolah"</strong> di atas agar Kepala Sekolah dapat mendisposisikan surat ini.</span>
							</div>
						<?php endif; ?>
					</div>
				<?php else: ?>
					<div class="list-group list-group-flush">
						<?php foreach ($dispositions as $d): ?>
							<a href="<?= site_url('disposisi/detail/'.$d['id']) ?>" class="list-group-item list-group-item-action p-3">
								<div class="d-flex w-100 justify-content-between align-items-center mb-1">
									<h6 class="mb-0 fw-bold text-navy"><?= e($d['disposition_number']) ?></h6>
									<div>
										<?= disposition_priority_badge($d['priority']) ?>
										<?= disposition_status_badge($d['status'], $d['deadline_date'] && strtotime($d['deadline_date']) < time()) ?>
									</div>
								</div>
								<p class="mb-1 text-muted small"><i class="fa-solid fa-quote-left me-1"></i><?= e($d['instruction']) ?></p>
								<div class="small text-muted d-flex justify-content-between mt-2">
									<span><i class="fa-solid fa-users me-1"></i> Penerima: <?= (int) $d['completed_recipients'] ?>/<?= (int) $d['total_recipients'] ?> Selesai</span>
									<span><i class="fa-solid fa-calendar-check me-1"></i> Tenggat: <?= $d['deadline_date'] ? e(tgl_indo($d['deadline_date'])) : 'Tidak ditentukan' ?></span>
								</div>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<div class="col-lg-5">
		<div class="card mb-3">
			<div class="card-header bg-white py-3">
				<h2 class="h6 card-title mb-0"><i class="fa-solid fa-paperclip text-primary me-2"></i>Berkas Fisik / Scan Surat</h2>
			</div>
			<div class="card-body">
				<?php if ($letter['file_path']): ?>
					<div class="p-3 border rounded text-center mb-3 bg-light">
						<i class="fa-solid fa-file-pdf fa-3x text-danger mb-2"></i>
						<div class="fw-bold small text-truncate" title="<?= e($letter['file_name']) ?>"><?= e($letter['file_name'] ?: 'berkas_surat_masuk.pdf') ?></div>
						<div class="small text-muted mb-3">Berkas scan resmi</div>
						<div class="d-flex gap-2 justify-content-center">
							<a href="<?= site_url('surat-masuk/berkas/'.$letter['id'].'?lihat=1') ?>" target="_blank" class="btn btn-sm btn-navy">
								<i class="fa-solid fa-eye me-1"></i> Buka / Pratinjau
							</a>
							<a href="<?= site_url('surat-masuk/berkas/'.$letter['id']) ?>" class="btn btn-sm btn-outline-primary">
								<i class="fa-solid fa-download me-1"></i> Unduh
							</a>
						</div>
					</div>
				<?php else: ?>
					<div class="p-4 border rounded text-center text-muted">
						<i class="fa-solid fa-file-circle-xmark fa-3x text-secondary opacity-50 mb-2"></i>
						<p class="small mb-0">Tidak ada file scan berkas yang diunggah.</p>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<div class="card">
			<div class="card-header bg-white py-3">
				<h2 class="h6 card-title mb-0"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Alur Surat Masuk</h2>
			</div>
			<div class="card-body small">
				<ol class="ps-3 mb-0">
					<li class="mb-2"><strong>Tata Usaha (TU):</strong> Menerima surat fisik/digital, memberi nomor agenda, mengunggah scan surat.</li>
					<li class="mb-2"><strong>Diteruskan:</strong> TU meneruskan surat ke Kepala Sekolah.</li>
					<li class="mb-2"><strong>Kepala Sekolah:</strong> Memeriksa surat dan membuat Lembar Disposisi (memilih penerima, instruksi, tenggat).</li>
					<li class="mb-2"><strong>Pegawai Penerima:</strong> Menerima notifikasi di "Disposisi Saya", melaksanakan tugas, dan melaporkan bukti tindak lanjut.</li>
					<li><strong>Selesai:</strong> Seluruh tindak lanjut selesai dan diarsipkan.</li>
				</ol>
			</div>
		</div>
	</div>
</div>
