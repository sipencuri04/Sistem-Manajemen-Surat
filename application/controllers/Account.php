<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pengaturan akun: ubah profil dan password.
 */
class Account extends Admin_Controller
{
	public function index()
	{
		$section = $this->input->post('section');
		if ($this->input->method(TRUE) === 'POST')
		{
			if ($section === 'password')
			{
				$this->change_password();
			}
			else
			{
				$this->update_profile();
			}
		}

		$this->render('account/index', array(
			'page_title' => 'Pengaturan Akun',
			'user'       => $this->User_model->find($this->current_user['id']),
			'section'    => $section,
		));
	}

	protected function update_profile()
	{
		$fv = $this->form_validation;
		$fv->set_rules('full_name', 'Nama lengkap', 'trim|required|max_length[150]');
		$fv->set_rules('username', 'Username', 'trim|required|min_length[4]|max_length[50]|alpha_dash|callback__unique_username');
		$fv->set_rules('email', 'Email', 'trim|max_length[150]|valid_email');
		if ( ! $fv->run())
		{
			return;
		}
		$this->User_model->update_profile($this->current_user['id'], array(
			'full_name' => trim($this->input->post('full_name')),
			'username'  => trim($this->input->post('username')),
			'email'     => trim((string) $this->input->post('email')) ?: NULL,
		));
		$this->session->set_userdata('username', trim($this->input->post('username')));
		set_flash('success', 'Profil berhasil diperbarui.');
		redirect('akun');
	}

	protected function change_password()
	{
		$fv = $this->form_validation;
		$fv->set_rules('current_password', 'Password saat ini', 'required|callback__check_current');
		$fv->set_rules('new_password', 'Password baru', 'required|min_length[8]|max_length[72]|callback__strong_password');
		$fv->set_rules('confirm_password', 'Konfirmasi password', 'required|matches[new_password]',
			array('matches' => 'Konfirmasi password tidak sama.'));
		if ( ! $fv->run())
		{
			return;
		}
		$this->User_model->set_password($this->current_user['id'], (string) $this->input->post('new_password'));
		// Ganti ID sesi setelah perubahan kredensial.
		$this->session->sess_regenerate(TRUE);
		log_message('info', 'Password diubah oleh '.$this->current_user['username']);
		set_flash('success', 'Password berhasil diubah.');
		redirect('akun');
	}

	public function _unique_username($username)
	{
		if ($this->User_model->username_taken(trim($username), $this->current_user['id']))
		{
			$this->form_validation->set_message('_unique_username', 'Username sudah digunakan.');
			return FALSE;
		}
		return TRUE;
	}

	public function _check_current($password)
	{
		if ( ! password_verify((string) $password, $this->current_user['password_hash']))
		{
			$this->form_validation->set_message('_check_current', 'Password saat ini salah.');
			return FALSE;
		}
		return TRUE;
	}

	public function _strong_password($password)
	{
		if ( ! preg_match('/[A-Za-z]/', $password) OR ! preg_match('/[0-9]/', $password))
		{
			$this->form_validation->set_message('_strong_password', '{field} harus mengandung huruf dan angka.');
			return FALSE;
		}
		return TRUE;
	}
}
