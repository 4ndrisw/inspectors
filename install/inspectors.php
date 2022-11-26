<?php defined('BASEPATH') or exit('No direct script access allowed');

if (!$CI->db->table_exists(db_prefix() . 'inspectors')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "inspectors` (
      `id` int(11) NOT NULL,
      `sent` tinyint(1) NOT NULL DEFAULT 0,
      `signed` tinyint(1) NOT NULL DEFAULT 0,
      `show_shipping_on_inspector` tinyint(1) NOT NULL DEFAULT 0,
      `datesend` datetime DEFAULT NULL,
      `clientid` int(11) NOT NULL,
      `deleted_customer_name` varchar(100) DEFAULT NULL,
      `program_id` int(11) NOT NULL DEFAULT 0,
      `number` int(11) NOT NULL,
      `prefix` varchar(50) DEFAULT NULL,
      `number_format` int(11) NOT NULL DEFAULT 0,
      `hash` varchar(32) DEFAULT NULL,
      `datecreated` datetime NOT NULL,
      `date` date NOT NULL,
      `expirydate` date DEFAULT NULL,
      `currency` int(11) NOT NULL,
      `subtotal` decimal(15,2) NOT NULL,
      `total_tax` decimal(15,2) NOT NULL DEFAULT 0.00,
      `total` decimal(15,2) NOT NULL,
      `adjustment` decimal(15,2) DEFAULT NULL,
      `addedfrom` int(11) NOT NULL,
      `state` int(11) NOT NULL DEFAULT 1,
      `clientnote` text DEFAULT NULL,
      `adminnote` text DEFAULT NULL,
      `discount_percent` decimal(15,2) DEFAULT 0.00,
      `discount_total` decimal(15,2) DEFAULT 0.00,
      `discount_type` varchar(30) DEFAULT NULL,
      `invoiceid` int(11) DEFAULT NULL,
      `invoiced_date` datetime DEFAULT NULL,
      `terms` text DEFAULT NULL,
      `reference_no` varchar(100) DEFAULT NULL,
      `sale_agent` int(11) NOT NULL DEFAULT 0,
      `billing_street` varchar(200) DEFAULT NULL,
      `billing_city` varchar(100) DEFAULT NULL,
      `billing_state` varchar(100) DEFAULT NULL,
      `billing_zip` varchar(100) DEFAULT NULL,
      `billing_country` int(11) DEFAULT NULL,
      `shipping_street` varchar(200) DEFAULT NULL,
      `shipping_city` varchar(100) DEFAULT NULL,
      `shipping_state` varchar(100) DEFAULT NULL,
      `shipping_zip` varchar(100) DEFAULT NULL,
      `shipping_country` int(11) DEFAULT NULL,
      `include_shipping` tinyint(1) NOT NULL,
      `show_shipping_on_schedule` tinyint(1) NOT NULL DEFAULT 1,
      `show_quantity_as` int(11) NOT NULL DEFAULT 1,
      `pipeline_order` int(11) DEFAULT 1,
      `is_expiry_notified` int(11) NOT NULL DEFAULT 0,
      `acceptance_firstname` varchar(50) DEFAULT NULL,
      `acceptance_lastname` varchar(50) DEFAULT NULL,
      `acceptance_email` varchar(100) DEFAULT NULL,
      `acceptance_date` datetime DEFAULT NULL,
      `acceptance_ip` varchar(40) DEFAULT NULL,
      `signature` varchar(40) DEFAULT NULL,
      `short_link` varchar(100) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'inspectors`
      ADD PRIMARY KEY (`id`),
      ADD UNIQUE( `number`),
      ADD KEY `signed` (`signed`),
      ADD KEY `state` (`state`),
      ADD KEY `clientid` (`clientid`),
      ADD KEY `program_id` (`program_id`)
      ;');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'inspectors`
      MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}
