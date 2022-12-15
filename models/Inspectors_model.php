<?php

use app\services\utilities\Arr;
use app\services\AbstractKanban;
use app\services\inspectors\InspectorsPipeline;

defined('BASEPATH') or exit('No direct script access allowed');

//class Inspectors_model extends App_Model
class Inspectors_model extends Clients_Model
{
    private $states;
    private $contact_columns;

    public function __construct()
    {
        parent::__construct();

        $this->states = hooks()->apply_filters('before_set_inspector_states', [
            3,
            4,
        ]);

        $this->load->model('clients_model');
        $this->contact_columns = hooks()->apply_filters('contact_columns', ['firstname', 'lastname', 'email', 'phonenumber', 'title', 'password', 'send_set_password_email', 'donotsendwelcomeemail', 'permissions', 'direction', 'invoice_emails', 'estimate_emails', 'credit_note_emails', 'contract_emails', 'task_emails', 'program_emails', 'ticket_emails', 'is_primary']);

        $this->load->model(['client_vault_entries_model', 'client_groups_model', 'statement_model']);
    }

    private function check_zero_columns($data)
    {
        if (!isset($data['show_primary_contact'])) {
            $data['show_primary_contact'] = 0;
        }

        if (isset($data['default_currency']) && $data['default_currency'] == '' || !isset($data['default_currency'])) {
            $data['default_currency'] = 0;
        }

        if (isset($data['country']) && $data['country'] == '' || !isset($data['country'])) {
            $data['country'] = 0;
        }

        if (isset($data['billing_country']) && $data['billing_country'] == '' || !isset($data['billing_country'])) {
            $data['billing_country'] = 0;
        }

        if (isset($data['shipping_country']) && $data['shipping_country'] == '' || !isset($data['shipping_country'])) {
            $data['shipping_country'] = 0;
        }

        return $data;
    }

    /**
     * Get unique sale agent for inspectors / Used for filters
     * @return array
     */
    public function get_sale_agents()
    {
        return $this->db->query("SELECT DISTINCT(sale_agent) as sale_agent, CONCAT(firstname, ' ', lastname) as full_name FROM " . db_prefix() . 'inspectors JOIN ' . db_prefix() . 'staff on ' . db_prefix() . 'staff.staffid=' . db_prefix() . 'inspectors.sale_agent WHERE sale_agent != 0')->result_array();
    }

    /**
     * Get client object based on passed clientid if not passed clientid return array of all clients
     * @param  mixed $id    client id
     * @param  array  $where
     * @return mixed
     */
    public function get($id = '', $where = [])
    {
        $this->db->select('*,'. db_prefix() . 'clients.userid as userid,');

        $this->db->join(db_prefix() . 'countries', '' . db_prefix() . 'countries.country_id = ' . db_prefix() . 'clients.country', 'left');
        $this->db->join(db_prefix() . 'contacts', '' . db_prefix() . 'contacts.userid = ' . db_prefix() . 'clients.userid AND is_primary = 1', 'left');

        if ((is_array($where) && count($where) > 0) || (is_string($where) && $where != '')) {
            $this->db->where($where);
        }

        if (is_numeric($id)) {

            $this->db->where(db_prefix() . 'clients.userid', $id);
            $client = $this->db->get(db_prefix() . 'clients')->row();

            if ($client && get_option('company_requires_vat_number_field') == 0) {
                $client->vat = null;
            }

            $this->load->model('email_schedule_model');
            $client->scheduled_email = $this->email_schedule_model->get($id, 'inspector');

            $GLOBALS['client'] = $client;

            return $client;
        }

        $this->db->order_by('company', 'asc');
        $result = $this->db->get(db_prefix() . 'clients')->result_array();
        return $result;
    }

    /**
     * Get inspector states
     * @return array
     */
    public function get_states()
    {
        return $this->states;
    }

    public function clear_signature($id)
    {
        $this->db->select('signature');
        $this->db->where('id', $id);
        $inspector = $this->db->get(db_prefix() . 'inspectors')->row();

        if ($inspector) {
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'inspectors', ['signature' => null]);

            if (!empty($inspector->signature)) {
                unlink(get_upload_path_by_type('inspector') . $id . '/' . $inspector->signature);
            }

            return true;
        }

        return false;
    }

    /**
     * Copy inspector
     * @param mixed $id inspector id to copy
     * @return mixed
     */
    public function copy($id)
    {
        $_inspector                       = $this->get($id);
        $new_inspector_data               = [];
        $new_inspector_data['clientid']   = $_inspector->clientid;
        $new_inspector_data['program_id'] = $_inspector->program_id;
        $new_inspector_data['number']     = get_option('next_inspector_number');
        $new_inspector_data['date']       = _d(date('Y-m-d'));
        $new_inspector_data['expirydate'] = null;

        if ($_inspector->expirydate && get_option('inspector_due_after') != 0) {
            $new_inspector_data['expirydate'] = _d(date('Y-m-d', strtotime('+' . get_option('inspector_due_after') . ' DAY', strtotime(date('Y-m-d')))));
        }

        $new_inspector_data['show_quantity_as'] = $_inspector->show_quantity_as;
        $new_inspector_data['currency']         = $_inspector->currency;
        $new_inspector_data['subtotal']         = $_inspector->subtotal;
        $new_inspector_data['total']            = $_inspector->total;
        $new_inspector_data['adminnote']        = $_inspector->adminnote;
        $new_inspector_data['adjustment']       = $_inspector->adjustment;
        $new_inspector_data['discount_percent'] = $_inspector->discount_percent;
        $new_inspector_data['discount_total']   = $_inspector->discount_total;
        $new_inspector_data['discount_type']    = $_inspector->discount_type;
        $new_inspector_data['terms']            = $_inspector->terms;
        $new_inspector_data['sale_agent']       = $_inspector->sale_agent;
        $new_inspector_data['reference_no']     = $_inspector->reference_no;
        // Since version 1.0.6
        $new_inspector_data['billing_street']   = clear_textarea_breaks($_inspector->billing_street);
        $new_inspector_data['billing_city']     = $_inspector->billing_city;
        $new_inspector_data['billing_state']    = $_inspector->billing_state;
        $new_inspector_data['billing_zip']      = $_inspector->billing_zip;
        $new_inspector_data['billing_country']  = $_inspector->billing_country;
        $new_inspector_data['shipping_street']  = clear_textarea_breaks($_inspector->shipping_street);
        $new_inspector_data['shipping_city']    = $_inspector->shipping_city;
        $new_inspector_data['shipping_state']   = $_inspector->shipping_state;
        $new_inspector_data['shipping_zip']     = $_inspector->shipping_zip;
        $new_inspector_data['shipping_country'] = $_inspector->shipping_country;
        if ($_inspector->include_shipping == 1) {
            $new_inspector_data['include_shipping'] = $_inspector->include_shipping;
        }
        $new_inspector_data['show_shipping_on_inspector'] = $_inspector->show_shipping_on_inspector;
        // Set to unpaid state automatically
        $new_inspector_data['state']     = 1;
        $new_inspector_data['clientnote'] = $_inspector->clientnote;
        $new_inspector_data['adminnote']  = '';
        $new_inspector_data['newitems']   = [];
        $custom_fields_items             = get_custom_fields('items');
        $key                             = 1;
        foreach ($_inspector->items as $item) {
            $new_inspector_data['newitems'][$key]['description']      = $item['description'];
            $new_inspector_data['newitems'][$key]['long_description'] = clear_textarea_breaks($item['long_description']);
            $new_inspector_data['newitems'][$key]['qty']              = $item['qty'];
            $new_inspector_data['newitems'][$key]['unit']             = $item['unit'];
            $new_inspector_data['newitems'][$key]['taxname']          = [];
            $taxes                                                   = get_inspector_item_taxes($item['id']);
            foreach ($taxes as $tax) {
                // tax name is in format TAX1|10.00
                array_push($new_inspector_data['newitems'][$key]['taxname'], $tax['taxname']);
            }
            $new_inspector_data['newitems'][$key]['rate']  = $item['rate'];
            $new_inspector_data['newitems'][$key]['order'] = $item['item_order'];
            foreach ($custom_fields_items as $cf) {
                $new_inspector_data['newitems'][$key]['custom_fields']['items'][$cf['id']] = get_custom_field_value($item['id'], $cf['id'], 'items', false);

                if (!defined('COPY_CUSTOM_FIELDS_LIKE_HANDLE_POST')) {
                    define('COPY_CUSTOM_FIELDS_LIKE_HANDLE_POST', true);
                }
            }
            $key++;
        }
        $id = $this->add($new_inspector_data);
        if ($id) {
            $custom_fields = get_custom_fields('inspector');
            foreach ($custom_fields as $field) {
                $value = get_custom_field_value($_inspector->id, $field['id'], 'inspector', false);
                if ($value == '') {
                    continue;
                }

                $this->db->insert(db_prefix() . 'customfieldsvalues', [
                    'relid'   => $id,
                    'fieldid' => $field['id'],
                    'fieldto' => 'inspector',
                    'value'   => $value,
                ]);
            }

            $tags = get_tags_in($_inspector->id, 'inspector');
            handle_tags_save($tags, $id, 'inspector');

            log_activity('Copied Inspector ' . format_inspector_number($_inspector->id));

            return $id;
        }

        return false;
    }

    /**
     * Performs inspectors totals state
     * @param array $data
     * @return array
     */
    public function get_inspectors_total($data)
    {
        $states            = $this->get_states();
        $has_permission_view = has_permission('inspectors', '', 'view');
        $this->load->model('currencies_model');
        
        $sql = 'SELECT';
        foreach ($states as $inspector_state) {
            $sql .= '(SELECT SUM(total) FROM ' . db_prefix() . 'inspectors WHERE state=' . $inspector_state;
            //$sql .= ' AND currency =' . $this->db->escape_str($currencyid);
            if (isset($data['years']) && count($data['years']) > 0) {
                $sql .= ' AND YEAR(date) IN (' . implode(', ', array_map(function ($year) {
                    return get_instance()->db->escape_str($year);
                }, $data['years'])) . ')';
            } else {
                $sql .= ' AND YEAR(date) = ' . date('Y');
            }
            $sql .= $where;
            $sql .= ') as "' . $inspector_state . '",';
        }

        $sql     = substr($sql, 0, -1);
        $result  = $this->db->query($sql)->result_array();
        $_result = [];
        $i       = 1;
        foreach ($result as $key => $val) {
            foreach ($val as $state => $total) {
                $_result[$i]['total']         = $total;
                $_result[$i]['symbol']        = $currency->symbol;
                $_result[$i]['currency_name'] = $currency->name;
                $_result[$i]['state']        = $state;
                $i++;
            }
        }
        $_result['currencyid'] = $currencyid;

        return $_result;
    }

    /**
     * @param array $_POST data
     * @param client_request is this request from the customer area
     * @return integer Insert ID
     * Add new client to database
     */
    public function add($data, $client_or_lead_convert_request = false)
    {
        $contact_data = [];

        foreach ($this->contact_columns as $field) {
            if (isset($data[$field])) {
                $contact_data[$field] = $data[$field];
                // Phonenumber is also used for the company profile
                if ($field != 'phonenumber') {
                    unset($data[$field]);
                }
            }
        }

        if (isset($data['groups_in'])) {
            $groups_in = $data['groups_in'];
            unset($data['groups_in']);
        }

        $data['datecreated'] = date('Y-m-d H:i:s');
        $data['hash'] = app_generate_hash();

        if (is_staff_logged_in()) {
            $data['addedfrom'] = get_staff_user_id();
        }

        // New filter action
        $data = hooks()->apply_filters('before_inspector_added', $data);
        
        //trigger exception in a "try" block
        try {
            $company_name_exist = $this->check_inspector_name_exist($data['company']);
            if($company_name_exist){
                return;
            }
            $this->db->insert(db_prefix() . 'clients', $data);
        }

        //catch exception
        catch(Exception $e) {
          echo 'Message: ' .$e->getMessage();
        }


        $userid = $this->db->insert_id();
        if ($userid) {
            // Update next inspector number in settings
            $this->db->where('name', 'next_inspector_number');
            $this->db->set('value', 'value+1', false);
            $this->db->update(db_prefix() . 'options');

            $log = 'ID: ' . $userid;

            if ($log == '' && isset($contact_id)) {
                $log = get_contact_full_name($contact_id);
            }

            $isStaff = null;
            if (!is_client_logged_in() && is_staff_logged_in()) {
                $log .= ', From Staff: ' . get_staff_user_id();
                $isStaff = get_staff_user_id();
            }
            $inspector = $this->get($userid);
            if ($inspector->assigned != 0) {
                if ($inspector->assigned != get_staff_user_id()) {
                    $notified = add_notification([
                        'description'     => 'not_inspector_already_created',
                        'touserid'        => get_staff_user_id(),
                        'fromuserid'      => get_staff_user_id(),
                        'link'            => 'inspector/list_inspector/' . $insert_id .'#' . $insert_id,
                        'additional_data' => serialize([
                            $inspector->subject,
                        ]),
                    ]);
                    if ($notified) {
                        pusher_trigger_notification([get_staff_user_id()]);
                    }
                }
            }
            hooks()->do_action('after_inspector_added', $userid);

            log_activity('New Inspector Created [' . $log . ']', $isStaff);
        }

        return $userid;
    }

    /**
     * All surveyor activity
     * @param mixed $id surveyorid
     * @return array
     */
    public function get_inspector_activity($id)
    {
        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'inspector');
        $this->db->order_by('date', 'desc');

        return $this->db->get(db_prefix() . 'surveyor_activity')->result_array();
    }


    /**
     * Add assignment
     * @since  Version 1.0.2
     * @param mixed $data All $_POST data for the assignment
     * @param mixed $id   relid id
     * @return boolean
     */
    public function add_assignment($data, $id)
    {
        if (isset($data['notify_by_email'])) {
            $data['notify_by_email'] = 1;
        } //isset($data['notify_by_email'])
        else {
            $data['notify_by_email'] = 0;
        }
        //$data['date']        = to_sql_date($data['date'], true);
        $data['description'] = nl2br($data['description']);
        $data['creator']     = get_staff_user_id();
        $this->db->insert(db_prefix() . 'assignments', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New assignment Added [' . ucfirst($data['rel_type']) . 'ID: ' . $data['rel_id'] . ' Description: ' . $data['description'] . ']');
            return true;
        } //$insert_id
        return false;
    }

    public function edit_assignment($data, $id)
    {
        if (isset($data['notify_by_email'])) {
            $data['notify_by_email'] = 1;
        } else {
            $data['notify_by_email'] = 0;
        }

        $data['date_issued']        = _d($data['date_issued'], true);
        $data['date_expired']        = _d($data['date_expired'], true);
        $category = get_kelompok_alat($data['category_id']);
        $data['category']           =  $category[0]['name'];
        $data['description'] = nl2br($data['description']);

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'assignments', $data);

        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }
    
    
    /**
     * Get all assignments or 1 assignment if id is passed
     * @since Version 1.0.2
     * @param  mixed $id assignment id OPTIONAL
     * @return array or object
     */
    public function get_assignments($id = '')
    {
        $this->db->join(db_prefix() . 'staff', '' . db_prefix() . 'staff.staffid = ' . db_prefix() . 'assignments.staff', 'left');
        if (is_numeric($id)) {
            $this->db->where(db_prefix() . 'assignments.id', $id);

            return $this->db->get(db_prefix() . 'assignments')->row();
        } //is_numeric($id)
        $this->db->order_by('date_expired', 'desc');

        return $this->db->get(db_prefix() . 'assignments')->result_array();
    }

    /**
     * Remove client assignment from database
     * @since Version 1.0.2
     * @param  mixed $id assignment id
     * @return boolean
     */
    public function delete_assignment($id)
    {
        $assignment = $this->get_assignments($id);
        if ($assignment->creator == get_staff_user_id() || is_admin()) {
            $this->db->where('id', $id);
            $this->db->delete(db_prefix() . 'assignments');
            if ($this->db->affected_rows() > 0) {
                log_activity('assignment Deleted [' . ucfirst($assignment->rel_type) . 'ID: ' . $assignment->id . ' Description: ' . $assignment->description . ']');

                return true;
            } //$this->db->affected_rows() > 0
            return false;
        } //$assignment->creator == get_staff_user_id() || is_admin()
        return false;
    }

    /**
     * @param  integer ID
     * @param  integer Status ID
     * @return boolean
     * Update assignment status Active/Inactive
     */
    public function change_assignment_status($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'assignments', [
            'is_active' => $status,
        ]);

        if ($this->db->affected_rows() > 0) {
            hooks()->do_action('assignment_status_changed', [
                'id'     => $id,
                'status' => $status,
            ]);

            log_activity('assignment Status Changed [ID: ' . $id . ' Status(Active/Inactive): ' . $status . ']');

            // Admin marked assignment
            $this->db->reset_query();
            $this->db->where('id', $id);
            $assignment = $this->db->get(db_prefix() . 'assignments')->row();
            $status_text = 'In active';
            if($status){
                $status_text = 'Active';
            }
            $this->log_assignment_activity($assignment->rel_id, 'assignment_assignment_status_changed', false, serialize([
                '<custom_data>'. 
                _l('assignment') . ' = '. $assignment->assignment_number .'<br />'. 
                'Staff  = '. get_staff_full_name($assignment->staff) .'<br />'. 
                'Status = '. $status_text .'<br />'. 
                _l('description') . ' = '. $assignment->description . 
                '</custom_data>',
            ]));

            return true;
        }

        return false;
    }
}
