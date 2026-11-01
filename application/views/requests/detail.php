<?php defined('BASEPATH') OR exit('No direct script access allowed');
$labels = Letter_request_model::$statuses;
$colors = array('pending' => 'warning', 'approved' => 'success', 'taken' => 'secondary', 'rejected' => 'danger');
$id = (int) $req['id'];
?>
<nav aria-label="breadcrumb">
	<ol class="breadcrumb small mb-2">
		<li class="breadcrumb-item"><a href="<?= site_url('permohonan-masuk') ?>">Permohonan Siswa</a></li>
		<li class="breadcrumb-item active" aria-current="page"><?= e($req['tracking_code']) ?></li>
	</ol>
</nav>

<div class="d-flex flex-wrap align-items-center gap-2 mb-3 page-actions">
	<div class="me-auto page-actions-title">
		<h2 class="h5 fw-bold text-navy mb-0"><?= e($req['type_name']) ?></h2>
		<div class="small text-muted">Kode <span class="letter-number"><?= e($req['tracking_code']) ?></span> &middot;
			<span class="badge rounded-pill text-bg-<?= $colors[$req['status']] ?>"><?= e($labels[$req['status']]) ?></span></div>
	</div>
	<?php if ($req['status'] === 'pending' && $type && (int) $type['is_active']): ?>
		<a href="<?= site_url('surat/buat/'.(int) $req['letter_type_id'].'?permohonan='.$id) ?>" class="btn btn-primary"><i class="fa-solid fa-file-circle-check me-1"></i> Proses Jadi Surat</a>
	<?php endif; ?>
	<?php if ($req['letter_id']): ?>
		<a href="<?= site_url('surat/detail/'.(int) $req['letter_id']) ?>" class="btn btn-outline-primary"><i class="fa-solid fa-envelope-open-text me-1"></i> Lihat Surat</a>
	<?php endif; ?>
	<?php if ($req['status'] === 'approved'): ?>
		<?= form_open('permohonan-masuk/diambil/'.$id, array('class' => 'd-inline')) ?>
			<button class="btn btn-success" type="submit"><i class="fa-solid fa-hand-holding me-1"></i> Tandai Sudah Diambil</button>
		<?= form_close() ?>
	<?php endif; ?>
</div>

<div class="row g-3">
	<div class="col-lg-7">
		<div class="card">
			<div class="card-header"><h3 class="card-title">Data yang Diisi Siswa</h3></div>
			<div class="card-body">
				<?php if ($req['status'] === 'pending'): ?>
					<div class="alert alert-warning small py-2"><i class="fa-solid fa-triangle-exclamation me-1"></i> Data diisi sendiri oleh siswa tanpa login. Cocokkan dengan buku induk/data sekolah sebelum menerbitkan surat.</div>
				<?php endif; ?>
				<dl class="dl-meta row mb-0">
				<?php if ($tpl): foreach ($tpl['fields'] as $name => $field): ?>
					<dt class="col-sm-5"><?= e($field['label']) ?></dt>
					<dd class="col-sm-7 text-break">
					<?php if ($field['type'] === 'rows'): ?>
						<?php if (empty($values[$name])): ?>-<?php else: ?>
							<ol class="ps-3 mb-0"><?php foreach ($values[$name] as $row): ?><li><?= e(implode(' · ', array_filter(array_map('strval', $row), 'strlen'))) ?></li><?php endforeach; ?></ol>
						<?php endif; ?>
					<?php else: ?>
						<?= $values[$name] !== '' ? nl2br(e($values[$name])) : '<span class="text-muted">-</span>' ?>
					<?php endif; ?>
					</dd>
				<?php endforeach; endif; ?>
				</dl>
			</div>
		</div>
	</div>
	<div class="col-lg-5">
		<div class="card mb-3">
			<div class="card-header"><h3 class="card-title">Pemohon</h3></div>
			<div class="card-body">
				<dl class="dl-meta mb-0">
					<dt>Nama</dt><dd><?= e($req['requester_name']) ?><?= $req['requester_class'] ? ' &middot; '.e($req['requester_class']) : '' ?></dd>
					<dt>WhatsApp/HP</dt><dd>
						<strong><?= e($req['requester_phone']) ?></strong>
						<?php 
						if (!empty($req['requester_phone'])) {
							if ($req['status'] === 'approved') {
								$msg_wa_to_siswa = "Halo *" . $req['requester_name'] . "*,\nPermohonan surat *" . $req['type_name'] . "* Anda di " . $school['school_name'] . " telah *SELESAI DIPROSES* dengan Nomor Surat: *" . $req['letter_number'] . "*.\nSilakan unduh dokumen atau ambil fisik surat di ruang Tata Usaha dengan menyebutkan Kode: *" . $req['tracking_code'] . "*.\nTerima kasih.";
							} elseif ($req['status'] === 'rejected') {
								$msg_wa_to_siswa = "Halo *" . $req['requester_name'] . "*,\nPermohonan surat *" . $req['type_name'] . "* Anda di " . $school['school_name'] . " (Kode: *" . $req['tracking_code'] . "*) belum dapat diproses karena:\n\"" . $req['admin_note'] . "\"\nSilakan hubungi ruang Tata Usaha untuk informasi lebih lanjut.";
							} else {
								$msg_wa_to_siswa = "Halo *" . $req['requester_name'] . "*,\nPermohonan surat *" . $req['type_name'] . "* Anda dengan Kode: *" . $req['tracking_code'] . "* sedang diperiksa oleh petugas Tata Usaha " . $school['school_name'] . ".";
							}
							$wa_siswa_href = build_wa_link($req['requester_phone'], $msg_wa_to_siswa);
						} else {
							$wa_siswa_href = '#';
						}
						?>
						<?php if (!empty($req['requester_phone'])): ?>
							<a class="btn btn-sm btn-outline-success ms-2 shadow-sm" href="<?= $wa_siswa_href ?>" target="_blank" rel="noopener">
								<i class="fa-brands fa-whatsapp me-1"></i> Kirim Notifikasi WA
							</a>
						<?php endif; ?>
					</dd>
					<dt>Catatan pemohon</dt><dd><?= $req['requester_note'] ? e($req['requester_note']) : '-' ?></dd>
					<dt>Diajukan</dt><dd class="small"><?= e(tgl_indo($req['created_at'], TRUE)) ?> <?= e(substr($req['created_at'], 11, 5)) ?> &middot; IP <?= e($req['ip_address']) ?></dd>
					<?php if ($req['processed_at']): ?>
						<dt>Diproses</dt><dd class="small"><?= e(tgl_indo($req['processed_at'])) ?> <?= e(substr($req['processed_at'], 11, 5)) ?> oleh <?= e($req['processed_by_name'] ?: '-') ?></dd>
					<?php endif; ?>
					<?php if ($req['letter_number']): ?><dt>Nomor surat</dt><dd class="letter-number"><?= e($req['letter_number']) ?></dd><?php endif; ?>
					<?php if ($req['admin_note']): ?><dt>Keterangan untuk siswa</dt><dd><?= nl2br(e($req['admin_note'])) ?></dd><?php endif; ?>
				</dl>
			</div>
		</div>

		<?php if ($req['status'] === 'pending'): ?>
		<div class="card border-danger-subtle">
			<div class="card-header"><h3 class="card-title text-danger"><i class="fa-solid fa-ban me-1"></i> Tolak Permohonan</h3></div>
			<div class="card-body">
				<?= form_open('permohonan-masuk/tolak/'.$id, array('data-confirm' => 'Tolak permohonan '.$req['tracking_code'].'? Alasan akan terlihat oleh siswa saat cek status.')) ?>
					<label class="form-label" for="admin_note">Alasan (terlihat oleh siswa)</label>
					<textarea class="form-control mb-2" id="admin_note" name="admin_note" rows="3" maxlength="500" required placeholder="Contoh: NIS tidak sesuai data sekolah. Silakan ajukan ulang atau datang ke ruang TU."></textarea>
					<button class="btn btn-outline-danger" type="submit">Tolak Permohonan</button>
				<?= form_close() ?>
			</div>
		</div>
		<?php endif; ?>
	</div>
</div>
