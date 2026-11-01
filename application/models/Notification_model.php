<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notification_model extends CI_Model
{
	protected $table = 'notifications';

	public function count_unread($user_id)
	{
		return $this->db->where('user_id', (int) $user_id)
			->where('is_read', 0)
			->count_all_results($this->table);
	}

	public function recent($user_id, $limit = 5)
	{
		return $this->db->where('user_id', (int) $user_id)
			->order_by('id', 'DESC')
			->limit((int) $limit)
			->get($this->table)->result_array();
	}

	public function all_for_user($user_id, $limit = 30, $offset = 0)
	{
		return $this->db->where('user_id', (int) $user_id)
			->order_by('id', 'DESC')
			->limit((int) $limit, (int) $offset)
			->get($this->table)->result_array();
	}

	public function count_all_for_user($user_id)
	{
		return $this->db->where('user_id', (int) $user_id)->count_all_results($this->table);
	}

	public function mark_as_read($id, $user_id)
	{
		return $this->db->where('id', (int) $id)
			->where('user_id', (int) $user_id)
			->update($this->table, array('is_read' => 1));
	}

	public function mark_all_as_read($user_id)
	{
		return $this->db->where('user_id', (int) $user_id)
			->where('is_read', 0)
			->update($this->table, array('is_read' => 1));
	}

	public function add($user_id, $title, $message, $link = NULL, $type = 'info')
	{
		$row = array(
			'user_id'    => (int) $user_id,
			'title'      => substr(trim($title), 0, 150),
			'message'    => trim($message),
			'link'       => $link ? trim($link) : NULL,
			'type'       => substr($type, 0, 30),
			'is_read'    => 0,
			'created_at' => date('Y-m-d H:i:s'),
		);
		$this->db->insert($this->table, $row);
		return (int) $this->db->insert_id();
	}
}
