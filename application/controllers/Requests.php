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
		$this->render('requests/detail', array(
			'page_title' => 'Detail Permohonan',
			'req'        => $req,
			'type'       => $type,
			'tpl'        => $tpl,
			'values'     => $tpl ? $this->template_registry->formatted_values($tpl, $req['data']) : array(),
		));
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
		}
		$this->Letter_request_model->update($req['id'], array('status' => 'taken'));
		set_flash('success', 'Permohonan '.$req['tracking_code'].' ditandai sudah diambil.');
		redirect('permohonan-masuk?status=approved');
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
		$this->email->initialize($config);
		$this->email->from($from_email, $from_name);
		$this->email->to($to_email);
		$this->email->subject($subject);

		$status_label = isset(Letter_request_model::$statuses[$req['status']]) ? Letter_request_model::$statuses[$req['status']] : ucfirst($req['status']);

		$html_body = '
		<div style="font-family:Arial, Helvetica, sans-serif; max-width:600px; margin:0 auto; padding:22px; border:1px solid #e2e8f0; border-radius:8px; background:#ffffff;">
			<div style="border-bottom:2px solid #1e3a8a; padding-bottom:12px; margin-bottom:18px;">
				<h3 style="color:#1e3a8a; margin:0 0 4px 0; font-size:18px;">' . htmlspecialchars($school['school_name'] ?: 'SMK Muhammadiyah Bandongan') . '</h3>
				<div style="font-size:12px; color:#64748b;">Layanan Persuratan &amp; Tata Usaha Sekolah</div>
			</div>
			<div style="font-size:14px; line-height:1.6; color:#334155; margin-bottom:20px;">
				' . nl2br(htmlspecialchars($message)) . '
			</div>
			<div style="background:#f8fafc; border-left:4px solid #3b82f6; padding:12px; font-size:13px; color:#475569; margin:18px 0; border-radius:0 6px 6px 0;">
				<div><strong>Kode Lacak:</strong> ' . htmlspecialchars($req['tracking_code']) . '</div>
				<div><strong>Jenis Surat:</strong> ' . htmlspecialchars($req['type_name']) . '</div>
				' . (!empty($req['letter_number']) ? '<div><strong>Nomor Surat:</strong> ' . htmlspecialchars($req['letter_number']) . '</div>' : '') . '
				<div><strong>Status:</strong> ' . htmlspecialchars($status_label) . '</div>
			</div>
			<div style="border-top:1px solid #e2e8f0; padding-top:12px; font-size:11px; color:#94a3b8; text-align:center;">
				Email ini dikirim resmi oleh Tata Usaha ' . htmlspecialchars($school['school_name'] ?: 'Sekolah') . ' melalui Sistem Persuratan Digital.
			</div>
		</div>';

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
		$this->email->initialize($config);
		$this->email->from($from_email, $from_name);
		$this->email->to($req['requester_email']);
		$this->email->subject('[' . $from_name . '] Pemberitahuan Permohonan Surat Belum Dapat Diproses - ' . $req['tracking_code']);

		$body = '
		<div style="font-family:Arial, Helvetica, sans-serif; max-width:600px; margin:0 auto; padding:22px; border:1px solid #fee2e2; border-radius:8px; background:#ffffff;">
			<div style="border-bottom:2px solid #dc2626; padding-bottom:12px; margin-bottom:18px;">
				<h3 style="color:#991b1b; margin:0 0 4px 0; font-size:18px;">' . htmlspecialchars($school['school_name'] ?: 'SMK Muhammadiyah Bandongan') . '</h3>
				<div style="font-size:12px; color:#64748b;">Layanan Persuratan &amp; Tata Usaha</div>
			</div>
			<p style="color:#334155; font-size:14px; line-height:1.5;">Halo <strong>' . htmlspecialchars($req['requester_name']) . '</strong>,</p>
			<p style="color:#334155; font-size:14px; line-height:1.5;">Mohon maaf, permohonan surat <strong>' . htmlspecialchars($req['type_name']) . '</strong> Anda dengan Kode Lacak <strong>' . htmlspecialchars($req['tracking_code']) . '</strong> belum dapat diproses oleh petugas Tata Usaha karena alasan berikut:</p>
			<div style="background:#fef2f2; border-left:4px solid #dc2626; padding:12px 15px; font-size:14px; color:#991b1b; margin:16px 0; border-radius:0 6px 6px 0;">
				' . nl2br(htmlspecialchars($reason)) . '
			</div>
			<p style="color:#475569; font-size:13px; line-height:1.5;">Silakan lengkapi berkas dan ajukan permohonan ulang melalui portal sekolah atau datang langsung ke ruang Tata Usaha pada jam kerja operasional sekolah.</p>
			<div style="border-top:1px solid #e2e8f0; padding-top:12px; font-size:11px; color:#94a3b8; text-align:center;">
				Email resmi dari Tata Usaha ' . htmlspecialchars($school['school_name'] ?: 'Sekolah') . '.
			</div>
		</div>';

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
