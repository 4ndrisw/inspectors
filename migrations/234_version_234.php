<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_234 extends App_module_migration {
    public function up() {
	        $CI = &get_instance();

	        $contacts = db_prefix() . 'contacts';
	        if (!$CI->db->field_exists('inspector_emails', $contacts)) {

	        	$CI->db->query("ALTER TABLE `" . $contacts . "` ADD `inspector_emails` TINYINT(1) DEFAULT '0'  AFTER `ticket_emails`;");

	        }
	}
}