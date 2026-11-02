<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Letter_model extends CI_Model
{
	protected $table = 'letters';

	/** Kolom yang boleh diisi dari controller. */
	protected $fillable = array(
		'letter_type_id', 'letter_number', 'number_mode', 'sequence_no', 'number_year',
		'letter_date', 'place', 'subject', 'attachment_text', 'recipient', 'recipient_address',
		'content_data', 'signer_name', 'signer_nip', 'signer_title', 'status', 'signature_type', 'stamp_type', 'approval_status',
		'approved_by', 'approved_at', 'rejection_reason', 'stamped_by', 'stamped_at', 'notes',
		'created_by', 'updated_by',
	);

	public function find($id)
	{
		$row = $this->db->select('l.*, t.name AS type_name, t.code AS type_code, t.template_key,
				u.full_name AS created_by_name, u2.full_name AS updated_by_name, u3.full_name AS stamped_by_name')
			->from($this->table.' l')
			->join('letter_types t', 't.id = l.letter_type_id', 'inner')
			->join('users u', 'u.id = l.created_by', 'left')
			->join('users u2', 'u2.id = l.updated_by', 'left')
			->join('users u3', 'u3.id = l.stamped_by', 'left')
			->where('l.id', (int) $id)
			->get()->row_array();
		if ( ! $row)
		{
			return NULL;
		}
		$row['data'] = $this->decode($row['content_data']);
		return $row;
	}

	public function decode($json)
	{
		$data = json_decode((string) $json, TRUE);
		return is_array($data) ? $data : array();
	}

	public function number_exists($number, $except_id = 0)
	{
		return $this->db->where('letter_number', $number)->where('id !=', (int) $except_id)
			->count_all_results($this->table) > 0;
	}

	public function insert(array $data)
	{
		$row = array_intersect_key($data, array_flip($this->fillable));
		$row['created_at'] = date('Y-m-d H:i:s');
		if ( ! $this->db->insert($this->table, $row))
		{
			return 0;
		}
		return (int) $this->db->insert_id();
	}

	public function update($id, array $data)
	{
		$row = array_intersect_key($data, array_flip($this->fillable));
		unset($row['created_by']);
		$row['updated_at'] = date('Y-m-d H:i:s');
		return $this->db->where('id', (int) $id)->update($this->table, $row);
	}

	public function delete($id)
	{
		return $this->db->where('id', (int) $id)->delete($this->table);
	}

	/* ------------------------------------------------------------------
	 * Pencarian & filter (dipakai Data Surat dan Arsip)
	 * ------------------------------------------------------------------ */

	protected function apply_filters(array $f)
	{
		if ( ! empty($f['q']))
		{
			$this->db->group_start()
				->like('l.letter_number', $f['q'])
				->or_like('l.subject', $f['q'])
				->or_like('l.recipient', $f['q'])
				->group_end();
		}
		if ( ! empty($f['type']))
		{
			$this->db->where('l.letter_type_id', (int) $f['type']);
		}
		if ( ! empty($f['status']) && in_array($f['status'], array('draft', 'final'), TRUE))
		{
			$this->db->where('l.status', $f['status']);
		}
		// Rentang tanggal (memanfaatkan indeks letter_date, bukan fungsi MONTH()/YEAR()).
		if ( ! empty($f['year']))
		{
			$year = (int) $f['year'];
			if ( ! empty($f['month']))
			{
				$month = max(1, min(12, (int) $f['month']));
				$start = sprintf('%04d-%02d-01', $year, $month);
				$end   = date('Y-m-t', strtotime($start));
			}
			else
			{
				$start = $year.'-01-01';
				$end   = $year.'-12-31';
			}
			$this->db->where('l.letter_date >=', $start)->where('l.letter_date <=', $end);
		}
		elseif ( ! empty($f['month']))
		{
			$this->db->where('MONTH(l.letter_date) =', (int) $f['month'], FALSE);
		}
	}

	public function search(array $filters, $limit = 10, $offset = 0)
	{
		$this->db->select('l.id, l.letter_number, l.letter_date, l.subject, l.recipient, l.status, l.signature_type,
				l.created_at, l.updated_at, t.name AS type_name, t.code AS type_code,
				(SELECT COUNT(*) FROM letter_files f WHERE f.letter_id = l.id AND f.file_type = \'pdf\') AS pdf_count', FALSE)
			->from($this->table.' l')
			->join('letter_types t', 't.id = l.letter_type_id', 'inner');
		$this->apply_filters($filters);

		$sort = isset($filters['sort']) ? $filters['sort'] : 'newest';
		if ($sort === 'oldest')
		{
			$this->db->order_by('l.letter_date', 'ASC')->order_by('l.id', 'ASC');
		}
		else
		{
			$this->db->order_by('l.letter_date', 'DESC')->order_by('l.id', 'DESC');
		}
		return $this->db->limit((int) $limit, (int) $offset)->get()->result_array();
	}

	public function count_search(array $filters)
	{
		$this->db->from($this->table.' l');
		$this->apply_filters($filters);
		return (int) $this->db->count_all_results();
	}

	/** Daftar tahun yang tersedia untuk filter. */
	public function years()
	{
		$rows = $this->db->query('SELECT DISTINCT YEAR(letter_date) AS y FROM letters ORDER BY y DESC')->result_array();
		$years = array_map('intval', array_column($rows, 'y'));
		if ( ! in_array((int) date('Y'), $years, TRUE))
		{
			array_unshift($years, (int) date('Y'));
		}
		rsort($years);
		return $years;
	}

	/* ------------------------------------------------------------------
	 * Statistik dashboard
	 * ------------------------------------------------------------------ */

	public function count_all()
	{
		return (int) $this->db->count_all($this->table);
	}

	public function count_between($start, $end)
	{
		return (int) $this->db->where('letter_date >=', $start)->where('letter_date <=', $end)
			->count_all_results($this->table);
	}

	public function count_by_status($status)
	{
		return (int) $this->db->where('status', $status)->count_all_results($this->table);
	}

	public function count_by_type()
	{
		return $this->db->select('t.id, t.name, t.code, COUNT(l.id) AS total', FALSE)
			->from('letter_types t')
			->join('letters l', 'l.letter_type_id = t.id', 'left')
			->group_by('t.id')
			->order_by('total', 'DESC')->order_by('t.sort_order', 'ASC')
			->get()->result_array();
	}

	/** Jumlah surat per bulan pada tahun tertentu (array 1..12). */
	public function monthly_counts($year)
	{
		$year = (int) $year;
		$rows = $this->db->query(
			'SELECT MONTH(letter_date) AS m, COUNT(*) AS total FROM letters
			 WHERE letter_date BETWEEN ? AND ? GROUP BY MONTH(letter_date)',
			array($year.'-01-01', $year.'-12-31')
		)->result_array();
		$out = array_fill(1, 12, 0);
		foreach ($rows as $r)
		{
			$out[(int) $r['m']] = (int) $r['total'];
		}
		return $out;
	}

	public function recent($limit = 5)
	{
		return $this->search(array(), $limit, 0);
	}

	public function find_by_number($number)
	{
		$row = $this->db->select('l.*, t.name AS type_name, t.code AS type_code, t.template_key,
				u.full_name AS created_by_name')
			->from($this->table.' l')
			->join('letter_types t', 't.id = l.letter_type_id', 'inner')
			->join('users u', 'u.id = l.created_by', 'left')
			->where('l.letter_number', trim($number))
			->get()->row_array();

		if ($row)
		{
			$row['data'] = $this->decode($row['content_data']);
		}
		return $row;
	}
}
