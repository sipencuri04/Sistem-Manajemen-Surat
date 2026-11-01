<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Arsip surat: pencarian, filter jenis/bulan/tahun, pagination, unduh PDF arsip.
 */
class Archive extends Admin_Controller
{
	const PER_PAGE = 10;

	public function __construct()
	{
		parent::__construct();
		$this->load->model(array('Letter_model', 'Letter_type_model', 'Letter_file_model'));
		$this->load->library('pagination');
	}

	public function index()
	{
		$year  = (int) $this->input->get('year');
		$month = (int) $this->input->get('month');
		$filters = array(
			'q'      => substr(trim((string) $this->input->get('q', TRUE)), 0, 100),
			'type'   => (int) $this->input->get('type'),
			'status' => (string) $this->input->get('status', TRUE),
			'year'   => ($year >= 2000 && $year <= 2100) ? $year : 0,
			'month'  => ($month >= 1 && $month <= 12) ? $month : 0,
			'sort'   => $this->input->get('sort') === 'oldest' ? 'oldest' : 'newest',
		);

		$total  = $this->Letter_model->count_search($filters);
		$page   = max(1, (int) $this->input->get('page'));
		$offset = ($page - 1) * self::PER_PAGE;
		$this->pagination->initialize(pagination_config('arsip', $total, self::PER_PAGE));

		$this->render('archive/index', array(
			'page_title' => 'Arsip Surat',
			'filters'    => $filters,
			'letters'    => $this->Letter_model->search($filters, self::PER_PAGE, $offset),
			'total'      => $total,
			'offset'     => $offset,
			'types'      => $this->Letter_type_model->all(),
			'years'      => $this->Letter_model->years(),
			'pagination' => $this->pagination->create_links(),
		));
	}

	/** Unduh file PDF arsip yang tersimpan (hanya untuk pengguna login). */
	public function file($id = 0)
	{
		$file = $this->Letter_file_model->find((int) $id);
		if ( ! $file)
		{
			$this->not_found('File arsip tidak ditemukan.');
		}

		// Pastikan path benar-benar berada di dalam folder storage (cegah path traversal).
		$base = realpath(storage_path());
		$path = realpath(storage_path($file['file_path']));
		if ( ! $base OR ! $path OR strpos($path, $base.DIRECTORY_SEPARATOR) !== 0 OR ! is_file($path))
		{
			log_message('error', 'File arsip #'.$file['id'].' hilang atau tidak valid: '.$file['file_path']);
			set_flash('warning', 'File PDF arsip tidak ditemukan di server. Silakan klik "Buat Ulang PDF".');
			redirect('surat/detail/'.(int) $file['letter_id']);
		}

		$inline = $this->input->get('lihat') === '1';
		$this->output
			->set_header('Content-Type: '.$file['mime_type'])
			->set_header('Content-Disposition: '.($inline ? 'inline' : 'attachment').'; filename="'.safe_filename(preg_replace('/\.pdf$/i', '', $file['original_name'])).'.pdf"')
			->set_header('Content-Length: '.filesize($path))
			->set_header('Cache-Control: private, no-store, max-age=0')
			->set_output(file_get_contents($path));
	}
}
