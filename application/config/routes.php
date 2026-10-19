<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'auth/login';
$route['login'] = 'auth/login';
$route['logout'] = 'auth/logout';
$route['dashboard'] = 'dashboard';
$route['pendaftaran'] = 'pendaftaran/index';
$route['pendaftaran/(:any)'] = 'pendaftaran/$1';
$route['pasien'] = 'pendaftaran/index';
$route['pasien/(:any)'] = 'pendaftaran/$1';
$route['order_lab'] = 'pendaftaran/index';
$route['order_lab/(:any)'] = 'pendaftaran/$1';
$route['tarif'] = 'tarif/index';
$route['tarif/template'] = 'tarif/template';
$route['tarif/metode'] = 'tarif/metode';
$route['tarif/(:any)'] = 'tarif/$1';
$route['master/tarif'] = 'tarif/index';
$route['master/template'] = 'tarif/template';
$route['master/metode'] = 'tarif/metode';
$route['menu'] = 'menu/index';
$route['menu/(:any)'] = 'menu/$1';
$route['pengaturan'] = 'pengaturan/index';
$route['pengaturan/profil'] = 'pengaturan/index';
$route['pengaturan/menu'] = 'menu/index';
$route['pengaturan/approval'] = 'pengaturan/approval';
$route['pengaturan/approval/(:any)'] = 'pengaturan/approval/$1';
$route['pengaturan/(:any)'] = 'pengaturan/$1';
$route['roles'] = 'dashboard/roles';
$route['roles/(:any)'] = 'dashboard/roles/$1';
$route['pegawai'] = 'pegawai/index';
$route['pegawai/(:any)'] = 'pegawai/$1';
$route['kepegawaian'] = 'kepegawaian/index';
$route['kepegawaian/(:any)'] = 'kepegawaian/$1';
$route['rekening'] = 'rekening/index';
$route['rekening/coa'] = 'rekening/coa';
$route['rekening/tahun'] = 'rekening/tahun';
$route['rekening/akun_bayar'] = 'rekening/akun_bayar';
$route['rekening/akun_piutang'] = 'rekening/akun_piutang';
$route['rekening/(:any)'] = 'rekening/$1';
$route['hasil_lab'] = 'hasil_lab/index';
$route['hasil_lab/(:any)'] = 'hasil_lab/$1';
$route['validasi'] = 'validasi/index';
$route['validasi/(:any)'] = 'validasi/$1';
$route['laporan'] = 'laporan/index';
$route['laporan/(:any)'] = 'laporan/$1';
$route['master/dokter'] = 'master/dokter';
$route['master/dokter/(:any)'] = 'master/dokter/$1';
$route['master/nilai_rujukan'] = 'master/nilai_rujukan';
$route['master/nilai_rujukan/(:any)'] = 'master/nilai_rujukan/$1';
$route['users'] = 'users/index';
$route['users/(:any)'] = 'users/$1';
$route['client'] = 'client/index';
$route['client/(:any)'] = 'client/$1';
$route['404_override'] = 'errors_handler/not_found';
$route['translate_uri_dashes'] = FALSE;
