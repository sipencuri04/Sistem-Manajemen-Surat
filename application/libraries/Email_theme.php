<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Library Email_theme
 * Penghasil template email HTML modern, mewah, dan responsif
 * Kompatibel dengan Gmail (Web, Android, iOS), Apple Mail, Outlook, dan Yahoo Mail.
 */
class Email_theme
{
	protected $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
	}

	/**
	 * Render template email modern dan elegan
	 *
	 * @param array $options
	 *   - school_name     : Nama sekolah (default: SMK MUHAMMADIYAH BANDONGAN)
	 *   - school_address  : Alamat sekolah
	 *   - school_npsn     : NPSN
	 *   - title           : Judul kartu email
	 *   - badge_type      : danger | success | info | warning | primary
	 *   - badge_text      : Teks badge (misal: "PERMOHONAN BELUM DAPAT DIPROSES")
	 *   - recipient_name  : Nama penerima (Halo, Agung Setyawan)
	 *   - message         : Isi pesan utama (bisa string dengan nl2br atau paragraf)
	 *   - callout_type    : danger | warning | info | success (opsional)
	 *   - callout_title   : Judul box highlight (misal: "Alasan dari Petugas Tata Usaha:")
	 *   - callout_text    : Isi box highlight
	 *   - details         : Array pasangan label => nilai
	 *   - has_attachment  : Boolean (apakah ada lampiran PDF)
	 *   - attachment_name : Nama berkas lampiran (opsional)
	 *   - button_url      : Tautan tombol aksi (opsional)
	 *   - button_text     : Label tombol aksi (opsional)
	 *   - closing_note    : Catatan penutup tambahan (opsional)
	 * @return string HTML siap kirim
	 */
	public function render(array $options)
	{
		$this->CI->load->model('Settings_model');
		$school = $this->CI->Settings_model->get(TRUE);

		$school_name    = !empty($options['school_name']) ? $options['school_name'] : (!empty($school['school_name']) ? $school['school_name'] : 'SMK MUHAMMADIYAH BANDONGAN');
		$school_address = !empty($options['school_address']) ? $options['school_address'] : (!empty($school['school_address']) ? $school['school_address'] : 'Jl. Bandongan - Windusari Km. 1, Bandongan, Kab. Magelang (56152)');
		$school_npsn    = !empty($options['school_npsn']) ? $options['school_npsn'] : (!empty($school['school_npsn']) ? $school['school_npsn'] : '20307689');
		$kop_line1      = !empty($school['kop_line1']) ? $school['kop_line1'] : 'PIMPINAN DAERAH MUHAMMADIYAH KABUPATEN MAGELANG';
		$kop_line2      = !empty($school['kop_line2']) ? $school['kop_line2'] : 'MAJELIS PENDIDIKAN DASAR DAN MENENGAH';

		$recipient_name = isset($options['recipient_name']) ? htmlspecialchars($options['recipient_name']) : '';
		$title          = isset($options['title']) ? htmlspecialchars($options['title']) : 'Pemberitahuan Layanan Persuratan';
		$badge_text     = isset($options['badge_text']) ? htmlspecialchars($options['badge_text']) : '';
		$badge_type     = isset($options['badge_type']) ? $options['badge_type'] : 'primary';

		// Tema warna badge & header
		$badge_styles = array(
			'danger'  => array('bg' => '#fef2f2', 'border' => '#fecaca', 'text' => '#b91c1c', 'icon' => '&#10060;'),
			'warning' => array('bg' => '#fffbeb', 'border' => '#fde68a', 'text' => '#b45309', 'icon' => '&#9888;'),
			'success' => array('bg' => '#f0fdf4', 'border' => '#bbf7d0', 'text' => '#15803d', 'icon' => '&#10004;'),
			'info'    => array('bg' => '#eff6ff', 'border' => '#bfdbfe', 'text' => '#1d4ed8', 'icon' => '&#128172;'),
			'primary' => array('bg' => '#f8fafc', 'border' => '#cbd5e1', 'text' => '#0f172a', 'icon' => '&#128196;'),
		);
		$cur_badge = isset($badge_styles[$badge_type]) ? $badge_styles[$badge_type] : $badge_styles['primary'];

		// Header gradient
		$header_gradient = 'background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 55%, #2563eb 100%);';

		// Callout box (alasan penolakan / catatan penting)
		$callout_html = '';
		if (!empty($options['callout_text']))
		{
			$c_type = isset($options['callout_type']) ? $options['callout_type'] : 'danger';
			$c_styles = array(
				'danger'  => array('bg' => '#fef2f2', 'border_left' => '#ef4444', 'title_col' => '#991b1b', 'text_col' => '#7f1d1d'),
				'warning' => array('bg' => '#fffbeb', 'border_left' => '#f59e0b', 'title_col' => '#92400e', 'text_col' => '#78350f'),
				'info'    => array('bg' => '#eff6ff', 'border_left' => '#3b82f6', 'title_col' => '#1e40af', 'text_col' => '#1e3a8a'),
				'success' => array('bg' => '#f0fdf4', 'border_left' => '#22c55e', 'title_col' => '#166534', 'text_col' => '#14532d'),
			);
			$c_style = isset($c_styles[$c_type]) ? $c_styles[$c_type] : $c_styles['danger'];
			$c_title = !empty($options['callout_title']) ? htmlspecialchars($options['callout_title']) : 'Catatan Petugas Tata Usaha:';

			$callout_html = '
			<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:20px 0; background:' . $c_style['bg'] . '; border-left:4px solid ' . $c_style['border_left'] . '; border-radius:0 10px 10px 0; border-top:1px solid rgba(0,0,0,0.04); border-right:1px solid rgba(0,0,0,0.04); border-bottom:1px solid rgba(0,0,0,0.04);">
				<tr>
					<td style="padding:16px 18px;">
						<div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.8px; color:' . $c_style['title_col'] . '; margin-bottom:6px;">
							' . $c_title . '
						</div>
						<div style="font-size:14px; line-height:1.6; color:' . $c_style['text_col'] . '; font-weight:500; font-style:italic;">
							&ldquo;' . nl2br(htmlspecialchars($options['callout_text'])) . '&rdquo;
						</div>
					</td>
				</tr>
			</table>';
		}

		// Rincian data (Details table)
		$details_html = '';
		if (!empty($options['details']) && is_array($options['details']))
		{
			$rows_html = '';
			foreach ($options['details'] as $label => $val)
			{
				if ($val === NULL || $val === '') continue;
				$rows_html .= '
				<tr>
					<td style="padding:10px 14px; font-size:12px; color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:0.3px; width:38%; border-bottom:1px solid #f1f5f9;">
						' . htmlspecialchars($label) . '
					</td>
					<td style="padding:10px 14px; font-size:13px; color:#0f172a; font-weight:600; border-bottom:1px solid #f1f5f9;">
						' . $val . '
					</td>
				</tr>';
			}

			if ($rows_html !== '')
			{
				$details_html = '
				<div style="margin:24px 0 16px 0;">
					<div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.8px; color:#475569; margin-bottom:8px;">
						Rincian Informasi Permohonan
					</div>
					<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; overflow:hidden;">
						' . $rows_html . '
					</table>
				</div>';
			}
		}

		// Info Lampiran PDF
		$attachment_html = '';
		if (!empty($options['has_attachment']))
		{
			$att_name = !empty($options['attachment_name']) ? htmlspecialchars($options['attachment_name']) : 'Dokumen_Surat_Resmi.pdf';
			$attachment_html = '
			<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:20px 0; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:12px;">
				<tr>
					<td style="padding:14px 18px;">
						<table role="presentation" cellpadding="0" cellspacing="0">
							<tr>
								<td style="vertical-align:middle; padding-right:12px; font-size:24px;">&#128206;</td>
								<td style="vertical-align:middle;">
									<div style="font-size:13px; font-weight:700; color:#15803d;">Berkas Surat Resmi Terlampir (PDF)</div>
									<div style="font-size:12px; color:#166534; margin-top:2px;">Dokumen berformat PDF telah dilampirkan pada bagian bawah email ini (<strong>' . $att_name . '</strong>). Silakan unduh atau cetak langsung.</div>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>';
		}

		// Tombol Aksi (CTA Button)
		$button_html = '';
		if (!empty($options['button_url']))
		{
			$btn_label = !empty($options['button_text']) ? htmlspecialchars($options['button_text']) : 'Buka di Sistem Persuratan';
			$button_html = '
			<div style="text-align:center; margin:28px 0 20px 0;">
				<a href="' . htmlspecialchars($options['button_url']) . '" target="_blank" style="display:inline-block; background:linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%); color:#ffffff; font-size:14px; font-weight:700; text-decoration:none; padding:13px 30px; border-radius:10px; box-shadow:0 4px 14px rgba(37,99,235,0.35); letter-spacing:0.3px;">
					' . $btn_label . ' &rarr;
				</a>
			</div>';
		}

		// Catatan penutup
		$closing_note = !empty($options['closing_note'])
			? '<div style="font-size:12px; color:#64748b; line-height:1.6; margin-top:16px;">' . $options['closing_note'] . '</div>'
			: '<div style="font-size:12px; color:#64748b; line-height:1.6; margin-top:16px;">Apabila Anda membutuhkan bantuan atau informasi lebih lanjut, silakan datang langsung ke ruang Tata Usaha pada hari dan jam kerja operasional sekolah.</div>';

		// Pesan pembuka
		$message_body = !empty($options['message']) ? $options['message'] : '';

		// Susun keseluruhan template HTML email
		$html = '
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>' . $title . '</title>
<!--[if mso]>
<style type="text/css">
body, table, td, a { font-family: Arial, Helvetica, sans-serif !important; }
</style>
<![endif]-->
</head>
<body style="margin:0; padding:0; background-color:#f1f5f9; font-family:-apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing:antialiased; color:#334155;">

<!-- Outer Background Wrapper -->
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f1f5f9; padding:35px 12px;">
	<tr>
		<td align="center">
			<!-- Main Card Container (Max 600px) -->
			<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px; background:#ffffff; border-radius:18px; overflow:hidden; box-shadow:0 10px 30px rgba(15,23,42,0.08); border:1px solid #e2e8f0; text-align:left;">
				
				<!-- Hero Header with School Identity -->
				<tr>
					<td style="' . $header_gradient . ' padding:28px 28px 24px 28px; text-align:center;">
						<div style="display:inline-block; background:rgba(255,255,255,0.14); border:1px solid rgba(255,255,255,0.25); border-radius:20px; padding:4px 14px; margin-bottom:10px;">
							<span style="font-size:11px; font-weight:700; color:#ffffff; letter-spacing:1px; text-transform:uppercase;">
								&#127979; ' . htmlspecialchars($kop_line2 ?: 'SMK MUHAMMADIYAH BANDONGAN') . '
							</span>
						</div>
						<h1 style="margin:0; font-size:20px; font-weight:800; color:#ffffff; letter-spacing:0.3px; line-height:1.3; text-transform:uppercase;">
							' . htmlspecialchars($school_name) . '
						</h1>
						<div style="margin-top:4px; font-size:11.5px; color:#bfdbfe; font-weight:500; letter-spacing:0.5px;">
							Layanan Persuratan &amp; Tata Usaha Digital Terintegrasi
						</div>
					</td>
				</tr>

				<!-- Content Body -->
				<tr>
					<td style="padding:28px 28px 22px 28px;">
						
						<!-- Status Badge Pill -->
						' . ($badge_text !== '' ? '
						<div style="margin-bottom:20px;">
							<span style="display:inline-block; background:' . $cur_badge['bg'] . '; color:' . $cur_badge['text'] . '; border:1px solid ' . $cur_badge['border'] . '; border-radius:30px; font-size:11px; font-weight:700; padding:5px 14px; letter-spacing:0.6px; text-transform:uppercase;">
								' . $cur_badge['icon'] . ' ' . $badge_text . '
							</span>
						</div>' : '') . '

						<!-- Recipient Greeting -->
						' . ($recipient_name !== '' ? '
						<h2 style="margin:0 0 14px 0; font-size:16px; font-weight:700; color:#0f172a;">
							Halo ' . $recipient_name . ',
						</h2>' : '') . '

						<!-- Main Narrative Message -->
						<div style="font-size:14.5px; line-height:1.65; color:#334155;">
							' . $message_body . '
						</div>

						<!-- Highlight Box (e.g. Reason or Note) -->
						' . $callout_html . '

						<!-- Information Details Table -->
						' . $details_html . '

						<!-- Attachment Indicator -->
						' . $attachment_html . '

						<!-- Call to Action Button -->
						' . $button_html . '

						<!-- Closing Note -->
						' . $closing_note . '

						<!-- Signature Sign-off -->
						<div style="margin-top:22px; padding-top:16px; border-top:1px solid #f1f5f9; font-size:13px; color:#475569;">
							Hormat kami,<br>
							<strong style="color:#0f172a;">Tata Usaha ' . htmlspecialchars($school_name) . '</strong>
						</div>
					</td>
				</tr>

				<!-- Official School Footer -->
				<tr>
					<td style="background:#f8fafc; border-top:1px solid #e2e8f0; padding:22px 28px; text-align:center;">
						<div style="font-size:12px; font-weight:700; color:#334155; margin-bottom:4px;">
							' . htmlspecialchars($school_name) . '
						</div>
						<div style="font-size:11px; color:#64748b; line-height:1.5;">
							' . htmlspecialchars($school_address) . '<br>
							NPSN: ' . htmlspecialchars($school_npsn) . ' &bull; Dokumen Resmi Terverifikasi Sistem
						</div>
						<div style="margin-top:12px; padding-top:10px; border-top:1px dashed #e2e8f0; font-size:10.5px; color:#94a3b8; line-height:1.4;">
							&#128274; Email ini diterbitkan secara otomatis dan sah melalui Sistem Persuratan Digital sekolah. Mohon tidak membalas langsung ke alamat email sistem ini.
						</div>
					</td>
				</tr>
			</table>
			<!-- End Main Card -->
		</td>
	</tr>
</table>
<!-- End Outer Wrapper -->

</body>
</html>';

		return $html;
	}
}
