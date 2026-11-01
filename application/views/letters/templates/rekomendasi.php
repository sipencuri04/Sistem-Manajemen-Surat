<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* Surat Rekomendasi Siswa */ ?>
<?= letter_paragraph($t['opening']) ?>
<?= letter_data_table(array(
	'Nama'                 => $v['nama_siswa'],
	'NIS / NISN'           => trim($v['nis'].($v['nisn'] !== '' ? ' / '.$v['nisn'] : '')),
	'Kelas'                => $v['kelas'],
	'Konsentrasi Keahlian' => $v['kompetensi_keahlian'],
)) ?>
<?= letter_paragraph($t['body']) ?>
<?php if ($v['prestasi'] !== ''): ?>
	<p class="justify">Adapun prestasi/catatan pendukung siswa tersebut antara lain:</p>
	<?= letter_list($v['prestasi']) ?>
<?php endif; ?>
<?= letter_paragraph($t['closing']) ?>
