<?php
defined('BASEPATH') or exit('No direct script access allowed');



$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;



// User
$route['user'] = 'User';
$route['login'] = 'User/login'; 
$route['user/update_login_status'] = 'User/update_login_status'; 

// attendance
$route['attendance'] = 'Attendance';
$route['attendance/get_employee_logs'] = 'Attendance/get_employee_logs';
