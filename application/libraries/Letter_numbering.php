<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Penomoran surat otomatis.
 *
 * Token format yang didukung:
 *   {NO}           nomor urut dengan nol di depan (panjang = number_padding)
 *   {NO_RAW}       nomor urut tanpa nol di depan
 *   {KODE}         kode jenis surat (letter_types.code)
 *   {SEKOLAH}      kode singkat sekolah (school_settings.school_code)
 *   {NPSN}         NPSN sekolah
 *   {BULAN}        bulan 2 digit (01-12)
 *   {BULAN_ROMAWI} bulan angka romawi (I-XII)
 *   {TAHUN}        tahun 4 digit
 *
 * Penghitung (tabel letter_counters) tidak pernah mundur, sehingga nomor yang
 * pernah dipakai tidak akan dipakai ulang secara otomatis walau suratnya dihapus.
 */
class Letter_numbering
{
	const TOKENS = array('{NO}', '{NO_RAW}', '{KODE}', '{SEKOLAH}', '{NPSN}', '{BULAN}', '{BULAN_ROMAWI}', '{TAHUN}');

	protected $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->model('Letter_model');
	}

	/** Tahun penomoran: tahun surat tetap dari pengaturan, atau tahun tanggal surat. */
	public function number_year(array $settings, $letter_date)
	{
		if ( ! empty($settings['letter_year']))
		{
			return (int) $settings['letter_year'];
		}
		return (int) date('Y', strtotime($letter_date));
	}

	public function scope_key(array $settings, array $type)
	{
		return ($settings['number_scope'] === 'per_type') ? 'type:'.(int) $type['id'] : 'global';
	}

	public function period_key(array $settings, $letter_date)
	{
		$year = $this->number_year($settings, $letter_date);
		switch ($settings['number_reset'])
		{
			case 'monthly': return sprintf('%04d-%02d', $year, (int) date('n', strtotime($letter_date)));
			case 'never':   return 'all';
			default:        return (string) $year;
		}
	}

	/** Susun nomor surat berdasarkan format. */
	public function format(array $settings, array $type, $sequence, $letter_date)
	{
		$month   = (int) date('n', strtotime($letter_date));
		$padding = max(1, min(6, (int) $settings['number_padding']));
		$format  = trim((string) $settings['number_format']) !== '' ? $settings['number_format'] : '{NO}/{KODE}/{BULAN_ROMAWI}/{TAHUN}';

		$number = strtr($format, array(
			'{NO}'           => str_pad((string) (int) $sequence, $padding, '0', STR_PAD_LEFT),
			'{NO_RAW}'       => (string) (int) $sequence,
			'{KODE}'         => (string) $type['code'],
			'{SEKOLAH}'      => (string) $settings['school_code'],
			'{NPSN}'         => (string) $settings['npsn'],
			'{BULAN}'        => sprintf('%02d', $month),
			'{BULAN_ROMAWI}' => bulan_romawi($month),
			'{TAHUN}'        => (string) $this->number_year($settings, $letter_date),
		));

		// Rapikan pemisah ganda akibat token kosong, mis. "001//X/2026" -> "001/X/2026".
		$number = preg_replace('#([/.\-])\1+#', '$1', $number);
		$number = trim($number, " /.-");
		return $number;
	}

	/** Validasi format: wajib mengandung {NO} atau {NO_RAW}. */
	public function validate_format($format)
	{
		if (strpos($format, '{NO}') === FALSE && strpos($format, '{NO_RAW}') === FALSE)
		{
			return 'Format nomor wajib mengandung token {NO} atau {NO_RAW}.';
		}
		if (preg_match_all('/\{[A-Z_]+\}/', $format, $m))
		{
			foreach ($m[0] as $tok)
			{
				if ( ! in_array($tok, self::TOKENS, TRUE))
				{
					return 'Token '.$tok.' tidak dikenal.';
				}
			}
		}
		return TRUE;
	}

	/** Perkiraan nomor berikutnya (tanpa mengunci / menyimpan). */
	public function preview(array $settings, array $type, $letter_date)
	{
		$row = $this->CI->db->get_where('letter_counters', array(
			'scope_key'  => $this->scope_key($settings, $type),
			'period_key' => $this->period_key($settings, $letter_date),
		))->row_array();
		$seq = $row ? (int) $row['last_number'] : 0;
		for ($i = 0; $i < 1000; $i++)
		{
			$seq++;
			$number = $this->format($settings, $type, $seq, $letter_date);
			if ( ! $this->CI->Letter_model->number_exists($number))
			{
				return $number;
			}
		}
		return $this->format($settings, $type, $seq, $letter_date);
	}

	/**
	 * Ambil nomor berikutnya secara atomik. HARUS dipanggil di dalam transaksi
	 * (SELECT ... FOR UPDATE mengunci baris penghitung sampai commit).
	 *
	 * @return array ['number' => string, 'sequence' => int, 'year' => int]
	 */
	public function allocate(array $settings, array $type, $letter_date)
	{
		$db     = $this->CI->db;
		$scope  = $this->scope_key($settings, $type);
		$period = $this->period_key($settings, $letter_date);

		$db->query('INSERT IGNORE INTO letter_counters (scope_key, period_key, last_number, updated_at) VALUES (?, ?, 0, ?)',
			array($scope, $period, date('Y-m-d H:i:s')));
		$row = $db->query('SELECT last_number FROM letter_counters WHERE scope_key = ? AND period_key = ? FOR UPDATE',
			array($scope, $period))->row_array();

		$seq = $row ? (int) $row['last_number'] : 0;
		$number = NULL;
		for ($i = 0; $i < 1000; $i++)
		{
			$seq++;
			$candidate = $this->format($settings, $type, $seq, $letter_date);
			// Lewati nomor yang sudah dipakai (misalnya diisi manual sebelumnya).
			if ( ! $this->CI->Letter_model->number_exists($candidate))
			{
				$number = $candidate;
				break;
			}
		}
		if ($number === NULL)
		{
			throw new RuntimeException('Tidak dapat membuat nomor surat unik. Periksa format penomoran.');
		}

		$db->query('UPDATE letter_counters SET last_number = ?, updated_at = ? WHERE scope_key = ? AND period_key = ?',
			array($seq, date('Y-m-d H:i:s'), $scope, $period));

		return array(
			'number'   => $number,
			'sequence' => $seq,
			'year'     => $this->number_year($settings, $letter_date),
		);
	}
}
