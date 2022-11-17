<?php

defined('BASEPATH') or exit('No direct script access allowed');

$dimensions = $pdf->getPageDimensions();

$info_right_column = '';
$info_left_column  = '';

$info_right_column .= '<span style="font-weight:bold;font-size:27px;">' . _l('inspector_pdf_heading') . '</span><br />';
$info_right_column .= '<b style="color:#4e4e4e;"># ' . $inspector_number . '</b>';

if (get_option('show_status_on_pdf_ei') == 1) {
    $info_right_column .= '<br /><span style="color:rgb(' . inspector_status_color_pdf($status) . ');text-transform:uppercase;">' . format_inspector_status($status, '', false) . '</span>';
}

// Add logo
$info_left_column .= pdf_logo_url();
// Write top left logo and right column info/text
pdf_multi_row($info_left_column, $info_right_column, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

$pdf->ln(10);

$organization_info = '<div style="color:#424242;">';
    $organization_info .= format_organization_info();
$organization_info .= '</div>';

// Inspector to
$inspector_info = '<b>' . _l('inspector_to') . '</b>';
$inspector_info .= '<div style="color:#424242;">';
$inspector_info .= format_customer_info($inspector, 'inspector', 'billing');
$inspector_info .= '</div>';

$organization_info .= '<p><strong>'. _l('inspector_members') . '</strong></p>';

$CI = &get_instance();
$CI->load->model('inspectors_model');
$inspector_members = $CI->inspectors_model->get_inspector_members($inspector->id,true);
$i=1;
foreach($inspector_members as $member){
  $organization_info .=  $i.'. ' .$member['firstname'] .' '. $member['lastname']. '<br />';
  $i++;
}

$inspector_info .= '<br />' . _l('inspector_data_date') . ': ' . _d($inspector->date) . '<br />';

if (!empty($inspector->expirydate)) {
    $inspector_info .= _l('inspector_data_expiry_date') . ': ' . _d($inspector->expirydate) . '<br />';
}

if (!empty($inspector->reference_no)) {
    $inspector_info .= _l('reference_no') . ': ' . $inspector->reference_no . '<br />';
}

if ($inspector->project_id != 0 && get_option('show_project_on_inspector') == 1) {
    $inspector_info .= _l('project') . ': ' . get_project_name_by_id($inspector->project_id) . '<br />';
}


$left_info  = $swap == '1' ? $inspector_info : $organization_info;
$right_info = $swap == '1' ? $organization_info : $inspector_info;

pdf_multi_row($left_info, $right_info, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

// The Table
$pdf->Ln(hooks()->apply_filters('pdf_info_and_table_separator', 6));

// The items table
$items = get_inspector_items_table_data($inspector, 'inspector', 'pdf');

$tblhtml = $items->table();

$pdf->writeHTML($tblhtml, true, false, false, false, '');

$pdf->SetFont($font_name, '', $font_size);

$assigned_path = <<<EOF
        <img width="150" height="150" src="$inspector->assigned_path">
    EOF;    
$assigned_info = '<div style="text-align:center;">';
    $assigned_info .= get_option('invoice_company_name') . '<br />';
    $assigned_info .= $assigned_path . '<br />';

if ($inspector->assigned != 0 && get_option('show_assigned_on_inspectors') == 1) {
    $assigned_info .= get_staff_full_name($inspector->assigned);
}
$assigned_info .= '</div>';

$acceptance_path = <<<EOF
    <img src="$inspector->acceptance_path">
EOF;
$client_info = '<div style="text-align:center;">';
    $client_info .= $inspector->client_company .'<br />';

if ($inspector->signed != 0) {
    $client_info .= _l('inspector_signed_by') . ": {$inspector->acceptance_firstname} {$inspector->acceptance_lastname}" . '<br />';
    $client_info .= _l('inspector_signed_date') . ': ' . _dt($inspector->acceptance_date_string) . '<br />';
    $client_info .= _l('inspector_signed_ip') . ": {$inspector->acceptance_ip}" . '<br />';

    $client_info .= $acceptance_path;
    $client_info .= '<br />';
}
$client_info .= '</div>';


$left_info  = $swap == '1' ? $client_info : $assigned_info;
$right_info = $swap == '1' ? $assigned_info : $client_info;
pdf_multi_row($left_info, $right_info, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

if (!empty($inspector->clientnote)) {
    $pdf->Ln(4);
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('inspector_order'), 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(2);
    $pdf->writeHTMLCell('', '', '', '', $inspector->clientnote, 0, 1, false, true, 'L', true);
}

if (!empty($inspector->terms)) {
    $pdf->Ln(4);
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('terms_and_conditions') . ":", 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(2);
    $pdf->writeHTMLCell('', '', '', '', $inspector->terms, 0, 1, false, true, 'L', true);
} 


