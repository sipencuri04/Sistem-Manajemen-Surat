<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Logs extends Admin_Controller
{
	const PER_PAGE = 25;

	public function __construct()
	{
		parent::__construct();
		$this->require_schema();
		$this->load->model('Audit_log_model');
		$this->load->library('pagination');
		$this->require_role(array('superuser', 'admin'));
	}

	public function audit()
	{
		$filters = array(
			'q'      => substr(trim((string) $this->input->get('q', TRUE)), 0, 100),
			'action' => (string) $this->input->get('action', TRUE),
		);

		$total  = $this->Audit_log_model->count_search($filters);
		$page   = max(1, (int) $this->input->get('page'));
		$offset = ($page - 1) * self::PER_PAGE;
		$this->pagination->initialize(pagination_config('log-aktivitas', $total, self::PER_PAGE));

		$this->render('logs/audit', array(
			'page_title' => 'Audit Log Aktivitas',
			'logs'       => $this->Audit_log_model->search($filters, self::PER_PAGE, $offset),
			'total'      => $total,
			'filters'    => $filters,
			'offset'     => $offset,
			'pagination' => $this->pagination->create_links(),
		));
	}

	public function system()
	{
		$log_dir = APPPATH.'logs';
		$files = array();
		if (is_dir($log_dir))
		{
			foreach (scandir($log_dir) as $f)
			{
				if (preg_match('/^log-\d{4}-\d{2}-\d{2}\.php$/', $f))
				{
					$files[] = $f;
				}
			}
			rsort($files);
		}

		$selected_file = $this->input->get('file', TRUE);
		if ( ! in_array($selected_file, $files, TRUE))
		{
			$selected_file = ! empty($files) ? $files[0] : NULL;
		}

		$content = '';
		if ($selected_file && is_file($log_dir.'/'.$selected_file))
		{
			$content = file_get_contents($log_dir.'/'.$selected_file);
			$content = preg_replace('/^<\?php.*?\?>\n+/ms', '', $content);
		}

		$this->render('logs/system', array(
			'page_title'    => 'System & Error Logs',
			'files'         => $files,
			'selected_file' => $selected_file,
			'content'       => $content,
		));
	}

	public function backup()
	{
		$backup_dir = storage_path('backups');
		if ( ! is_dir($backup_dir)) @mkdir($backup_dir, 0755, TRUE);

		$files = array();
		if (is_dir($backup_dir))
		{
			foreach (scandir($backup_dir) as $f)
			{
				if (preg_match('/\.sql(\.gz)?$/', $f))
				{
					$p = $backup_dir.DIRECTORY_SEPARATOR.$f;
					$files[] = array(
						'name' => $f,
						'size' => format_bytes(filesize($p)),
						'date' => date('Y-m-d H:i:s', filemtime($p)),
					);
				}
			}
			usort($files, function ($a, $b) { return strcmp($b['name'], $a['name']); });
		}

		$storage_stats = array(
			'letters'  => $this->dir_size(storage_path('letters')),
			'incoming' => $this->dir_size(storage_path('incoming_letters')),
			'backups'  => $this->dir_size($backup_dir),
		);

		$this->render('logs/backup', array(
			'page_title'    => 'Backup Database & Pemeliharaan',
			'files'         => $files,
			'storage_stats' => $storage_stats,
		));
	}

	public function create_backup()
	{
		$this->require_post();
		$this->load->dbutil();

		$prefs = array(
			'format'   => 'txt',
			'filename' => 'backup_persuratan.sql',
			'add_drop' => TRUE,
			'add_insert' => TRUE,
			'newline'  => "\n",
		);

		try {
			$backup = $this->dbutil->backup($prefs);
			$filename = 'backup_db_'.date('Ymd_His').'.sql';
			$path = storage_path('backups/'.$filename);

			file_put_contents($path, $backup);
			audit_log('DATABASE_BACKUP', 'system', NULL, 'Membuat backup database: '.$filename);
			set_flash('success', 'Backup database '.$filename.' berhasil dibuat.');
		} catch (Exception $e) {
			set_flash('danger', 'Gagal membuat backup database: '.$e->getMessage());
		}
		redirect('backup');
	}

	/**
	 * Backup Khusus Data Surat & Persuratan (SQL Mingguan)
	 */
	public function create_letters_backup()
	{
		$this->require_post();
		$this->load->dbutil();

		$letter_tables = array(
			'letters',
			'letter_files',
			'letter_templates',
			'letter_types',
			'letter_counters',
			'letter_requests',
			'incoming_letters',
			'dispositions',
			'disposition_recipients',
			'disposition_activities'
		);

		$existing = array();
		foreach ($letter_tables as $t)
		{
			if ($this->db->table_exists($t))
			{
				$existing[] = $t;
			}
		}

		$prefs = array(
			'tables'     => $existing,
			'format'     => 'txt',
			'filename'   => 'backup_surat_smk.sql',
			'add_drop'   => TRUE,
			'add_insert' => TRUE,
			'newline'    => "\n",
		);

		try {
			$backup = $this->dbutil->backup($prefs);
			$filename = 'backup_surat_'.date('Ymd_His').'.sql';
			$path = storage_path('backups/'.$filename);

			file_put_contents($path, $backup);
			audit_log('DATABASE_BACKUP', 'system', NULL, 'Membuat backup khusus data surat: '.$filename);
			set_flash('success', 'Backup khusus data persuratan <strong>'.$filename.'</strong> berhasil dibuat ('.count($existing).' tabel tersimpan).');
		} catch (Exception $e) {
			set_flash('danger', 'Gagal membuat backup data surat: '.$e->getMessage());
		}
		redirect('backup');
	}

	/**
	 * Endpoint Cron Job Otomatis Mingguan (Jalankan via cPanel Cron Job atau CLI)
	 */
	public function cron_weekly_backup()
	{
		$is_cli = is_cli();
		$token  = (string) $this->input->get('token');
		$secret = 'smk_weekly_backup_secret';

		if ( ! $is_cli && $token !== $secret)
		{
			$user_id = (int) $this->session->userdata('user_id');
			if ( ! $user_id)
			{
				show_error('Akses ditolak: Token cron tidak valid.', 403, 'Akses Ditolak');
				return;
			}
		}

		$this->load->dbutil();
		$letter_tables = array(
			'letters',
			'letter_files',
			'letter_templates',
			'letter_types',
			'letter_counters',
			'letter_requests',
			'incoming_letters',
			'dispositions',
			'disposition_recipients',
			'disposition_activities'
		);

		$existing = array();
		foreach ($letter_tables as $t)
		{
			if ($this->db->table_exists($t))
			{
				$existing[] = $t;
			}
		}

		$prefs = array(
			'tables'     => $existing,
			'format'     => 'txt',
			'filename'   => 'backup_surat_smk.sql',
			'add_drop'   => TRUE,
			'add_insert' => TRUE,
			'newline'    => "\n",
		);

		$backup_dir = storage_path('backups');
		if ( ! is_dir($backup_dir)) @mkdir($backup_dir, 0755, TRUE);

		$filename = 'backup_surat_mingguan_'.date('Ymd_His').'.sql';
		$path = storage_path('backups/'.$filename);

		$backup = $this->dbutil->backup($prefs);
		file_put_contents($path, $backup);

		// Rotasi otomatis: Hapus backup surat mingguan yang lebih lama dari 60 hari
		$now = time();
		foreach (scandir($backup_dir) as $f)
		{
			if (strpos($f, 'backup_surat_mingguan_') === 0 && preg_match('/\.sql$/i', $f))
			{
				$f_path = $backup_dir . DIRECTORY_SEPARATOR . $f;
				if ($now - filemtime($f_path) > 60 * 86400)
				{
					@unlink($f_path);
				}
			}
		}

		$this->load->helper('number');
		$msg = 'OK: Backup surat mingguan berhasil dibuat: ' . $filename . ' (' . count($existing) . ' tabel, ' . byte_format(filesize($path)) . ')';
		log_message('info', '[CRON] ' . $msg);

		if ($is_cli)
		{
			echo $msg . PHP_EOL;
		}
		else
		{
			$this->output->set_content_type('application/json')->set_output(json_encode(array(
				'success'  => TRUE,
				'message'  => $msg,
				'filename' => $filename,
				'size'     => byte_format(filesize($path)),
				'time'     => date('Y-m-d H:i:s'),
			)));
		}
	}

	public function download_backup($filename = '')
	{
		$filename = safe_filename($filename);
		$path = realpath(storage_path('backups/'.$filename.'.sql'));
		$base = realpath(storage_path('backups'));

		if ( ! $path OR ! $base OR strpos($path, $base.DIRECTORY_SEPARATOR) !== 0 OR ! is_file($path))
		{
			$this->not_found('File backup tidak ditemukan.');
		}

		$this->output
			->set_header('Content-Type: text/plain')
			->set_header('Content-Disposition: attachment; filename="'.$filename.'.sql"')
			->set_header('Content-Length: '.filesize($path))
			->set_output(file_get_contents($path));
	}

	public function clean_logs()
	{
		$this->require_post();
		$log_dir = APPPATH.'logs';
		$deleted = 0;
		$today = date('Y-m-d');
		if (is_dir($log_dir))
		{
			foreach (scandir($log_dir) as $f)
			{
				if (preg_match('/^log-(\d{4}-\d{2}-\d{2})\.php$/', $f, $m))
				{
					if ($m[1] !== $today)
					{
						@unlink($log_dir.'/'.$f);
						$deleted++;
					}
				}
			}
		}
		audit_log('CLEAN_LOGS', 'system', NULL, 'Membersihkan '.$deleted.' file log lama.');
		set_flash('success', $deleted.' file log lama berhasil dibersihkan.');
		redirect('log-sistem');
	}

	private function dir_size($dir)
	{
		$size = 0;
		if (is_dir($dir))
		{
			foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)) as $file)
			{
				$size += $file->getSize();
			}
		}
		return format_bytes($size);
	}

}
