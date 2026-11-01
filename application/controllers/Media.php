<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Menyajikan gambar yang tersimpan di storage/ (folder tidak dapat diakses langsung).
 * Logo boleh tampil di halaman login; tanda tangan & stempel hanya untuk pengguna login.
 */
class Media extends MY_Controller
{
	protected $map = array(
		'logo'       => array('field' => 'logo_path',       'public' => TRUE),
		'logo-kanan' => array('field' => 'logo_right_path', 'public' => TRUE),
		'ttd'        => array('field' => 'signature_path',  'public' => FALSE),
		'stempel'    => array('field' => 'stamp_path',      'public' => FALSE),
	);

	public function image($name = '')
	{
		if ( ! isset($this->map[$name]))
		{
			show_404();
		}
		$item = $this->map[$name];
		if ( ! $item['public'] && ! $this->session->userdata('user_id'))
		{
			show_error('Akses ditolak.', 403, 'Akses Ditolak');
		}

		$path = $this->Settings_model->image_file($item['field']);
		if ( ! $path)
		{
			show_404();
		}
		$info = @getimagesize($path);
		if ( ! $info OR ! in_array($info['mime'], array('image/png', 'image/jpeg'), TRUE))
		{
			show_404();
		}

		$this->output
			->set_content_type($info['mime'])
			->set_header('Cache-Control: private, max-age=300')
			->set_header('X-Content-Type-Options: nosniff')
			->set_output(file_get_contents($path));
	}
}
