<?php
defined('BASEPATH') or exit('No direct script access allowed');

function get_inspector_name_by_id($inspector_id){
    $CI = &get_instance();
        $CI->db->select(['company']);
        $CI->db->where('userid', $inspector_id);
    return $CI->db->get(db_prefix() . 'clients')->row('company');
}


function is_inspector_staff($staff_id){
    $CI = &get_instance();
    $CI->db->select(['staffid']);
    $CI->db->where('client_type', 'inspector');
    $CI->db->where('is_not_staff', 0);
    $CI->db->where('staffid', $staff_id);
    return (bool)$CI->db->get(db_prefix() . 'staff')->result();
}

function get_inspector_id_by_staff_id($staff_id){
    $CI = &get_instance();
        $CI->db->select(['client_id']);
        $CI->db->where('staffid', $staff_id);
    return $CI->db->get(db_prefix() . 'staff')->row('client_id');
}
/*
function get_institution_id_by_staff_id($staff_id){
    $CI = &get_instance();
    $inspector_id = get_inspector_id_by_staff_id($staff_id);
    log_activity('staff_id. '. $staff_id);
    log_activity('inspector_id. '. $inspector_id);
        $CI->db->select('institution_id');
        $CI->db->where('userid', $inspector_id);
    return $CI->db->get(db_prefix() . 'clients')->row('institution_id');
}
*/

function get_institution_id_by_staff_id($staff_id){
    $CI = &get_instance();
    $inspector_id = get_inspector_id_by_staff_id($staff_id);
        $CI->db->select('institution_id');
        $CI->db->join(db_prefix() . 'clients', 'client_id = userid');
        
        $CI->db->where('staffid', $staff_id);

    return $CI->db->get(db_prefix() . 'staff')->row('institution_id');
}

function get_institution_id_by_inspector_id($inspector_id){
    $CI = &get_instance();
        $CI->db->select('institution_id');
        $CI->db->where('userid', $inspector_id);
    return $CI->db->get(db_prefix() . 'clients')->row('institution_id');
}


function get_inspector_staff_data($userid='')
{
    $CI = &get_instance();
        $CI->db->select(['staffid','firstname', 'lastname']);
        $CI->db->where('client_type', 'inspector');
    /*
    if ($userid) {
        $CI->db->where('userid', $userid);
    }
    */
    return $CI->db->get(db_prefix() . 'staff')->result_array();
}

function inspectors_notification()
{
    $CI = &get_instance();
    $CI->load->model('inspectors/inspectors_model');
    $inspectors = $CI->inspectors_model->get('', true);
    /*
    foreach ($inspectors as $goal) {
        $achievement = $CI->inspectors_model->calculate_goal_achievement($goal['id']);

        if ($achievement['percent'] >= 100) {
            if (date('Y-m-d') >= $goal['end_date']) {
                if ($goal['notify_when_achieve'] == 1) {
                    $CI->inspectors_model->notify_staff_members($goal['id'], 'success', $achievement);
                } else {
                    $CI->inspectors_model->mark_as_notified($goal['id']);
                }
            }
        } else {
            // not yet achieved, check for end date
            if (date('Y-m-d') > $goal['end_date']) {
                if ($goal['notify_when_fail'] == 1) {
                    $CI->inspectors_model->notify_staff_members($goal['id'], 'failed', $achievement);
                } else {
                    $CI->inspectors_model->mark_as_notified($goal['id']);
                }
            }
        }
    }
    */
}


/**
 * Function that return inspector item taxes based on passed item id
 * @param  mixed $itemid
 * @return array
 */
function get_inspectors_staff_data($staffid)
{
    $CI = & get_instance();
    $q  = '';
    if ($CI->input->post('q')) {
        $q = $CI->input->post('q');
        $q = trim($q);
    }
    $q = '';
    $data = [];
    //if ($type == 'inspector' || $type == 'inspectors') {
        $where_clients = ''; 
        if ($q) {
            //$where_clients .= '(company LIKE "%' . $CI->db->escape_like_str($q) . '%" ESCAPE \'!\' OR CONCAT(firstname, " ", lastname) LIKE "%' . $CI->db->escape_like_str($q) . '%" ESCAPE \'!\' OR email LIKE "%' . $CI->db->escape_like_str($q) . '%" ESCAPE \'!\') AND ' . db_prefix() . 'clients.active = 1';
            $where_clients .= '(CONCAT(firstname, " ", lastname) LIKE "%' . $CI->db->escape_like_str($q) . '%" ESCAPE \'!\' OR email LIKE "%' . $CI->db->escape_like_str($q) . '%" ESCAPE \'!\') AND ' . db_prefix() . 'staff.active = 1';
            $where_clients .= ' AND client_types = 1';
        }
    //}

    $data = $CI->staff_model->get($staffid, $where_clients);
    return $data;
}


/**
 * Ger relation values eq invoice number or inspector_staff name etc based on passed relation parsed results
 * from function get_customer_data
 * $relation can be object or array
 * @param  mixed $relation
 * @param  string $type
 * @return mixed
 */
function get_inspector_values($relation, $type)
{
    if ($relation == '') {
        return [
            'name'      => '',
            'id'        => '',
            'link'      => '',
            'addedfrom' => 0,
            'subtext'   => '',
            ];
    }

    $addedfrom = 0;
    $name      = '';
    $id        = '';
    $link      = '';
    $subtext   = '';

    //if ($type == 'staff') {
        if (is_array($relation)) {
            $id   = $relation['staffid'];
            $name = $relation['firstname'] . ' ' . $relation['lastname'];
        } else {
            $id   = $relation->staffid;
            $name = $relation->firstname . ' ' . $relation->lastname;
        }
        $link = admin_url('profile/' . $id);
    //}

    return hooks()->apply_filters('relation_values', [
        'id'        => $id,
        'name'      => $name,
        'link'      => $link,
        'addedfrom' => $addedfrom,
        'subtext'   => $subtext,
        'type'      => $type,
        ]);
}

/**
 * Get Inspector short_url
 * @since  Version 2.7.3
 * @param  object $inspector
 * @return string Url
 */
function get_inspector_shortlink($inspector)
{
    $long_url = site_url("inspector/{$inspector->id}/{$inspector->hash}");
    if (!get_option('bitly_access_token')) {
        return $long_url;
    }

    // Check if inspector has short link, if yes return short link
    if (!empty($inspector->short_link)) {
        return $inspector->short_link;
    }

    // Create short link and return the newly created short link
    $short_link = app_generate_short_link([
        'long_url'  => $long_url,
        'title'     => format_inspector_number($inspector->id)
    ]);

    if ($short_link) {
        $CI = &get_instance();
        $CI->db->where('id', $inspector->id);
        $CI->db->update(db_prefix() . 'clients', [
            'short_link' => $short_link
        ]);
        return $short_link;
    }
    return $long_url;
}

/**
 * Check inspector restrictions - hash, clientid
 * @param  mixed $id   inspector id
 * @param  string $hash inspector hash
 */
function check_inspector_restrictions($id, $hash)
{
    $CI = &get_instance();
    $CI->load->model('inspectors_model');
    if (!$hash || !$id) {
        show_404();
    }
    if (!is_client_logged_in() && !is_staff_logged_in()) {
        if (get_option('view_inspector_only_logged_in') == 1) {
            redirect_after_login_to_current_url();
            redirect(site_url('authentication/login'));
        }
    }
    $inspector = $CI->inspectors_model->get($id);
    if (!$inspector || ($inspector->hash != $hash)) {
        show_404();
    }
    // Do one more check
    if (!is_staff_logged_in()) {
        if (get_option('view_inspector_only_logged_in') == 1) {
            if ($inspector->clientid != get_client_user_id()) {
                show_404();
            }
        }
    }
}

/**
 * Check if inspector email template for expiry reminders is enabled
 * @return boolean
 */
function is_inspectors_email_expiry_reminder_enabled()
{
    return total_rows(db_prefix() . 'emailtemplates', ['slug' => 'inspector-expiry-reminder', 'active' => 1]) > 0;
}

/**
 * Check if there are sources for sending inspector expiry reminders
 * Will be either email or SMS
 * @return boolean
 */
function is_inspectors_expiry_reminders_enabled()
{
    return is_inspectors_email_expiry_reminder_enabled() || is_sms_trigger_active(SMS_TRIGGER_INSPECTOR_EXP_REMINDER);
}

/**
 * Return RGBa inspector state color for PDF documents
 * @param  mixed $state_id current inspector state
 * @return string
 */
function inspector_state_color_pdf($state_id)
{
    if ($state_id == 1) {
        $stateColor = '119, 119, 119';
    } elseif ($state_id == 2) {
        // Sent
        $stateColor = '3, 169, 244';
    } elseif ($state_id == 3) {
        //Declines
        $stateColor = '252, 45, 66';
    } elseif ($state_id == 4) {
        //Accepted
        $stateColor = '0, 191, 54';
    } else {
        // Expired
        $stateColor = '255, 111, 0';
    }

    return hooks()->apply_filters('inspector_state_pdf_color', $stateColor, $state_id);
}

/**
 * Format inspector state
 * @param  integer  $state
 * @param  string  $classes additional classes
 * @param  boolean $label   To include in html label or not
 * @return mixed
 */
function format_inspector_state($state, $classes = '', $label = true)
{
    $id          = $state;
    $label_class = inspector_state_color_class($state);
    $state      = inspector_state_by_id($state);
    if ($label == true) {
        return '<span class="label label-' . $label_class . ' ' . $classes . ' s-state inspector-state-' . $id . ' inspector-state-' . $label_class . '">' . $state . '</span>';
    }

    return $state;
}

/**
 * Return inspector state translated by passed state id
 * @param  mixed $id inspector state id
 * @return string
 */
function inspector_state_by_id($id)
{
    $state = '';
    if ($id == 1) {
        $state = _l('inspector_state_draft');
    } elseif ($id == 2) {
        $state = _l('inspector_state_sent');
    } elseif ($id == 3) {
        $state = _l('inspector_state_declined');
    } elseif ($id == 4) {
        $state = _l('inspector_state_accepted');
    } elseif ($id == 5) {
        // state 5
        $state = _l('inspector_state_expired');
    } else {
        if (!is_numeric($id)) {
            if ($id == 'not_sent') {
                $state = _l('not_sent_indicator');
            }
        }
    }

    return hooks()->apply_filters('inspector_state_label', $state, $id);
}

/**
 * Return inspector state color class based on twitter bootstrap
 * @param  mixed  $id
 * @param  boolean $replace_default_by_muted
 * @return string
 */
function inspector_state_color_class($id, $replace_default_by_muted = false)
{
    $class = '';
    if ($id == 1) {
        $class = 'default';
        if ($replace_default_by_muted == true) {
            $class = 'muted';
        }
    } elseif ($id == 2) {
        $class = 'info';
    } elseif ($id == 3) {
        $class = 'danger';
    } elseif ($id == 4) {
        $class = 'success';
    } elseif ($id == 5) {
        // state 5
        $class = 'warning';
    } else {
        if (!is_numeric($id)) {
            if ($id == 'not_sent') {
                $class = 'default';
                if ($replace_default_by_muted == true) {
                    $class = 'muted';
                }
            }
        }
    }

    return hooks()->apply_filters('inspector_state_color_class', $class, $id);
}

/**
 * Check if the inspector id is last invoice
 * @param  mixed  $id inspectorid
 * @return boolean
 */
function is_last_inspector($id)
{
    $CI = &get_instance();
    $CI->db->select('userid')->from(db_prefix() . 'clients')->order_by('userid', 'desc')->limit(1);
    $query            = $CI->db->get();
    $last_inspector_id = $query->row()->userid;
    if ($last_inspector_id == $id) {
        return true;
    }

    return false;
}

/**
 * Format inspector number based on description
 * @param  mixed $id
 * @return string
 */
function format_inspector_number($id)
{
    $CI = &get_instance();
    $CI->db->select('datecreated,number,prefix,number_format')->from(db_prefix() . 'clients')->where('userid', $id);
    $inspector = $CI->db->get()->row();

    if (!$inspector) {
        return '';
    }

    $number = inspector_number_format($inspector->number, $inspector->number_format, $inspector->prefix, $inspector->datecreated);

    return hooks()->apply_filters('format_inspector_number', $number, [
        'userid'       => $id,
        'inspector' => $inspector,
    ]);
}


function inspector_number_format($number, $format, $applied_prefix, $date)
{
    $originalNumber = $number;
    $prefixPadding  = get_option('number_padding_prefixes');

    if ($format == 1) {
        // Number based
        $number = $applied_prefix . str_pad($number, $prefixPadding, '0', STR_PAD_LEFT);
    } elseif ($format == 2) {
        // Year based
        $number = $applied_prefix . date('Y', strtotime($date)) . '.' . str_pad($number, $prefixPadding, '0', STR_PAD_LEFT);
    } elseif ($format == 3) {
        // Number-yy based
        $number = $applied_prefix . str_pad($number, $prefixPadding, '0', STR_PAD_LEFT) . '-' . date('y', strtotime($date));
    } elseif ($format == 4) {
        // Number-mm-yyyy based
        $number = $applied_prefix . str_pad($number, $prefixPadding, '0', STR_PAD_LEFT) . '.' . date('m', strtotime($date)) . '.' . date('Y', strtotime($date));
    }

    return hooks()->apply_filters('inspector_number_format', $number, [
        'format'         => $format,
        'date'           => $date,
        'number'         => $originalNumber,
        'prefix_padding' => $prefixPadding,
    ]);
}

/**
 * Calculate inspectors percent by state
 * @param  mixed $state          inspector state
 * @return array
 */
function get_inspectors_percent_by_state($state, $inspector_staff_id = null)
{
    $has_permission_view = has_permission('inspectors', '', 'view');
    $where               = '';

    if (isset($inspector_staff_id)) {
        $where .= 'inspector_staff_id=' . get_instance()->db->escape_str($inspector_staff_id) . ' AND ';
    }
    if (!$has_permission_view) {
        $where .= get_inspectors_where_sql_for_staff(get_staff_user_id());
    }

    $where = trim($where);

    if (endsWith($where, ' AND')) {
        $where = substr_replace($where, '', -3);
    }

    $total_inspectors = total_rows(db_prefix() . 'clients', $where);

    $data            = [];
    $total_by_state = 0;

    if (!is_numeric($state)) {
        if ($state == 'not_sent') {
            $total_by_state = total_rows(db_prefix() . 'clients', 'sent=0 AND state NOT IN(2,3,4)' . ($where != '' ? ' AND (' . $where . ')' : ''));
        }
    } else {
        $whereByStatus = 'state=' . $state;
        if ($where != '') {
            $whereByStatus .= ' AND (' . $where . ')';
        }
        $total_by_state = total_rows(db_prefix() . 'clients', $whereByStatus);
    }

    $percent                 = ($total_inspectors > 0 ? number_format(($total_by_state * 100) / $total_inspectors, 2) : 0);
    $data['total_by_state'] = $total_by_state;
    $data['percent']         = $percent;
    $data['total']           = $total_inspectors;

    return $data;
}

function get_inspectors_where_sql_for_staff($staff_id)
{
    $CI = &get_instance();
    $has_permission_view_own             = has_permission('inspectors', '', 'view_own');
    $allow_staff_view_inspectors_assigned = get_option('allow_staff_view_inspectors_assigned');
    $whereUser                           = '';
    if ($has_permission_view_own) {
        $whereUser = '((' . db_prefix() . 'inspectors.addedfrom=' . $CI->db->escape_str($staff_id) . ' AND ' . db_prefix() . 'inspectors.addedfrom IN (SELECT staff_id FROM ' . db_prefix() . 'staff_permissions WHERE feature = "inspectors" AND capability="view_own"))';
        if ($allow_staff_view_inspectors_assigned == 1) {
            $whereUser .= ' OR assigned=' . $CI->db->escape_str($staff_id);
        }
        $whereUser .= ')';
    } else {
        $whereUser .= 'assigned=' . $CI->db->escape_str($staff_id);
    }

    return $whereUser;
}
/**
 * Check if staff member have assigned inspectors / added as sale agent
 * @param  mixed $staff_id staff id to check
 * @return boolean
 */
function staff_has_assigned_inspectors($staff_id = '')
{
    $CI       = &get_instance();
    $staff_id = is_numeric($staff_id) ? $staff_id : get_staff_user_id();
    $cache    = $CI->app_object_cache->get('staff-total-assigned-inspectors-' . $staff_id);

    if (is_numeric($cache)) {
        $result = $cache;
    } else {
        $result = total_rows(db_prefix() . 'clients', ['assigned' => $staff_id]);
        $CI->app_object_cache->add('staff-total-assigned-inspectors-' . $staff_id, $result);
    }

    return $result > 0 ? true : false;
}
/**
 * Check if staff member can view inspector
 * @param  mixed $id inspector id
 * @param  mixed $staff_id
 * @return boolean
 */
function user_can_view_inspector($id, $staff_id = false)
{
    $CI = &get_instance();

    $staff_id = $staff_id ? $staff_id : get_staff_user_id();

    if (has_permission('inspectors', $staff_id, 'view')) {
        return true;
    }

    if(is_client_logged_in()){

        $CI = &get_instance();
        $CI->load->model('inspectors_model');
       
        $inspector = $CI->inspectors_model->get($id);
        if (!$inspector) {
            show_404();
        }
        // Do one more check
        if (get_option('view_inspectort_only_logged_in') == 1) {
            if ($inspector->clientid != get_client_user_id()) {
                show_404();
            }
        }
    
        return true;
    }
    
    $CI->db->select('userid, addedfrom, assigned');
    $CI->db->from(db_prefix() . 'clients');
    $CI->db->where('userid', $id);
    $inspector = $CI->db->get()->row();

    if ((has_permission('inspectors', $staff_id, 'view_own') && $inspector->addedfrom == $staff_id)
        || ($inspector->assigned == $staff_id && get_option('allow_staff_view_inspectors_assigned') == '1')
    ) {
        return true;
    }

    return false;
}


/**
 * Prepare general inspector pdf
 * @since  Version 1.0.2
 * @param  object $inspector inspector as object with all necessary fields
 * @param  string $tag tag for bulk pdf exporter
 * @return mixed object
 */
function inspector_pdf($inspector, $tag = '')
{
    return app_pdf('inspector',  module_libs_path(INSPECTORS_MODULE_NAME) . 'pdf/Inspector_pdf', $inspector, $tag);
}



/**
 * Get items table for preview
 * @param  object  $transaction   e.q. invoice, inspector from database result row
 * @param  string  $type          type, e.q. invoice, inspector, proposal
 * @param  string  $for           where the items will be shown, html or pdf
 * @param  boolean $admin_preview is the preview for admin area
 * @return object
 */
function get_inspector_items_table_data($transaction, $type, $for = 'html', $admin_preview = false)
{
    include_once(module_libs_path(INSPECTORS_MODULE_NAME) . 'Inspector_items_table.php');

    $class = new Inspector_items_table($transaction, $type, $for, $admin_preview);

    $class = hooks()->apply_filters('items_table_class', $class, $transaction, $type, $for, $admin_preview);

    if (!$class instanceof App_items_table_template) {
        show_error(get_class($class) . ' must be instance of "Inspector_items_template"');
    }

    return $class;
}



/**
 * Add new item do database, used for proposals,inspectors,credit notes,invoices
 * This is repetitive action, that's why this function exists
 * @param array $item     item from $_POST
 * @param mixed $rel_id   relation id eq. invoice id
 * @param string $rel_type relation type eq invoice
 */
function add_new_inspector_item_post($item, $rel_id, $rel_type)
{

    $CI = &get_instance();

    $CI->db->insert(db_prefix() . 'itemable', [
                    'description'      => $item['description'],
                    'long_description' => nl2br($item['long_description']),
                    'qty'              => $item['qty'],
                    'rel_id'           => $rel_id,
                    'rel_type'         => $rel_type,
                    'item_order'       => $item['order'],
                    'unit'             => isset($item['unit']) ? $item['unit'] : 'unit',
                ]);

    $id = $CI->db->insert_id();

    return $id;
}

/**
 * Prepares email template preview $data for the view
 * @param  string $template    template class name
 * @param  mixed $customer_id_or_email customer ID to fetch the primary contact email or email
 * @return array
 */
function inspector_mail_preview_data($template, $customer_id_or_email, $mailClassParams = [])
{
    $CI = &get_instance();

    if (is_numeric($customer_id_or_email)) {
        $contact = $CI->clients_model->get_contact(get_primary_contact_user_id($customer_id_or_email));
        $email   = $contact ? $contact->email : '';
    } else {
        $email = $customer_id_or_email;
    }

    $CI->load->model('emails_model');

    $data['template'] = $CI->app_mail_template->prepare($email, $template);
    $slug             = $CI->app_mail_template->get_default_property_value('slug', $template, $mailClassParams);

    $data['template_name'] = $slug;

    $template_result = $CI->emails_model->get(['slug' => $slug, 'language' => 'english'], 'row');

    $data['template_system_name'] = $template_result->name;
    $data['template_id']          = $template_result->emailtemplateid;

    $data['template_disabled'] = $template_result->active == 0;

    return $data;
}


/**
 * Function that return full path for upload based on passed type
 * @param  string $type
 * @return string
 */
function get_inspector_upload_path($type=NULL)
{
   $type = 'inspector';
   $path = INSPECTOR_ATTACHMENTS_FOLDER;
   
    return hooks()->apply_filters('get_upload_path_by_type', $path, $type);
}

/**
 * Remove and format some common used data for the inspector feature eq invoice,inspectors etc..
 * @param  array $data $_POST data
 * @return array
 */
function _format_data_inspector_feature($data)
{
    foreach (_get_inspector_feature_unused_names() as $u) {
        if (isset($data['data'][$u])) {
            unset($data['data'][$u]);
        }
    }

    if (isset($data['data']['date'])) {
        $data['data']['date'] = to_sql_date($data['data']['date']);
    }

    if (isset($data['data']['open_till'])) {
        $data['data']['open_till'] = to_sql_date($data['data']['open_till']);
    }

    if (isset($data['data']['expirydate'])) {
        $data['data']['expirydate'] = to_sql_date($data['data']['expirydate']);
    }

    if (isset($data['data']['duedate'])) {
        $data['data']['duedate'] = to_sql_date($data['data']['duedate']);
    }

    if (isset($data['data']['clientnote'])) {
        $data['data']['clientnote'] = nl2br_save_html($data['data']['clientnote']);
    }

    if (isset($data['data']['terms'])) {
        $data['data']['terms'] = nl2br_save_html($data['data']['terms']);
    }

    if (isset($data['data']['adminnote'])) {
        $data['data']['adminnote'] = nl2br($data['data']['adminnote']);
    }

    foreach (['country', 'billing_country', 'shipping_country', 'inspector_staff_id', 'assigned'] as $should_be_zero) {
        if (isset($data['data'][$should_be_zero]) && $data['data'][$should_be_zero] == '') {
            $data['data'][$should_be_zero] = 0;
        }
    }

    return $data;
}


if (!function_exists('format_inspector_info')) {
    /**
     * Format inspector info format
     * @param  object $inspector inspector from database
     * @param  string $for      where this info will be used? Admin area, HTML preview?
     * @return string
     */
    function format_inspector_info($inspector, $for = '')
    {
        $format = get_option('company_info_format');
        $countryCode = '';
        $countryName = '';
        
        if ($country = get_country($inspector->country)) {
            $countryCode = $country->iso2;
            $countryName = $country->short_name;
        }
        
        $inspectorTo = '<b>' . $inspector->company . '</b>';

        if ($for == 'admin') {
            $hrefAttrs = '';
            $hrefAttrs = ' href="' . admin_url('clients/client/' . $inspector->userid) . '" data-toggle="tooltip" data-title="' . _l('client') . '"';
            $inspectorTo = '<a' . $hrefAttrs . '>' . $inspectorTo . '</a>';
        }

        if ($for == 'html' || $for == 'admin') {
            $phone = '<a href="tel:' . $inspector->phone . '">' . $inspector->phone . '</a>';
            $email = '<a href="mailto:' . $inspector->email . '">' . $inspector->email . '</a>';
        }

        $format = _info_format_replace('company_name', $inspectorTo, $format);
        $format = _info_format_replace('address', $inspector->address . ' ' . $inspector->city, $format);

        $format = _info_format_replace('city', NULL, $format);
        $format = _info_format_replace('state', $inspector->state . ' ' . $inspector->zip, $format);

        $format = _info_format_replace('country_code', $countryCode, $format);
        $format = _info_format_replace('country_name', $countryName, $format);

        $format = _info_format_replace('zip_code', '', $format);
        $format = _info_format_replace('vat_number_with_label', '', $format);

        $whereCF = [];
        if (is_custom_fields_for_customers_portal()) {
            $whereCF['show_on_client_portal'] = 1;
        }
        $customFieldsProposals = get_custom_fields('inspector', $whereCF);

        foreach ($customFieldsProposals as $field) {
            $value  = get_custom_field_value($inspector->id, $field['id'], 'inspector');
            $format = _info_format_custom_field($field['id'], $field['name'], $value, $format);
        }

        // If no custom fields found replace all custom fields merge fields to empty
        $format = _info_format_custom_fields_check($customFieldsProposals, $format);
        $format = _maybe_remove_first_and_last_br_tag($format);

        // Remove multiple white spaces
        $format = preg_replace('/\s+/', ' ', $format);
        $format = trim($format);

        return hooks()->apply_filters('inspector_info_text', $format, ['inspector' => $inspector, 'for' => $for]);
    }
}

/**
 * Unsed $_POST request names, mostly they are used as helper inputs in the form
 * The top function will check all of them and unset from the $data
 * @return array
 */
function _get_inspector_feature_unused_names()
{
    return [
        'taxname', 'description',
        'currency_symbol', 'price',
        'isedit', 'taxid',
        'long_description', 'unit',
        'rate', 'quantity',
        'item_select', 'tax',
        'billed_tasks', 'billed_expenses',
        'task_select', 'task_id',
        'expense_id', 'repeat_every_custom',
        'repeat_type_custom', 'bill_expenses',
        'save_and_send', 'merge_current_invoice',
        'cancel_merged_invoices', 'invoices_to_merge',
        'tags', 's_prefix', 'save_and_record_payment',
    ];
}

/**
 * Check if customer has inspector_staff assigned
 * @param  mixed $customer_id customer id to check
 * @return boolean
 */
function inspector_staff_has_inspectors($inspector_staff_id)
{
    $totalProgramsInspectord = total_rows(db_prefix() . 'clients', 'inspector_staff_id=' . get_instance()->db->escape_str($inspector_staff_id));

    return ($totalProgramsInspectord > 0 ? true : false);
}


function is_staff_related_to_inspector($client_id){
    $CI = &get_instance();
    $CI->db->where('staffid', get_staff_user_id());
    $CI->db->where('client_id', $client_id);
    $result = $CI->db->get(db_prefix() . 'staff')->result();
    if (count($result) > 0) {
        return true;
    }

    return false;    
}




function get_inspector_staff_data_ajax($searchTerm = '')
{
    $CI = &get_instance();
    
    $CI->db->select('staffid, CONCAT(firstname, '.', lastname) AS name', FALSE);
    $CI->db->from( db_prefix() .'staff');
    $CI->db->where('client_type', 'inspector');    
    if($searchTerm){
        $CI->db->like('name', $searchTerm);
    }
    $CI->db->order_by('name', 'ASC');

    return $CI->db->get()->result();
}


/**
 * Get inspector_staff name by passed id
 * @param  mixed $id
 * @return string
 */
function get_inspector_staff_name_by_id($id)
{
    $CI      = & get_instance();
    $inspector_staff = $CI->app_object_cache->get('inspector-staff-name-data-' . $id);

    if (!$inspector_staff) {
        $CI->db->select('name');
        $CI->db->where('id', $id);
        $inspector_staff = $CI->db->get(db_prefix() . 'inspector_staffs')->row();
        $CI->app_object_cache->add('inspector_staff-name-data-' . $id, $inspector_staff);
    }

    if ($inspector_staff) {
        return $inspector_staff->name;
    }

    return '';
}