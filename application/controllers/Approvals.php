<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Approvals extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->require_schema();
		$this->load->model(array('Letter_model', 'Letter_type_model', 'Letter_file_model'));
		$this->load->library(array('Template_registry', 'Letter_renderer'));
		$this->require_role(array('kepala_sekolah', 'admin'));
	}

	public function index()
	{
		$letters = $this->db->select('l.*, t.name AS type_name, t.code AS type_code, u.full_name AS created_by_name')
			->from('letters l')
			->join('letter_types t', 't.id = l.letter_type_id', 'inner')
			->join('users u', 'u.id = l.created_by', 'left')
			->where('l.approval_status', 'menunggu_persetujuan')
			->order_by('l.id', 'DESC')
			->get()->result_array();

		$history = $this->db->select('l.*, t.name AS type_name, t.code AS type_code, u.full_name AS created_by_name, u2.full_name AS approved_by_name')
			->from('letters l')
			->join('letter_types t', 't.id = l.letter_type_id', 'inner')
			->join('users u', 'u.id = l.created_by', 'left')
			->join('users u2', 'u2.id = l.approved_by', 'left')
			->where_in('l.approval_status', array('disetujui', 'ditolak'))
			->order_by('l.approved_at', 'DESC')
			->limit(15)
			->get()->result_array();

		$this->render('approvals/index', array(
			'page_title' => 'Persetujuan Surat Keluar',
			'letters'    => $letters,
			'history'    => $history,
		));
	}

	public function approve($id = 0)
	{
		$this->require_post();
		$letter = $this->Letter_model->find((int) $id);
		if ( ! $letter OR $letter['approval_status'] !== 'menunggu_persetujuan')
		{
			set_flash('warning', 'Surat tidak ditemukan atau sudah diproses sebelumnya.');
			redirect('persetujuan-surat');
		}

		$sig_type = $this->input->post('signature_type') === 'basah' ? 'basah' : 'digital';

		$this->db->where('id', $letter['id'])->update('letters', array(
			'approval_status' => 'disetujui',
			'status'          => 'final',
			'signature_type'  => $sig_type,
			'approved_by'     => (int) $this->current_user['id'],
			'approved_at'     => date('Y-m-d H:i:s'),
			'updated_at'      => date('Y-m-d H:i:s'),
		));

		// Muat data surat terbaru dengan tanda tangan yang sudah disetujui
		$updated_letter = $this->Letter_model->find($letter['id']);

		// Buat PDF arsip
		$this->archive_approved_pdf($updated_letter);

		// Notifikasi ke pembuat surat (TU)
		if ($letter['created_by'])
		{
			$sig_note = $sig_type === 'digital' ? 'ditandatangani secara digital' : 'disetujui untuk tanda tangan basah fisik';
			send_notification(
				(int) $letter['created_by'],
				'Surat Disetujui Kepala Sekolah',
				'Surat No. '.$letter['letter_number'].' perihal "'.$letter['subject'].'" telah ' . $sig_note . ' oleh Kepala Sekolah. Silakan periksa dan bubuhkan stempel TU.',
				site_url('surat/detail/'.$letter['id']),
				'persetujuan'
			);
		}

		audit_log('LETTER_APPROVE', 'letters', $letter['id'], 'Kepala Sekolah menyetujui surat '.$letter['letter_number']);
		set_flash('success', 'Surat No. '.$letter['letter_number'].' telah disetujui dan berstatus Final.');
		redirect('persetujuan-surat');
	}

	public function reject($id = 0)
	{
		$this->require_post();
		$letter = $this->Letter_model->find((int) $id);
		if ( ! $letter OR $letter['approval_status'] !== 'menunggu_persetujuan')
		{
			set_flash('warning', 'Surat tidak ditemukan atau sudah diproses.');
			redirect('persetujuan-surat');
		}

		$reason = trim((string) $this->input->post('rejection_reason', TRUE));
		if ($reason === '')
		{
			set_flash('danger', 'Alasan penolakan / revisi surat wajib diisi.');
			redirect('persetujuan-surat');
		}

		$this->db->where('id', $letter['id'])->update('letters', array(
			'approval_status'  => 'ditolak',
			'status'           => 'draft',
			'rejection_reason' => $reason,
			'approved_by'      => (int) $this->current_user['id'],
			'approved_at'      => date('Y-m-d H:i:s'),
			'updated_at'       => date('Y-m-d H:i:s'),
		));

		// Notifikasi pembuat surat (TU)
		if ($letter['created_by'])
		{
			send_notification(
				(int) $letter['created_by'],
				'Surat Ditolak / Perlu Revisi',
				'Surat No. '.$letter['letter_number'].' ditolak oleh Kepala Sekolah. Catatan revisi: '.$reason,
				site_url('surat/detail/'.$letter['id']),
				'warning'
			);
		}

		audit_log('LETTER_REJECT', 'letters', $letter['id'], 'Kepala Sekolah menolak surat '.$letter['letter_number'].'. Alasan: '.$reason);
		set_flash('warning', 'Surat No. '.$letter['letter_number'].' telah ditolak dan dikembalikan ke TU untuk direvisi.');
		redirect('persetujuan-surat');
	}

	protected function archive_approved_pdf(array $letter)
	{
		try {
			$type = $this->Letter_type_model->find_with_template((int) $letter['letter_type_id']);
			$html = $this->letter_renderer->html($letter, $type, $this->school, 'pdf');
			$binary = $this->letter_renderer->pdf_string($html);

			$dir_rel = 'letters/'.date('Y', strtotime($letter['letter_date'])).'/'.date('m', strtotime($letter['letter_date']));
			$dir_abs = storage_path($dir_rel);
			if ( ! is_dir($dir_abs)) @mkdir($dir_abs, 0755, TRUE);

			$name = 'surat-'.(int) $letter['id'].'-'.bin2hex(random_bytes(6)).'.pdf';
			file_put_contents($dir_abs.DIRECTORY_SEPARATOR.$name, $binary);

			$this->Letter_file_model->delete_by_letter($letter['id']);
			$this->Letter_file_model->add(array(
				'letter_id'     => (int) $letter['id'],
				'file_type'     => 'pdf',
				'file_path'     => $dir_rel.'/'.$name,
				'original_name' => safe_filename($letter['type_code'].'_'.$letter['letter_number']).'.pdf',
				'mime_type'     => 'application/pdf',
				'file_size'     => strlen($binary),
				'checksum'      => hash('sha256', $binary),
				'created_by'    => (int) $this->current_user['id'],
			));
		} catch (Exception $e) {
			log_message('error', 'Gagal membuat arsip PDF saat persetujuan surat: '.$e->getMessage());
		}
	}
}
