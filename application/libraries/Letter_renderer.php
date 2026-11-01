<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Menyusun HTML surat (dipakai untuk pratinjau & PDF) dan mengubahnya menjadi
 * PDF A4 menggunakan Dompdf.
 */
class Letter_renderer
{
	protected $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->library('Template_registry');
	}

	/** Ubah file gambar menjadi data URI (agar Dompdf tidak perlu akses remote). */
	public function image_data_uri($path)
	{
		if ( ! $path OR ! is_file($path))
		{
			return NULL;
		}
		$info = @getimagesize($path);
		if ( ! $info OR ! in_array($info['mime'], array('image/png', 'image/jpeg'), TRUE))
		{
			return NULL;
		}
		return 'data:'.$info['mime'].';base64,'.base64_encode(file_get_contents($path));
	}

	/**
	 * Susun HTML surat lengkap.
	 *
	 * @param array  $letter  baris surat (+ 'data' => array field khusus)
	 * @param array  $type    jenis surat + teks template (find_with_template)
	 * @param array  $school  pengaturan sekolah
	 * @param string $mode    'pdf' | 'html'
	 * @param array  $opts    ['watermark' => string|null]
	 */
	public function html(array $letter, array $type, array $school, $mode = 'pdf', array $opts = array())
	{
		$registry = $this->CI->template_registry;
		$tpl = $registry->get($type['template_key']);
		if ( ! $tpl)
		{
			throw new RuntimeException('Template "'.$type['template_key'].'" tidak ditemukan di config/letter_templates.php');
		}

		$data   = isset($letter['data']) && is_array($letter['data']) ? $letter['data'] : array();
		$values = $registry->formatted_values($tpl, $data);

		$map = $values + array(
			'nama_sekolah'          => $school['school_name'],
			'alamat_sekolah'        => $school['address'],
			'kota'                  => $school['city'],
			'npsn'                  => $school['npsn'],
			'jabatan_penandatangan' => $letter['signer_title'],
			'nama_penandatangan'    => $letter['signer_name'],
			'perihal'               => $letter['subject'],
			'tujuan'                => (string) $letter['recipient'],
			'nomor_surat'           => $letter['letter_number'],
			'tanggal_surat'         => tgl_indo($letter['letter_date']),
		);

		$texts = array(
			'opening' => $registry->render_text(isset($type['opening_text']) ? $type['opening_text'] : '', $map),
			'body'    => $registry->render_text(isset($type['body_text']) ? $type['body_text'] : '', $map),
			'closing' => $registry->render_text(isset($type['closing_text']) ? $type['closing_text'] : '', $map),
		);

		$S = $this->CI->Settings_model;
		$is_basah = (isset($opts['blank_signature']) && $opts['blank_signature']) 
			|| (isset($letter['signature_type']) && $letter['signature_type'] === 'basah' && ! (isset($opts['force_signature']) && $opts['force_signature']));

		$images = array(
			'logo'       => $this->image_data_uri($S->image_file('logo_path')),
			'logo_right' => $this->image_data_uri($S->image_file('logo_right_path')),
			'signature'  => $is_basah ? NULL : $this->image_data_uri($S->image_file('signature_path')),
			'stamp'      => $is_basah ? NULL : $this->image_data_uri($S->image_file('stamp_path')),
		);

		$view = array(
			'mode'      => $mode,
			'letter'    => $letter,
			'type'      => $type,
			'tpl'       => $tpl,
			'd'         => $data,
			'v'         => $values,
			't'         => $texts,
			'school'    => $school,
			'images'    => $images,
			'watermark' => isset($opts['watermark']) ? $opts['watermark'] : NULL,
		);
		$view['body_html'] = $this->CI->load->view('letters/templates/'.$tpl['key'], $view, TRUE);

		return $this->CI->load->view('letters/pdf/document', $view, TRUE);
	}

	protected function dompdf($html)
	{
		$cache = APPPATH.'cache/dompdf';
		if ( ! is_dir($cache))
		{
			@mkdir($cache, 0755, TRUE);
		}

		$options = new Options();
		$options->set('isRemoteEnabled', FALSE);       // tidak mengambil resource dari internet
		$options->set('isPhpEnabled', FALSE);          // tidak mengeksekusi PHP di HTML
		$options->set('isHtml5ParserEnabled', TRUE);
		$options->set('defaultFont', 'Times-Roman');
		$options->set('defaultPaperSize', 'a4');
		$options->set('dpi', 96);
		$options->set('chroot', array(FCPATH));
		$options->set('fontCache', $cache);
		$options->set('tempDir', $cache);

		$dompdf = new Dompdf($options);
		$dompdf->loadHtml($html, 'UTF-8');
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->render();
		return $dompdf;
	}

	/** Hasilkan PDF sebagai string biner. */
	public function pdf_string($html)
	{
		return $this->dompdf($html)->output();
	}

	/** Kirim PDF ke browser (inline atau unduh). */
	public function stream($html, $filename, $download = TRUE)
	{
		$pdf = $this->pdf_string($html);
		$this->send_pdf($pdf, $filename, $download);
	}

	public function send_pdf($binary, $filename, $download = TRUE)
	{
		$filename = safe_filename(preg_replace('/\.pdf$/i', '', $filename)).'.pdf';
		$this->CI->output
			->set_header('Content-Type: application/pdf')
			->set_header('Content-Disposition: '.($download ? 'attachment' : 'inline').'; filename="'.$filename.'"')
			->set_header('Content-Length: '.strlen($binary))
			->set_header('Cache-Control: private, no-store, max-age=0')
			->set_header('X-Content-Type-Options: nosniff')
			->set_output($binary);
	}
}
