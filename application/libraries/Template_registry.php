<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Registry template surat: membaca config/letter_templates.php,
 * memvalidasi & membersihkan input field khusus tiap jenis surat,
 * serta mengganti placeholder pada teks template.
 */
class Template_registry
{
	protected $CI;
	protected $templates = array();

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->config->load('letter_templates', TRUE);
		$this->templates = (array) $this->CI->config->item('letter_templates', 'letter_templates');
	}

	public function all()
	{
		return $this->templates;
	}

	/** [kunci => label] untuk dropdown. */
	public function options()
	{
		$out = array();
		foreach ($this->templates as $key => $tpl)
		{
			$out[$key] = $tpl['label'];
		}
		return $out;
	}

	public function exists($key)
	{
		return isset($this->templates[$key]) && is_file(APPPATH.'views/letters/templates/'.$key.'.php');
	}

	/** Konfigurasi template lengkap dengan nilai default. */
	public function get($key)
	{
		if ( ! isset($this->templates[$key]))
		{
			return NULL;
		}
		$tpl = $this->templates[$key] + array(
			'layout'            => 'dinas',
			'title'             => '',
			'default_subject'   => '',
			'default_recipient' => '',
			'recipient'         => 'optional',
			'recipient_field'   => NULL,
			'attachment'        => FALSE,
			'fields'            => array(),
			'guide'             => array(),
		);
		foreach ($tpl['fields'] as $name => $field)
		{
			$tpl['fields'][$name] = $field + array(
				'type' => 'text', 'required' => FALSE, 'max' => 255, 'placeholder' => '',
				'help' => '', 'col' => 12, 'options' => array(), 'columns' => array(),
				'format' => NULL, 'rows' => 3,
			);
		}
		$tpl['guide'] += array('purpose' => '', 'when' => array(), 'tip' => '');
		$tpl['key'] = $key;
		return $tpl;
	}

	/** Bersihkan string: trim & buang karakter kontrol (kecuali baris baru). */
	protected function clean_string($value, $multiline = FALSE)
	{
		$value = is_scalar($value) ? (string) $value : '';
		$value = str_replace("\r\n", "\n", $value);
		$pattern = $multiline ? '/[\x00-\x09\x0B-\x1F\x7F]/u' : '/[\x00-\x1F\x7F]/u';
		$value = preg_replace($pattern, '', $value);
		return trim((string) $value);
	}

	protected function str_len($value)
	{
		return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
	}

	/**
	 * Validasi & bersihkan input field khusus.
	 *
	 * @param  array $tpl    hasil get()
	 * @param  array $input  $_POST['f']
	 * @return array [array $data, array $errors]
	 */
	public function collect(array $tpl, $input)
	{
		$input  = is_array($input) ? $input : array();
		$data   = array();
		$errors = array();

		foreach ($tpl['fields'] as $name => $field)
		{
			$raw   = isset($input[$name]) ? $input[$name] : NULL;
			$label = $field['label'];

			if ($field['type'] === 'rows')
			{
				$rows = array();
				if (is_array($raw))
				{
					foreach ($raw as $row)
					{
						if ( ! is_array($row)) continue;
						$clean = array();
						$filled = FALSE;
						foreach ($field['columns'] as $col => $col_label)
						{
							$v = $this->clean_string(isset($row[$col]) ? $row[$col] : '');
							if ($this->str_len($v) > 255)
							{
								$errors[$name] = $label.' kolom '.$col_label.' maksimal 255 karakter.';
							}
							$clean[$col] = $v;
							$filled = ($filled || $v !== '');
						}
						if ($filled)
						{
							$rows[] = $clean;
						}
					}
				}
				if (count($rows) > 100)
				{
					$errors[$name] = $label.' maksimal 100 baris.';
				}
				if ($field['required'] && empty($rows))
				{
					$errors[$name] = $label.' wajib diisi minimal satu baris.';
				}
				$data[$name] = array_values($rows);
				continue;
			}

			$value = $this->clean_string($raw, $field['type'] === 'textarea');

			if ($value === '')
			{
				if ($field['required'])
				{
					$errors[$name] = $label.' wajib diisi.';
				}
				$data[$name] = '';
				continue;
			}

			switch ($field['type'])
			{
				case 'date':
					if ( ! valid_date_string($value))
					{
						$errors[$name] = $label.' harus berupa tanggal yang valid.';
					}
					break;
				case 'number':
					if ( ! is_numeric($value))
					{
						$errors[$name] = $label.' harus berupa angka.';
					}
					break;
				case 'select':
					if ( ! in_array($value, $field['options'], TRUE))
					{
						$errors[$name] = 'Pilihan '.$label.' tidak valid.';
					}
					break;
			}

			if ( ! isset($errors[$name]) && $this->str_len($value) > (int) $field['max'])
			{
				$errors[$name] = $label.' maksimal '.$field['max'].' karakter.';
			}

			$data[$name] = $value;
		}

		return array($data, $errors);
	}

	/**
	 * Nilai field yang sudah diformat untuk dicetak (tanggal Indonesia, dll).
	 * Nilai belum di-escape.
	 */
	public function formatted_values(array $tpl, array $data)
	{
		$out = array();
		foreach ($tpl['fields'] as $name => $field)
		{
			$v = isset($data[$name]) ? $data[$name] : '';

			// Dukungan fallback alias jika kunci field menggunakan varian nama/jurusan
			if ($v === '')
			{
				if ($name === 'nama_siswa' && ! empty($data['nama'])) $v = $data['nama'];
				elseif ($name === 'kompetensi_keahlian' && ! empty($data['jurusan'])) $v = $data['jurusan'];
				elseif ($name === 'alamat_instansi' && ! empty($data['alamat'])) $v = $data['alamat'];
			}

			if ($field['type'] === 'rows')
			{
				$out[$name] = is_array($v) ? $v : array();
				continue;
			}
			if ($field['type'] === 'date' && $v !== '')
			{
				$v = tgl_indo($v, $field['format'] === 'day_date');
			}
			$out[$name] = (string) $v;
		}
		return $out;
	}

	/**
	 * Ganti placeholder {kunci} pada teks template.
	 * Teks & nilai di-escape terlebih dahulu sehingga aman untuk HTML,
	 * baris baru diubah menjadi <br>.
	 */
	public function render_text($text, array $map)
	{
		$text = e((string) $text);
		$replace = array();
		foreach ($map as $key => $value)
		{
			if (is_array($value)) continue;
			$replace['{'.$key.'}'] = e($value);
		}
		$text = strtr($text, $replace);
		return nl2br($text, FALSE);
	}
}
