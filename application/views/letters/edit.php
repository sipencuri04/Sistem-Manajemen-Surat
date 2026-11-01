<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<nav aria-label="breadcrumb">
	<ol class="breadcrumb small mb-2">
		<li class="breadcrumb-item"><a href="<?= site_url('surat') ?>">Data Surat</a></li>
		<li class="breadcrumb-item"><a href="<?= site_url('surat/detail/'.(int) $letter['id']) ?>"><?= e($letter['letter_number']) ?></a></li>
		<li class="breadcrumb-item active" aria-current="page">Edit</li>
	</ol>
</nav>
<?php if ($letter['status'] === 'final'): ?>
	<div class="alert alert-info"><i class="fa-solid fa-circle-info me-1"></i> Surat ini berstatus <strong>Final</strong>. Setelah disimpan, PDF arsip akan dibuat ulang sesuai perubahan.</div>
<?php endif; ?>
<?php $this->load->view('letters/_form'); ?>
