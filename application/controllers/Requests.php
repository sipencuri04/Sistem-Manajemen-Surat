<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pengelolaan permohonan surat dari siswa oleh TU (wajib login).
 * Alur: Menunggu -> (Proses jadi surat) Siap Diambil -> Sudah Diambil, atau Ditolak.
 */
class Requests extends Admin_Controller
{
	const PER_PAGE = 15;

	public function __construct()
	{
		parent::__construct();
		$this->require_schema();
		$this->load->model(array('Letter_request_model', 'Letter_type_model'));
		$this->load->library(array('Template_registry', 'pagination'));
	}

	public function index()
	{
		$status = (string) $this->input->get('status', TRUE);
		if ($status === '' && $this->input->get('status') === NULL)
		{
			$status = 'pending';
		}
		$filters = array(
			'status' => $status === 'semua' ? '' : $status,
			'q'      => substr(trim((string) $this->input->get('q', TRUE)), 0, 100),
		);
		$total  = $this->Letter_request_model->count_search($filters);
		$page   = max(1, (int) $this->input->get('page'));
		$offset = ($page - 1) * self::PER_PAGE;
		$this->pagination->initialize(pagination_config('permohonan-masuk', $total, self::PER_PAGE));

		$counts = array();
		foreach (array_keys(Letter_request_model::$statuses) as $s)
		{
			$counts[$s] = $this->Letter_request_model->count_status($s);
		}

		$this->render('requests/index', array(
			'page_title' => 'Permohonan Siswa',
			'status'     => $status,
			'filters'    => $filters,
			'requests'   => $this->Letter_request_model->search($filters, self::PER_PAGE, $offset),
			'total'      => $total,
			'offset'     => $offset,
			'counts'     => $counts,
			'pagination' => $this->pagination->create_links(),
		));
	}

	public function detail($id = 0)
	{
		$req = $this->load_request($id);
		$type = $this->Letter_type_model->find($req['letter_type_id']);
		$tpl  = $this->template_registry->get($req['template_key']);

		$data = $req['data'];
		// Pastikan data penting selalu terisi dari identitas pemohon jika belum ada di data JSON
		if (empty($data['nama_siswa']) && ! empty($req['requester_name']))
		{
			$data['nama_siswa'] = $req['requester_name'];
		}
		if (empty($data['kelas']) && ! empty($req['requester_class']))
		{
			$data['kelas'] = $req['requester_class'];
		}

		$this->load->library('Letter_numbering');
		$this->load->model('Settings_model');
		$school = $this->Settings_model->get(TRUE);
		$next_number = $type ? $this->letter_numbering->preview($school, $type, date('Y-m-d')) : '-';

		$this->render('requests/detail', array(
			'page_title'  => 'Detail Permohonan',
			'req'         => $req,
			'type'        => $type,
			'tpl'         => $tpl,
			'school'      => $school,
			'next_number' => $next_number,
			'values'      => $tpl ? $this->template_registry->formatted_values($tpl, $data) : array(),
		));
	}

	/** Pratinjau surat untuk permohonan siswa (tampil di modal). */
	public function preview($id = 0)
	{
		$this->require_role(array('tu', 'superuser', 'admin', 'kepala_sekolah'));
		$req = $this->load_request($id);
		$type = $this->Letter_type_model->find($req['letter_type_id']);
		if ( ! $type)
		{
			show_404();
		}

		$this->load->library(array('Letter_numbering', 'Letter_renderer'));
		$this->load->model('Settings_model');
		$school = $this->Settings_model->get(TRUE);
		$number = $this->letter_numbering->preview($school, $type, date('Y-m-d'));

		$kepsek = $this->db->get_where('users', array('role' => 'kepala_sekolah', 'is_active' => 1))->row_array();

		$data = $req['data'];
		if (empty($data['nama_siswa']) && ! empty($req['requester_name'])) $data['nama_siswa'] = $req['requester_name'];
		if (empty($data['kelas']) && ! empty($req['requester_class'])) $data['kelas'] = $req['requester_class'];
		if (empty($data['kompetensi_keahlian']) && ! empty($data['jurusan'])) $data['kompetensi_keahlian'] = $data['jurusan'];
		if (empty($data['alamat_instansi']) && ! empty($data['alamat'])) $data['alamat_instansi'] = $data['alamat'];

		$mock_letter = array(
			'letter_number'   => $req['letter_number'] ?: $number,
			'letter_date'     => date('Y-m-d'),
			'subject'         => $type['name'] . ' a.n. ' . $req['requester_name'],
			'recipient'       => !empty($type['recipient_default']) ? $type['recipient_default'] : 'Siswa yang bersangkutan',
			'signer_name'     => $kepsek ? $kepsek['full_name'] : (!empty($school['headmaster_name']) ? $school['headmaster_name'] : 'Kepala Sekolah'),
			'signer_nip'      => $kepsek ? $kepsek['nip'] : (!empty($school['headmaster_nip']) ? $school['headmaster_nip'] : '-'),
			'signer_title'    => 'Kepala Sekolah',
			'signature_type'  => 'digital',
			'stamp_type'      => 'digital',
			'approval_status' => 'menunggu_persetujuan',
			'status'          => 'draft',
			'content_data'    => json_encode($data),
			'data'            => $data,
		);

		$html = $this->letter_renderer->html($mock_letter, $type, $school, 'html', array('watermark' => 'PRATINJAU DOKUMEN'));
		$this->output->set_output($html);
	}

	/** Buat surat secara otomatis dari data permohonan siswa & langsung ajukan ke Kepala Sekolah. */
	public function kirim_kepsek($id = 0)
	{
		$this->require_role(array('tu', 'superuser', 'admin'));
		$this->require_post();
		$req = $this->load_request($id);

		if ($req['status'] !== 'pending')
		{
			set_flash('warning', 'Permohonan ini sudah diproses sebelumnya.');
			redirect('permohonan-masuk/'.(int) $req['id']);
			return;
		}

		$type = $this->Letter_type_model->find($req['letter_type_id']);
		if ( ! $type)
		{
			set_flash('danger', 'Jenis surat tidak valid.');
			redirect('permohonan-masuk/'.(int) $req['id']);
			return;
		}

		$this->load->library('Letter_numbering');
		$this->load->model(array('Letter_model', 'Settings_model'));
		$school = $this->Settings_model->get(TRUE);

		$letter_number = trim((string) $this->input->post('letter_number', TRUE));
		$letter_date   = trim((string) $this->input->post('letter_date', TRUE));
		if ( ! valid_date_string($letter_date))
		{
			$letter_date = date('Y-m-d');
		}

		$signature_type = (string) $this->input->post('signature_type', TRUE);
		if ( ! in_array($signature_type, array('digital', 'basah'), TRUE))
		{
			$signature_type = 'digital';
		}

		$kepsek = $this->db->get_where('users', array('role' => 'kepala_sekolah', 'is_active' => 1))->row_array();

		$data = $req['data'];
		if (empty($data['nama_siswa']) && ! empty($req['requester_name'])) $data['nama_siswa'] = $req['requester_name'];
		if (empty($data['kelas']) && ! empty($req['requester_class'])) $data['kelas'] = $req['requester_class'];
		if (empty($data['kompetensi_keahlian']) && ! empty($data['jurusan'])) $data['kompetensi_keahlian'] = $data['jurusan'];
		if (empty($data['alamat_instansi']) && ! empty($data['alamat'])) $data['alamat_instansi'] = $data['alamat'];

		// Alokasi nomor jika kosong / otomatis
		$sequence_no = 0;
		$number_year = (int) date('Y', strtotime($letter_date));
		if ($letter_number === '')
		{
			$alloc = $this->letter_numbering->allocate($school, $type, $letter_date);
			$letter_number = $alloc['number'];
			$sequence_no   = $alloc['sequence'];
			$number_year   = $alloc['year'];
		}
		else
		{
			// Cek apakah nomor manual bentrok
			if ($this->Letter_model->number_exists($letter_number, 0))
			{
				set_flash('danger', 'Nomor surat "'.$letter_number.'" sudah terpakai. Silakan gunakan nomor lain.');
				redirect('permohonan-masuk/'.(int) $req['id']);
				return;
			}
		}

		$row = array(
			'letter_type_id'   => (int) $type['id'],
			'letter_number'    => $letter_number,
			'letter_date'      => $letter_date,
			'subject'          => $type['name'] . ' a.n. ' . $req['requester_name'],
			'recipient'        => !empty($type['recipient_default']) ? $type['recipient_default'] : 'Siswa yang bersangkutan',
			'content_data'     => json_encode($data),
			'signer_name'      => $kepsek ? $kepsek['full_name'] : (!empty($school['headmaster_name']) ? $school['headmaster_name'] : 'Kepala Sekolah'),
			'signer_nip'       => $kepsek ? $kepsek['nip'] : (!empty($school['headmaster_nip']) ? $school['headmaster_nip'] : '-'),
			'signer_title'     => 'Kepala Sekolah',
			'status'           => 'draft',
			'approval_status'  => 'menunggu_persetujuan',
			'signature_type'   => $signature_type,
			'stamp_type'       => 'belum_stempel',
			'sequence_no'      => $sequence_no,
			'number_year'      => $number_year,
			'notes'            => 'Dari permohonan siswa kode ' . $req['tracking_code'],
			'created_by'       => (int) $this->current_user['id'],
		);

		$letter_id = $this->Letter_model->insert($row);
		if ( ! $letter_id)
		{
			set_flash('danger', 'Gagal membuat surat. Terjadi kesalahan pada database.');
			redirect('permohonan-masuk/'.(int) $req['id']);
			return;
		}

		// Update permohonan siswa menjadi 'processed'
		$this->Letter_request_model->update($req['id'], array(
			'status'       => 'processed',
			'letter_id'    => $letter_id,
			'admin_note'   => 'Surat telah dibuat oleh TU (No. ' . $letter_number . '), sedang menunggu tanda tangan Kepala Sekolah.',
			'processed_by' => (int) $this->current_user['id'],
			'processed_at' => date('Y-m-d H:i:s'),
		));

		// Kirim notifikasi internal ke Kepala Sekolah
		$kepseks = $this->db->get_where('users', array('role' => 'kepala_sekolah', 'is_active' => 1))->result_array();
		foreach ($kepseks as $ks)
		{
			send_notification(
				(int) $ks['id'],
				'Permohonan Surat Siswa Baru',
				'Surat No. ' . $letter_number . ' (' . $type['name'] . ' a.n. ' . $req['requester_name'] . ') diajukan oleh TU dan siap ditandatangani.',
				site_url('persetujuan-surat'),
				'persetujuan'
			);
		}

		$school_name = !empty($school['school_name']) ? $school['school_name'] : 'SMK Muhammadiyah Bandongan';
		$send_wa = (bool) $this->input->post('send_wa');
		$send_email = (bool) $this->input->post('send_email');

		// 1. Tautan WhatsApp otomatis ke siswa jika dicentang
		$wa_url = '';
		if ($send_wa && !empty($req['requester_phone']))
		{
			$msg_wa = "Halo *" . $req['requester_name'] . "*," . "\n\n"
				. "Kabar baik! Permohonan surat *" . $type['name'] . "* Anda di *" . $school_name . "* (Kode: *" . $req['tracking_code'] . "*) telah *DIVERIFIKASI OLEH TATA USAHA* dengan Nomor Surat: *" . $letter_number . "*." . "\n\n"
				. "Saat ini surat telah diajukan ke meja Kepala Sekolah untuk proses penandatanganan dan stempel resmi." . "\n\n"
				. "Anda dapat melacak status surat secara berkala di: " . site_url('permohonan/cek?kode=' . $req['tracking_code']) . "\n\n"
				. "Terima kasih." . "\n"
				. "- *Tata Usaha " . $school_name . "*";
			$wa_url = build_wa_link($req['requester_phone'], $msg_wa);
			$this->session->set_flashdata('open_wa_url', $wa_url);
		}

		// 2. Kirim email notifikasi ke siswa jika dicentang
		$email_sent_info = '';
		if ($send_email && !empty($req['requester_email']) && !empty($school['smtp_user']) && !empty($school['smtp_pass']) && !empty($school['smtp_enabled']))
		{
			try
			{
				$this->load->library('email');
				$this->load->library('email_theme');
				$smtp_crypto = !empty($school['smtp_crypto']) ? $school['smtp_crypto'] : 'tls';
				if ($smtp_crypto === 'none') $smtp_crypto = '';
				$config = array(
					'protocol'    => 'smtp',
					'smtp_host'   => !empty($school['smtp_host']) ? $school['smtp_host'] : 'smtp.gmail.com',
					'smtp_user'   => $school['smtp_user'],
					'smtp_pass'   => $school['smtp_pass'],
					'smtp_port'   => !empty($school['smtp_port']) ? (int) $school['smtp_port'] : 587,
					'smtp_crypto' => $smtp_crypto,
					'mailtype'    => 'html',
					'charset'     => 'utf-8',
					'newline'     => "\r\n",
					'crlf'        => "\r\n",
				);
				$this->email->initialize($config);
				$from_name = !empty($school['smtp_from_name']) ? $school['smtp_from_name'] : $school_name;
				$this->email->from($school['smtp_user'], $from_name);
				$this->email->to($req['requester_email']);
				$this->email->subject('[' . $from_name . '] Surat Anda Sedang Diproses - ' . $req['tracking_code']);

				$cek_url = site_url('permohonan/cek?kode=' . $req['tracking_code']);
				$body = $this->email_theme->render(array(
					'title'          => 'Permohonan Surat Sedang Diproses - ' . $req['tracking_code'],
					'school_name'    => $school_name,
					'recipient_name' => $req['requester_name'],
					'badge_type'     => 'info',
					'badge_text'     => 'SEDANG DIPROSES',
					'message'        => 'Permohonan surat <strong>' . htmlspecialchars($type['name']) . '</strong> Anda telah diverifikasi oleh petugas Tata Usaha dan saat ini telah diajukan ke meja Kepala Sekolah untuk proses penandatanganan dan stempel resmi.',
					'details'        => array(
						'Kode Lacak'   => '<code style="font-family:monospace; font-size:14px; background:#e2e8f0; padding:2px 8px; border-radius:4px; font-weight:700; color:#0f172a;">' . htmlspecialchars($req['tracking_code']) . '</code>',
						'Jenis Surat'  => htmlspecialchars($type['name']),
						'Nomor Surat'  => '<strong>' . htmlspecialchars($letter_number) . '</strong>',
						'Status'       => '<span style="color:#2563eb; font-weight:700;">Sedang Diproses (Menunggu TTD Kepsek)</span>',
					),
					'button_url'     => $cek_url,
					'button_text'    => 'Lacak Status Surat',
				));
				$this->email->message($body);
				if (@$this->email->send())
				{
					$email_sent_info = ' serta notifikasi otomatis telah dikirim ke Gmail siswa (' . $req['requester_email'] . ')';
				}
			}
			catch (Throwable $e)
			{
				log_message('error', 'Gagal mengirim email pemberitahuan ke siswa: ' . $e->getMessage());
			}
		}

		$wa_btn = '';
		if (!empty($wa_url))
		{
			$wa_btn = ' <a href="' . $wa_url . '" target="_blank" rel="noopener" class="btn btn-xs btn-success rounded-pill px-2 py-1 ms-2 fw-semibold text-white"><i class="fa-brands fa-whatsapp me-1"></i> Buka WhatsApp Siswa</a>';
		}

		audit_log('LETTER_CREATE_FROM_REQUEST', 'letters', $letter_id, 'Membuat surat '.$letter_number.' langsung dari permohonan '.$req['tracking_code'].' dan diteruskan ke Kepala Sekolah');
		set_flash('success', '<strong>Berhasil!</strong> Surat No. <strong>' . $letter_number . '</strong> berhasil diterbitkan dan langsung diteruskan ke meja Kepala Sekolah untuk ditandatangani' . $email_sent_info . '.' . $wa_btn, TRUE);
		redirect('permohonan-masuk/'.(int) $req['id']);
	}

	/** Tolak permohonan dengan alasan (wajib) dan siapkan notifikasi WhatsApp / Gmail. */
	public function reject($id = 0)
	{
		$this->require_post();
		$req = $this->load_request($id);
		if ($req['status'] !== 'pending')
		{
			set_flash('warning', 'Permohonan ini sudah diproses sebelumnya.');
			redirect('permohonan-masuk/'.(int) $req['id']);
		}
		$reason = trim((string) $this->input->post('admin_note'));
		if ($reason === '' OR mb_strlen($reason) > 500)
		{
			set_flash('danger', 'Alasan penolakan wajib diisi (maksimal 500 karakter). Alasan ini terlihat oleh siswa.');
			redirect('permohonan-masuk/'.(int) $req['id']);
		}
		$this->Letter_request_model->update($req['id'], array(
			'status'       => 'rejected',
			'admin_note'   => $reason,
			'processed_by' => (int) $this->current_user['id'],
			'processed_at' => date('Y-m-d H:i:s'),
		));

		$this->load->model('Settings_model');
		$school = $this->Settings_model->get(TRUE);
		$school_name = !empty($school['school_name']) ? $school['school_name'] : 'SMK Muhammadiyah Bandongan';

		$send_wa = (bool) $this->input->post('send_wa');
		$send_email = (bool) $this->input->post('send_email');

		// 1. Siapkan tautan WhatsApp prefilled dengan alasan penolakan
		$wa_url = '';
		if ($send_wa && !empty($req['requester_phone']))
		{
			$msg_wa = "Halo *" . $req['requester_name'] . "*," . "\n\n"
				. "Mohon maaf, permohonan surat *" . $req['type_name'] . "* Anda di *" . $school_name . "* (Kode: *" . $req['tracking_code'] . "*) *BELUM DAPAT KAMI PROSES* karena alasan berikut:" . "\n\n"
				. "\"" . $reason . "\"" . "\n\n"
				. "Silakan perbaiki data dan ajukan ulang secara online atau datang langsung ke ruang Tata Usaha." . "\n\n"
				. "Terima kasih." . "\n"
				. "- *Tata Usaha " . $school_name . "*";
			$wa_url = build_wa_link($req['requester_phone'], $msg_wa);
			$this->session->set_flashdata('open_wa_url', $wa_url);
		}

		// 2. Kirim email penolakan otomatis jika dicentang dan siswa memiliki email
		$email_sent_text = '';
		if ($send_email && !empty($req['requester_email']) && !empty($school['smtp_user']) && !empty($school['smtp_pass']) && !empty($school['smtp_enabled']))
		{
			try {
				$this->send_reject_email($req, $reason, $school);
				$email_sent_text = ' &amp; email pemberitahuan ke <u>' . htmlspecialchars($req['requester_email']) . '</u>';
			} catch (Exception $e) {
				log_message('error', 'Gagal kirim email penolakan: ' . $e->getMessage());
			}
		}

		$wa_btn = '';
		if (!empty($wa_url))
		{
			$wa_btn = '<div class="mt-2"><a href="' . $wa_url . '" target="_blank" class="btn btn-sm btn-success fw-bold shadow-sm"><i class="fa-brands fa-whatsapp me-1"></i> Buka WhatsApp &amp; Kirim Alasan ke ' . htmlspecialchars($req['requester_phone']) . '</a></div>';
		}

		set_flash('success', '<strong>Permohonan ' . $req['tracking_code'] . ' Ditolak.</strong> Alasan penolakan telah disimpan' . $email_sent_text . '.' . $wa_btn, TRUE);
		redirect('permohonan-masuk/'.(int) $req['id']);
	}

	/** Tandai surat sudah diambil siswa. */
	public function taken($id = 0)
	{
		$this->require_post();
		$req = $this->load_request($id);
		if ($req['status'] !== 'approved')
		{
			set_flash('warning', 'Hanya permohonan berstatus Siap Diambil yang dapat ditandai sudah diambil.');
			redirect('permohonan-masuk/'.(int) $req['id']);
			return;
		}

		if ( ! empty($req['letter_id']))
		{
			$this->load->model('Letter_model');
			$letter = $this->Letter_model->find((int) $req['letter_id']);
			if ( ! $letter || (isset($letter['approval_status']) && $letter['approval_status'] !== 'disetujui') || empty($letter['stamp_type']) || $letter['stamp_type'] === 'belum_stempel')
			{
				set_flash('danger', 'Surat belum lengkap! Pastikan surat telah ditandatangani Kepala Sekolah dan telah distempel oleh TU sebelum diserahkan ke pemohon.');
				redirect('permohonan-masuk/'.(int) $req['id']);
				return;
			}
		}

		$this->Letter_request_model->update($req['id'], array('status' => 'taken'));
		set_flash('success', 'Permohonan '.$req['tracking_code'].' ditandai sudah diambil.');
		redirect('permohonan-masuk?status=taken');
	}

	/** Kirim email pemberitahuan/berkas ke Gmail siswa oleh petugas TU. */
	public function send_email($id = 0)
	{
		$this->require_post();
		$req = $this->load_request($id);

		$to_email = trim((string) $this->input->post('recipient_email', TRUE));
		$subject  = trim((string) $this->input->post('email_subject', TRUE));
		$message  = trim((string) $this->input->post('email_message'));
		$attach_pdf = (bool) $this->input->post('attach_pdf');

		if ( ! filter_var($to_email, FILTER_VALIDATE_EMAIL))
		{
			set_flash('danger', 'Alamat email tujuan tidak valid.');
			redirect('permohonan-masuk/'.(int) $req['id']);
			return;
		}

		if ($subject === '' || $message === '')
		{
			set_flash('danger', 'Subjek dan isi pesan email wajib diisi.');
			redirect('permohonan-masuk/'.(int) $req['id']);
			return;
		}

		// Perbarui data email siswa jika sebelumnya kosong atau diubah
		if ($to_email !== $req['requester_email'])
		{
			$this->Letter_request_model->update($req['id'], array('requester_email' => $to_email));
		}

		$this->load->model('Settings_model');
		$school = $this->Settings_model->get(TRUE);

		$smtp_host   = !empty($school['smtp_host']) ? $school['smtp_host'] : (getenv('SMTP_HOST') ?: (isset($_ENV['SMTP_HOST']) ? $_ENV['SMTP_HOST'] : 'smtp.gmail.com'));
		$smtp_user   = !empty($school['smtp_user']) ? $school['smtp_user'] : (getenv('SMTP_USER') ?: (isset($_ENV['SMTP_USER']) ? $_ENV['SMTP_USER'] : ''));
		$smtp_pass   = !empty($school['smtp_pass']) ? $school['smtp_pass'] : (getenv('SMTP_PASS') ?: (isset($_ENV['SMTP_PASS']) ? $_ENV['SMTP_PASS'] : ''));
		$smtp_port   = !empty($school['smtp_port']) ? (int) $school['smtp_port'] : 587;
		$smtp_crypto = !empty($school['smtp_crypto']) ? $school['smtp_crypto'] : 'tls';
		if ($smtp_crypto === 'none') $smtp_crypto = '';

		$from_email = !empty($smtp_user) ? $smtp_user : 'noreply@sekolah.sch.id';
		$from_name  = !empty($school['smtp_from_name']) ? $school['smtp_from_name'] : (!empty($school['school_name']) ? $school['school_name'] : 'Tata Usaha');

		if (empty($smtp_user) || empty($smtp_pass))
		{
			set_flash('danger', 'Pengaturan Gmail TU belum disimpan. Buka menu Pengaturan &raquo; Email Notifikasi untuk menghubungkan Gmail terlebih dahulu.');
			redirect('permohonan-masuk/'.(int) $req['id']);
			return;
		}

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

		$this->load->library('email');
		$this->load->library('email_theme');
		$this->email->initialize($config);
		$this->email->from($from_email, $from_name);
		$this->email->to($to_email);
		$this->email->subject($subject);

		$status_label = isset(Letter_request_model::$statuses[$req['status']]) ? Letter_request_model::$statuses[$req['status']] : ucfirst($req['status']);
		$badge_type = ($req['status'] === 'approved') ? 'success' : (($req['status'] === 'rejected') ? 'danger' : 'info');
		$badge_text = ($req['status'] === 'approved') ? 'SURAT RESMI TELAH DITERBITKAN' : (($req['status'] === 'rejected') ? 'PERMOHONAN PERLU PERBAIKAN' : 'PEMBERITAHUAN TATA USAHA');

		$details = array(
			'Kode Lacak' => '<code style="font-family:monospace; font-size:14px; background:#e2e8f0; padding:2px 8px; border-radius:4px; font-weight:700; color:#0f172a;">' . htmlspecialchars($req['tracking_code']) . '</code>',
			'Jenis Surat' => htmlspecialchars($req['type_name']),
		);
		if (!empty($req['letter_number'])) {
			$details['Nomor Surat'] = '<strong style="color:#1e3a8a;">' . htmlspecialchars($req['letter_number']) . '</strong>';
		}
		$details['Status Permohonan'] = '<span style="display:inline-block; padding:3px 10px; border-radius:12px; font-size:11.5px; font-weight:700; background:#e0f2fe; color:#0369a1;">' . htmlspecialchars($status_label) . '</span>';

		$cek_url = site_url('permohonan/cek?kode=' . $req['tracking_code']);
		$html_body = $this->email_theme->render(array(
			'title'          => $subject,
			'recipient_name' => $req['requester_name'],
			'badge_type'     => $badge_type,
			'badge_text'     => $badge_text,
			'message'        => nl2br(htmlspecialchars($message)),
			'details'        => $details,
			'has_attachment' => ($attach_pdf && !empty($req['letter_id'])),
			'attachment_name'=> !empty($req['letter_number']) ? 'Surat_' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $req['letter_number']) . '.pdf' : 'Dokumen_Surat_Resmi.pdf',
			'button_url'     => $cek_url,
			'button_text'    => 'Lacak Status Permohonan Online',
		));

		$this->email->message($html_body);

		// Lampirkan PDF jika diminta dan berkas ada
		if ($attach_pdf && !empty($req['letter_id']))
		{
			$this->load->model('Letter_model');
			$letter = $this->Letter_model->find($req['letter_id']);
			if ($letter && !empty($letter['file_path']))
		{
				$pdf_path = storage_path($letter['file_path']);
				if (is_file($pdf_path))
				{
					$this->email->attach($pdf_path);
				}
			}
		}

		if ($this->email->send())
		{
			set_flash('success', 'Email pemberitahuan berhasil dikirim ke Gmail siswa (<u>' . htmlspecialchars($to_email) . '</u>).', TRUE);
		}
		else
		{
			$debug_msg = $this->email->print_debugger(array('headers', 'subject', 'body'));
			set_flash('danger', 'Gagal mengirim email ke siswa. Keterangan: ' . htmlspecialchars(strip_tags($debug_msg)));
		}

		redirect('permohonan-masuk/'.(int) $req['id']);
	}

	/** Kirim email pemberitahuan penolakan otomatis ke Gmail siswa. */
	protected function send_reject_email($req, $reason, $school)
	{
		$smtp_host   = !empty($school['smtp_host']) ? $school['smtp_host'] : (getenv('SMTP_HOST') ?: (isset($_ENV['SMTP_HOST']) ? $_ENV['SMTP_HOST'] : 'smtp.gmail.com'));
		$smtp_user   = !empty($school['smtp_user']) ? $school['smtp_user'] : (getenv('SMTP_USER') ?: (isset($_ENV['SMTP_USER']) ? $_ENV['SMTP_USER'] : ''));
		$smtp_pass   = !empty($school['smtp_pass']) ? $school['smtp_pass'] : (getenv('SMTP_PASS') ?: (isset($_ENV['SMTP_PASS']) ? $_ENV['SMTP_PASS'] : ''));
		$smtp_port   = !empty($school['smtp_port']) ? (int) $school['smtp_port'] : 587;
		$smtp_crypto = !empty($school['smtp_crypto']) ? $school['smtp_crypto'] : 'tls';
		if ($smtp_crypto === 'none') $smtp_crypto = '';

		$from_email  = $smtp_user;
		$from_name   = !empty($school['smtp_from_name']) ? $school['smtp_from_name'] : (!empty($school['school_name']) ? $school['school_name'] : 'Tata Usaha');

		$config = array(
			'protocol'    => 'smtp',
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

		$this->load->library('email');
		$this->load->library('email_theme');
		$this->email->initialize($config);
		$this->email->from($from_email, $from_name);
		$this->email->to($req['requester_email']);
		$this->email->subject('[' . $from_name . '] Pemberitahuan Permohonan Belum Dapat Diproses - ' . $req['tracking_code']);

		$cek_url = site_url('permohonan/cek?kode=' . $req['tracking_code']);
		$body = $this->email_theme->render(array(
			'title'          => 'Pemberitahuan Permohonan Surat - ' . $req['tracking_code'],
			'recipient_name' => $req['requester_name'],
			'badge_type'     => 'danger',
			'badge_text'     => 'PERMOHONAN BELUM DAPAT DIPROSES',
			'message'        => 'Mohon maaf, permohonan surat <strong>' . htmlspecialchars($req['type_name']) . '</strong> Anda dengan Kode Lacak <strong>' . htmlspecialchars($req['tracking_code']) . '</strong> belum dapat diproses oleh petugas Tata Usaha sekolah karena alasan berikut:',
			'callout_type'   => 'danger',
			'callout_title'  => 'Alasan dari Petugas Tata Usaha:',
			'callout_text'   => $reason,
			'details'        => array(
				'Kode Lacak' => '<code style="font-family:monospace; font-size:14px; background:#e2e8f0; padding:2px 8px; border-radius:4px; font-weight:700; color:#0f172a;">' . htmlspecialchars($req['tracking_code']) . '</code>',
				'Jenis Surat' => htmlspecialchars($req['type_name']),
				'Status Permohonan' => '<span style="display:inline-block; padding:3px 10px; border-radius:12px; font-size:11.5px; font-weight:700; background:#fef2f2; color:#b91c1c; border:1px solid #fecaca;">Ditolak / Belum Dapat Diproses</span>',
			),
			'button_url'     => $cek_url,
			'button_text'    => 'Perbaiki & Ajukan Ulang Permohonan',
			'closing_note'   => 'Silakan perbaiki kekurangan data/dokumen di atas dan ajukan kembali permohonan secara online melalui portal sekolah atau datang langsung ke ruang Tata Usaha pada jam operasional sekolah.',
		));

		$this->email->message($body);
		@$this->email->send();
	}

	protected function load_request($id)
	{
		$req = $this->Letter_request_model->find((int) $id);
		if ( ! $req)
		{
			$this->not_found('Permohonan tidak ditemukan.');
		}
		return $req;
	}
}
