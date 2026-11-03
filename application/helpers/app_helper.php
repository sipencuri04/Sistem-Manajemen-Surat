<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Helper umum aplikasi Persuratan SMK.
 */

if ( ! function_exists('e'))
{
	/** Escape output HTML (pencegahan XSS). */
	function e($value)
	{
		return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	}
}

if ( ! function_exists('storage_path'))
{
	/** Path absolut folder storage (di luar akses publik melalui .htaccess). */
	function storage_path($relative = '')
	{
		$base = rtrim(FCPATH, '/\\').DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR;
		$relative = ltrim(str_replace(array('\\', '..'), array('/', ''), (string) $relative), '/');
		return $base.str_replace('/', DIRECTORY_SEPARATOR, $relative);
	}
}

if ( ! function_exists('bulan_indo'))
{
	function bulan_indo($month)
	{
		$names = array(1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli',
			'Agustus', 'September', 'Oktober', 'November', 'Desember');
		$month = (int) $month;
		return isset($names[$month]) ? $names[$month] : '';
	}
}

if ( ! function_exists('hari_indo'))
{
	function hari_indo($dayOfWeek)
	{
		$names = array('Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu');
		return isset($names[(int) $dayOfWeek]) ? $names[(int) $dayOfWeek] : '';
	}
}

if ( ! function_exists('valid_date_string'))
{
	/** TRUE jika string berformat Y-m-d dan merupakan tanggal yang valid. */
	function valid_date_string($date)
	{
		if ( ! is_string($date) OR ! preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $m))
		{
			return FALSE;
		}
		return checkdate((int) $m[2], (int) $m[3], (int) $m[1]);
	}
}

if ( ! function_exists('tgl_indo'))
{
	/**
	 * Format tanggal Y-m-d menjadi "27 Oktober 2026" atau "Selasa, 27 Oktober 2026".
	 */
	function tgl_indo($date, $with_day = FALSE)
	{
		if (empty($date))
		{
			return '';
		}
		$date = substr((string) $date, 0, 10);
		if ( ! valid_date_string($date))
		{
			return (string) $date;
		}
		$ts = strtotime($date);
		$out = (int) date('j', $ts).' '.bulan_indo(date('n', $ts)).' '.date('Y', $ts);
		return $with_day ? hari_indo(date('w', $ts)).', '.$out : $out;
	}
}

if ( ! function_exists('bulan_romawi'))
{
	function bulan_romawi($month)
	{
		$r = array(1 => 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII');
		return isset($r[(int) $month]) ? $r[(int) $month] : '';
	}
}

if ( ! function_exists('set_flash'))
{
	/** type: success | danger | warning | info */
	function set_flash($type, $message, $is_html = FALSE)
	{
		$CI =& get_instance();
		$CI->session->set_flashdata('flash', array('type' => $type, 'message' => $message, 'is_html' => (bool)$is_html));
	}
}

if ( ! function_exists('status_badge'))
{
	function status_badge($status)
	{
		if ($status === 'final')
		{
			return '<span class="badge rounded-pill text-bg-success">Final</span>';
		}
		return '<span class="badge rounded-pill text-bg-secondary">Draft</span>';
	}
}

if ( ! function_exists('menu_active'))
{
	/** Mengembalikan 'active' bila segmen URI pertama cocok. */
	function menu_active($segments, $exclude_second = NULL)
	{
		$CI =& get_instance();
		$first  = (string) $CI->uri->segment(1);
		$second = (string) $CI->uri->segment(2);
		foreach ((array) $segments as $seg)
		{
			list($a, $b) = array_pad(explode('/', $seg, 2), 2, NULL);
			if ($a === $first OR ($a === 'dashboard' && $first === ''))
			{
				if ($b === NULL OR $b === $second)
				{
					if ($exclude_second !== NULL && in_array($second, (array) $exclude_second, TRUE))
					{
						continue;
					}
					return 'active';
				}
			}
		}
		return '';
	}
}

if ( ! function_exists('format_bytes'))
{
	function format_bytes($bytes)
	{
		$bytes = (int) $bytes;
		if ($bytes >= 1048576) return number_format($bytes / 1048576, 2, ',', '.').' MB';
		if ($bytes >= 1024) return number_format($bytes / 1024, 1, ',', '.').' KB';
		return $bytes.' B';
	}
}

if ( ! function_exists('safe_filename'))
{
	/** Membuat nama file aman dari teks bebas (mis. nomor surat). */
	function safe_filename($text, $max = 80)
	{
		$text = preg_replace('/[^A-Za-z0-9\-_.]+/', '-', (string) $text);
		$text = trim(preg_replace('/-+/', '-', $text), '-.');
		return $text === '' ? 'surat' : substr($text, 0, $max);
	}
}

if ( ! function_exists('text_lines'))
{
	/** Pecah teks multi-baris menjadi array baris yang tidak kosong. */
	function text_lines($text)
	{
		$lines = preg_split('/\r\n|\r|\n/', (string) $text);
		return array_values(array_filter(array_map('trim', $lines), 'strlen'));
	}
}

if ( ! function_exists('letter_data_table'))
{
	/**
	 * Tabel "label : nilai" untuk isi surat. Baris dengan nilai kosong dilewati
	 * kecuali $keep_empty TRUE. Mengembalikan HTML yang sudah di-escape.
	 */
	function letter_data_table(array $rows, $keep_empty = FALSE, $class = 'data')
	{
		$html = '';
		foreach ($rows as $label => $value)
		{
			$value = (string) $value;
			if ($value === '' && ! $keep_empty) continue;
			$html .= '<tr><td class="lbl">'.e($label).'</td><td class="sep">:</td><td>'.nl2br(e($value), FALSE).'</td></tr>';
		}
		return $html === '' ? '' : '<table class="'.e($class).'">'.$html.'</table>';
	}
}

if ( ! function_exists('letter_paragraph'))
{
	/** Paragraf rata kanan-kiri dari HTML aman (hasil render_text). */
	function letter_paragraph($safe_html, $class = 'justify')
	{
		return trim(strip_tags($safe_html)) === '' ? '' : '<p class="'.e($class).'">'.$safe_html.'</p>';
	}
}

if ( ! function_exists('letter_list'))
{
	/** Daftar bernomor dari teks multi-baris. */
	function letter_list($text)
	{
		$lines = text_lines($text);
		if (empty($lines)) return '';
		$html = '<ol class="list">';
		foreach ($lines as $line) $html .= '<li>'.e($line).'</li>';
		return $html.'</ol>';
	}
}

if ( ! function_exists('letter_grid'))
{
	/** Tabel bergaris untuk field bertipe rows. */
	function letter_grid(array $columns, $rows)
	{
		$rows = is_array($rows) ? $rows : array();
		if (empty($rows)) return '';
		$html = '<table class="grid"><thead><tr><th>No</th>';
		foreach ($columns as $label) $html .= '<th>'.e($label).'</th>';
		$html .= '</tr></thead><tbody>';
		foreach ($rows as $i => $row)
		{
			$html .= '<tr><td class="no">'.($i + 1).'</td>';
			foreach ($columns as $key => $label)
			{
				$html .= '<td>'.e(isset($row[$key]) && $row[$key] !== '' ? $row[$key] : '-').'</td>';
			}
			$html .= '</tr>';
		}
		return $html.'</tbody></table>';
	}
}

if ( ! function_exists('pagination_config'))
{
	/** Konfigurasi library Pagination CI3 bergaya Bootstrap 5, mempertahankan query string filter. */
	function pagination_config($uri, $total, $per_page)
	{
		$query = $_GET;
		unset($query['page']);
		return array(
			'base_url'             => site_url($uri).'?'.http_build_query($query),
			'total_rows'           => (int) $total,
			'per_page'             => (int) $per_page,
			'page_query_string'    => TRUE,
			'query_string_segment' => 'page',
			'use_page_numbers'     => TRUE,
			'reuse_query_string'   => FALSE,
			'num_links'            => 2,
			'full_tag_open'        => '<nav aria-label="Halaman"><ul class="pagination pagination-sm mb-0">',
			'full_tag_close'       => '</ul></nav>',
			'first_link'           => '&laquo;',
			'last_link'            => '&raquo;',
			'next_link'            => '&rsaquo;',
			'prev_link'            => '&lsaquo;',
			'first_tag_open'       => '<li class="page-item">', 'first_tag_close' => '</li>',
			'last_tag_open'        => '<li class="page-item">', 'last_tag_close'  => '</li>',
			'next_tag_open'        => '<li class="page-item">', 'next_tag_close'  => '</li>',
			'prev_tag_open'        => '<li class="page-item">', 'prev_tag_close'  => '</li>',
			'num_tag_open'         => '<li class="page-item">', 'num_tag_close'   => '</li>',
			'cur_tag_open'         => '<li class="page-item active"><span class="page-link">',
			'cur_tag_close'        => '</span></li>',
			'attributes'           => array('class' => 'page-link'),
		);
	}
}

if ( ! function_exists('asset_url'))
{
	/**
	 * URL aset dengan versi berdasarkan waktu ubah file, sehingga browser
	 * otomatis memuat CSS/JS terbaru setelah aplikasi diperbarui (tanpa Ctrl+F5).
	 */
	function asset_url($path)
	{
		$path = ltrim($path, '/');
		$file = FCPATH.str_replace('/', DIRECTORY_SEPARATOR, $path);
		$v = is_file($file) ? filemtime($file) : 0;
		return base_url($path).($v ? '?v='.$v : '');
	}
}

if ( ! function_exists('role_title'))
{
	function role_title($role)
	{
		$titles = array(
			'superuser'      => 'Superuser / Developer',
			'kepala_sekolah' => 'Kepala Sekolah',
			'tu'             => 'Tata Usaha',
			'pegawai'        => 'Pegawai / Guru',
			'admin'          => 'Administrator (Legacy)',
		);
		return isset($titles[$role]) ? $titles[$role] : ucfirst($role);
	}
}

if ( ! function_exists('role_badge'))
{
	function role_badge($role)
	{
		$colors = array(
			'superuser'      => 'dark',
			'kepala_sekolah' => 'primary',
			'tu'             => 'info',
			'pegawai'        => 'secondary',
			'admin'          => 'success',
		);
		$c = isset($colors[$role]) ? $colors[$role] : 'light';
		return '<span class="badge text-bg-'.$c.'">'.e(role_title($role)).'</span>';
	}
}

if ( ! function_exists('audit_log'))
{
	function audit_log($action, $entity_type = NULL, $entity_id = NULL, $details = NULL, $user = NULL)
	{
		$ci =& get_instance();
		if ( ! isset($ci->Audit_log_model))
		{
			$ci->load->model('Audit_log_model');
		}
		return $ci->Audit_log_model->log($action, $entity_type, $entity_id, $details, $user);
	}
}

if ( ! function_exists('send_notification'))
{
	function send_notification($user_id, $title, $message, $link = NULL, $type = 'info')
	{
		$ci =& get_instance();
		if ( ! isset($ci->Notification_model))
		{
			$ci->load->model('Notification_model');
		}
		$id = $ci->Notification_model->add($user_id, $title, $message, $link, $type);

		// Kirim email via SMTP jika terkonfigurasi di school_settings atau environment
		$ci->load->model('Settings_model');
		$school = $ci->Settings_model->get();

		$smtp_enabled = isset($school['smtp_enabled']) ? (int) $school['smtp_enabled'] : 1;
		$smtp_host    = !empty($school['smtp_host']) ? $school['smtp_host'] : (getenv('SMTP_HOST') ?: (isset($_ENV['SMTP_HOST']) ? $_ENV['SMTP_HOST'] : NULL));

		if ($smtp_enabled && $smtp_host)
		{
			try {
				mail_notification($user_id, $title, $message, $link);
			} catch (Exception $e) {
				log_message('error', 'SMTP notification error: '.$e->getMessage());
			}
		}
		return $id;
	}
}

if ( ! function_exists('mail_notification'))
{
	/** Kirim email notifikasi ke pengguna jika memiliki alamat email valid. */
	function mail_notification($user_id, $title, $message, $link = NULL)
	{
		$ci =& get_instance();
		$user = $ci->db->get_where('users', array('id' => (int) $user_id))->row_array();
		if (empty($user['email'])) return FALSE;

		$ci->load->model('Settings_model');
		$school = $ci->Settings_model->get();

		$smtp_host   = !empty($school['smtp_host']) ? $school['smtp_host'] : (getenv('SMTP_HOST') ?: (isset($_ENV['SMTP_HOST']) ? $_ENV['SMTP_HOST'] : ''));
		$smtp_user   = !empty($school['smtp_user']) ? $school['smtp_user'] : (getenv('SMTP_USER') ?: (isset($_ENV['SMTP_USER']) ? $_ENV['SMTP_USER'] : ''));
		$smtp_pass   = !empty($school['smtp_pass']) ? $school['smtp_pass'] : (getenv('SMTP_PASS') ?: (isset($_ENV['SMTP_PASS']) ? $_ENV['SMTP_PASS'] : ''));
		$smtp_port   = !empty($school['smtp_port']) ? (int) $school['smtp_port'] : (getenv('SMTP_PORT') ? (int) getenv('SMTP_PORT') : 587);
		$smtp_crypto = !empty($school['smtp_crypto']) ? $school['smtp_crypto'] : (getenv('SMTP_CRYPTO') ?: 'tls');
		if ($smtp_crypto === 'none') $smtp_crypto = '';

		$from_email  = !empty($school['smtp_from_email']) ? $school['smtp_from_email'] : (!empty($smtp_user) ? $smtp_user : (getenv('SMTP_FROM_EMAIL') ?: 'noreply@sekolah.sch.id'));
		$from_name   = !empty($school['smtp_from_name']) ? $school['smtp_from_name'] : (!empty($school['school_name']) ? $school['school_name'] : (getenv('SMTP_FROM_NAME') ?: 'Sistem Persuratan Sekolah'));

		$config = array(
			'protocol'    => !empty($smtp_host) ? 'smtp' : 'mail',
			'smtp_host'   => $smtp_host,
			'smtp_port'   => $smtp_port,
			'smtp_user'   => $smtp_user,
			'smtp_pass'   => $smtp_pass,
			'smtp_crypto' => $smtp_crypto,
			'mailtype'    => 'html',
			'charset'     => 'utf-8',
			'newline'     => "\r\n",
			'crlf'        => "\r\n",
			'wordwrap'    => TRUE,
		);

		$ci->load->library('email');
		$ci->email->initialize($config);
		$ci->email->from($from_email, $from_name);
		$ci->email->to($user['email']);
		$ci->email->subject('[' . $from_name . '] ' . $title);

		$ci->load->library('email_theme');
		$url = $link ? (filter_var($link, FILTER_VALIDATE_URL) ? $link : site_url($link)) : null;
		$body = $ci->email_theme->render(array(
			'title'          => $title,
			'recipient_name' => $user['full_name'],
			'badge_type'     => 'info',
			'badge_text'     => 'PEMBERITAHUAN SISTEM PERSURATAN',
			'message'        => nl2br(e($message)),
			'button_url'     => $url,
			'button_text'    => 'Buka di Sistem Persuratan',
			'closing_note'   => 'Notifikasi resmi dari Sistem Manajemen Persuratan Sekolah.',
		));

		$ci->email->message($body);
		return @$ci->email->send();
	}
}

if ( ! function_exists('disposition_priority_badge'))
{
	function disposition_priority_badge($priority)
	{
		$p = array(
			'biasa'   => array('secondary', 'Biasa'),
			'penting' => array('primary', 'Penting'),
			'segera'  => array('warning', 'Segera'),
			'rahasia' => array('danger', 'Rahasia'),
		);
		$item = isset($p[$priority]) ? $p[$priority] : array('secondary', ucfirst($priority));
		return '<span class="badge text-bg-'.$item[0].'">'.e($item[1]).'</span>';
	}
}

if ( ! function_exists('disposition_status_badge'))
{
	function disposition_status_badge($status, $is_overdue = FALSE)
	{
		$s = array(
			'menunggu'           => array('warning', 'Menunggu'),
			'diproses'           => array('info', 'Sedang Diproses'),
			'menunggu_informasi' => array('secondary', 'Menunggu Info'),
			'selesai'            => array('success', 'Selesai'),
			'dibatalkan'         => array('dark', 'Dibatalkan'),
		);
		$item = isset($s[$status]) ? $s[$status] : array('secondary', ucfirst($status));
		$badge = '<span class="badge text-bg-'.$item[0].'">'.e($item[1]).'</span>';
		if ($is_overdue && $status !== 'selesai')
		{
			$badge .= ' <span class="badge text-bg-danger" title="Tenggat waktu terlewati"><i class="fa-solid fa-clock me-1"></i>Terlambat</span>';
		}
		return $badge;
	}
}

if ( ! function_exists('incoming_status_badge'))
{
	function incoming_status_badge($status)
	{
		$s = array(
			'baru'        => array('warning', 'Baru Diterima'),
			'diteruskan'  => array('primary', 'Diteruskan ke Kepsek'),
			'didisposisi' => array('info', 'Sudah Didisposisi'),
			'selesai'     => array('success', 'Selesai'),
			'diarsipkan'  => array('secondary', 'Diarsipkan'),
		);
		$item = isset($s[$status]) ? $s[$status] : array('secondary', ucfirst($status));
		return '<span class="badge text-bg-'.$item[0].'">'.e($item[1]).'</span>';
	}
}

if ( ! function_exists('is_logged_in'))
{
	function is_logged_in()
	{
		$CI =& get_instance();
		return !empty($CI->session->userdata('user_id'));
	}
}

if ( ! function_exists('current_user'))
{
	function current_user()
	{
		$CI =& get_instance();
		return isset($CI->current_user) ? $CI->current_user : NULL;
	}
}

if ( ! function_exists('has_role'))
{
	function has_role($roles)
	{
		$user = current_user();
		if ( ! $user) return FALSE;
		$role = isset($user['role']) ? $user['role'] : '';
		if ($role === 'admin') return TRUE;
		if (is_array($roles)) return in_array($role, $roles, TRUE);
		return $role === $roles;
	}
}

if ( ! function_exists('is_superuser'))
{
	function is_superuser()
	{
		$user = current_user();
		if ( ! $user) return FALSE;
		return in_array($user['role'], array('superuser', 'admin'), TRUE);
	}
}

if ( ! function_exists('is_kepala_sekolah'))
{
	function is_kepala_sekolah()
	{
		$user = current_user();
		if ( ! $user) return FALSE;
		return in_array($user['role'], array('kepala_sekolah', 'admin'), TRUE);
	}
}

if ( ! function_exists('is_tu'))
{
	function is_tu()
	{
		$user = current_user();
		if ( ! $user) return FALSE;
		return in_array($user['role'], array('tu', 'admin'), TRUE);
	}
}

if ( ! function_exists('is_pegawai'))
{
	function is_pegawai()
	{
		$user = current_user();
		if ( ! $user) return FALSE;
		return $user['role'] === 'pegawai';
	}
}

if ( ! function_exists('format_wa_phone'))
{
	/**
	 * Ubah format nomor HP lokal Indonesia menjadi format internasional WhatsApp (628xxx).
	 */
	function format_wa_phone($phone)
	{
		$digits = preg_replace('/[^0-9]/', '', (string) $phone);
		if (strpos($digits, '08') === 0)
		{
			$digits = '628' . substr($digits, 2);
		}
		elseif (strpos($digits, '8') === 0 && strlen($digits) >= 9)
		{
			$digits = '62' . $digits;
		}
		elseif (strpos($digits, '+62') === 0)
		{
			$digits = substr($digits, 1);
		}
		return $digits;
	}
}

if ( ! function_exists('build_wa_link'))
{
	/**
	 * Buat link WhatsApp API yang siap diklik untuk membuka chat dengan teks pesan prefilled.
	 */
	function build_wa_link($phone, $message)
	{
		$num = format_wa_phone($phone);
		if ($num === '') return '#';
		return 'https://api.whatsapp.com/send?phone=' . rawurlencode($num) . '&text=' . rawurlencode($message);
	}
}


