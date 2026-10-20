<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/userguide3/general/hooks.html
|
*/

// Audit trail: catat setiap aksi tulis pengguna ke tabel activity_log.
$hook['post_controller_constructor'][] = array(
	'class'    => 'Audit_hook',
	'function' => 'catat',
	'filename' => 'Audit_hook.php',
	'filepath' => 'hooks'
);
