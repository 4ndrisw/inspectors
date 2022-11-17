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

        $data['inspector_statuses'] = $this->inspectors_model->get_statuses();
        if ($isPipeline && !$this->input->get('status') && !$this->input->get('filter')) {
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
            if ($this->input->get('status') || $this->input->get('filter') && $isPipeline) {
                $this->pipeline(0, true);
            }

            $data['inspectorid']            = $id;
            $data['switch_pipeline']       = true;
            $data['title']                 = _l('inspectors');
            $data['bodyclass']             = 'inspectors-total-manual';
            $data['inspectors_years']       = $this->inspectors_model->get_inspectors_years();
            $data['inspectors_sale_agents'] = $this->inspectors_model->get_sale_agents();
            if($id){
                $this->load->view('admin/inspectors/manage_small_table', $data);

            }else{
                $this->load->view('admin/inspectors/manage_table', $data);

            }

        }
    }

    public function table($clientid = '')
    {
        if (!has_permission('inspectors', '', 'view') && !has_permission('inspectors', '', 'view_own') && get_option('allow_staff_view_inspectors_assigned') == '0') {
            ajax_access_denied();
        }
        $this->app->get_table_data(module_views_path('inspectors', 'admin/tables/table',[
            'clientid' => $clientid,
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
                if (!has_permission('inspectors', '', 'edit')) {
                    access_denied('inspectors');
                }
                $success = $this->inspectors_model->update($inspector_data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('inspector')));
                }
                if ($this->set_inspector_pipeline_autoload($id)) {
                    redirect(admin_url('inspectors/list_inspectors/'));
                } else {
                    redirect(admin_url('inspectors/list_inspectors/' . $id));
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

        if ($this->input->get('customer_id')) {
            $data['customer_id'] = $this->input->get('customer_id');
        }

        if ($this->input->get('inspector_request_id')) {
            $data['inspector_request_id'] = $this->input->get('inspector_request_id');
        }

        $this->load->model('taxes_model');
        $data['taxes'] = $this->taxes_model->get();
        $this->load->model('currencies_model');
        $data['currencies'] = $this->currencies_model->get();

        $data['base_currency'] = $this->currencies_model->get_base_currency();

        $this->load->model('invoice_items_model');

        $data['ajaxItems'] = false;
        if (total_rows(db_prefix() . 'items') <= ajax_on_total_items()) {
            $data['items'] = $this->invoice_items_model->get_grouped();
        } else {
            $data['items']     = [];
            $data['ajaxItems'] = true;
        }
        $data['items_groups'] = $this->invoice_items_model->get_groups();

        $data['staff']             = $this->staff_model->get('', ['active' => 1]);
        $data['inspector_statuses'] = $this->inspectors_model->get_statuses();
        $data['title']             = $title;
//        $this->load->view(module_views_path('inspectors','admin/inspectors/inspector'), $data);
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

        $inspector = $this->inspectors_model->get($id);

        if (!$inspector || !user_can_view_inspector($id)) {
            echo _l('inspector_not_found');
            die;
        }

//        $inspector->date       = _d($inspector->date);
//        $inspector->expirydate = _d($inspector->expirydate);
//        if ($inspector->invoiceid !== null) {
//            $this->load->model('invoices_model');
//            $inspector->invoice = $this->invoices_model->get($inspector->invoiceid);
//        }

//        $data = prepare_mail_preview_data($template_name, $inspector->clientid);

        $data['activity']          = $this->inspectors_model->get_inspector_activity($id);
        $data['inspector']          = $inspector;
//        $data['members']           = $this->staff_model->get('', ['active' => 1]);
        $data['inspector_statuses'] = $this->inspectors_model->get_statuses();
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

    public function mark_action_status($status, $id)
    {
        if (!has_permission('inspectors', '', 'edit')) {
            access_denied('inspectors');
        }
        $success = $this->inspectors_model->mark_action_status($status, $id);
        if ($success) {
            set_alert('success', _l('inspector_status_changed_success'));
        } else {
            set_alert('danger', _l('inspector_status_changed_fail'));
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
            $data['inspector_statuses'] = $this->inspectors_model->get_statuses();
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
        if (has_permission('inspectors', '', 'edit')) {
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
        $status = $this->input->get('status');
        $page   = $this->input->get('page');

        $inspectors = (new InspectorsPipeline($status))
            ->search($this->input->get('search'))
            ->sortBy(
                $this->input->get('sort_by'),
                $this->input->get('sort')
            )
            ->page($page)->get();

        foreach ($inspectors as $inspector) {
            $this->load->view('admin/inspectors/pipeline/_kanban_card', [
                'inspector' => $inspector,
                'status'   => $status,
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
}
