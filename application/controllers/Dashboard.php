<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model(array('Letter_model', 'Incoming_letter_model', 'Disposition_model'));
	}

	public function index()
	{
		$role = strtolower($this->current_user['role']);
		$user_id = (int) $this->current_user['id'];

		$year = (int) $this->input->get('tahun');
		if ($year < 2000 OR $year > 2100)
		{
			$year = (int) date('Y');
		}

		$month_start = date('Y-m-01');
		$month_end   = date('Y-m-t');
		$monthly     = $this->Letter_model->monthly_counts($year);

		$labels = array();
		for ($m = 1; $m <= 12; $m++)
		{
			$labels[] = substr(bulan_indo($m), 0, 3);
		}

		$data = array(
			'page_title'      => 'Dashboard ('.role_title($role).')',
			'use_chart'       => TRUE,
			'total'           => $this->Letter_model->count_all(),
			'this_month'      => $this->Letter_model->count_between($month_start, $month_end),
			'this_year'       => $this->Letter_model->count_between(date('Y').'-01-01', date('Y').'-12-31'),
			'drafts'          => $this->Letter_model->count_by_status('draft'),
			'by_type'         => $this->Letter_model->count_by_type(),
			'recent'          => $this->Letter_model->recent(6),
			'chart'           => array('labels' => $labels, 'values' => array_values($monthly)),
			'chart_year'      => $year,
			'years'           => $this->Letter_model->years(),
			'settings_ready'  => $this->Settings_model->is_complete(),
			'role'            => $role,
		);

		// Metrik Surat Masuk & Disposisi
		$data['total_incoming'] = $this->Incoming_letter_model->count_all();
		$data['total_disposition'] = $this->Disposition_model->count_all();

		// Data spesifik Pegawai
		if ($role === 'pegawai')
		{
			$data['my_dispositions_count'] = $this->Disposition_model->count_my_dispositions($user_id);
			$data['my_pending_count']      = $this->Disposition_model->count_pending_for_user($user_id);
			$data['my_completed_count']    = $this->Disposition_model->count_my_dispositions($user_id, array('status' => 'selesai'));
			$data['my_recent_dispositions']= $this->Disposition_model->my_dispositions($user_id, array(), 5);
		}

		// Data spesifik Kepala Sekolah
		if (in_array($role, array('kepala_sekolah', 'superuser', 'admin'), TRUE))
		{
			$data['incoming_need_disposition'] = $this->Incoming_letter_model->count_status('diteruskan');
			$data['pending_approvals_total']   = $this->db->where('approval_status', 'menunggu_persetujuan')->count_all_results('letters');
			$data['active_dispositions']       = $this->Disposition_model->search(array('status' => 'menunggu'), 5);
		}

		// Data spesifik Superuser
		if (in_array($role, array('superuser', 'admin'), TRUE))
		{
			$this->load->model('User_model');
			$data['total_users'] = $this->User_model->count_search();
			$data['recent_logs'] = $this->db->order_by('id', 'DESC')->limit(5)->get('audit_logs')->result_array();
		}

		$this->render('dashboard/index', $data);
	}
}
