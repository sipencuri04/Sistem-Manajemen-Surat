<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings_model extends CI_Model
{
	const ROW_ID = 1;

	protected $table = 'school_settings';
	protected $cache = NULL;

	/** Kolom yang boleh diubah lewat form pengaturan. */
	public $editable = array(
		'kop_line1', 'kop_line2', 'school_name', 'school_code', 'npsn', 'address', 'city',
		'postal_code', 'phone', 'email', 'website', 'kop_template', 'headmaster_name',
		'headmaster_nip', 'signer_title', 'show_nip', 'number_format', 'number_padding',
		'number_reset', 'number_scope', 'letter_year',
		'smtp_enabled', 'smtp_host', 'smtp_port', 'smtp_crypto', 'smtp_user', 'smtp_pass',
		'smtp_from_email', 'smtp_from_name',
	);

	/** Kolom gambar yang dapat diunggah. */
	public $image_fields = array('logo_path', 'logo_right_path', 'signature_path', 'stamp_path');

	public function get($fresh = FALSE)
	{
		if ($this->cache !== NULL && ! $fresh)
		{
			return $this->cache;
		}
		$row = $this->db->get_where($this->table, array('id' => self::ROW_ID))->row_array();
		if ( ! $row)
		{
			// Baris pengaturan wajib ada; buat otomatis bila belum ada.
			$this->db->insert($this->table, array('id' => self::ROW_ID, 'updated_at' => date('Y-m-d H:i:s')));
			$row = $this->db->get_where($this->table, array('id' => self::ROW_ID))->row_array();
		}
		$this->cache = $row;
		return $row;
	}

	public function update(array $data)
	{
		$clean = array();
		foreach ($data as $key => $value)
		{
			if (in_array($key, $this->editable, TRUE) OR in_array($key, $this->image_fields, TRUE))
			{
				$clean[$key] = $value;
			}
		}
		if (empty($clean))
		{
			return TRUE;
		}
		$clean['updated_at'] = date('Y-m-d H:i:s');
		$this->cache = NULL;
		return $this->db->where('id', self::ROW_ID)->update($this->table, $clean);
	}

	/** Path absolut file gambar pengaturan (atau NULL jika tidak ada). */
	public function image_file($field)
	{
		$s = $this->get();
		if ( ! in_array($field, $this->image_fields, TRUE) OR empty($s[$field]))
		{
			return NULL;
		}
		$path = storage_path($s[$field]);
		return is_file($path) ? $path : NULL;
	}

	/** Apakah identitas minimal sudah diisi. */
	public function is_complete()
	{
		$s = $this->get();
		return trim($s['school_name']) !== '' && trim($s['headmaster_name']) !== '' && trim($s['city']) !== '';
	}
}
