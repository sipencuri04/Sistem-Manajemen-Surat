<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('User_model');
	}

	public function index()
	{
		redirect('login');
	}

	public function login()
	{
		if ($this->session->userdata('user_id'))
		{
			redirect('dashboard');
		}

		$error = NULL;
		if ($this->input->method(TRUE) === 'POST')
		{
			$this->form_validation->set_rules('username', 'Username', 'trim|required|max_length[50]');
			$this->form_validation->set_rules('password', 'Password', 'required|max_length[255]');

			if ($this->form_validation->run())
			{
				$result = $this->User_model->attempt(
					$this->input->post('username', TRUE),
					(string) $this->input->post('password')
				);
				if (is_array($result))
				{
					// Ganti ID sesi untuk mencegah session fixation.
					$this->session->sess_regenerate(TRUE);
					$this->session->set_userdata(array(
						'user_id'  => (int) $result['id'],
						'username' => $result['username'],
					));
					log_message('info', 'Login berhasil: '.$result['username'].' dari '.$this->input->ip_address());
					set_flash('success', 'Selamat datang, '.$result['full_name'].'.');
					redirect('dashboard');
				}
				log_message('error', 'Login gagal untuk username "'.substr((string) $this->input->post('username', TRUE), 0, 50).'" dari '.$this->input->ip_address());
				$error = $result;
			}
			else
			{
				$error = strip_tags(validation_errors(' ', ' '));
			}
		}

		$this->output->set_header('X-Frame-Options: DENY');
		$this->load->view('auth/login', array(
			'school' => $this->school,
			'error'  => $error,
			'has_logo' => (bool) $this->Settings_model->image_file('logo_path'),
		));
	}

	/** Logout hanya via POST (dilindungi CSRF). */
	public function logout()
	{
		$this->require_post();
		$this->session->unset_userdata(array('user_id', 'username'));
		$this->session->sess_destroy();
		redirect('login?logout=1');
	}
}
