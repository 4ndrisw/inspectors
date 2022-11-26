<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Inspectors
Description: Default module for defining inspectors
Version: 1.0.1
Requires at least: 2.3.*
*/

define('INSPECTORS_MODULE_NAME', 'inspectors');
define('INSPECTOR_ATTACHMENTS_FOLDER', 'uploads/inspectors/');

hooks()->add_filter('before_inspector_updated', '_format_data_inspector_feature');
hooks()->add_filter('before_inspector_added', '_format_data_inspector_feature');

hooks()->add_action('after_cron_run', 'inspectors_notification');
hooks()->add_action('admin_init', 'inspectors_module_init_menu_items');
hooks()->add_action('admin_init', 'inspectors_permissions');
hooks()->add_action('admin_init', 'inspectors_settings_tab');
hooks()->add_action('clients_init', 'inspectors_clients_area_menu_items');

hooks()->add_action('staff_member_deleted', 'inspectors_staff_member_deleted');

hooks()->add_filter('migration_tables_to_replace_old_links', 'inspectors_migration_tables_to_replace_old_links');
hooks()->add_filter('global_search_result_query', 'inspectors_global_search_result_query', 10, 3);
hooks()->add_filter('global_search_result_output', 'inspectors_global_search_result_output', 10, 2);
hooks()->add_filter('get_dashboard_widgets', 'inspectors_add_dashboard_widget');
hooks()->add_filter('module_inspectors_action_links', 'module_inspectors_action_links');


function inspectors_add_dashboard_widget($widgets)
{
    /*
    $widgets[] = [
        'path'      => 'inspectors/widgets/inspector_this_week',
        'container' => 'left-8',
    ];
    $widgets[] = [
        'path'      => 'inspectors/widgets/program_not_scheduled',
        'container' => 'left-8',
    ];
    */

    return $widgets;
}


function inspectors_staff_member_deleted($data)
{
    $CI = &get_instance();
    $CI->db->where('staff_id', $data['id']);
    $CI->db->update(db_prefix() . 'inspectors', [
            'staff_id' => $data['transfer_data_to'],
        ]);
}

function inspectors_global_search_result_output($output, $data)
{
    if ($data['type'] == 'inspectors') {
        $output = '<a href="' . admin_url('inspectors/inspector/' . $data['result']['id']) . '">' . format_inspector_number($data['result']['id']) . '</a>';
    }

    return $output;
}

function inspectors_global_search_result_query($result, $q, $limit)
{
    $CI = &get_instance();
    if (has_permission('inspectors', '', 'view')) {

        // inspectors
        $CI->db->select()
           ->from(db_prefix() . 'inspectors')
           ->like(db_prefix() . 'inspectors.formatted_number', $q)->limit($limit);
        
        $result[] = [
                'result'         => $CI->db->get()->result_array(),
                'type'           => 'inspectors',
                'search_heading' => _l('inspectors'),
            ];
        
        if(isset($result[0]['result'][0]['id'])){
            return $result;
        }

        // inspectors
        $CI->db->select()->from(db_prefix() . 'inspectors')->like(db_prefix() . 'clients.company', $q)->or_like(db_prefix() . 'inspectors.formatted_number', $q)->limit($limit);
        $CI->db->join(db_prefix() . 'clients',db_prefix() . 'inspectors.clientid='.db_prefix() .'clients.userid', 'left');
        $CI->db->order_by(db_prefix() . 'clients.company', 'ASC');

        $result[] = [
                'result'         => $CI->db->get()->result_array(),
                'type'           => 'inspectors',
                'search_heading' => _l('inspectors'),
            ];
    }

    return $result;
}

function inspectors_migration_tables_to_replace_old_links($tables)
{
    $tables[] = [
                'table' => db_prefix() . 'inspectors',
                'field' => 'description',
            ];

    return $tables;
}

function inspectors($permissions){
        $item = array(
            'id'         => 8,
            'name'       => _l('inspectors'),
            'short_name' => 'inspectors',
        );
        $permissions[] = $item;
      return $permissions;

}

function inspectors_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
            'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'edit_own'   => _l('permission_edit_own'),
            'delete' => _l('permission_delete'),
    ];

    register_staff_capabilities('inspectors', $capabilities, _l('inspectors'));
}


/**
* Register activation module hook
*/
register_activation_hook(INSPECTORS_MODULE_NAME, 'inspectors_module_activation_hook');

function inspectors_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
* Register deactivation module hook
*/
register_deactivation_hook(INSPECTORS_MODULE_NAME, 'inspectors_module_deactivation_hook');

function inspectors_module_deactivation_hook()
{

     log_activity( 'Hello, world! . inspectors_module_deactivation_hook ' );
}

//hooks()->add_action('deactivate_' . $module . '_module', $function);

/**
* Register language files, must be registered if the module is using languages
*/
register_language_files(INSPECTORS_MODULE_NAME, [INSPECTORS_MODULE_NAME]);

/**
 * Init inspectors module menu items in setup in admin_init hook
 * @return null
 */
function inspectors_module_init_menu_items()
{
    $CI = &get_instance();

    $CI->app->add_quick_actions_link([
            'name'       => _l('inspector'),
            'url'        => 'inspectors',
            'permission' => 'inspectors',
            'icon'     => 'fa-solid fa-building',
            'position'   => 57,
            ]);

    if (has_permission('inspectors', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('inspectors', [
                'slug'     => 'inspectors-tracking',
                'name'     => _l('inspectors'),
                'icon'     => 'fa-solid fa-building',
                'href'     => admin_url('inspectors'),
                'position' => 12,
        ]);
    }
}

function module_inspectors_action_links($actions)
{
    $actions[] = '<a href="' . admin_url('settings?group=inspectors') . '">' . _l('settings') . '</a>';

    return $actions;
}

function inspectors_clients_area_menu_items()
{   
    // Show menu item only if client is logged in
    if (is_client_logged_in() && has_contact_permission('inspectors')) {
        add_theme_menu_item('inspectors', [
                    'name'     => _l('inspectors'),
                    'href'     => site_url('inspectors/list'),
                    'position' => 15,
                    'icon'     => 'fa-solid fa-building',
        ]);
    }
}

/**
 * [perfex_dark_theme_settings_tab net menu item in setup->settings]
 * @return void
 */
function inspectors_settings_tab()
{
    $CI = &get_instance();
    $CI->app_tabs->add_settings_tab('inspectors', [
        'name'     => _l('settings_group_inspectors'),
        //'view'     => module_views_path(INSPECTORS_MODULE_NAME, 'admin/settings/includes/inspectors'),
        'view'     => 'inspectors/inspectors_settings',
        'position' => 51,
        'icon'     => 'fa-solid fa-building',
    ]);
}

$CI = &get_instance();
$CI->load->helper(INSPECTORS_MODULE_NAME . '/inspectors');
if(($CI->uri->segment(1)=='admin' && $CI->uri->segment(2)=='inspectors') || $CI->uri->segment(1)=='inspectors'){
    $CI->app_css->add(INSPECTORS_MODULE_NAME.'-css', base_url('modules/'.INSPECTORS_MODULE_NAME.'/assets/css/'.INSPECTORS_MODULE_NAME.'.css'));
    $CI->app_scripts->add(INSPECTORS_MODULE_NAME.'-js', base_url('modules/'.INSPECTORS_MODULE_NAME.'/assets/js/'.INSPECTORS_MODULE_NAME.'.js'));
}

if(($CI->uri->segment(1)=='admin' && $CI->uri->segment(2)=='staff') && $CI->uri->segment(3)=='edit_provile'){
    $CI->app_css->add(INSPECTORS_MODULE_NAME.'-css', base_url('modules/'.INSPECTORS_MODULE_NAME.'/assets/css/'.INSPECTORS_MODULE_NAME.'.css'));
}

