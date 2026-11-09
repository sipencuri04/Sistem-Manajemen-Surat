<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * View fallback untuk notifikasi email umum
 */
$CI =& get_instance();
$CI->load->library('email_theme');

$options = array(
    'title'          => isset($title) ? $title : 'Pemberitahuan Layanan Surat',
    'school_name'    => isset($school_name) ? $school_name : 'SMK MUHAMMADIYAH BANDONGAN',
    'recipient_name' => isset($recipient_name) ? $recipient_name : '',
    'badge_type'     => isset($badge_type) ? $badge_type : 'info',
    'badge_text'     => isset($badge_text) ? $badge_text : 'PEMBERITAHUAN',
    'message'        => isset($message) ? $message : '',
    'details'        => isset($details) && is_array($details) ? $details : array(),
    'button_url'     => isset($action_url) ? $action_url : (isset($button_url) ? $button_url : ''),
    'button_text'    => isset($action_text) ? $action_text : (isset($button_text) ? $button_text : 'Lihat Detail'),
);

echo $CI->email_theme->render($options);

