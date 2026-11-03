<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Antrean extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Queue_model');
		$this->load->model('Settings_model');
		// Jalankan migrasi skema database jika belum
		$this->load->library('schema_upgrade');
		$this->schema_upgrade->run();
	}

	/**
	 * Halaman Kiosk Mesin Antrean Mandiri
	 * Ditaruh di tablet / layar sentuh / PC depan ruang TU untuk siswa & tamu.
	 */
	public function index()
	{
		$this->kiosk();
	}

	public function kiosk()
	{
		$school   = $this->Settings_model->get(TRUE);
		$services = $this->Queue_model->get_services(TRUE);
		$lokets   = $this->Queue_model->get_active_lokets();
		$stats    = $this->Queue_model->get_stats_today();

		$data = array(
			'page_title' => 'Ambil Nomor Antrean Tata Usaha',
			'school'     => $school,
			'services'   => $services,
			'lokets'     => $lokets,
			'stats'      => $stats,
		);

		$this->load->view('antrean/kiosk', $data);
	}

	/**
	 * Buat tiket baru (Dipanggil saat siswa/tamu memilih layanan di Kiosk)
	 */
	public function ambil_tiket($service_id = 0)
	{
		$service_id = (int) $service_id;
		$service = $this->Queue_model->get_service($service_id);
		if ( ! $service)
		{
			$this->Queue_model->ensure_tables();
			$service = $this->Queue_model->get_service($service_id);
			if ( ! $service)
			{
				$all_srv = $this->Queue_model->get_services(TRUE);
				if ( ! empty($all_srv))
				{
					$service = $all_srv[0];
					$service_id = (int) $service['id'];
				}
			}
		}

		if ( ! $service || empty($service['is_active']))
		{
			if ($this->input->is_ajax_request() || $this->input->get('format') === 'json')
			{
				$this->output->set_content_type('application/json')->set_output(json_encode(array(
					'success' => FALSE,
					'message' => 'Layanan antrean tidak ditemukan atau sedang tidak aktif.',
				)));
				return;
			}
			set_flash('danger', 'Layanan tidak valid.');
			redirect('antrean');
			return;
		}

		$ticket = $this->Queue_model->create_ticket($service_id);
		$school = $this->Settings_model->get(TRUE);

		if ($this->input->is_ajax_request() || $this->input->get('format') === 'json')
		{
			$this->output->set_content_type('application/json')->set_output(json_encode(array(
				'success' => TRUE,
				'ticket'  => $ticket,
				'html'    => $this->load->view('antrean/_ticket_card', array('ticket' => $ticket, 'school' => $school), TRUE),
			)));
			return;
		}

		// Jika akses langsung dari browser, tampilkan struk cetak tiket
		$this->load->view('antrean/ticket_print', array(
			'ticket' => $ticket,
			'school' => $school,
		));
	}

	/**
	 * Layar Display Monitor TV Ruang Tunggu Fullscreen
	 * Diakses langsung dari Smart TV, Android TV, atau PC Display di dinding TU.
	 */
	public function display()
	{
		$school   = $this->Settings_model->get(TRUE);
		$services = $this->Queue_model->get_services(TRUE);
		$lokets   = $this->Queue_model->get_active_lokets();
		$last     = $this->Queue_model->get_last_called_ticket();

		$data = array(
			'page_title'  => 'Layar Monitor Antrean TU',
			'school'      => $school,
			'services'    => $services,
			'lokets'      => $lokets,
			'last_called' => $last,
		);

		$this->load->view('antrean/display', $data);
	}

	/**
	 * Endpoint API Polling Ringan untuk TV Display (setiap 2-3 detik)
	 */
	public function api_display_data()
	{
		$lokets = $this->Queue_model->get_active_lokets();
		$last   = $this->Queue_model->get_last_called_ticket();
		$stats  = $this->Queue_model->get_stats_today();

		$this->output->set_content_type('application/json')->set_output(json_encode(array(
			'success'     => TRUE,
			'last_called' => $last,
			'lokets'      => $lokets,
			'stats'       => $stats,
			'server_time' => date('H:i:s'),
		)));
	}

	/**
	 * Panel Petugas Loket TU (Untuk staf TU memanggil antrean)
	 * Wajib Login (TU, Admin, Superuser)
	 */
	public function loket()
	{
		$user_id = (int) $this->session->userdata('user_id');
		if ( ! $user_id)
		{
			set_flash('warning', 'Silakan login terlebih dahulu untuk mengakses Panel Loket TU.');
			redirect('login');
			return;
		}

		$this->load->model('User_model');
		$current_user = $this->User_model->find($user_id);
		if ( ! $current_user || ! (int) $current_user['is_active'])
		{
			redirect('logout');
			return;
		}
		$this->current_user = $current_user;
		$services = $this->Queue_model->get_services(TRUE);

		// Loket yang sedang dipilih staf TU saat ini
		$selected_service_id = (int) $this->input->get_post('service_id');
		if ( ! $selected_service_id && ! empty($services))
		{
			// Default loket pertama atau dari sesi
			$selected_service_id = (int) $this->session->userdata('tu_loket_service_id') ?: $services[0]['id'];
		}
		$this->session->set_userdata('tu_loket_service_id', $selected_service_id);

		$current_service = $this->Queue_model->get_service($selected_service_id);
		$today_tickets   = $this->Queue_model->get_today_tickets_list($selected_service_id, 30);
		$lokets_status   = $this->Queue_model->get_active_lokets();
		$stats           = $this->Queue_model->get_stats_today();

		// Tiket yang sedang dilayani di loket ini
		$active_ticket = NULL;
		if ($current_service)
		{
			$loket_num = (int) $current_service['loket_number'];
			if (isset($lokets_status[$loket_num]) && $lokets_status[$loket_num]['current_ticket'] !== '-')
			{
				$active_ticket = $this->Queue_model->get_last_called_ticket();
				if ($active_ticket && (int) $active_ticket['loket_number'] !== $loket_num)
				{
					$active_ticket = NULL;
				}
			}
		}

		$this->current_user = $current_user;
		$data = array(
			'page_title'      => 'Panel Loket Antrean TU',
			'current_user'    => $current_user,
			'services'        => $services,
			'current_service' => $current_service,
			'selected_id'     => $selected_service_id,
			'today_tickets'   => $today_tickets,
			'lokets_status'   => $lokets_status,
			'stats'           => $stats,
			'active_ticket'   => $active_ticket,
		);

		$this->render('antrean/loket', $data);
	}

	/**
	 * Aksi Panggil Antrean Berikutnya
	 */
	public function api_call_next()
	{
		if ( ! is_logged_in())
		{
			$this->output->set_content_type('application/json')->set_output(json_encode(array(
				'success' => FALSE,
				'message' => 'Sesi login telah berakhir.',
			)));
			return;
		}

		$service_id = (int) $this->input->post('service_id');
		$service    = $this->Queue_model->get_service($service_id);
		if ( ! $service)
		{
			$this->output->set_content_type('application/json')->set_output(json_encode(array(
				'success' => FALSE,
				'message' => 'Layanan loket tidak valid.',
			)));
			return;
		}

		$next_ticket = $this->Queue_model->get_next_waiting_ticket($service_id);
		if ( ! $next_ticket)
		{
			$this->output->set_content_type('application/json')->set_output(json_encode(array(
				'success' => FALSE,
				'message' => 'Tidak ada antrean yang sedang menunggu pada ' . $service['name'] . '.',
			)));
			return;
		}

		$user_id = (int) $this->session->userdata('user_id');
		$called = $this->Queue_model->call_ticket($next_ticket['id'], $service['loket_number'], $user_id);

		$this->output->set_content_type('application/json')->set_output(json_encode(array(
			'success' => TRUE,
			'message' => 'Memanggil antrean ' . $called['ticket_number'] . ' ke Loket ' . $service['loket_number'],
			'ticket'  => $called,
		)));
	}

	/**
	 * Aksi Panggil Ulang (Recall)
	 */
	public function api_recall()
	{
		if ( ! is_logged_in())
		{
			$this->output->set_content_type('application/json')->set_output(json_encode(array('success' => FALSE)));
			return;
		}

		$ticket_id = (int) $this->input->post('ticket_id');
		$ticket    = $this->Queue_model->get_ticket($ticket_id);
		if ( ! $ticket)
		{
			$this->output->set_content_type('application/json')->set_output(json_encode(array(
				'success' => FALSE,
				'message' => 'Tiket tidak ditemukan.',
			)));
			return;
		}

		$recalled = $this->Queue_model->recall_ticket($ticket_id);

		$this->output->set_content_type('application/json')->set_output(json_encode(array(
			'success' => TRUE,
			'message' => 'Panggilan ulang ' . $recalled['ticket_number'] . ' berhasil dikirim ke layar TV.',
			'ticket'  => $recalled,
		)));
	}

	/**
	 * Aksi Selesai Melayani
	 */
	public function api_complete()
	{
		if ( ! is_logged_in())
		{
			$this->output->set_content_type('application/json')->set_output(json_encode(array('success' => FALSE)));
			return;
		}

		$ticket_id = (int) $this->input->post('ticket_id');
		$this->Queue_model->complete_ticket($ticket_id);

		$this->output->set_content_type('application/json')->set_output(json_encode(array(
			'success' => TRUE,
			'message' => 'Layanan antrean telah selesai.',
		)));
	}

	/**
	 * Aksi Lewati (Skip)
	 */
	public function api_skip()
	{
		if ( ! is_logged_in())
		{
			$this->output->set_content_type('application/json')->set_output(json_encode(array('success' => FALSE)));
			return;
		}

		$ticket_id = (int) $this->input->post('ticket_id');
		$this->Queue_model->skip_ticket($ticket_id);

		$this->output->set_content_type('application/json')->set_output(json_encode(array(
			'success' => TRUE,
			'message' => 'Antrean telah dilewati.',
		)));
	}

	/**
	 * Reset Antrean Hari Ini
	 */
	public function reset_today()
	{
		if ( ! is_logged_in() || ! (is_tu() || is_superuser() || is_admin()))
		{
			set_flash('danger', 'Akses ditolak.');
			redirect('dashboard');
			return;
		}

		$this->Queue_model->reset_today();
		set_flash('success', 'Seluruh antrean hari ini telah berhasil direset ke nomor awal.');
		redirect('antrean/loket');
	}
}

