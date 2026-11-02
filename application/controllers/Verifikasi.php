<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Verifikasi Dokumen & Surat Resmi (Akses Publik Tanpa Login).
 * Menampilkan hasil pemindaian QR Code untuk membuktikan keaslian surat dan mencegah pemalsuan.
 */
class Verifikasi extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->require_schema();
		$this->load->model(array('Letter_model', 'Letter_type_model'));
		$this->output->set_header('X-Frame-Options: DENY');
		$this->output->set_header('X-Content-Type-Options: nosniff');
	}

	/** Halaman pencarian / cek nomor surat manual */
	public function index()
	{
		$nomor = trim((string) $this->input->get('nomor', TRUE));
		$letter = NULL;
		$error  = NULL;

		if ($nomor !== '')
		{
			$letter = $this->Letter_model->find_by_number($nomor);
			if ( ! $letter)
			{
				$error = 'Surat dengan nomor "' . htmlspecialchars($nomor) . '" tidak ditemukan dalam basis data resmi sekolah.';
			}
		}

		$this->public_view('public/verifikasi', array(
			'page_title' => 'Verifikasi Keaslian Dokumen',
			'letter'     => $letter,
			'nomor'      => $nomor,
			'error'      => $error,
			'is_scan'    => FALSE,
		));
	}

	/** Halaman hasil scan QR Code dari lembar PDF surat */
	public function cek($id = 0, $hash = '')
	{
		$id = (int) $id;
		$letter = $this->Letter_model->find($id);

		if ( ! $letter)
		{
			$this->public_view('public/verifikasi', array(
				'page_title' => 'Dokumen Tidak Ditemukan',
				'letter'     => NULL,
				'error'      => 'Dokumen surat ini tidak tercatat dalam arsip resmi sekolah atau telah dibatalkan.',
				'is_scan'    => TRUE,
			));
			return;
		}

		// Validasi hash keamanan anti-tamper (mencegah tebak ID surat)
		$expected_hash = substr(md5($letter['letter_number'] . 'PERSURATAN_SMK_' . $letter['id']), 0, 8);
		$is_valid_hash = ($hash !== '' && hash_equals($expected_hash, (string) $hash));

		$type = $this->Letter_type_model->find($letter['letter_type_id']);

		$this->public_view('public/verifikasi', array(
			'page_title'    => 'Hasil Validasi Keaslian Dokumen',
			'letter'        => $letter,
			'type'          => $type,
			'is_valid_hash' => $is_valid_hash,
			'is_scan'       => TRUE,
			'error'         => NULL,
		));
	}
}

