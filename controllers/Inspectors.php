<?php

use app\services\inspectors\InspectorsPipeline;

defined('BASEPATH') or exit('No direct script access allowed');

class Inspectors extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('inspectors_model');
        $this->load->model('clients_model');
        $this->load->model('staff_model');
    }

    /* Get all inspectors in case user go on index page */
    public function index($id = '')
    {
        $this->list_inspectors($id);
    }

    /* List all inspectors datatables */
    public function list_inspectors($id = '')
    {
        if (!has_permission('inspectors', '', 'view') && !has_permission('inspectors', '', 'view_own') && get_option('allow_staff_view_inspectors_assigned') == '0') {
            access_denied('inspectors');
        }

        $isPipeline = $this->session->userdata('inspector_pipeline') == 'true';

        $data['inspector_states'] = $this->inspectors_model->get_states();
        if ($isPipeline && !$this->input->get('state') && !$this->input->get('filter')) {
            $data['title']           = _l('inspectors_pipeline');
            $data['bodyclass']       = 'inspectors-pipeline inspectors-total-manual';
            $data['switch_pipeline'] = false;

            if (is_numeric($id)) {
                $data['inspectorid'] = $id;
            } else {
                $data['inspectorid'] = $this->session->flashdata('inspectorid');
            }

            $this->load->view('admin/inspectors/pipeline/manage', $data);
        } else {

            // Pipeline was initiated but user click from home page and need to show table only to filter
            if ($this->input->get('state') || $this->input->get('filter') && $isPipeline) {
                $this->pipeline(0, true);
            }
            
            $data['inspectorid']            = $id;
            $data['switch_pipeline']       = true;
            $data['title']                 = _l('inspectors');
            $data['bodyclass']             = 'inspectors-total-manual';
            //$data['inspectors_years']       = $this->inspectors_model->get_inspectors_years();
            $data['inspectors_sale_agents'] = $this->inspectors_model->get_sale_agents();
            if($id){
                $this->load->view('admin/inspectors/manage_small_table', $data);

            }else{
                $this->load->view('admin/inspectors/manage_table', $data);

            }

        }
    }

    public function table($client_id = '')
    {
        if (!has_permission('inspectors', '', 'view') && !has_permission('inspectors', '', 'view_own') && get_option('allow_staff_view_inspectors_assigned') == '0') {
            ajax_access_denied();
        }
        $this->app->get_table_data(module_views_path('inspectors', 'admin/tables/table',[
            'client_id' => $client_id,
        ]));
    }

    /* Add new inspector or update existing */
    public function inspector($id = '')
    {
        if ($this->input->post()) {
            $inspector_data = $this->input->post();

            $save_and_send_later = false;
            if (isset($inspector_data['save_and_send_later'])) {
                unset($inspector_data['save_and_send_later']);
                $save_and_send_later = true;
            }

            if ($id == '') {
                if (!has_permission('inspectors', '', 'create')) {
                    access_denied('inspectors');
                }
                $inspector_data['is_inspector'] = '1';
                $next_inspector_number = get_option('next_inspector_number');
                $_format = get_option('inspector_number_format');
                $_prefix = get_option('inspector_prefix');
                
                $prefix  = isset($inspector->prefix) ? $inspector->prefix : $_prefix;
                $number_format  = isset($inspector->number_format) ? $inspector->number_format : $_format;
                $number  = isset($inspector->number) ? $inspector->number : $next_inspector_number;

                $inspector_data['prefix'] = $prefix;
                $inspector_data['number_format'] = $number_format;
                $date = date('Y-m-d');
                $id = $this->inspectors_model->add($inspector_data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('inspector')));

                    $redUrl = admin_url('inspectors/list_inspectors/' . $id);

                    if ($save_and_send_later) {
                        $this->session->set_userdata('send_later', true);
                        // die(redirect($redUrl));
                    }

                    redirect(
                        !$this->set_inspector_pipeline_autoload($id) ? $redUrl : admin_url('inspectors/list_inspectors/')
                    );
                }
            } else {
                if (has_permission('inspectors', '', 'edit') || 
                   (has_permission('inspectors', '', 'edit_own') && is_staff_related_to_inspector($id))
                   ) {
                  
                    $success = $this->inspectors_model->update($inspector_data, $id);
                    if ($success) {
                        set_alert('success', _l('updated_successfully', _l('inspector')));
                    }
                    if ($this->set_inspector_pipeline_autoload($id)) {
                        redirect(admin_url('inspectors/list_inspectors/'));
                    } else {
                        redirect(admin_url('inspectors/#' . $id));
                    }
                }else{
                    access_denied('inspectors');
                }
            }
        }
        if ($id == '') {
            $title = _l('create_new_inspector');
        } else {
            $inspector = $this->inspectors_model->get($id);

            if (!$inspector || !user_can_view_inspector($id)) {
                blank_page(_l('inspector_not_found'));
            }

            $data['inspector'] = $inspector;
            $data['edit']     = true;
            $title            = _l('edit', _l('inspector_lowercase'));
        }
        $data['institutions'] = get_institutions_sql();
        $data['inspector_states'] = $this->inspectors_model->get_states();
        $data['title']             = $title;

        $this->load->view('admin/inspectors/inspector', $data);
    }
    
    public function clear_signature($id)
    {
        if (has_permission('inspectors', '', 'delete')) {
            $this->inspectors_model->clear_signature($id);
        }

        redirect(admin_url('inspectors/list_inspectors/' . $id));
    }

    public function update_number_settings($id)
    {
        $response = [
            'success' => false,
            'message' => '',
        ];
        if (has_permission('inspectors', '', 'edit')) {
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'inspectors', [
                'prefix' => $this->input->post('prefix'),
            ]);
            if ($this->db->affected_rows() > 0) {
                $response['success'] = true;
                $response['message'] = _l('updated_successfully', _l('inspector'));
            }
        }

        echo json_encode($response);
        die;
    }

    public function validate_inspector_number()
    {
        $isedit          = $this->input->post('isedit');
        $number          = $this->input->post('number');
        $date            = $this->input->post('date');
        $original_number = $this->input->post('original_number');
        $number          = trim($number);
        $number          = ltrim($number, '0');

        if ($isedit == 'true') {
            if ($number == $original_number) {
                echo json_encode(true);
                die;
            }
        }

        if (total_rows(db_prefix() . 'inspectors', [
            'YEAR(date)' => date('Y', strtotime(to_sql_date($date))),
            'number' => $number,
        ]) > 0) {
            echo 'false';
        } else {
            echo 'true';
        }
    }

    public function delete_attachment($id)
    {
        $file = $this->misc_model->get_file($id);
        if ($file->staffid == get_staff_user_id() || is_admin()) {
            echo $this->inspectors_model->delete_attachment($id);
        } else {
            header('HTTP/1.0 400 Bad error');
            echo _l('access_denied');
            die;
        }
    }

    /* Get all inspector data used when user click on inspector number in a datatable left side*/
    public function get_inspector_data_ajax($id, $to_return = false)
    {
        if (!has_permission('inspectors', '', 'view') && !has_permission('inspectors', '', 'view_own') && get_option('allow_staff_view_inspectors_assigned') == '0') {
            echo _l('access_denied');
            die;
        }

        if (!$id) {
            die('No inspector found');
        }

        $staff_id = get_staff_user_id();
        $institution_id = get_institution_id_by_inspector_id($id);
        
        if(is_inspector_staff($staff_id)){
          if(get_option('allow_inspector_staff_only_view_inspectors_in_same_institution') && ($institution_id != get_institution_id_by_staff_id($staff_id))){
            echo _l('access_denied');
            die;
          }
        }

        $inspector = $this->inspectors_model->get($id);

        if (!$inspector || !user_can_view_inspector($id)) {
            echo _l('inspector_not_found');
            die;
        }

        // $data = prepare_mail_preview_data($template_name, $inspector->clientid);
        $data['title'] = 'Form add / Edit Staff';
        $data['activity']          = $this->inspectors_model->get_inspector_activity($id);
        $data['inspector']          = $inspector;

        $data['categories']          = get_kelompok_alat();


        $_institution          = get_institutions($inspector->institution_id);
        $data['institution']   = $_institution[0];
        $data['members']           = $this->staff_model->get('', ['active' => 1, 'client_id'=>$inspector->userid]);

        $data['inspector_states'] = $this->inspectors_model->get_states();
        $data['totalNotes']        = total_rows(db_prefix() . 'notes', ['rel_id' => $id, 'rel_type' => 'inspector']);

        $data['send_later'] = false;
        if ($this->session->has_userdata('send_later')) {
            $data['send_later'] = true;
            $this->session->unset_userdata('send_later');
        }

        if ($to_return == false) {
            $this->load->view('admin/inspectors/inspector_preview_template', $data);
        } else {
            return $this->load->view('admin/inspectors/inspector_preview_template', $data, true);
        }
    }

    public function get_inspectors_total()
    {
        if ($this->input->post()) {
            $data['totals'] = $this->inspectors_model->get_inspectors_total($this->input->post());

            $this->load->model('currencies_model');

            if (!$this->input->post('customer_id')) {
                $multiple_currencies = call_user_func('is_using_multiple_currencies', db_prefix() . 'inspectors');
            } else {
                $multiple_currencies = call_user_func('is_client_using_multiple_currencies', $this->input->post('customer_id'), db_prefix() . 'inspectors');
            }

            if ($multiple_currencies) {
                $data['currencies'] = $this->currencies_model->get();
            }

            $data['inspectors_years'] = $this->inspectors_model->get_inspectors_years();

            if (
                count($data['inspectors_years']) >= 1
                && !\app\services\utilities\Arr::inMultidimensional($data['inspectors_years'], 'year', date('Y'))
            ) {
                array_unshift($data['inspectors_years'], ['year' => date('Y')]);
            }

            $data['_currency'] = $data['totals']['currencyid'];
            unset($data['totals']['currencyid']);
            $this->load->view('admin/inspectors/inspectors_total_template', $data);
        }
    }

    public function add_note($rel_id)
    {
        if ($this->input->post() && user_can_view_inspector($rel_id)) {
            $this->misc_model->add_note($this->input->post(), 'inspector', $rel_id);
            echo $rel_id;
        }
    }

    public function get_notes($id)
    {
        if (user_can_view_inspector($id)) {
            $data['notes'] = $this->misc_model->get_notes($id, 'inspector');
            $this->load->view('admin/includes/sales_notes_template', $data);
        }
    }

    public function mark_action_state($state, $id)
    {
        if (!has_permission('inspectors', '', 'edit') || !has_permission('inspectors', '', 'edit_own')) {
            access_denied('inspectors');
        }
        $success = $this->inspectors_model->mark_action_state($state, $id);
        if ($success) {
            set_alert('success', _l('inspector_state_changed_success'));
        } else {
            set_alert('danger', _l('inspector_state_changed_fail'));
        }
        if ($this->set_inspector_pipeline_autoload($id)) {
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            redirect(admin_url('inspectors/list_inspectors/' . $id));
        }
    }

    public function send_expiry_reminder($id)
    {
        $canView = user_can_view_inspector($id);
        if (!$canView) {
            access_denied('Inspectors');
        } else {
            if (!has_permission('inspectors', '', 'view') && !has_permission('inspectors', '', 'view_own') && $canView == false) {
                access_denied('Inspectors');
            }
        }

        $success = $this->inspectors_model->send_expiry_reminder($id);
        if ($success) {
            set_alert('success', _l('sent_expiry_reminder_success'));
        } else {
            set_alert('danger', _l('sent_expiry_reminder_fail'));
        }
        if ($this->set_inspector_pipeline_autoload($id)) {
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            redirect(admin_url('inspectors/list_inspectors/' . $id));
        }
    }

    /* Send inspector to email */
    public function send_to_email($id)
    {
        $canView = user_can_view_inspector($id);
        if (!$canView) {
            access_denied('inspectors');
        } else {
            if (!has_permission('inspectors', '', 'view') && !has_permission('inspectors', '', 'view_own') && $canView == false) {
                access_denied('inspectors');
            }
        }

        try {
            $success = $this->inspectors_model->send_inspector_to_client($id, '', $this->input->post('attach_pdf'), $this->input->post('cc'));
        } catch (Exception $e) {
            $message = $e->getMessage();
            echo $message;
            if (strpos($message, 'Unable to get the size of the image') !== false) {
                show_pdf_unable_to_get_image_size_error();
            }
            die;
        }

        // In case client use another language
        load_admin_language();
        if ($success) {
            set_alert('success', _l('inspector_sent_to_client_success'));
        } else {
            set_alert('danger', _l('inspector_sent_to_client_fail'));
        }
        if ($this->set_inspector_pipeline_autoload($id)) {
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            redirect(admin_url('inspectors/list_inspectors/' . $id));
        }
    }

    /* Convert inspector to invoice */
    public function convert_to_invoice($id)
    {
        if (!has_permission('invoices', '', 'create')) {
            access_denied('invoices');
        }
        if (!$id) {
            die('No inspector found');
        }
        $draft_invoice = false;
        if ($this->input->get('save_as_draft')) {
            $draft_invoice = true;
        }
        $invoiceid = $this->inspectors_model->convert_to_invoice($id, false, $draft_invoice);
        if ($invoiceid) {
            set_alert('success', _l('inspector_convert_to_invoice_successfully'));
            redirect(admin_url('invoices/list_invoices/' . $invoiceid));
        } else {
            if ($this->session->has_userdata('inspector_pipeline') && $this->session->userdata('inspector_pipeline') == 'true') {
                $this->session->set_flashdata('inspectorid', $id);
            }
            if ($this->set_inspector_pipeline_autoload($id)) {
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                redirect(admin_url('inspectors/list_inspectors/' . $id));
            }
        }
    }

    public function copy($id)
    {
        if (!has_permission('inspectors', '', 'create')) {
            access_denied('inspectors');
        }
        if (!$id) {
            die('No inspector found');
        }
        $new_id = $this->inspectors_model->copy($id);
        if ($new_id) {
            set_alert('success', _l('inspector_copied_successfully'));
            if ($this->set_inspector_pipeline_autoload($new_id)) {
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                redirect(admin_url('inspectors/inspector/' . $new_id));
            }
        }
        set_alert('danger', _l('inspector_copied_fail'));
        if ($this->set_inspector_pipeline_autoload($id)) {
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            redirect(admin_url('inspectors/inspector/' . $id));
        }
    }

    /* Delete inspector */
    public function delete($id)
    {
        if (!has_permission('inspectors', '', 'delete')) {
            access_denied('inspectors');
        }
        if (!$id) {
            redirect(admin_url('inspectors/list_inspectors'));
        }
        $success = $this->inspectors_model->delete($id);
        if (is_array($success)) {
            set_alert('warning', _l('is_invoiced_inspector_delete_error'));
        } elseif ($success == true) {
            set_alert('success', _l('deleted', _l('inspector')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('inspector_lowercase')));
        }
        redirect(admin_url('inspectors/list_inspectors'));
    }

    public function clear_acceptance_info($id)
    {
        if (is_admin()) {
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'inspectors', get_acceptance_info_array(true));
        }

        redirect(admin_url('inspectors/list_inspectors/' . $id));
    }

    /* Generates inspector PDF and senting to email  */
    public function pdf($id)
    {
        $canView = user_can_view_inspector($id);
        if (!$canView) {
            access_denied('Inspectors');
        } else {
            if (!has_permission('inspectors', '', 'view') && !has_permission('inspectors', '', 'view_own') && $canView == false) {
                access_denied('Inspectors');
            }
        }
        if (!$id) {
            redirect(admin_url('inspectors/list_inspectors'));
        }
        $inspector        = $this->inspectors_model->get($id);
        $inspector_number = format_inspector_number($inspector->id);

        try {
            $pdf = inspector_pdf($inspector);
        } catch (Exception $e) {
            $message = $e->getMessage();
            echo $message;
            if (strpos($message, 'Unable to get the size of the image') !== false) {
                show_pdf_unable_to_get_image_size_error();
            }
            die;
        }

        $type = 'D';

        if ($this->input->get('output_type')) {
            $type = $this->input->get('output_type');
        }

        if ($this->input->get('print')) {
            $type = 'I';
        }

        $fileNameHookData = hooks()->apply_filters('inspector_file_name_admin_area', [
                            'file_name' => mb_strtoupper(slug_it($inspector_number)) . '.pdf',
                            'inspector'  => $inspector,
                        ]);

        $pdf->Output($fileNameHookData['file_name'], $type);
    }

    // Pipeline
    public function get_pipeline()
    {
        if (has_permission('inspectors', '', 'view') || has_permission('inspectors', '', 'view_own') || get_option('allow_staff_view_inspectors_assigned') == '1') {
            $data['inspector_states'] = $this->inspectors_model->get_states();
            $this->load->view('admin/inspectors/pipeline/pipeline', $data);
        }
    }

    public function pipeline_open($id)
    {
        $canView = user_can_view_inspector($id);
        if (!$canView) {
            access_denied('Inspectors');
        } else {
            if (!has_permission('inspectors', '', 'view') && !has_permission('inspectors', '', 'view_own') && $canView == false) {
                access_denied('Inspectors');
            }
        }

        $data['userid']       = $id;
        $data['inspector'] = $this->get_inspector_data_ajax($id, true);
        $this->load->view('admin/inspectors/pipeline/inspector', $data);
    }

    public function update_pipeline()
    {
        if (has_permission('inspectors', '', 'edit') || has_permission('inspectors', '', 'edit_own')) {
            $this->inspectors_model->update_pipeline($this->input->post());
        }
    }

    public function pipeline($set = 0, $manual = false)
    {
        if ($set == 1) {
            $set = 'true';
        } else {
            $set = 'false';
        }
        $this->session->set_userdata([
            'inspector_pipeline' => $set,
        ]);
        if ($manual == false) {
            redirect(admin_url('inspectors/list_inspectors'));
        }
    }

    public function pipeline_load_more()
    {
        $state = $this->input->get('state');
        $page   = $this->input->get('page');

        $inspectors = (new InspectorsPipeline($state))
            ->search($this->input->get('search'))
            ->sortBy(
                $this->input->get('sort_by'),
                $this->input->get('sort')
            )
            ->page($page)->get();

        foreach ($inspectors as $inspector) {
            $this->load->view('admin/inspectors/pipeline/_kanban_card', [
                'inspector' => $inspector,
                'state'   => $state,
            ]);
        }
    }

    public function set_inspector_pipeline_autoload($id)
    {
        if ($id == '') {
            return false;
        }

        if ($this->session->has_userdata('inspector_pipeline')
                && $this->session->userdata('inspector_pipeline') == 'true') {
            $this->session->set_flashdata('inspectorid', $id);

            return true;
        }

        return false;
    }

    public function get_due_date()
    {
        if ($this->input->post()) {
            $date    = $this->input->post('date');
            $duedate = '';
            if (get_option('inspector_due_after') != 0) {
                $date    = to_sql_date($date);
                $d       = date('Y-m-d', strtotime('+' . get_option('inspector_due_after') . ' DAY', strtotime($date)));
                $duedate = _d($d);
                echo $duedate;
            }
        }
    }
/*
    public function get_staff($userid='')
    {
        $this->app->get_table_data(module_views_path('inspectors', 'admin/tables/staff'));
    }
*/
    public function table_staffs($client_id,$inspector = true)
    {
        if (
            !has_permission('inspectors', '', 'view')
            && !has_permission('inspectors', '', 'view_own')
            && get_option('allow_staff_view_inspectors_assigned') == 0
        ) {
            ajax_access_denied();
        }
        $this->app->get_table_data(module_views_path('inspectors', 'admin/tables/staff'), array('client_id'=>$client_id));
    }


    /* Since version 1.0.2 add inspector assignment */
    public function add_assignment($rel_id, $rel_type)
    {
        $message    = '';
        $alert_type = 'warning';
        if ($this->input->post()) {
            $success = $this->inspectors_model->add_assignment($this->input->post(), $rel_id);
            if ($success) {
                $alert_type = 'success';
                $message    = _l('assignment_added_successfully');
            }else{
                $alert_type = 'warning';
                $message    = _l('assignment_failed_to_add');
            }
        }
        echo json_encode([
            'alert_type' => $alert_type,
            'message'    => $message,
        ]);
    }

    public function get_assignments($id, $rel_type)
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('inspectors', 'admin/tables/assignments'), [
                'id'       => $id,
                'rel_type' => $rel_type,
            ]);
        }
    }

    public function get_staff_assignments($id, $rel_type)
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('inspectors', 'admin/tables/assignments'), [
                'id'       => $id,
                'rel_type' => $rel_type,
            ]);
        }
    }

    public function my_assignments()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('inspectors', 'admin/tables/staff_assignments'));
        }
    }

    public function assignments()
    {
        $this->load->model('staff_model');
        $data['members']   = $this->staff_model->get('', ['active' => 1]);
        $data['title']     = _l('assignments');
        $data['bodyclass'] = 'all-assignments';
        $this->load->view('admin/utilities/all_assignments', $data);
    }

    public function assignments_table()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('inspectors', 'admin/tables/all_assignments'));
        }
    }

    /* Since version 1.0.2 delete client assignment */
    public function delete_assignment($rel_id, $id, $rel_type)
    {
        if (!$id && !$rel_id) {
            die('No assignment found');
        }
        $success    = $this->inspectors_model->delete_assignment($id);
        $alert_type = 'warning';
        $message    = _l('assignment_failed_to_delete');
        if ($success) {
            $alert_type = 'success';
            $message    = _l('assignment_deleted');
        }
        echo json_encode([
            'alert_type' => $alert_type,
            'message'    => $message,
        ]);
    }

    public function get_assignment($id)
    {
        $assignment = $this->inspectors_model->get_assignments($id);
        if ($assignment) {
            if ($assignment->creator == get_staff_user_id() || is_admin()) {
                $assignment->date_issued        = _d($assignment->date_issued);
                $assignment->date_expired        = _d($assignment->date_expired);
                //$assignment->category        = $assignment->category;
                $assignment->description = clear_textarea_breaks($assignment->description);
                echo json_encode($assignment);
            }
        }
    }



    public function edit_assignment($id)
    {
        $assignment = $this->inspectors_model->get_assignments($id);
        if ($assignment && ($assignment->creator == get_staff_user_id() || is_admin()) && $assignment->isnotified == 0) {
            $success = $this->inspectors_model->edit_assignment($this->input->post(), $id);
            echo json_encode([
                    'alert_type' => 'success',
                    'message'    => ($success ? _l('updated_successfully', _l('assignment')) : ''),
                ]);
        }
    }

}
