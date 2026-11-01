<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* Surat Keterangan Lulus */ ?>
<?= letter_paragraph($t['opening']) ?>
<?= letter_data_table(array(
	'Nama'                  => $v['nama_siswa'],
	'Tempat, Tanggal Lahir' => trim($v['tempat_lahir'].', '.$v['tanggal_lahir'], ', '),
	'NIS / NISN'            => trim($v['nis'].($v['nisn'] !== '' ? ' / '.$v['nisn'] : '')),
	'Nama Orang Tua/Wali'   => $v['nama_orang_tua'],
	'Konsentrasi Keahlian'  => $v['kompetensi_keahlian'],
	'Nomor Ijazah'          => $v['nomor_ijazah'],
)) ?>
<?= letter_paragraph($t['body']) ?>
<?php if ($v['keperluan'] !== ''): ?><p class="justify">Surat keterangan ini diberikan untuk keperluan <?= e($v['keperluan']) ?>.</p><?php endif; ?>
<?= letter_paragraph($t['closing']) ?>
