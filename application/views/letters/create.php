<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<nav aria-label="breadcrumb">
	<ol class="breadcrumb small mb-2">
		<li class="breadcrumb-item"><a href="<?= site_url('surat/buat') ?>">Buat Surat</a></li>
		<li class="breadcrumb-item active" aria-current="page"><?= e($type['name']) ?></li>
	</ol>
</nav>
<div class="mb-3">
	<h2 class="h5 fw-bold text-navy mb-1">Langkah 2 &mdash; Isi data surat</h2>
	<p class="text-muted mb-0">Kolom bertanda <span class="text-danger">*</span> wajib diisi. Klik <strong>Pratinjau</strong> untuk melihat hasil sebelum menyimpan.</p>
</div>
<?php $this->load->view('letters/_form'); ?>
