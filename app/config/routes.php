<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// $route['ws_bpjs']                   = 'ws_bpjs';


$route['auth']['POST'] 				= 'rest/view_token';
$route['login/gettoken']['POST'] 	= 'rest/gettoken';

$route['members/(:num)']['GET'] 	= 'members/index/id/$1';
$route['members']['POST'] 			= 'members/index';
$route['members/(:num)']['PUT'] 	= 'members/index/id/$1';
$route['members/(:num)']['DELETE']	= 'members/index/id/$1';


$route['antrian']['POST']           = 'antrian/index';
$route['antrian/rekap']['POST']     = 'antrianrekap/index';

$route['operasi/rekap']['POST']     = 'operasiall/index';
