<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Audit_log_model extends CI_Model
{
	protected $table = 'audit_logs';

	public function log($action, $entity_type = NULL, $entity_id = NULL, $details = NULL, $user = NULL)
	{
		$ci =& get_instance();
		$user_id = NULL;
		$username = 'System/Guest';

		if ($user && is_array($user))
		{
			$user_id = isset($user['id']) ? (int) $user['id'] : NULL;
			$username = isset($user['username']) ? $user['username'] : 'User';
		}
		elseif (isset($ci->current_user) && is_array($ci->current_user))
		{
			$user_id = (int) $ci->current_user['id'];
			$username = $ci->current_user['username'];
		}

		$ip = $ci->input->ip_address();
		$ua = substr((string) $ci->input->user_agent(), 0, 250);

		$row = array(
			'user_id'     => $user_id,
			'username'    => $username,
			'action'      => substr((string) $action, 0, 100),
			'entity_type' => $entity_type ? substr((string) $entity_type, 0, 50) : NULL,
			'entity_id'   => $entity_id ? (int) $entity_id : NULL,
			'details'     => is_array($details) ? json_encode($details, JSON_UNESCAPED_UNICODE) : (string) $details,
			'ip_address'  => $ip,
			'user_agent'  => $ua,
			'created_at'  => date('Y-m-d H:i:s'),
		);

		return $this->db->insert($this->table, $row);
	}

	public function search(array $filters = array(), $limit = 20, $offset = 0)
	{
		$this->apply_filters($filters);
		return $this->db->order_by('id', 'DESC')->limit((int) $limit, (int) $offset)->get($this->table)->result_array();
	}

	public function count_search(array $filters = array())
	{
		$this->apply_filters($filters);
		return $this->db->count_all_results($this->table);
	}

	protected function apply_filters(array $f)
	{
		if ( ! empty($f['q']))
		{
			$this->db->group_start()
				->like('username', $f['q'])
				->or_like('action', $f['q'])
				->or_like('details', $f['q'])
				->group_end();
		}
		if ( ! empty($f['action']))
		{
			$this->db->where('action', $f['action']);
		}
		if ( ! empty($f['user_id']))
		{
			$this->db->where('user_id', (int) $f['user_id']);
		}
	}
}
