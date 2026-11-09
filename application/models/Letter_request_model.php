<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Permohonan surat dari siswa (formulir publik tanpa login).
 */
class Letter_request_model extends CI_Model
{
	protected $table = 'letter_requests';

	public static $statuses = array(
		'pending'   => 'Menunggu',
		'processed' => 'Sedang Diproses',
		'approved'  => 'Siap Diambil',
		'taken'     => 'Sudah Diambil',
		'rejected'  => 'Ditolak',
	);

	/** Kode lacak acak 10 karakter tanpa huruf yang mirip (0/O, 1/I/L). */
	public function generate_code()
	{
		$chars = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
		for ($try = 0; $try < 20; $try++)
		{
			$code = '';
			for ($i = 0; $i < 10; $i++)
			{
				$code .= $chars[random_int(0, strlen($chars) - 1)];
			}
			if ( ! $this->db->where('tracking_code', $code)->count_all_results($this->table))
			{
				return $code;
			}
		}
		throw new RuntimeException('Gagal membuat kode lacak.');
	}

	public function insert(array $row)
	{
		$row['created_at'] = date('Y-m-d H:i:s');
		$this->db->insert($this->table, $row);
		return (int) $this->db->insert_id();
	}

	public function update($id, array $row)
	{
		$row['updated_at'] = date('Y-m-d H:i:s');
		return $this->db->where('id', (int) $id)->update($this->table, $row);
	}

	public function find($id)
	{
		$row = $this->db->select('r.*, t.name AS type_name, t.code AS type_code, t.template_key,
				l.letter_number, l.approval_status, l.stamp_type, l.signature_type, u.full_name AS processed_by_name')
			->from($this->table.' r')
			->join('letter_types t', 't.id = r.letter_type_id', 'inner')
			->join('letters l', 'l.id = r.letter_id', 'left')
			->join('users u', 'u.id = r.processed_by', 'left')
			->where('r.id', (int) $id)
			->get()->row_array();
		if ($row)
		{
			$data = json_decode((string) $row['content_data'], TRUE);
			$row['data'] = is_array($data) ? $data : array();
		}
		return $row ?: NULL;
	}

	public function find_by_code($code)
	{
		$raw = strtoupper(trim((string) $code));
		if ($raw === '')
		{
			return NULL;
		}

		$clean = preg_replace('/[^A-Za-z0-9]/', '', $raw);
		if (strlen($clean) < 4 || strlen($clean) > 20)
		{
			return NULL;
		}

		return $this->db->select('r.id, r.tracking_code, r.requester_name, r.requester_email, r.status, r.admin_note, r.created_at,
				r.processed_at, r.letter_id, t.name AS type_name, l.letter_number, l.signature_type, l.approval_status, l.stamp_type')
			->from($this->table.' r')
			->join('letter_types t', 't.id = r.letter_type_id', 'inner')
			->join('letters l', 'l.id = r.letter_id', 'left')
			->group_start()
				->where('r.tracking_code', $raw)
				->or_where('r.tracking_code', $clean)
				->or_where("REPLACE(r.tracking_code, '-', '') =", $clean)
			->group_end()
			->get()->row_array() ?: NULL;
	}

	protected function apply_filters(array $f)
	{
		if ( ! empty($f['status']) && isset(self::$statuses[$f['status']]))
		{
			$this->db->where('r.status', $f['status']);
		}
		if ( ! empty($f['q']))
		{
			$this->db->group_start()
				->like('r.requester_name', $f['q'])
				->or_like('r.tracking_code', $f['q'])
				->or_like('r.requester_phone', $f['q'])
				->or_like('r.requester_email', $f['q'])
				->group_end();
		}
	}

	public function search(array $f, $limit, $offset)
	{
		$this->db->select('r.id, r.tracking_code, r.requester_name, r.requester_class, r.requester_phone, r.requester_email, r.status,
				r.created_at, r.letter_id, t.name AS type_name, t.code AS type_code, l.letter_number')
			->from($this->table.' r')
			->join('letter_types t', 't.id = r.letter_type_id', 'inner')
			->join('letters l', 'l.id = r.letter_id', 'left');
		$this->apply_filters($f);
		// Permohonan yang menunggu tampil paling atas, terlama lebih dulu (antrean).
		$this->db->order_by("FIELD(r.status, 'pending', 'processed', 'approved', 'taken', 'rejected')", '', FALSE)
			->order_by('r.created_at', 'ASC');
		return $this->db->limit((int) $limit, (int) $offset)->get()->result_array();
	}

	public function count_search(array $f)
	{
		$this->db->from($this->table.' r');
		$this->apply_filters($f);
		return (int) $this->db->count_all_results();
	}

	public function count_status($status)
	{
		return (int) $this->db->where('status', $status)->count_all_results($this->table);
	}

	/** Jumlah permohonan dari satu IP sejak waktu tertentu (pembatasan spam). */
	public function count_recent_by_ip($ip, $since)
	{
		return (int) $this->db->where('ip_address', $ip)->where('created_at >=', $since)
			->count_all_results($this->table);
	}

	/** Jumlah permohonan yang masih menunggu untuk satu nomor HP. */
	public function count_pending_by_phone($phone)
	{
		return (int) $this->db->where('requester_phone', $phone)->where('status', 'pending')
			->count_all_results($this->table);
	}
}
