<?php defined('BASEPATH') or exit('No direct script access allowed');

$table_data = array(
   _l('the_number_sign'),
   _l('pjk3'),
   _l('contact_primary'),
   array(
      'name'=>_l('company_primary_email'),
      'th_attrs'=>array('class'=>'not_visible')
   ),
   array(
      'name'=>_l('company_primary_email'),
      'th_attrs'=>array('class'=> (isset($client) ? 'not_visible' : ''))
   ),
   _l('company_siup'),
   _l('clients_list_phone'),
   _l('customer_active'),
//   _l('inspector_dt_table_heading_expirydate'),
   //_l('reference_no'),
   _l('preffered_pjk3'));

$custom_fields = get_custom_fields('inspector',array('show_on_table'=>1));

foreach($custom_fields as $field){
   array_push($table_data,$field['name']);
}

$table_data = hooks()->apply_filters('inspectors_table_columns', $table_data);

render_datatable($table_data, isset($class) ? $class : 'inspectors');
