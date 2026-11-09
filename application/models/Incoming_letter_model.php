<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Incoming_letter_model extends CI_Model
{
	protected $table = 'incoming_letters';

	public static $statuses = array(
		'baru'        => 'Baru Diterima',
		'diteruskan'  => 'Diteruskan ke Kepala Sekolah',
		'didisposisi' => 'Sudah Didisposisi',
		'selesai'     => 'Selesai Tindak Lanjut',
		'diarsipkan'  => 'Diarsipkan',
	);

	public function find($id)
	{
		return $this->db->select('i.*, u.full_name AS created_by_name,
				(SELECT COUNT(*) FROM dispositions d WHERE d.incoming_letter_id = i.id) AS disposition_count')
			->from($this->table.' i')
			->join('users u', 'u.id = i.created_by', 'left')
			->where('i.id', (int) $id)
			->get()->row_array();
	}

	public function search(array $filters = array(), $limit = 15, $offset = 0)
	{
		$this->apply_filters($filters);
		return $this->db->select('i.*, u.full_name AS created_by_name,
				(SELECT COUNT(*) FROM dispositions d WHERE d.incoming_letter_id = i.id) AS disposition_count')
			->from($this->table.' i')
			->join('users u', 'u.id = i.created_by', 'left')
			->order_by('i.received_date', 'DESC')
			->order_by('i.id', 'DESC')
			->limit((int) $limit, (int) $offset)
			->get()->result_array();
	}

	public function count_search(array $filters = array())
	{
		$this->apply_filters($filters);
		return $this->db->count_all_results($this->table.' i');
	}

	protected function apply_filters(array $f)
	{
		if ( ! empty($f['status']) && $f['status'] !== 'semua')
		{
			$this->db->where('i.status', $f['status']);
		}
		if ( ! empty($f['q']))
		{
			$this->db->group_start()
				->like('i.agenda_number', $f['q'])
				->or_like('i.letter_number', $f['q'])
				->or_like('i.sender', $f['q'])
				->or_like('i.subject', $f['q'])
				->or_like('i.description', $f['q'])
				->group_end();
		}
		if ( ! empty($f['from']))
		{
			$this->db->where('i.received_date >=', $f['from']);
		}
		if ( ! empty($f['to']))
		{
			$this->db->where('i.received_date <=', $f['to']);
		}
	}

	public function insert(array $data)
	{
		$data['created_at'] = date('Y-m-d H:i:s');
		$this->db->insert($this->table, $data);
		return (int) $this->db->insert_id();
	}

	public function update($id, array $data)
	{
		$data['updated_at'] = date('Y-m-d H:i:s');
		return $this->db->where('id', (int) $id)->update($this->table, $data);
	}

	public function delete($id)
	{
		$letter = $this->find($id);
		if ($letter && $letter['file_path'])
		{
			$p = storage_path($letter['file_path']);
			if (is_file($p)) @unlink($p);
		}
		return $this->db->where('id', (int) $id)->delete($this->table);
	}

	public function count_all()
	{
		return $this->db->count_all_results($this->table);
	}

	public function count_status($status)
	{
		return $this->db->where('status', $status)->count_all_results($this->table);
	}

	public function generate_agenda_number($date = NULL)
	{
		$year = $date ? date('Y', strtotime($date)) : date('Y');
		$row = $this->db->select('MAX(id) AS max_id')->where('YEAR(received_date)', $year)->get($this->table)->row_array();
		$count = $this->db->where('YEAR(received_date)', $year)->count_all_results($this->table) + 1;
		return 'SM-'.str_pad($count, 3, '0', STR_PAD_LEFT).'/'.$year;
	}

	public function find_by_tracking($code)
	{
		$raw = trim((string) $code);
		if ($raw === '') return NULL;
		$clean = strtoupper($raw);

		// 1. Coba exact match di tracking_code
		$row = $this->db->select('i.*, u.full_name AS created_by_name')
			->from($this->table.' i')
			->join('users u', 'u.id = i.created_by', 'left')
			->where('i.tracking_code', $clean)
			->get()->row_array();
		if ($row) return $row;

		// 2. Coba exact match di agenda_number (misal SM-005/2026)
		$row = $this->db->select('i.*, u.full_name AS created_by_name')
			->from($this->table.' i')
			->join('users u', 'u.id = i.created_by', 'left')
			->where('i.agenda_number', $clean)
			->get()->row_array();
		if ($row) return $row;

		// 3. Coba toleransi format agenda_number (misal 'SM-005' tanpa tahun, atau 'SM0052026' tanpa tanda baca)
		$alphanumeric = preg_replace('/[^A-Za-z0-9]/', '', $clean);
		$row = $this->db->select('i.*, u.full_name AS created_by_name')
			->from($this->table.' i')
			->join('users u', 'u.id = i.created_by', 'left')
			->group_start()
				->like('i.agenda_number', $clean)
				->or_where("REPLACE(REPLACE(REPLACE(UPPER(i.agenda_number), '-', ''), '/', ''), '.', '') = ", $alphanumeric)
				->or_where('i.letter_number', $clean)
			->group_end()
			->order_by('i.id', 'DESC')
			->get()->row_array();

		return $row ?: NULL;
	}

	public function generate_tracking_code()
	{
		$chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
		do {
			$code = 'SM-';
			for ($i = 0; $i < 6; $i++) {
				$code .= $chars[random_int(0, strlen($chars) - 1)];
			}
			$exists = $this->db->where('tracking_code', $code)->count_all_results($this->table);
		} while ($exists > 0);
		return $code;
	}
}
