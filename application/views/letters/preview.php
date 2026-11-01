<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?><!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow">
	<title>Pratinjau Surat</title>
	<style>
		body { margin: 0; background: #e9edf3; font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif; }
		.bar { position: sticky; top: 0; background: #0b2a5b; color: #fff; padding: 10px 16px; display: flex; gap: 12px; align-items: center; z-index: 10; font-size: 14px; flex-wrap: wrap; }
		.bar button { background: #1d6fe0; border: 0; color: #fff; padding: 7px 14px; border-radius: 6px; cursor: pointer; font-size: 14px; }
		.bar .grow { flex: 1; }
		.errors { max-width: 21cm; margin: 16px auto 0; background: #fff4e5; border: 1px solid #f0c27b; color: #7a4b00; border-radius: 8px; padding: 10px 16px; font-size: 14px; }
		.errors ul { margin: 6px 0 0; padding-left: 18px; }
		.sheet-wrap { padding: 20px 10px 40px; overflow-x: auto; }
		@media print { .bar, .errors { display: none; } body { background: #fff; } .sheet-wrap { padding: 0; } }
	</style>
</head>
<body>
	<div class="bar">
		<strong>Pratinjau surat</strong>
		<span class="grow"><?= $is_auto ? 'Nomor surat masih perkiraan &mdash; nomor final ditetapkan saat disimpan.' : 'Perubahan belum disimpan.' ?></span>
		<button type="button" onclick="window.close()">Tutup</button>
	</div>
	<?php if ( ! empty($errors)): ?>
		<div class="errors"><strong>Masih ada data yang belum valid:</strong>
			<ul><?php foreach ($errors as $er): ?><li><?= e(strip_tags($er)) ?></li><?php endforeach; ?></ul>
		</div>
	<?php endif; ?>
	<div class="sheet-wrap"><?= $html ?></div>
</body>
</html>
