<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Disposition_model extends CI_Model
{
	protected $table = 'dispositions';
	protected $table_recipients = 'disposition_recipients';
	protected $table_activities = 'disposition_activities';

	public static $priorities = array(
		'biasa'   => array('secondary', 'Biasa'),
		'penting' => array('primary', 'Penting'),
		'segera'  => array('warning', 'Segera'),
		'rahasia' => array('danger', 'Rahasia'),
	);

	public static $statuses = array(
		'menunggu'   => array('warning', 'Menunggu'),
		'diproses'   => array('info', 'Sedang Diproses'),
		'selesai'    => array('success', 'Selesai'),
		'dibatalkan' => array('secondary', 'Dibatalkan'),
	);

	public static $recipient_statuses = array(
		'menunggu'           => array('warning', 'Menunggu Tindak Lanjut'),
		'diproses'           => array('info', 'Sedang Dikerjakan'),
		'menunggu_informasi' => array('secondary', 'Menunggu Informasi'),
		'selesai'            => array('success', 'Selesai'),
	);

	public function find($id)
	{
		$row = $this->db->select('d.*, u.full_name AS creator_name, u.position AS creator_position,
				i.agenda_number, i.letter_number AS incoming_letter_number, i.letter_date AS incoming_letter_date,
				i.sender AS incoming_sender, i.subject AS incoming_subject, i.file_path AS incoming_file_path')
			->from($this->table.' d')
			->join('users u', 'u.id = d.created_by', 'left')
			->join('incoming_letters i', 'i.id = d.incoming_letter_id', 'left')
			->where('d.id', (int) $id)
			->get()->row_array();

		if ($row)
		{
			$row['recipients'] = $this->recipients($row['id']);
			$row['activities'] = $this->activities($row['id']);
		}
		return $row;
	}

	public function recipients($disposition_id)
	{
		return $this->db->select('r.*, u.full_name, u.nip, u.position, u.email, u.phone,
				u2.full_name AS delegated_to_name, u2.position AS delegated_to_position')
			->from($this->table_recipients.' r')
			->join('users u', 'u.id = r.user_id', 'inner')
			->join('users u2', 'u2.id = r.delegated_to', 'left')
			->where('r.disposition_id', (int) $disposition_id)
			->order_by('r.id', 'ASC')
			->get()->result_array();
	}

	public function recipient_find($recipient_id)
	{
		return $this->db->select('r.*, d.disposition_number, d.disposition_date, d.deadline_date,
				d.priority, d.instruction, d.notes, d.created_by, d.incoming_letter_id,
				u.full_name, u.position,
				i.agenda_number, i.letter_number AS incoming_letter_number, i.sender AS incoming_sender,
				i.subject AS incoming_subject, i.file_path AS incoming_file_path,
				creator.full_name AS creator_name, creator.position AS creator_position')
			->from($this->table_recipients.' r')
			->join($this->table.' d', 'd.id = r.disposition_id', 'inner')
			->join('users u', 'u.id = r.user_id', 'inner')
			->join('users creator', 'creator.id = d.created_by', 'left')
			->join('incoming_letters i', 'i.id = d.incoming_letter_id', 'left')
			->where('r.id', (int) $recipient_id)
			->get()->row_array();
	}

	public function search(array $filters = array(), $limit = 15, $offset = 0)
	{
		$this->apply_filters($filters);
		return $this->db->select('d.*, u.full_name AS creator_name,
				i.agenda_number, i.letter_number AS incoming_letter_number, i.sender AS incoming_sender, i.subject AS incoming_subject,
				(SELECT COUNT(*) FROM disposition_recipients dr WHERE dr.disposition_id = d.id) AS total_recipients,
				(SELECT COUNT(*) FROM disposition_recipients dr2 WHERE dr2.disposition_id = d.id AND dr2.status = "selesai") AS completed_recipients')
			->from($this->table.' d')
			->join('users u', 'u.id = d.created_by', 'left')
			->join('incoming_letters i', 'i.id = d.incoming_letter_id', 'left')
			->order_by('d.id', 'DESC')
			->limit((int) $limit, (int) $offset)
			->get()->result_array();
	}

	public function count_search(array $filters = array())
	{
		$this->apply_filters($filters);
		return $this->db->count_all_results($this->table.' d');
	}

	protected function apply_filters(array $f)
	{
		if ( ! empty($f['status']))
		{
			$this->db->where('d.status', $f['status']);
		}
		if ( ! empty($f['priority']))
		{
			$this->db->where('d.priority', $f['priority']);
		}
		if ( ! empty($f['q']))
		{
			$this->db->group_start()
				->like('d.disposition_number', $f['q'])
				->or_like('d.instruction', $f['q'])
				->or_like('i.sender', $f['q'])
				->or_like('i.subject', $f['q'])
				->or_like('i.agenda_number', $f['q'])
				->group_end();
		}
		if ( ! empty($f['overdue']) && $f['overdue'] === 'yes')
		{
			$this->db->where('d.deadline_date <', date('Y-m-d'))->where('d.status !=', 'selesai');
		}
	}

	public function my_dispositions($user_id, array $filters = array(), $limit = 15, $offset = 0)
	{
		$this->apply_my_filters($filters);
		return $this->db->select('r.*, d.disposition_number, d.disposition_date, d.deadline_date,
				d.priority, d.instruction, d.notes, d.created_by, d.incoming_letter_id, d.outgoing_letter_id,
				i.agenda_number, i.letter_number AS incoming_letter_number, i.sender AS incoming_sender,
				i.subject AS incoming_subject, i.file_path AS incoming_file_path,
				creator.full_name AS creator_name, creator.position AS creator_position')
			->from($this->table_recipients.' r')
			->join($this->table.' d', 'd.id = r.disposition_id', 'inner')
			->join('users creator', 'creator.id = d.created_by', 'left')
			->join('incoming_letters i', 'i.id = d.incoming_letter_id', 'left')
			->where('r.user_id', (int) $user_id)
			->order_by('CASE WHEN r.status = "selesai" THEN 1 ELSE 0 END', 'ASC', FALSE)
			->order_by('d.deadline_date', 'ASC')
			->order_by('r.id', 'DESC')
			->limit((int) $limit, (int) $offset)
			->get()->result_array();
	}

	public function count_my_dispositions($user_id, array $filters = array())
	{
		$this->apply_my_filters($filters);
		return $this->db->where('r.user_id', (int) $user_id)
			->from($this->table_recipients.' r')
			->join($this->table.' d', 'd.id = r.disposition_id', 'inner')
			->join('incoming_letters i', 'i.id = d.incoming_letter_id', 'left')
			->count_all_results();
	}

	protected function apply_my_filters(array $f)
	{
		if ( ! empty($f['status']))
		{
			$this->db->where('r.status', $f['status']);
		}
		if ( ! empty($f['priority']))
		{
			$this->db->where('d.priority', $f['priority']);
		}
		if ( ! empty($f['q']))
		{
			$this->db->group_start()
				->like('d.disposition_number', $f['q'])
				->or_like('d.instruction', $f['q'])
				->or_like('i.sender', $f['q'])
				->or_like('i.subject', $f['q'])
				->group_end();
		}
	}

	public function insert(array $disp_data, array $recipient_user_ids)
	{
		$disp_data['created_at'] = date('Y-m-d H:i:s');
		$this->db->trans_start();

		$this->db->insert($this->table, $disp_data);
		$disp_id = (int) $this->db->insert_id();

		// Update incoming letter status if linked
		if ( ! empty($disp_data['incoming_letter_id']))
		{
			$this->db->where('id', (int) $disp_data['incoming_letter_id'])
				->update('incoming_letters', array('status' => 'didisposisi', 'updated_at' => date('Y-m-d H:i:s')));
		}

		// Insert recipients
		foreach ($recipient_user_ids as $uid)
		{
			$this->db->insert($this->table_recipients, array(
				'disposition_id' => $disp_id,
				'user_id'        => (int) $uid,
				'status'         => 'menunggu',
				'progress_percent' => 0,
				'created_at'     => date('Y-m-d H:i:s'),
			));
		}

		// Activity log
		$this->add_activity($disp_id, $disp_data['created_by'], 'dibuat', 'Disposisi dibuat dengan '.count($recipient_user_ids).' pegawai penerima.');

		$this->db->trans_complete();
		return $this->db->trans_status() ? $disp_id : 0;
	}

	public function update_progress($recipient_id, array $data, $user_id)
	{
		$recip = $this->recipient_find($recipient_id);
		if ( ! $recip) return FALSE;

		$this->db->trans_start();

		$update = array(
			'status'           => $data['status'],
			'progress_percent' => (int) $data['progress_percent'],
			'progress_notes'   => trim($data['progress_notes']),
			'updated_at'       => date('Y-m-d H:i:s'),
		);

		if ( ! empty($data['evidence_file']))
		{
			$update['evidence_file'] = $data['evidence_file'];
			$update['evidence_name'] = isset($data['evidence_name']) ? $data['evidence_name'] : NULL;
		}

		if ($data['status'] === 'selesai')
		{
			$update['completed_at'] = date('Y-m-d H:i:s');
			$update['progress_percent'] = 100;
		}

		$this->db->where('id', (int) $recipient_id)->update($this->table_recipients, $update);

		// Record activity
		$desc = 'Status tindak lanjut diubah menjadi: '.self::$recipient_statuses[$data['status']][1].' ('.$update['progress_percent'].'%). Catatan: '.$update['progress_notes'];
		$this->add_activity($recip['disposition_id'], $user_id, 'update_progres', $desc);

		// Check if all recipients completed
		$pending_count = $this->db->where('disposition_id', (int) $recip['disposition_id'])
			->where('status !=', 'selesai')
			->count_all_results($this->table_recipients);

		if ($pending_count === 0)
		{
			$this->db->where('id', (int) $recip['disposition_id'])
				->update($this->table, array('status' => 'selesai', 'updated_at' => date('Y-m-d H:i:s')));

			if ($recip['incoming_letter_id'])
			{
				$this->db->where('id', (int) $recip['incoming_letter_id'])
					->update('incoming_letters', array('status' => 'selesai', 'updated_at' => date('Y-m-d H:i:s')));
			}
		}
		else
		{
			$this->db->where('id', (int) $recip['disposition_id'])
				->update($this->table, array('status' => 'diproses', 'updated_at' => date('Y-m-d H:i:s')));
		}

		$this->db->trans_complete();
		return $this->db->trans_status();
	}

	public function delegate($recipient_id, $from_user_id, $to_user_id, $reason)
	{
		$recip = $this->recipient_find($recipient_id);
		if ( ! $recip) return FALSE;

		$this->db->trans_start();

		// Mark old recipient as delegated
		$this->db->where('id', (int) $recipient_id)->update($this->table_recipients, array(
			'delegated_to'      => (int) $to_user_id,
			'delegation_reason' => trim($reason),
			'delegated_at'      => date('Y-m-d H:i:s'),
			'status'            => 'menunggu_informasi',
			'updated_at'        => date('Y-m-d H:i:s'),
		));

		// Check if already in recipient list
		$exists = $this->db->where('disposition_id', (int) $recip['disposition_id'])
			->where('user_id', (int) $to_user_id)
			->count_all_results($this->table_recipients) > 0;

		if ( ! $exists)
		{
			$this->db->insert($this->table_recipients, array(
				'disposition_id'   => (int) $recip['disposition_id'],
				'user_id'          => (int) $to_user_id,
				'status'           => 'menunggu',
				'progress_percent' => 0,
				'created_at'       => date('Y-m-d H:i:s'),
			));
		}

		$to_user = $this->db->get_where('users', array('id' => (int) $to_user_id))->row_array();
		$to_name = $to_user ? $to_user['full_name'] : 'User #'.$to_user_id;

		$this->add_activity($recip['disposition_id'], $from_user_id, 'didelegasikan',
			'Tugas didelegasikan kepada: '.$to_name.'. Alasan: '.trim($reason));

		$this->db->trans_complete();
		return $this->db->trans_status();
	}

	public function add_activity($disposition_id, $user_id, $action, $description)
	{
		return $this->db->insert($this->table_activities, array(
			'disposition_id' => (int) $disposition_id,
			'user_id'        => $user_id ? (int) $user_id : NULL,
			'action'         => $action,
			'description'    => $description,
			'created_at'     => date('Y-m-d H:i:s'),
		));
	}

	public function activities($disposition_id)
	{
		return $this->db->select('a.*, u.full_name, u.position')
			->from($this->table_activities.' a')
			->join('users u', 'u.id = a.user_id', 'left')
			->where('a.disposition_id', (int) $disposition_id)
			->order_by('a.id', 'ASC')
			->get()->result_array();
	}

	public function count_pending_for_user($user_id)
	{
		return $this->db->where('user_id', (int) $user_id)
			->where('status !=', 'selesai')
			->count_all_results($this->table_recipients);
	}

	public function count_all()
	{
		return $this->db->count_all_results($this->table);
	}

	public function count_status($status)
	{
		return $this->db->where('status', $status)->count_all_results($this->table);
	}

	public function generate_disposition_number($year = NULL)
	{
		$year = $year ?: date('Y');
		$cnt = $this->db->where('YEAR(disposition_date)', $year)->count_all_results($this->table) + 1;
		return 'DSP-'.str_pad($cnt, 3, '0', STR_PAD_LEFT).'/'.$year;
	}
}
