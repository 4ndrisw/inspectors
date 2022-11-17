<?php

defined('BASEPATH') or exit('No direct script access allowed');

$route['inspectors/inspector/(:num)/(:any)'] = 'inspector/index/$1/$2';

/**
 * @since 2.0.0
 */
$route['inspectors/list'] = 'myinspector/list';
$route['inspectors/show/(:num)/(:any)'] = 'myinspector/show/$1/$2';
$route['inspectors/office/(:num)/(:any)'] = 'myinspector/office/$1/$2';
$route['inspectors/pdf/(:num)'] = 'myinspector/pdf/$1';
$route['inspectors/office_pdf/(:num)'] = 'myinspector/office_pdf/$1';
