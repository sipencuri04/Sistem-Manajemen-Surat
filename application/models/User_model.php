<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model
{
	const MAX_ATTEMPTS = 5;
	const LOCK_MINUTES = 10;

	protected $table = 'users';

	public function find($id)
	{
		return $this->db->get_where($this->table, array('id' => (int) $id))->row_array();
	}

	public function find_by_username($username)
	{
		return $this->db->get_where($this->table, array('username' => (string) $username))->row_array();
	}

	public function username_taken($username, $except_id = 0)
	{
		return $this->db->where('username', $username)
			->where('id !=', (int) $except_id)
			->count_all_results($this->table) > 0;
	}

	/**
	 * Verifikasi login. Mengembalikan array user jika berhasil,
	 * atau string pesan error jika gagal.
	 */
	public function attempt($username, $password)
	{
		$user = $this->find_by_username($username);
		$generic = 'Username atau password salah.';

		if ( ! $user)
		{
			// Tetap jalankan verifikasi agar waktu respons serupa (mitigasi enumerasi user).
			password_verify($password, '$2y$10$3a7nupt9eUqm6QZHl/G60Ou8oySJxz9AvA0Wy0tKsmVYqKovsWUui');
			return $generic;
		}
		if ( ! (int) $user['is_active'])
		{
			return 'Akun dinonaktifkan. Hubungi administrator.';
		}
		if ( ! empty($user['locked_until']) && strtotime($user['locked_until']) > time())
		{
			$minutes = (int) ceil((strtotime($user['locked_until']) - time()) / 60);
			return 'Terlalu banyak percobaan login. Coba lagi dalam '.$minutes.' menit.';
		}
		if ( ! password_verify($password, $user['password_hash']))
		{
			$attempts = (int) $user['failed_attempts'] + 1;
			$update = array('failed_attempts' => $attempts);
			if ($attempts >= self::MAX_ATTEMPTS)
			{
				$update['failed_attempts'] = 0;
				$update['locked_until'] = date('Y-m-d H:i:s', time() + self::LOCK_MINUTES * 60);
			}
			$this->db->where('id', $user['id'])->update($this->table, $update);
			return $generic;
		}

		$update = array(
			'failed_attempts' => 0,
			'locked_until'    => NULL,
			'last_login_at'   => date('Y-m-d H:i:s'),
		);
		if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT))
		{
			$update['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
		}
		$this->db->where('id', $user['id'])->update($this->table, $update);
		return $user;
	}

	public static $roles = array(
		'superuser'      => 'Superuser / Developer',
		'kepala_sekolah' => 'Kepala Sekolah',
		'tu'             => 'Tata Usaha',
		'pegawai'        => 'Pegawai / Guru',
		'admin'          => 'Administrator (Legacy)',
	);

	public function all_roles()
	{
		return self::$roles;
	}

	public function get_active_employees()
	{
		return $this->db->where('is_active', 1)
			->where_in('role', array('pegawai', 'tu', 'kepala_sekolah', 'admin'))
			->order_by('full_name', 'ASC')
			->get($this->table)->result_array();
	}

	public function search(array $filters = array(), $limit = 15, $offset = 0)
	{
		$this->apply_filters($filters);
		return $this->db->order_by('role', 'ASC')
			->order_by('full_name', 'ASC')
			->limit((int) $limit, (int) $offset)
			->get($this->table)->result_array();
	}

	public function count_search(array $filters = array())
	{
		$this->apply_filters($filters);
		return $this->db->count_all_results($this->table);
	}

	protected function apply_filters(array $f)
	{
		if ( ! empty($f['role']))
		{
			$this->db->where('role', $f['role']);
		}
		if (isset($f['is_active']) && $f['is_active'] !== '')
		{
			$this->db->where('is_active', (int) $f['is_active']);
		}
		if ( ! empty($f['q']))
		{
			$this->db->group_start()
				->like('username', $f['q'])
				->or_like('full_name', $f['q'])
				->or_like('nip', $f['q'])
				->or_like('position', $f['q'])
				->or_like('email', $f['q'])
				->group_end();
		}
	}

	public function create($username, $password, $full_name, $email = NULL, $role = 'tu', $nip = NULL, $position = NULL, $phone = NULL)
	{
		$this->db->insert($this->table, array(
			'username'      => $username,
			'password_hash' => password_hash($password, PASSWORD_DEFAULT),
			'full_name'     => $full_name,
			'email'         => $email,
			'role'          => $role,
			'nip'           => $nip,
			'position'      => $position,
			'phone'         => $phone,
			'is_active'     => 1,
			'created_at'    => date('Y-m-d H:i:s'),
		));
		return (int) $this->db->insert_id();
	}

	public function update_user($id, array $data)
	{
		$allowed = array_intersect_key($data, array_flip(array('username', 'full_name', 'email', 'role', 'nip', 'position', 'phone', 'is_active')));
		$allowed['updated_at'] = date('Y-m-d H:i:s');
		return $this->db->where('id', (int) $id)->update($this->table, $allowed);
	}

	public function update_profile($id, array $data)
	{
		$allowed = array_intersect_key($data, array_flip(array('username', 'full_name', 'email', 'nip', 'position', 'phone')));
		$allowed['updated_at'] = date('Y-m-d H:i:s');
		return $this->db->where('id', (int) $id)->update($this->table, $allowed);
	}

	public function set_password($id, $password)
	{
		return $this->db->where('id', (int) $id)->update($this->table, array(
			'password_hash' => password_hash($password, PASSWORD_DEFAULT),
			'failed_attempts' => 0,
			'locked_until'  => NULL,
			'updated_at'    => date('Y-m-d H:i:s'),
		));
	}
}
