<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notifications extends Admin_Controller
{
	const PER_PAGE = 20;

	public function __construct()
	{
		parent::__construct();
		$this->require_schema();
		$this->load->model('Notification_model');
		$this->load->library('pagination');
	}

	public function index()
	{
		$user_id = (int) $this->current_user['id'];
		$total   = $this->Notification_model->count_all_for_user($user_id);
		$page    = max(1, (int) $this->input->get('page'));
		$offset  = ($page - 1) * self::PER_PAGE;
		$this->pagination->initialize(pagination_config('notifikasi', $total, self::PER_PAGE));

		$this->render('notifications/index', array(
			'page_title'    => 'Pusat Notifikasi',
			'notifications' => $this->Notification_model->all_for_user($user_id, self::PER_PAGE, $offset),
			'total'         => $total,
			'unread_count'  => $this->Notification_model->count_unread($user_id),
			'offset'        => $offset,
			'pagination'    => $this->pagination->create_links(),
		));
	}

	public function read($id = 0)
	{
		$user_id = (int) $this->current_user['id'];
		$notif   = $this->db->get_where('notifications', array('id' => (int) $id, 'user_id' => $user_id))->row_array();

		if ($notif)
		{
			$this->Notification_model->mark_as_read($id, $user_id);
			if ( ! empty($notif['link']))
			{
				redirect($notif['link']);
				return;
			}
		}
		redirect('notifikasi');
	}

	public function mark_all_read()
	{
		$this->require_post();
		$user_id = (int) $this->current_user['id'];
		$this->Notification_model->mark_all_as_read($user_id);
		set_flash('success', 'Semua notifikasi telah ditandai sebagai dibaca.');
		redirect('notifikasi');
	}
}
