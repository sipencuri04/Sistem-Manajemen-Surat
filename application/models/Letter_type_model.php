<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Letter_type_model extends CI_Model
{
	protected $table = 'letter_types';

	public function all($active_only = FALSE)
	{
		if ($active_only)
		{
			$this->db->where('is_active', 1);
		}
		return $this->db->order_by('sort_order', 'ASC')->order_by('name', 'ASC')
			->get($this->table)->result_array();
	}

	/** Jenis surat aktif yang boleh diajukan siswa lewat formulir publik. */
	public function all_public()
	{
		return $this->db->where('is_active', 1)->where('allow_public', 1)
			->order_by('sort_order', 'ASC')->order_by('name', 'ASC')
			->get($this->table)->result_array();
	}

	/** Daftar jenis surat beserta jumlah surat (untuk halaman master). */
	public function all_with_counts()
	{
		return $this->db->select('t.*, COUNT(l.id) AS letter_count', FALSE)
			->from($this->table.' t')
			->join('letters l', 'l.letter_type_id = t.id', 'left')
			->group_by('t.id')
			->order_by('t.sort_order', 'ASC')->order_by('t.name', 'ASC')
			->get()->result_array();
	}

	public function find($id)
	{
		return $this->db->get_where($this->table, array('id' => (int) $id))->row_array();
	}

	/** Jenis surat + teks template (pembuka/isi/penutup). */
	public function find_with_template($id)
	{
		$row = $this->db->select('t.*, tp.opening_text, tp.body_text, tp.closing_text')
			->from($this->table.' t')
			->join('letter_templates tp', 'tp.letter_type_id = t.id', 'left')
			->where('t.id', (int) $id)
			->get()->row_array();
		return $row ?: NULL;
	}

	public function code_taken($code, $except_id = 0)
	{
		return $this->db->where('code', $code)->where('id !=', (int) $except_id)
			->count_all_results($this->table) > 0;
	}

	public function count_letters($id)
	{
		return (int) $this->db->where('letter_type_id', (int) $id)->count_all_results('letters');
	}

	public function create(array $type, array $texts)
	{
		$this->db->trans_start();
		$type['created_at'] = date('Y-m-d H:i:s');
		$this->db->insert($this->table, $type);
		$id = (int) $this->db->insert_id();
		$this->save_texts($id, $texts);
		$this->db->trans_complete();
		return $this->db->trans_status() ? $id : 0;
	}

	public function update($id, array $type, array $texts)
	{
		$this->db->trans_start();
		$type['updated_at'] = date('Y-m-d H:i:s');
		$this->db->where('id', (int) $id)->update($this->table, $type);
		$this->save_texts($id, $texts);
		$this->db->trans_complete();
		return $this->db->trans_status();
	}

	protected function save_texts($id, array $texts)
	{
		$row = array(
			'opening_text' => isset($texts['opening_text']) ? $texts['opening_text'] : '',
			'body_text'    => isset($texts['body_text']) ? $texts['body_text'] : '',
			'closing_text' => isset($texts['closing_text']) ? $texts['closing_text'] : '',
			'updated_at'   => date('Y-m-d H:i:s'),
		);
		$exists = $this->db->where('letter_type_id', (int) $id)->count_all_results('letter_templates') > 0;
		if ($exists)
		{
			$this->db->where('letter_type_id', (int) $id)->update('letter_templates', $row);
		}
		else
		{
			$row['letter_type_id'] = (int) $id;
			$this->db->insert('letter_templates', $row);
		}
	}

	/** Hapus hanya jika belum dipakai surat (FK juga RESTRICT). */
	public function delete($id)
	{
		if ($this->count_letters($id) > 0)
		{
			return FALSE;
		}
		return $this->db->where('id', (int) $id)->delete($this->table);
	}
}
