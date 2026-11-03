<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Queue_model extends CI_Model
{
	protected $table_services = 'queue_services';
	protected $table_tickets  = 'queue_tickets';

	public function __construct()
	{
		parent::__construct();
		$this->ensure_tables();
	}

	/** Pastikan tabel antrean dan 3 layanan utama selalu tersedia otomatis */
	public function ensure_tables()
	{
		if ( ! $this->db->table_exists($this->table_services))
		{
			$this->db->query("CREATE TABLE IF NOT EXISTS `queue_services` (
			  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
			  `code` VARCHAR(5) NOT NULL,
			  `name` VARCHAR(100) NOT NULL,
			  `loket_number` INT NOT NULL DEFAULT 1,
			  `description` VARCHAR(255) NULL DEFAULT NULL,
			  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
			  `created_at` DATETIME NOT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `uq_queue_code` (`code`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

			$now = date('Y-m-d H:i:s');
			$this->db->query("INSERT IGNORE INTO `queue_services` (`id`, `code`, `name`, `loket_number`, `description`, `is_active`, `created_at`) VALUES
			(1, 'A', 'Layanan Surat & Legalisir', 1, 'Pengajuan surat keterangan aktif, legalisir ijazah, surat rekomendasi', 1, '{$now}'),
			(2, 'B', 'Keuangan & Administrasi SPP', 2, 'Pembayaran SPP, cek tunggakan, beasiswa, dan administrasi keuangan', 1, '{$now}'),
			(3, 'C', 'Layanan Umum & Mutasi Siswa', 3, 'Layanan tamu pimpinan, konsultasi kesiswaan, mutasi peserta didik', 1, '{$now}');");
		}
		else
		{
			// Bila tabel ada tapi belum ada baris layanan
			$count = $this->db->count_all($this->table_services);
			if ($count === 0)
			{
				$now = date('Y-m-d H:i:s');
				$this->db->query("INSERT IGNORE INTO `queue_services` (`id`, `code`, `name`, `loket_number`, `description`, `is_active`, `created_at`) VALUES
				(1, 'A', 'Layanan Surat & Legalisir', 1, 'Pengajuan surat keterangan aktif, legalisir ijazah, surat rekomendasi', 1, '{$now}'),
				(2, 'B', 'Keuangan & Administrasi SPP', 2, 'Pembayaran SPP, cek tunggakan, beasiswa, dan administrasi keuangan', 1, '{$now}'),
				(3, 'C', 'Layanan Umum & Mutasi Siswa', 3, 'Layanan tamu pimpinan, konsultasi kesiswaan, mutasi peserta didik', 1, '{$now}');");
			}
		}

		if ( ! $this->db->table_exists($this->table_tickets))
		{
			$this->db->query("CREATE TABLE IF NOT EXISTS `queue_tickets` (
			  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
			  `service_id` INT UNSIGNED NOT NULL,
			  `ticket_number` VARCHAR(15) NOT NULL,
			  `queue_date` DATE NOT NULL,
			  `sequence` INT NOT NULL,
			  `status` ENUM('waiting', 'calling', 'serving', 'completed', 'skipped') NOT NULL DEFAULT 'waiting',
			  `loket_number` INT NULL DEFAULT NULL,
			  `called_by` INT UNSIGNED NULL DEFAULT NULL,
			  `called_at` DATETIME NULL DEFAULT NULL,
			  `completed_at` DATETIME NULL DEFAULT NULL,
			  `created_at` DATETIME NOT NULL,
			  PRIMARY KEY (`id`),
			  KEY `idx_queue_date_status` (`queue_date`, `status`),
			  KEY `idx_queue_service` (`service_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
		}
	}

	/** Ambil semua kategori layanan antrean */
	public function get_services($only_active = TRUE)
	{
		if ($only_active)
		{
			$this->db->where('is_active', 1);
		}
		return $this->db->order_by('code', 'ASC')->get($this->table_services)->result_array();
	}

	/** Ambil 1 layanan berdasarkan ID */
	public function get_service($id)
	{
		return $this->db->where('id', (int) $id)->get($this->table_services)->row_array();
	}

	/** Ambil 1 layanan berdasarkan Kode (A, B, C) */
	public function get_service_by_code($code)
	{
		return $this->db->where('code', strtoupper(trim($code)))->get($this->table_services)->row_array();
	}

	/** Ambil 1 tiket berdasarkan ID */
	public function get_ticket($id)
	{
		return $this->db->select('t.*, s.name AS service_name, s.code AS service_code, u.full_name AS caller_name')
			->from($this->table_tickets.' t')
			->join($this->table_services.' s', 's.id = t.service_id', 'left')
			->join('users u', 'u.id = t.called_by', 'left')
			->where('t.id', (int) $id)
			->get()->row_array();
	}

	/** Buat tiket baru untuk layanan tertentu hari ini */
	public function create_ticket($service_id)
	{
		$service = $this->get_service($service_id);
		if ( ! $service) return FALSE;

		$today = date('Y-m-d');
		$now   = date('Y-m-d H:i:s');

		// Dapatkan nomor urut berikutnya untuk layanan ini hari ini
		$row = $this->db->select('COALESCE(MAX(sequence), 0) + 1 AS next_seq')
			->from($this->table_tickets)
			->where('service_id', (int) $service_id)
			->where('queue_date', $today)
			->get()->row_array();

		$next_seq = (int) $row['next_seq'];
		$ticket_number = $service['code'] . '-' . str_pad($next_seq, 3, '0', STR_PAD_LEFT);

		$data = array(
			'service_id'    => (int) $service_id,
			'ticket_number' => $ticket_number,
			'queue_date'    => $today,
			'sequence'      => $next_seq,
			'status'        => 'waiting',
			'loket_number'  => $service['loket_number'],
			'created_at'    => $now,
		);

		$this->db->insert($this->table_tickets, $data);
		$ticket_id = $this->db->insert_id();

		// Hitung antrean di depan tiket ini
		$waiting_before = $this->db->from($this->table_tickets)
			->where('service_id', (int) $service_id)
			->where('queue_date', $today)
			->where('status', 'waiting')
			->where('id <', $ticket_id)
			->count_all_results();

		$res = $this->get_ticket($ticket_id);
		$res['waiting_before'] = $waiting_before;
		return $res;
	}

	/** Ambil tiket berikutnya yang sedang menunggu */
	public function get_next_waiting_ticket($service_id = NULL)
	{
		$today = date('Y-m-d');
		$this->db->select('t.*, s.name AS service_name, s.code AS service_code')
			->from($this->table_tickets.' t')
			->join($this->table_services.' s', 's.id = t.service_id', 'left')
			->where('t.queue_date', $today)
			->where('t.status', 'waiting');

		if ($service_id)
		{
			$this->db->where('t.service_id', (int) $service_id);
		}

		return $this->db->order_by('t.id', 'ASC')->limit(1)->get()->row_array();
	}

	/** Panggil tiket (ubah status menjadi calling) */
	public function call_ticket($ticket_id, $loket_number, $user_id)
	{
		$now = date('Y-m-d H:i:s');
		$this->db->where('id', (int) $ticket_id)->update($this->table_tickets, array(
			'status'       => 'calling',
			'loket_number' => (int) $loket_number,
			'called_by'    => (int) $user_id,
			'called_at'    => $now,
		));

		return $this->get_ticket($ticket_id);
	}

	/** Panggil ulang tiket (update called_at agar trigger audio panggilan di TV) */
	public function recall_ticket($ticket_id)
	{
		$now = date('Y-m-d H:i:s');
		$this->db->where('id', (int) $ticket_id)->update($this->table_tickets, array(
			'called_at' => $now,
		));

		return $this->get_ticket($ticket_id);
	}

	/** Tandai tiket selesai dilayani */
	public function complete_ticket($ticket_id)
	{
		$now = date('Y-m-d H:i:s');
		return $this->db->where('id', (int) $ticket_id)->update($this->table_tickets, array(
			'status'       => 'completed',
			'completed_at' => $now,
		));
	}

	/** Lewati tiket (siswa/tamu tidak hadir) */
	public function skip_ticket($ticket_id)
	{
		$now = date('Y-m-d H:i:s');
		return $this->db->where('id', (int) $ticket_id)->update($this->table_tickets, array(
			'status'       => 'skipped',
			'completed_at' => $now,
		));
	}

	/** Ambil tiket terakhir yang baru saja dipanggil hari ini (untuk sorotan display TV) */
	public function get_last_called_ticket()
	{
		$today = date('Y-m-d');
		return $this->db->select('t.*, s.name AS service_name, s.code AS service_code')
			->from($this->table_tickets.' t')
			->join($this->table_services.' s', 's.id = t.service_id', 'left')
			->where('t.queue_date', $today)
			->where_in('t.status', array('calling', 'serving'))
			->order_by('t.called_at', 'DESC')
			->limit(1)
			->get()->row_array();
	}

	/** Dapatkan status tiket yang sedang aktif di masing-masing loket */
	public function get_active_lokets()
	{
		$today = date('Y-m-d');
		$services = $this->get_services(TRUE);
		$lokets = array();

		foreach ($services as $srv)
		{
			$loket_num = (int) $srv['loket_number'];

			// Ambil tiket yang sedang calling/serving di loket ini
			$current = $this->db->select('t.*, s.name AS service_name, s.code AS service_code')
				->from($this->table_tickets.' t')
				->join($this->table_services.' s', 's.id = t.service_id', 'left')
				->where('t.queue_date', $today)
				->where('t.loket_number', $loket_num)
				->where_in('t.status', array('calling', 'serving'))
				->order_by('t.called_at', 'DESC')
				->limit(1)
				->get()->row_array();

			// Hitung sisa antrean menunggu di layanan ini
			$waiting_count = $this->db->from($this->table_tickets)
				->where('queue_date', $today)
				->where('service_id', (int) $srv['id'])
				->where('status', 'waiting')
				->count_all_results();

			$lokets[$loket_num] = array(
				'loket_number'   => $loket_num,
				'service_id'     => $srv['id'],
				'service_code'   => $srv['code'],
				'service_name'   => $srv['name'],
				'current_ticket' => $current ? $current['ticket_number'] : '-',
				'called_at'      => $current ? $current['called_at'] : NULL,
				'status'         => $current ? $current['status'] : 'idle',
				'waiting_count'  => $waiting_count,
			);
		}

		return $lokets;
	}

	/** Statistik antrean hari ini */
	public function get_stats_today()
	{
		$today = date('Y-m-d');
		$total     = $this->db->from($this->table_tickets)->where('queue_date', $today)->count_all_results();
		$waiting   = $this->db->from($this->table_tickets)->where('queue_date', $today)->where('status', 'waiting')->count_all_results();
		$calling   = $this->db->from($this->table_tickets)->where('queue_date', $today)->where_in('status', array('calling', 'serving'))->count_all_results();
		$completed = $this->db->from($this->table_tickets)->where('queue_date', $today)->where('status', 'completed')->count_all_results();
		$skipped   = $this->db->from($this->table_tickets)->where('queue_date', $today)->where('status', 'skipped')->count_all_results();

		return array(
			'total'     => $total,
			'waiting'   => $waiting,
			'calling'   => $calling,
			'completed' => $completed,
			'skipped'   => $skipped,
		);
	}

	/** Ambil riwayat tiket hari ini untuk panel loket */
	public function get_today_tickets_list($service_id = NULL, $limit = 50)
	{
		$today = date('Y-m-d');
		$this->db->select('t.*, s.name AS service_name, s.code AS service_code, u.full_name AS caller_name')
			->from($this->table_tickets.' t')
			->join($this->table_services.' s', 's.id = t.service_id', 'left')
			->join('users u', 'u.id = t.called_by', 'left')
			->where('t.queue_date', $today);

		if ($service_id)
		{
			$this->db->where('t.service_id', (int) $service_id);
		}

		return $this->db->order_by('t.id', 'DESC')->limit($limit)->get()->result_array();
	}

	/** Reset antrean hari ini */
	public function reset_today()
	{
		$today = date('Y-m-d');
		$this->db->where('queue_date', $today)->delete($this->table_tickets);
		$cnt = $this->db->count_all($this->table_tickets);
		if ($cnt === 0)
		{
			$this->db->truncate($this->table_tickets);
		}
		return TRUE;
	}
}

