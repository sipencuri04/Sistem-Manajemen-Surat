<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Letter_file_model extends CI_Model
{
	protected $table = 'letter_files';

	public function find($id)
	{
		return $this->db->get_where($this->table, array('id' => (int) $id))->row_array();
	}

	public function by_letter($letter_id)
	{
		return $this->db->where('letter_id', (int) $letter_id)
			->order_by('created_at', 'DESC')->order_by('id', 'DESC')
			->get($this->table)->result_array();
	}

	public function latest_pdf($letter_id)
	{
		return $this->db->where('letter_id', (int) $letter_id)->where('file_type', 'pdf')
			->order_by('id', 'DESC')->limit(1)
			->get($this->table)->row_array();
	}

	public function add(array $row)
	{
		$row['created_at'] = date('Y-m-d H:i:s');
		$this->db->insert($this->table, $row);
		return (int) $this->db->insert_id();
	}

	/** Hapus metadata & file fisik seluruh berkas milik surat. */
	public function delete_by_letter($letter_id)
	{
		foreach ($this->by_letter($letter_id) as $file)
		{
			$path = storage_path($file['file_path']);
			if (is_file($path))
			{
				@unlink($path);
			}
		}
		return $this->db->where('letter_id', (int) $letter_id)->delete($this->table);
	}
}
