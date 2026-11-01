<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Master jenis surat (termasuk teks pembuka/isi/penutup yang dapat diedit).
 */
class Letter_types extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->require_role(array('tu', 'admin'));
		$this->load->model('Letter_type_model');
		$this->load->library('Template_registry');
	}

	public function index()
	{
		$this->render('letter_types/index', array(
			'page_title' => 'Jenis Surat',
			'types'      => $this->Letter_type_model->all_with_counts(),
			'templates'  => $this->template_registry->options(),
		));
	}

	public function create()
	{
		$type = array('id' => 0, 'code' => '', 'name' => '', 'template_key' => '', 'description' => '',
			'is_active' => 1, 'allow_public' => 0, 'sort_order' => 0, 'opening_text' => '', 'body_text' => '', 'closing_text' => '');
		if ($this->input->method(TRUE) === 'POST' && $this->save(0))
		{
			return;
		}
		$this->show_form($type);
	}

	public function edit($id = 0)
	{
		$type = $this->Letter_type_model->find_with_template((int) $id);
		if ( ! $type)
		{
			$this->not_found('Jenis surat tidak ditemukan.');
		}
		if ($this->input->method(TRUE) === 'POST' && $this->save((int) $type['id']))
		{
			return;
		}
		$this->show_form($type);
	}

	public function delete($id = 0)
	{
		$this->require_post();
		$type = $this->Letter_type_model->find((int) $id);
		if ( ! $type)
		{
			$this->not_found('Jenis surat tidak ditemukan.');
		}
		if ($this->Letter_type_model->delete($type['id']))
		{
			set_flash('success', 'Jenis surat "'.$type['name'].'" dihapus.');
		}
		else
		{
			set_flash('warning', 'Jenis surat "'.$type['name'].'" sudah dipakai oleh surat sehingga tidak dapat dihapus. Nonaktifkan saja bila tidak dipakai lagi.');
		}
		redirect('jenis-surat');
	}

	protected function save($id)
	{
		$this->_except_id = $id;
		$fv = $this->form_validation;
		$fv->set_rules('name', 'Nama jenis surat', 'trim|required|max_length[150]');
		$fv->set_rules('code', 'Kode', 'trim|required|max_length[20]|regex_match[/^[A-Za-z0-9.\-]+$/]|callback__unique_code',
			array('regex_match' => '{field} hanya boleh huruf, angka, titik, dan tanda minus.'));
		$fv->set_rules('template_key', 'Template', 'required|in_list['.implode(',', array_keys($this->template_registry->options())).']');
		$fv->set_rules('description', 'Keterangan', 'trim|max_length[255]');
		$fv->set_rules('sort_order', 'Urutan', 'trim|integer|greater_than_equal_to[0]|less_than_equal_to[9999]');
		$fv->set_rules('opening_text', 'Teks pembuka', 'trim|max_length[3000]');
		$fv->set_rules('body_text', 'Teks isi', 'trim|max_length[3000]');
		$fv->set_rules('closing_text', 'Teks penutup', 'trim|max_length[3000]');

		if ( ! $fv->run())
		{
			return FALSE;
		}

		$type = array(
			'name'         => trim($this->input->post('name')),
			'code'         => strtoupper(trim($this->input->post('code'))),
			'template_key' => $this->input->post('template_key'),
			'description'  => trim((string) $this->input->post('description')) ?: NULL,
			'sort_order'   => (int) $this->input->post('sort_order'),
			'is_active'    => $this->input->post('is_active') ? 1 : 0,
		);
		if ($this->schema_ready)
		{
			$type['allow_public'] = $this->input->post('allow_public') ? 1 : 0;
		}
		$texts = array(
			'opening_text' => str_replace("\r\n", "\n", trim((string) $this->input->post('opening_text'))),
			'body_text'    => str_replace("\r\n", "\n", trim((string) $this->input->post('body_text'))),
			'closing_text' => str_replace("\r\n", "\n", trim((string) $this->input->post('closing_text'))),
		);

		$ok = $id ? $this->Letter_type_model->update($id, $type, $texts) : $this->Letter_type_model->create($type, $texts);
		if ( ! $ok)
		{
			set_flash('danger', 'Jenis surat gagal disimpan.');
			return FALSE;
		}
		set_flash('success', 'Jenis surat "'.$type['name'].'" berhasil disimpan.');
		redirect('jenis-surat');
		return TRUE;
	}

	protected $_except_id = 0;

	public function _unique_code($code)
	{
		if ($this->Letter_type_model->code_taken(strtoupper(trim($code)), $this->_except_id))
		{
			$this->form_validation->set_message('_unique_code', 'Kode sudah digunakan jenis surat lain.');
			return FALSE;
		}
		return TRUE;
	}

	protected function show_form(array $type)
	{
		$this->render('letter_types/form', array(
			'page_title' => $type['id'] ? 'Edit Jenis Surat' : 'Tambah Jenis Surat',
			'type'       => $type,
			'templates'  => $this->template_registry->all(),
			'used'       => $type['id'] ? $this->Letter_type_model->count_letters($type['id']) : 0,
		));
	}
}
