<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller dasar.
 * Atribut AllowDynamicProperties mencegah notice "deprecated" pada PHP 8.2+
 * (CodeIgniter 3 menambahkan properti library secara dinamis).
 */
#[\AllowDynamicProperties]
class MY_Controller extends CI_Controller
{
	/** @var array Identitas sekolah (baris school_settings). */
	protected $school = array();

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Settings_model');
		$this->school = $this->Settings_model->get();

		// Perbarui struktur database lama secara otomatis (sekali saja).
		$this->load->library('Schema_upgrade');
		$this->schema_ready = $this->schema_upgrade->run();
	}

	/** @var bool TRUE bila struktur database sudah versi terbaru. */
	protected $schema_ready = TRUE;

	/** Hentikan bila fitur membutuhkan tabel yang belum ada. */
	protected function require_schema()
	{
		if ( ! $this->schema_ready)
		{
			show_error('Fitur ini membutuhkan pembaruan database. Minta petugas IT mengimpor file database/update_permohonan_siswa.sql melalui phpMyAdmin.', 503, 'Pembaruan Database Diperlukan');
		}
	}

	/**
	 * Render view di dalam layout dashboard.
	 */
	protected function render($view, array $data = array())
	{
		$data['school']       = $this->school;
		$data['current_user'] = isset($this->current_user) ? $this->current_user : NULL;
		$data['page_title']   = isset($data['page_title']) ? $data['page_title'] : 'Dashboard';

		if ($data['current_user'] && $this->schema_ready)
		{
			$user_id = (int) $data['current_user']['id'];
			$role    = strtolower($data['current_user']['role']);

			// Notifikasi internal
			$this->load->model('Notification_model');
			$data['unread_notifications_count'] = $this->Notification_model->count_unread($user_id);
			$data['recent_notifications']       = $this->Notification_model->recent($user_id, 5);

			// Optimasi Caching Badge Sidebar (60 Detik TTL via Session Cache)
			$now = time();
			$cache_badge = $this->session->userdata('_sidebar_badges');
			$badge_data = (is_array($cache_badge) && isset($cache_badge['time']) && ($now - $cache_badge['time'] < 60)) 
				? $cache_badge['data'] 
				: NULL;

			if ($badge_data === NULL)
			{
				$badge_data = array(
					'pending_requests'        => 0,
					'pending_incoming_count'  => 0,
					'my_pending_dispositions' => 0,
					'pending_approvals_count' => 0,
				);

				// Badge permohonan siswa (TU / Superuser / Admin)
				if (in_array($role, array('tu', 'superuser', 'admin'), TRUE))
				{
					$this->load->model('Letter_request_model');
					$badge_data['pending_requests'] = $this->Letter_request_model->count_status('pending');
				}

				// Badge surat masuk belum didisposisi (Kepsek & TU)
				if (in_array($role, array('kepala_sekolah', 'tu', 'superuser', 'admin'), TRUE))
				{
					$badge_data['pending_incoming_count'] = $this->db->where_in('status', array('baru', 'diteruskan'))->count_all_results('incoming_letters');
				}

				// Badge disposisi aktif milik pegawai
				if ($role === 'pegawai')
				{
					$this->load->model('Disposition_model');
					$badge_data['my_pending_dispositions'] = $this->Disposition_model->count_pending_for_user($user_id);
				}

				// Badge surat menunggu persetujuan (Kepala Sekolah)
				if (in_array($role, array('kepala_sekolah', 'superuser', 'admin'), TRUE))
				{
					$badge_data['pending_approvals_count'] = $this->db->where('approval_status', 'menunggu_persetujuan')->count_all_results('letters');
				}

				$this->session->set_userdata('_sidebar_badges', array('time' => $now, 'data' => $badge_data));
			}

			// Masukkan data badge ke template
			$data['pending_requests']        = $badge_data['pending_requests'];
			$data['pending_incoming_count']  = $badge_data['pending_incoming_count'];
			$data['my_pending_dispositions'] = $badge_data['my_pending_dispositions'];
			$data['pending_approvals_count'] = $badge_data['pending_approvals_count'];
		}

		$data['content'] = $this->load->view($view, $data, TRUE);
		$this->load->view('layouts/app', $data);
	}

	/** Hentikan request dengan 404 jika data tidak ditemukan. */
	protected function not_found($message = 'Data tidak ditemukan.')
	{
		show_error($message, 404, 'Tidak Ditemukan');
	}

	/** Hanya izinkan method POST (untuk aksi yang mengubah data). */
	protected function require_post()
	{
		if ($this->input->method(TRUE) !== 'POST')
		{
			show_error('Metode tidak diizinkan.', 405, 'Metode Tidak Diizinkan');
		}
	}
}

/**
 * Controller untuk halaman yang wajib login.
 */
#[\AllowDynamicProperties]
class Admin_Controller extends MY_Controller
{
	/** @var array|null Pengguna yang sedang login. */
	public $current_user;

	public function get_current_user()
	{
		return $this->current_user;
	}

	public function __construct()
	{
		parent::__construct();

		// Izinkan cron job via CLI atau endpoint cron ber-token rahasia
		if (is_cli() || ($this->uri->segment(1) === 'backup' && $this->uri->segment(2) === 'cron' && $this->input->get('token') === 'smk_weekly_backup_secret'))
		{
			return;
		}

		$this->load->model('User_model');

		$user_id = (int) $this->session->userdata('user_id');
		$user    = $user_id ? $this->User_model->find($user_id) : NULL;

		if ( ! $user OR ! (int) $user['is_active'])
		{
			$this->session->unset_userdata(array('user_id', 'username'));
			if ($this->input->is_ajax_request())
			{
				$this->output->set_status_header(401)
					->set_content_type('application/json')
					->set_output(json_encode(array('error' => 'Sesi berakhir, silakan login kembali.')))
					->_display();
				exit;
			}
			set_flash('warning', 'Silakan login terlebih dahulu.');
			redirect('login');
		}

		$this->current_user = $user;

		// Izinkan browser menyimpan cache privat lokal untuk bfcache & instant prerender (<0.10s LCP),
		// sembari tetap aman (private, no-cache memaksa browser memverifikasi sesi).
		$this->output->set_header('Cache-Control: private, no-cache, must-revalidate');
		$this->output->set_header('Pragma: no-cache');
		$this->output->set_header('X-Frame-Options: SAMEORIGIN');
		$this->output->set_header('X-Content-Type-Options: nosniff');
	}

	/**
	 * Periksa apakah pengguna memiliki salah satu role yang diminta.
	 * Akun legacy 'admin' diperlakukan memiliki kewenangan TU dan Superuser.
	 */
	public function has_role($roles)
	{
		if ( ! $this->current_user) return FALSE;
		$user_role = strtolower($this->current_user['role']);
		$roles = is_array($roles) ? $roles : array($roles);
		$roles = array_map('strtolower', $roles);

		if ($user_role === 'admin' && (in_array('tu', $roles, TRUE) || in_array('superuser', $roles, TRUE) || in_array('admin', $roles, TRUE)))
		{
			return TRUE;
		}

		return in_array($user_role, $roles, TRUE);
	}

	/**
	 * Wajibkan pengguna memiliki role tertentu, atau tolak dengan HTTP 403 Forbidden.
	 */
	public function require_role($roles)
	{
		if ( ! $this->has_role($roles))
		{
			$this->forbidden('Akses ditolak. Peran Anda ('.e(role_title($this->current_user['role'])).') tidak memiliki izin untuk mengakses halaman ini.');
		}
	}

	public function forbidden($message = 'Akses ditolak.')
	{
		show_error($message, 403, '403 Akses Ditolak');
	}

	public function is_superuser()
	{
		return $this->has_role('superuser');
	}

	public function is_kepala_sekolah()
	{
		return $this->has_role('kepala_sekolah');
	}

	public function is_tu()
	{
		return $this->has_role('tu');
	}

	public function is_pegawai()
	{
		return $this->has_role('pegawai');
	}
}
