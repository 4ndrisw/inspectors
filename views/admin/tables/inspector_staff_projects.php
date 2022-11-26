<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns         = ['name', 'start_date', 'deadline', db_prefix() . 'programs.state'];
$sIndexColumn     = 'id';
$sTable           = db_prefix() . 'programs';
$additionalSelect = ['id'];
$join             = [
    'JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'programs.clientid',
    ];

$where    = [];
$staff_id = get_staff_user_id();
if ($this->ci->input->post('staff_id')) {
    $staff_id = $this->ci->input->post('staff_id');
} else {
    // Request from dashboard, finished and canceled not need to be shown
    array_push($where, ' AND state != 4 AND state != 5');
}

array_push($where, ' AND ' . db_prefix() . 'programs.id IN (SELECT program_id FROM ' . db_prefix() . 'program_members WHERE staff_id=' . $this->ci->db->escape_str($staff_id) . ')');

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalSelect);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0 ; $i < count($aColumns) ; $i++) {
        $_data = $aRow[ $aColumns[$i] ];

        if ($aColumns[$i] == 'start_date' || $aColumns[$i] == 'deadline') {
            $_data = _d($_data);
        } elseif ($aColumns[$i] == 'name') {
            $_data = '<a href="' . admin_url('programs/view/' . $aRow['id']) . '">' . $_data . '</a>';
        } elseif ($aColumns[$i] == 'state') {
            $state = get_program_state_by_id($_data);
            $state = '<span class="label label program-state-' . $_data . '" style="color:' . $state['color'] . ';border:1px solid ' . $state['color'] . '">' . $state['name'] . '</span>';
            $_data  = $state;
        }

        $row[] = $_data;
    }
    $output['aaData'][] = $row;
}