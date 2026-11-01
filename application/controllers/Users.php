<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends Admin_Controller
{
	const PER_PAGE = 15;

	public function __construct()
	{
		parent::__construct();
		$this->require_schema();
		$this->load->model('User_model');
		$this->load->library('pagination');
		$this->require_role(array('superuser', 'admin'));
	}

	public function index()
	{
		$filters = array(
			'q'         => substr(trim((string) $this->input->get('q', TRUE)), 0, 100),
			'role'      => (string) $this->input->get('role', TRUE),
			'is_active' => $this->input->get('is_active') !== NULL && $this->input->get('is_active') !== '' ? (int) $this->input->get('is_active') : '',
		);

		$total  = $this->User_model->count_search($filters);
		$page   = max(1, (int) $this->input->get('page'));
		$offset = ($page - 1) * self::PER_PAGE;
		$this->pagination->initialize(pagination_config('pengguna', $total, self::PER_PAGE));

		$this->render('users/index', array(
			'page_title' => 'Kelola Akun & Role Pengguna',
			'users'      => $this->User_model->search($filters, self::PER_PAGE, $offset),
			'total'      => $total,
			'filters'    => $filters,
			'roles'      => $this->User_model->all_roles(),
			'offset'     => $offset,
			'pagination' => $this->pagination->create_links(),
		));
	}

	public function create()
	{
		$this->render('users/create', array(
			'page_title' => 'Tambah Akun Pengguna',
			'roles'      => $this->User_model->all_roles(),
		));
	}

	public function store()
	{
		$this->require_post();
		$this->load->library('form_validation');
		$fv = $this->form_validation;
		$fv->set_rules('username', 'Username', 'trim|required|min_length[3]|max_length[50]|alpha_dash|is_unique[users.username]',
			array('is_unique' => 'Username ini sudah digunakan. Pilih username lain.'));
		$fv->set_rules('password', 'Password', 'required|min_length[8]');
		$fv->set_rules('full_name', 'Nama Lengkap', 'trim|required|max_length[150]');
		$fv->set_rules('role', 'Peran / Role', 'required');
		$fv->set_rules('email', 'Email', 'trim|valid_email|max_length[150]');
		$fv->set_rules('nip', 'NIP', 'trim|max_length[30]');
		$fv->set_rules('position', 'Jabatan', 'trim|max_length[100]');
		$fv->set_rules('phone', 'No. Telepon/WA', 'trim|max_length[30]');

		if ( ! $fv->run())
		{
			$this->render('users/create', array(
				'page_title' => 'Tambah Akun Pengguna',
				'roles'      => $this->User_model->all_roles(),
			));
			return;
		}

		$id = $this->User_model->create(
			$this->input->post('username', TRUE),
			$this->input->post('password'),
			$this->input->post('full_name', TRUE),
			$this->input->post('email', TRUE) ?: NULL,
			$this->input->post('role', TRUE),
			$this->input->post('nip', TRUE) ?: NULL,
			$this->input->post('position', TRUE) ?: NULL,
			$this->input->post('phone', TRUE) ?: NULL
		);

		if ($id)
		{
			audit_log('USER_CREATE', 'users', $id, 'Membuat pengguna baru: '.$this->input->post('username').' dengan role '.$this->input->post('role'));
			set_flash('success', 'Akun pengguna '.$this->input->post('username').' berhasil dibuat.');
			redirect('pengguna');
		}
		else
		{
			set_flash('danger', 'Gagal membuat akun.');
			redirect('pengguna/tambah');
		}
	}

	public function edit($id = 0)
	{
		$user = $this->load_target_user($id);
		$this->render('users/edit', array(
			'page_title' => 'Edit Pengguna: '.$user['username'],
			'user'       => $user,
			'roles'      => $this->User_model->all_roles(),
		));
	}

	public function update($id = 0)
	{
		$this->require_post();
		$user = $this->load_target_user($id);

		$this->load->library('form_validation');
		$fv = $this->form_validation;
		$fv->set_rules('full_name', 'Nama Lengkap', 'trim|required|max_length[150]');
		$fv->set_rules('role', 'Peran / Role', 'required');
		$fv->set_rules('email', 'Email', 'trim|valid_email|max_length[150]');
		$fv->set_rules('nip', 'NIP', 'trim|max_length[30]');
		$fv->set_rules('position', 'Jabatan', 'trim|max_length[100]');
		$fv->set_rules('phone', 'No. Telepon/WA', 'trim|max_length[30]');

		if ( ! $fv->run())
		{
			$this->render('users/edit', array(
				'page_title' => 'Edit Pengguna: '.$user['username'],
				'user'       => array_merge($user, $this->input->post()),
				'roles'      => $this->User_model->all_roles(),
			));
			return;
		}

		$old_role = $user['role'];
		$new_role = $this->input->post('role', TRUE);

		$data = array(
			'full_name' => $this->input->post('full_name', TRUE),
			'role'      => $new_role,
			'email'     => $this->input->post('email', TRUE) ?: NULL,
			'nip'       => $this->input->post('nip', TRUE) ?: NULL,
			'position'  => $this->input->post('position', TRUE) ?: NULL,
			'phone'     => $this->input->post('phone', TRUE) ?: NULL,
		);

		// Pencegahan: jangan izinkan superuser mengubah dirinya sendiri menjadi non-superuser jika tidak ada superuser lain
		if ((int) $user['id'] === (int) $this->current_user['id'] && $old_role === 'superuser' && $new_role !== 'superuser')
		{
			set_flash('danger', 'Anda tidak dapat menghapus hak Superuser dari akun Anda sendiri.');
			redirect('pengguna/edit/'.$user['id']);
			return;
		}

		$this->User_model->update_user($user['id'], $data);

		if ($old_role !== $new_role)
		{
			audit_log('USER_ROLE_CHANGE', 'users', $user['id'], 'Mengubah role '.$user['username'].' dari '.$old_role.' menjadi '.$new_role);
		}
		else
		{
			audit_log('USER_UPDATE', 'users', $user['id'], 'Memperbarui profil '.$user['username']);
		}

		set_flash('success', 'Data pengguna '.$user['username'].' berhasil diperbarui.');
		redirect('pengguna');
	}

	public function reset_password($id = 0)
	{
		$this->require_post();
		$user = $this->load_target_user($id);
		$password = trim((string) $this->input->post('password'));

		if (strlen($password) < 8)
		{
			set_flash('danger', 'Password baru minimal 8 karakter.');
			redirect('pengguna');
			return;
		}

		$this->User_model->set_password($user['id'], $password);
		audit_log('USER_PASSWORD_RESET', 'users', $user['id'], 'Reset password akun '.$user['username']);
		set_flash('success', 'Password pengguna '.$user['username'].' berhasil direset.');
		redirect('pengguna');
	}

	public function toggle_status($id = 0)
	{
		$this->require_post();
		$user = $this->load_target_user($id);

		if ((int) $user['id'] === (int) $this->current_user['id'])
		{
			set_flash('danger', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
			redirect('pengguna');
			return;
		}

		$new_status = (int) $user['is_active'] ? 0 : 1;
		$this->User_model->update_user($user['id'], array('is_active' => $new_status));
		$status_label = $new_status ? 'diaktifkan' : 'dinonaktifkan';

		audit_log('USER_STATUS_CHANGE', 'users', $user['id'], 'Akun '.$user['username'].' '.$status_label);
		set_flash('success', 'Akun '.$user['username'].' berhasil '.$status_label.'.');
		redirect('pengguna');
	}

	protected function load_target_user($id)
	{
		$user = $this->User_model->find((int) $id);
		if ( ! $user)
		{
			$this->not_found('Pengguna tidak ditemukan.');
		}
		return $user;
	}
}
