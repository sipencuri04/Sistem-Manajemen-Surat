<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pengelolaan permohonan surat dari siswa oleh TU (wajib login).
 * Alur: Menunggu -> (Proses jadi surat) Siap Diambil -> Sudah Diambil, atau Ditolak.
 */
class Requests extends Admin_Controller
{
	const PER_PAGE = 15;

	public function __construct()
	{
		parent::__construct();
		$this->require_schema();
		$this->load->model(array('Letter_request_model', 'Letter_type_model'));
		$this->load->library(array('Template_registry', 'pagination'));
	}

	public function index()
	{
		$status = (string) $this->input->get('status', TRUE);
		if ($status === '' && $this->input->get('status') === NULL)
		{
			$status = 'pending';
		}
		$filters = array(
			'status' => $status === 'semua' ? '' : $status,
			'q'      => substr(trim((string) $this->input->get('q', TRUE)), 0, 100),
		);
		$total  = $this->Letter_request_model->count_search($filters);
		$page   = max(1, (int) $this->input->get('page'));
		$offset = ($page - 1) * self::PER_PAGE;
		$this->pagination->initialize(pagination_config('permohonan-masuk', $total, self::PER_PAGE));

		$counts = array();
		foreach (array_keys(Letter_request_model::$statuses) as $s)
		{
			$counts[$s] = $this->Letter_request_model->count_status($s);
		}

		$this->render('requests/index', array(
			'page_title' => 'Permohonan Siswa',
			'status'     => $status,
			'filters'    => $filters,
			'requests'   => $this->Letter_request_model->search($filters, self::PER_PAGE, $offset),
			'total'      => $total,
			'offset'     => $offset,
			'counts'     => $counts,
			'pagination' => $this->pagination->create_links(),
		));
	}

	public function detail($id = 0)
	{
		$req = $this->load_request($id);
		$type = $this->Letter_type_model->find($req['letter_type_id']);
		$tpl  = $this->template_registry->get($req['template_key']);
		$this->render('requests/detail', array(
			'page_title' => 'Detail Permohonan',
			'req'        => $req,
			'type'       => $type,
			'tpl'        => $tpl,
			'values'     => $tpl ? $this->template_registry->formatted_values($tpl, $req['data']) : array(),
		));
	}

	/** Tolak permohonan dengan alasan (wajib). */
	public function reject($id = 0)
	{
		$this->require_post();
		$req = $this->load_request($id);
		if ($req['status'] !== 'pending')
		{
			set_flash('warning', 'Permohonan ini sudah diproses sebelumnya.');
			redirect('permohonan-masuk/'.(int) $req['id']);
		}
		$reason = trim((string) $this->input->post('admin_note'));
		if ($reason === '' OR mb_strlen($reason) > 500)
		{
			set_flash('danger', 'Alasan penolakan wajib diisi (maksimal 500 karakter). Alasan ini terlihat oleh siswa.');
			redirect('permohonan-masuk/'.(int) $req['id']);
		}
		$this->Letter_request_model->update($req['id'], array(
			'status'       => 'rejected',
			'admin_note'   => $reason,
			'processed_by' => (int) $this->current_user['id'],
			'processed_at' => date('Y-m-d H:i:s'),
		));
		set_flash('success', 'Permohonan '.$req['tracking_code'].' ditolak.');
		redirect('permohonan-masuk');
	}

	/** Tandai surat sudah diambil siswa. */
	public function taken($id = 0)
	{
		$this->require_post();
		$req = $this->load_request($id);
		if ($req['status'] !== 'approved')
		{
			set_flash('warning', 'Hanya permohonan berstatus Siap Diambil yang dapat ditandai sudah diambil.');
			redirect('permohonan-masuk/'.(int) $req['id']);
		}
		$this->Letter_request_model->update($req['id'], array('status' => 'taken'));
		set_flash('success', 'Permohonan '.$req['tracking_code'].' ditandai sudah diambil.');
		redirect('permohonan-masuk?status=approved');
	}

	protected function load_request($id)
	{
		$req = $this->Letter_request_model->find((int) $id);
		if ( ! $req)
		{
			$this->not_found('Permohonan tidak ditemukan.');
		}
		return $req;
	}
}
