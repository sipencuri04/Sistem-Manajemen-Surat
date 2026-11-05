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
	 * API endpoint: kembalikan data tiket dalam format JSON untuk QZ Tray ESC/POS
	 * URL: antrean/escpos_data/<ticket_id>
	 */
	public function escpos_data($ticket_id = 0)
	{
		$ticket_id = (int) $ticket_id;
		$ticket = $this->Queue_model->get_ticket($ticket_id);
		$school = $this->Settings_model->get(TRUE);

		if ( ! $ticket)
		{
			$this->output->set_content_type('application/json')
				->set_output(json_encode(['success' => FALSE, 'message' => 'Tiket tidak ditemukan.']));
			return;
		}

		$school_name = strtoupper($school['school_name'] ?: 'SMK MUHAMMADIYAH BANDONGAN');
		$ticket_number = $ticket['ticket_number'];
		$service_name  = strtoupper($ticket['service_name']);
		$loket_number  = (int) $ticket['loket_number'];
		$waiting       = (int) $ticket['waiting_before'];
		$tanggal       = date('d/m/Y');
		$waktu         = date('H:i');

		// ============================================================
		// Susun perintah ESC/POS sebagai array byte (dikirim ke QZ Tray)
		// ESC/POS Reference: Epson Technical Reference Guide
		// ============================================================
		$ESC = "\x1B";
		$GS  = "\x1D";
		$LF  = "\x0A";
		$INIT          = $ESC . "\x40";           // Initialize printer
		$CENTER        = $ESC . "\x61\x01";       // Align center
		$LEFT          = $ESC . "\x61\x00";       // Align left
		$BOLD_ON       = $ESC . "\x45\x01";       // Bold on
		$BOLD_OFF      = $ESC . "\x45\x00";       // Bold off
		$DBLHEIGHT_ON  = $ESC . "\x21\x10";       // Double height text
		$DBLBOTH_ON    = $ESC . "\x21\x30";       // Double height + width
		$NORMAL        = $ESC . "\x21\x00";       // Normal text
		$SIZE_BIG      = $GS  . "\x21\x33";       // 4x width, 4x height
		$SIZE_MEDIUM   = $GS  . "\x21\x11";       // 2x width, 2x height
		$SIZE_NORMAL   = $GS  . "\x21\x00";       // Normal size
		$DIVIDER_THICK = str_repeat("=", 32) . $LF;
		$DIVIDER_THIN  = str_repeat("-", 32) . $LF;
		$CUT           = $GS  . "\x56\x41\x03";  // Partial cut with feed

		$lines = [];

		// Init
		$lines[] = $INIT;

		// Header sekolah
		$lines[] = $CENTER . $BOLD_ON . $DBLHEIGHT_ON;
		$lines[] = $school_name . $LF;
		$lines[] = $NORMAL . $BOLD_OFF;
		$lines[] = "Layanan Tata Usaha & Administrasi" . $LF;

		// Garis tebal
		$lines[] = $BOLD_ON . $DIVIDER_THICK . $BOLD_OFF;

		// Label nomor
		$lines[] = $CENTER . $BOLD_ON . "NOMOR ANTREAN ANDA" . $LF . $BOLD_OFF;

		// Nomor antrean besar
		$lines[] = $CENTER . $BOLD_ON . $SIZE_BIG;
		$lines[] = "  " . $ticket_number . "  " . $LF;
		$lines[] = $SIZE_NORMAL . $BOLD_OFF;

		// Nama layanan + loket
		$lines[] = $CENTER . $BOLD_ON . $SIZE_MEDIUM;
		$lines[] = $service_name . $LF;
		$lines[] = $SIZE_NORMAL . $BOLD_OFF;
		$lines[] = $CENTER . $BOLD_ON . "LOKET " . $loket_number . $LF . $BOLD_OFF;

		// Garis tebal
		$lines[] = $BOLD_ON . $DIVIDER_THICK . $BOLD_OFF;

		// Info tanggal, waktu, antrian
		$lines[] = $LEFT;
		$col_w = 16;
		$lines[] = $BOLD_ON . str_pad("Tanggal", $col_w) . ": " . $tanggal . $LF;
		$lines[] = str_pad("Waktu Ambil", $col_w) . ": " . $waktu . " WIB" . $LF;
		$lines[] = str_pad("Antrian Depan", $col_w) . ": " . $waiting . " Orang" . $LF . $BOLD_OFF;

		// Garis tipis
		$lines[] = $DIVIDER_THIN;

		// Footer
		$lines[] = $CENTER . "Simpan tiket & lihat monitor." . $LF;
		$lines[] = "Terima kasih telah tertib antre." . $LF . $LF . $LF;

		// Cut
		$lines[] = $CUT;

		$this->output->set_content_type('application/json')->set_output(json_encode([
			'success'       => TRUE,
			'ticket_number' => $ticket_number,
			'escpos_raw'    => base64_encode(implode('', $lines)),
		]));
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
		$last          = $this->Queue_model->get_last_called_ticket();
		$recent_called = $this->Queue_model->get_recent_called_tickets(3);

		$data = array(
			'page_title'    => 'Layar Monitor Antrean TU',
			'school'        => $school,
			'services'      => $services,
			'lokets'        => $lokets,
			'last_called'   => $last,
			'recent_called' => $recent_called,
		);

		$this->load->view('antrean/display', $data);
	}

	/**
	 * Endpoint API Polling Ringan untuk TV Display (Ultra-lightweight Change Detection)
	 * Mengurangi beban server cPanel hingga 95% dengan version-check cache.
	 */
	public function api_display_data()
	{
		$server_v = $this->Queue_model->get_version();
		$client_v = (string) $this->input->get('v');

		// Jika data belum berubah sama sekali, langsung kembalikan respons mikro (0 query MySQL)
		if ($client_v !== '' && $client_v === $server_v)
		{
			$this->output->set_content_type('application/json')->set_output(json_encode(array(
				'success'     => TRUE,
				'changed'     => FALSE,
				'version'     => $server_v,
				'server_time' => date('H:i:s'),
			)));
			return;
		}

		$lokets        = $this->Queue_model->get_active_lokets();
		$last          = $this->Queue_model->get_last_called_ticket();
		$recent_called = $this->Queue_model->get_recent_called_tickets(3);
		$stats         = $this->Queue_model->get_stats_today();

		$this->output->set_content_type('application/json')->set_output(json_encode(array(
			'success'       => TRUE,
			'changed'       => TRUE,
			'version'       => $server_v,
			'last_called'   => $last,
			'recent_called' => $recent_called,
			'lokets'        => $lokets,
			'stats'         => $stats,
			'server_time'   => date('H:i:s'),
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
	 * API Polling Realtime untuk Panel Loket Staf TU (Background Auto-Sync Tanpa Refresh)
	 */
	public function api_loket_data()
	{
		$server_v = $this->Queue_model->get_version();
		$client_v = (string) $this->input->get('v');

		// Jika data belum berubah, kembalikan respons mikro tanpa query berat
		if ($client_v !== '' && $client_v === $server_v)
		{
			$this->output->set_content_type('application/json')->set_output(json_encode(array(
				'success'     => TRUE,
				'changed'     => FALSE,
				'version'     => $server_v,
				'server_time' => date('H:i:s'),
			)));
			return;
		}

		$service_id = (int) $this->input->get('service_id');
		$stats = $this->Queue_model->get_stats_today();
		$today_tickets = $this->Queue_model->get_today_tickets_list($service_id ?: NULL, 50);
		$active_ticket = $this->Queue_model->get_active_serving_ticket($service_id ?: NULL);

		// Hitung antrean yang menunggu khusus pada layanan/loket ini
		$waiting_this = 0;
		if ($service_id)
		{
			$waiting_this = $this->db->from('queue_tickets')
				->where('queue_date', date('Y-m-d'))
				->where('service_id', (int) $service_id)
				->where('status', 'waiting')
				->count_all_results();
		}

		$this->output->set_content_type('application/json')->set_output(json_encode(array(
			'success'       => TRUE,
			'changed'       => TRUE,
			'version'       => $server_v,
			'active_ticket' => $active_ticket,
			'stats'         => $stats,
			'waiting_this'  => $waiting_this,
			'tickets'       => $today_tickets,
			'server_time'   => date('H:i:s'),
		)));
	}

	/**
	 * Reset Antrean Hari Ini
	 */
	public function reset_today()
	{
		$user_id = (int) $this->session->userdata('user_id');
		if ( ! $user_id)
		{
			set_flash('danger', 'Akses ditolak. Silakan login terlebih dahulu.');
			redirect('login');
			return;
		}

		$this->load->model('User_model');
		$user = $this->User_model->find($user_id);
		if ( ! $user || ! in_array($user['role'], array('tu', 'superuser', 'admin'), TRUE))
		{
			set_flash('danger', 'Akses ditolak. Anda tidak memiliki izin untuk mereset antrean.');
			redirect('dashboard');
			return;
		}

		$this->Queue_model->reset_today();
		set_flash('success', '<strong>Antrean Berhasil Direset!</strong> Seluruh nomor antrean hari ini telah dikosongkan dan akan dimulai kembali dari nomor 001.');
		redirect('antrean/loket');
	}
}

