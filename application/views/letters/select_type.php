<?php defined('BASEPATH') OR exit('No direct script access allowed');
$icons = array(
	'siswa_aktif' => 'fa-user-check', 'lulus' => 'fa-graduation-cap', 'pindah' => 'fa-right-left',
	'panggilan_ortu' => 'fa-people-roof', 'undangan_rapat' => 'fa-calendar-check', 'permohonan' => 'fa-hand-holding-hand',
	'pemberitahuan' => 'fa-bullhorn', 'tugas' => 'fa-briefcase', 'pengantar_pkl' => 'fa-industry',
	'kerja_sama' => 'fa-handshake', 'selesai_pkl' => 'fa-certificate', 'rekomendasi' => 'fa-thumbs-up',
	'izin' => 'fa-door-open', 'pengantar' => 'fa-paper-plane', 'balasan' => 'fa-reply', 'berita_acara' => 'fa-file-signature',
);
?>
<div class="mb-3">
	<h2 class="h5 fw-bold text-navy mb-1">Langkah 1 &mdash; Pilih jenis surat</h2>
	<p class="text-muted mb-0">Formulir akan menyesuaikan jenis surat yang dipilih. Klik ikon <i class="fa-solid fa-circle-exclamation text-primary"></i> di pojok kartu untuk melihat kegunaan surat.</p>
</div>

<div class="mb-3">
	<input type="search" class="form-control" placeholder="Cari jenis surat..." id="typeSearch" aria-label="Cari jenis surat"
		oninput="var q=this.value.toLowerCase();document.querySelectorAll('[data-type-name]').forEach(function(el){el.classList.toggle('d-none',el.getAttribute('data-type-name').indexOf(q)===-1)})">
</div>

<?php if (empty($types)): ?>
	<div class="card"><div class="empty-state"><i class="fa-solid fa-layer-group d-block"></i>
		<p>Belum ada jenis surat aktif.</p><a class="btn btn-primary btn-sm" href="<?= site_url('jenis-surat') ?>">Kelola Jenis Surat</a></div></div>
<?php else: ?>
<div class="row g-3">
	<?php foreach ($types as $t): ?>
		<?php
		$ok  = $registry->exists($t['template_key']);
		$tpl = $registry->get($t['template_key']);
		$required = array();
		if ($tpl)
		{
			foreach ($tpl['fields'] as $f) { if ($f['required']) $required[] = $f['label']; }
		}
		$guide = array(
			'name'     => $t['name'],
			'code'     => $t['code'],
			'layout'   => $tpl && $tpl['layout'] === 'keterangan' ? 'Surat keterangan (judul di tengah, nomor di bawah judul)' : 'Surat dinas (Nomor, Lampiran, Perihal, dan Kepada Yth.)',
			'purpose'  => $tpl && $tpl['guide']['purpose'] !== '' ? $tpl['guide']['purpose'] : (string) $t['description'],
			'when'     => $tpl ? $tpl['guide']['when'] : array(),
			'tip'      => $tpl ? $tpl['guide']['tip'] : '',
			'required' => $required,
			'url'      => $ok ? site_url('surat/buat/'.(int) $t['id']) : '',
		);
		?>
		<div class="col-sm-6 col-lg-4 col-xxl-3" data-type-name="<?= e(strtolower($t['name'].' '.$t['code'])) ?>">
			<div class="type-card<?= $ok ? '' : ' opacity-50' ?>"<?= $ok ? '' : ' title="Template tidak ditemukan"' ?>>
				<div class="icon"><i class="fa-solid <?= isset($icons[$t['template_key']]) ? $icons[$t['template_key']] : 'fa-file-lines' ?>"></i></div>
				<div class="min-w-0 flex-grow-1">
					<div class="code"><?= e($t['code']) ?></div>
					<?php if ($ok): ?>
						<a class="fw-semibold text-navy text-decoration-none stretched-link d-block" href="<?= site_url('surat/buat/'.(int) $t['id']) ?>"><?= e($t['name']) ?></a>
					<?php else: ?>
						<div class="fw-semibold text-navy"><?= e($t['name']) ?></div>
					<?php endif; ?>
					<?php if ( ! empty($t['description'])): ?><div class="small text-muted"><?= e($t['description']) ?></div><?php endif; ?>
				</div>
				<button type="button" class="type-info" data-guide="<?= e(json_encode($guide, JSON_UNESCAPED_UNICODE)) ?>"
					title="Kegunaan <?= e($t['name']) ?>" aria-label="Lihat kegunaan <?= e($t['name']) ?>">
					<i class="fa-solid fa-circle-exclamation"></i>
				</button>
			</div>
		</div>
	<?php endforeach; ?>
</div>

<!-- Pop-up kegunaan jenis surat -->
<div class="modal fade" id="guideModal" tabindex="-1" aria-labelledby="guideTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header">
				<div>
					<span class="badge text-bg-light border mb-1" id="guideCode"></span>
					<h5 class="modal-title text-navy fw-bold" id="guideTitle"></h5>
				</div>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
			</div>
			<div class="modal-body">
				<h6 class="guide-h"><i class="fa-solid fa-bullseye me-1"></i> Kegunaan</h6>
				<p id="guidePurpose" class="mb-3"></p>
				<div id="guideWhenBox">
					<h6 class="guide-h"><i class="fa-solid fa-list-check me-1"></i> Contoh pemakaian</h6>
					<ul id="guideWhen" class="mb-3 ps-3"></ul>
				</div>
				<h6 class="guide-h"><i class="fa-solid fa-file-lines me-1"></i> Bentuk surat</h6>
				<p id="guideLayout" class="mb-3"></p>
				<div id="guideReqBox">
					<h6 class="guide-h"><i class="fa-solid fa-asterisk me-1"></i> Data yang wajib disiapkan</h6>
					<div id="guideRequired" class="d-flex flex-wrap gap-1 mb-3"></div>
				</div>
				<div id="guideTipBox" class="alert alert-info py-2 small mb-0"><i class="fa-solid fa-lightbulb me-1"></i> <span id="guideTip"></span></div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
				<a href="#" class="btn btn-primary" id="guideCreate"><i class="fa-solid fa-file-circle-plus me-1"></i> Buat Surat Ini</a>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>
