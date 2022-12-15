<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'assignment_number',
    'date_expired',
    'staff',
    'description',
    'is_active'
    ];

$sIndexColumn = 'id';
$sTable       = db_prefix().'assignments';
$where        = [
    'AND rel_id=' . $id . ' AND rel_type="' . $rel_type . '"',
    ];
$join = [
    'JOIN '.db_prefix().'staff ON '.db_prefix().'staff.staffid = '.db_prefix().'assignments.staff',
    ];
$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'firstname',
    'lastname',
    'id',
    'rel_id',
    'creator',
    'rel_type',
    'isnotified',
    ]);
$output  = $result['output'];
$rResult = $result['rResult'];
foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];
        if ($aColumns[$i] == 'staff') {
            $_data = '<a href="' . admin_url('pengguna/profile/' . $aRow['staff']) . '">' . staff_profile_image($aRow['staff'], [
                'staff-profile-image-small',
                ]) . ' ' . $aRow['firstname'] . ' ' . $aRow['lastname'] . '</a>';
        } elseif ($aColumns[$i] == 'assignment_number') {
            if ($aRow['creator'] == get_staff_user_id() || is_admin()) {
                $_data .= '<div class="row-options">';
                if ($aRow['isnotified'] == 0) {
                    $_data .= '<a href="#" onclick="edit_assignment(' . $aRow['id'] . ',this); return false;" class="edit-assignment">' . _l('edit') . '</a> | ';
                }
                $_data .= '<a href="' . admin_url('surveyors/delete_assignment/' . $id . '/' . $aRow['id'] . '/' . $aRow['rel_type']) . '" class="text-danger delete-assignment">' . _l('delete') . '</a>';
                $_data .= '</div>';
            }
        } elseif ($aColumns[$i] == 'isnotified') {
            if ($_data == 1) {
                $_data = _l('assignment_is_notified_boolean_yes');
            } else {
                $_data = _l('assignment_is_notified_boolean_no');
            }
        } elseif ($aColumns[$i] == 'date_expired') {
            $_data = html_date($_data);
        }elseif($aColumns[$i] == 'is_active'){
            // Toggle active/inactive customer
            $toggleActive = '<div class="onoffswitch" data-toggle="tooltip" data-title="' . _l('assignment_active_inactive_help') . '">
            <input type="checkbox"' . ' data-switch-url="' . admin_url() . 'surveyors/change_assignment_status" name="onoffswitch" class="onoffswitch-checkbox" id="' . $aRow['id'] . '" data-id="' . $aRow['id']. '" data-rel_id="' . $aRow['rel_id'] . '" ' . ($aRow['is_active'] == 1 ? 'checked' : '') . '>
            <label class="onoffswitch-label" for="' . $aRow['id'] . '"></label>
            </div>';

            // For exporting
            $toggleActive .= '<span class="hide">' . ($aRow['is_active'] == 1 ? _l('is_active_export') : _l('is_not_active_export')) . '</span>';

            $_data = $toggleActive;


        }

        $row[] = $_data;
    }
    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
