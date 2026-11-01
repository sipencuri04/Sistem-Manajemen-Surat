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

		$this->render('logs/backup', array(
			'page_title' => 'Backup Database & Pemeliharaan',
			'files'      => $files,
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
}
