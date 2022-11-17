<?php defined('BASEPATH') or exit('No direct script access allowed');

class Myinspector extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('inspectors_model');
        $this->load->model('clients_model');
    }

    /* Get all inspectors in case user go on index page */
    public function list($id = '')
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('inspectors', 'admin/tables/table'));
        }
        $contact_id = get_contact_user_id();
        $user_id = get_user_id_by_contact_id($contact_id);
        $client = $this->clients_model->get($user_id);
        $data['inspectors'] = $this->inspectors_model->get_client_inspectors($client);
        $data['inspectorid']            = $id;
        $data['title']                 = _l('inspectors_tracking');

        $data['bodyclass'] = 'inspectors';
        $this->data($data);
        $this->view('themes/'. active_clients_theme() .'/views/inspectors/inspectors');
        $this->layout();
    }

    public function show($id, $hash)
    {
        check_inspector_restrictions($id, $hash);
        $inspector = $this->inspectors_model->get($id);

        if (!is_client_logged_in()) {
            load_client_language($inspector->clientid);
        }

        $identity_confirmation_enabled = get_option('inspector_accept_identity_confirmation');

        if ($this->input->post('inspector_action')) {
            $action = $this->input->post('inspector_action');

            // Only decline and accept allowed
            if ($action == 4 || $action == 3) {
                $success = $this->inspectors_model->mark_action_status($action, $id, true);

                $redURL   = $this->uri->uri_string();
                $accepted = false;

                if (is_array($success)) {
                    if ($action == 4) {
                        $accepted = true;
                        set_alert('success', _l('clients_inspector_accepted_not_invoiced'));
                    } else {
                        set_alert('success', _l('clients_inspector_declined'));
                    }
                } else {
                    set_alert('warning', _l('clients_inspector_failed_action'));
                }
                if ($action == 4 && $accepted = true) {
                    process_digital_signature_image($this->input->post('signature', false), SCHEDULE_ATTACHMENTS_FOLDER . $id);

                    $this->db->where('id', $id);
                    $this->db->update(db_prefix() . 'inspectors', get_acceptance_info_array());
                }
            }
            redirect($redURL);
        }
        // Handle Inspector PDF generator

        $inspector_number = format_inspector_number($inspector->id);
        /*
        if ($this->input->post('inspectorpdf')) {
            try {
                $pdf = inspector_pdf($inspector);
            } catch (Exception $e) {
                echo $e->getMessage();
                die;
            }

            //$inspector_number = format_inspector_number($inspector->id);
            $companyname     = get_option('company_name');
            if ($companyname != '') {
                $inspector_number .= '-' . mb_strtoupper(slug_it($companyname), 'UTF-8');
            }

            $filename = hooks()->apply_filters('customers_area_download_inspector_filename', mb_strtoupper(slug_it($inspector_number), 'UTF-8') . '.pdf', $inspector);

            $pdf->Output($filename, 'D');
            die();
        }
        */

        $data['title'] = $inspector_number;
        $this->disableNavigation();
        $this->disableSubMenu();

        $data['inspector_number']              = $inspector_number;
        $data['hash']                          = $hash;
        $data['can_be_accepted']               = false;
        $data['inspector']                     = hooks()->apply_filters('inspector_html_pdf_data', $inspector);
        $data['bodyclass']                     = 'viewinspector';
        $data['client_company']                = $this->clients_model->get($inspector->clientid)->company;
        $setSize = get_option('inspector_qrcode_size');

        $data['identity_confirmation_enabled'] = $identity_confirmation_enabled;
        if ($identity_confirmation_enabled == '1') {
            $data['bodyclass'] .= ' identity-confirmation';
        }
        $data['inspector_members']  = $this->inspectors_model->get_inspector_members($inspector->id,true);

        $qrcode_data  = '';
        $qrcode_data .= _l('inspector_number') . ' : ' . $inspector_number ."\r\n";
        $qrcode_data .= _l('inspector_date') . ' : ' . $inspector->date ."\r\n";
        $qrcode_data .= _l('inspector_datesend') . ' : ' . $inspector->datesend ."\r\n";
        //$qrcode_data .= _l('inspector_assigned_string') . ' : ' . get_staff_full_name($inspector->assigned) ."\r\n";
        //$qrcode_data .= _l('inspector_url') . ' : ' . site_url('inspectors/show/'. $inspector->id .'/'.$inspector->hash) ."\r\n";


        $inspector_path = get_upload_path_by_type('inspectors') . $inspector->id . '/';
        _maybe_create_upload_path('uploads/inspectors');
        _maybe_create_upload_path('uploads/inspectors/'.$inspector_path);

        $params['data'] = $qrcode_data;
        $params['writer'] = 'png';
        $params['setSize'] = isset($setSize) ? $setSize : 160;
        $params['encoding'] = 'UTF-8';
        $params['setMargin'] = 0;
        $params['setForegroundColor'] = ['r'=>0,'g'=>0,'b'=>0];
        $params['setBackgroundColor'] = ['r'=>255,'g'=>255,'b'=>255];

        $params['crateLogo'] = true;
        $params['logo'] = './uploads/company/favicon.png';
        $params['setResizeToWidth'] = 60;

        $params['crateLabel'] = false;
        $params['label'] = $inspector_number;
        $params['setTextColor'] = ['r'=>255,'g'=>0,'b'=>0];
        $params['ErrorCorrectionLevel'] = 'hight';

        $params['saveToFile'] = FCPATH.'uploads/inspectors/'.$inspector_path .'assigned-'.$inspector_number.'.'.$params['writer'];

        $this->load->library('endroid_qrcode');
        $this->endroid_qrcode->generate($params);

        $this->data($data);
        $this->app_scripts->theme('sticky-js', 'assets/plugins/sticky/sticky.js');
        $this->view('themes/'. active_clients_theme() .'/views/inspectors/inspectorhtml');
        add_views_tracking('inspector', $id);
        hooks()->do_action('inspector_html_viewed', $id);
        no_index_customers_area();
        $this->layout();
    }


    public function office($id, $hash)
    {
        check_inspector_restrictions($id, $hash);
        $inspector = $this->inspectors_model->get($id);

        if (!is_client_logged_in()) {
            load_client_language($inspector->clientid);
        }

        $identity_confirmation_enabled = get_option('inspector_accept_identity_confirmation');

        if ($this->input->post('inspector_action')) {
            $action = $this->input->post('inspector_action');

            // Only decline and accept allowed
            if ($action == 4 || $action == 3) {
                $success = $this->inspectors_model->mark_action_status($action, $id, true);

                $redURL   = $this->uri->uri_string();
                $accepted = false;

                if (is_array($success)) {
                    if ($action == 4) {
                        $accepted = true;
                        set_alert('success', _l('clients_inspector_accepted_not_invoiced'));
                    } else {
                        set_alert('success', _l('clients_inspector_declined'));
                    }
                } else {
                    set_alert('warning', _l('clients_inspector_failed_action'));
                }
                if ($action == 4 && $accepted = true) {
                    process_digital_signature_image($this->input->post('signature', false), SCHEDULE_ATTACHMENTS_FOLDER . $id);

                    $this->db->where('id', $id);
                    $this->db->update(db_prefix() . 'inspectors', get_acceptance_info_array());
                }
            }
            redirect($redURL);
        }
        // Handle Inspector PDF generator

        $inspector_number = format_inspector_number($inspector->id);
        /*
        if ($this->input->post('inspectorpdf')) {
            try {
                $pdf = inspector_pdf($inspector);
            } catch (Exception $e) {
                echo $e->getMessage();
                die;
            }

            //$inspector_number = format_inspector_number($inspector->id);
            $companyname     = get_option('company_name');
            if ($companyname != '') {
                $inspector_number .= '-' . mb_strtoupper(slug_it($companyname), 'UTF-8');
            }

            $filename = hooks()->apply_filters('customers_area_download_inspector_filename', mb_strtoupper(slug_it($inspector_number), 'UTF-8') . '.pdf', $inspector);

            $pdf->Output($filename, 'D');
            die();
        }
        */

        $data['title'] = $inspector_number;
        $this->disableNavigation();
        $this->disableSubMenu();

        $data['inspector_number']              = $inspector_number;
        $data['hash']                          = $hash;
        $data['can_be_accepted']               = false;
        $data['inspector']                     = hooks()->apply_filters('inspector_html_pdf_data', $inspector);
        $data['bodyclass']                     = 'viewinspector';
        $data['client_company']                = $this->clients_model->get($inspector->clientid)->company;
        $setSize = get_option('inspector_qrcode_size');

        $data['identity_confirmation_enabled'] = $identity_confirmation_enabled;
        if ($identity_confirmation_enabled == '1') {
            $data['bodyclass'] .= ' identity-confirmation';
        }
        $data['inspector_members']  = $this->inspectors_model->get_inspector_members($inspector->id,true);

        $qrcode_data  = '';
        $qrcode_data .= _l('inspector_number') . ' : ' . $inspector_number ."\r\n";
        $qrcode_data .= _l('inspector_date') . ' : ' . $inspector->date ."\r\n";
        $qrcode_data .= _l('inspector_datesend') . ' : ' . $inspector->datesend ."\r\n";
        //$qrcode_data .= _l('inspector_assigned_string') . ' : ' . get_staff_full_name($inspector->assigned) ."\r\n";
        //$qrcode_data .= _l('inspector_url') . ' : ' . site_url('inspectors/show/'. $inspector->id .'/'.$inspector->hash) ."\r\n";


        $inspector_path = get_upload_path_by_type('inspectors') . $inspector->id . '/';
        _maybe_create_upload_path('uploads/inspectors');
        _maybe_create_upload_path('uploads/inspectors/'.$inspector_path);

        $params['data'] = $qrcode_data;
        $params['writer'] = 'png';
        $params['setSize'] = isset($setSize) ? $setSize : 160;
        $params['encoding'] = 'UTF-8';
        $params['setMargin'] = 0;
        $params['setForegroundColor'] = ['r'=>0,'g'=>0,'b'=>0];
        $params['setBackgroundColor'] = ['r'=>255,'g'=>255,'b'=>255];

        $params['crateLogo'] = true;
        $params['logo'] = './uploads/company/favicon.png';
        $params['setResizeToWidth'] = 60;

        $params['crateLabel'] = false;
        $params['label'] = $inspector_number;
        $params['setTextColor'] = ['r'=>255,'g'=>0,'b'=>0];
        $params['ErrorCorrectionLevel'] = 'hight';

        $params['saveToFile'] = FCPATH.'uploads/inspectors/'.$inspector_path .'assigned-'.$inspector_number.'.'.$params['writer'];

        $this->load->library('endroid_qrcode');
        $this->endroid_qrcode->generate($params);

        $this->data($data);
        $this->app_scripts->theme('sticky-js', 'assets/plugins/sticky/sticky.js');
        $this->view('themes/'. active_clients_theme() .'/views/inspectors/inspector_office_html');
        add_views_tracking('inspector', $id);
        hooks()->do_action('inspector_html_viewed', $id);
        no_index_customers_area();
        $this->layout();
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
            redirect(admin_url('inspectors'));
        }
        $inspector        = $this->inspectors_model->get($id);
        $inspector_number = format_inspector_number($inspector->id);
        
        $inspector->assigned_path = FCPATH . get_inspector_upload_path('inspector').$inspector->id.'/assigned-'.$inspector_number.'.png';
        $inspector->acceptance_path = FCPATH . get_inspector_upload_path('inspector').$inspector->id .'/'.$inspector->signature;
        
        $inspector->client_company = $this->clients_model->get($inspector->clientid)->company;
        $inspector->acceptance_date_string = _dt($inspector->acceptance_date);


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

    /* Generates inspector PDF and senting to email  */
    public function office_pdf($id)
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
            redirect(admin_url('inspectors'));
        }
        $inspector        = $this->inspectors_model->get($id);
        $inspector_number = format_inspector_number($inspector->id);
        
        $inspector->assigned_path = FCPATH . get_inspector_upload_path('inspector').$inspector->id.'/assigned-'.$inspector_number.'.png';
        $inspector->acceptance_path = FCPATH . get_inspector_upload_path('inspector').$inspector->id .'/'.$inspector->signature;
        
        $inspector->client_company = $this->clients_model->get($inspector->clientid)->company;
        $inspector->acceptance_date_string = _dt($inspector->acceptance_date);


        try {
            $pdf = inspector_office_pdf($inspector);
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
                            'file_name' => str_replace("SCH", "SCH-UPT", mb_strtoupper(slug_it($inspector_number)) . '.pdf'),
                            'inspector'  => $inspector,
                        ]);

        $pdf->Output($fileNameHookData['file_name'], $type);
    }
}
