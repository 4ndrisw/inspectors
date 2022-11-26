<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once('install/inspectors.php');
require_once('install/inspector_activity.php');
require_once('install/inspector_items.php');
require_once('install/inspector_members.php');



$CI->db->query("
INSERT INTO `tblemailtemplates` (`type`, `slug`, `language`, `name`, `subject`, `message`, `fromname`, `fromemail`, `plaintext`, `active`, `order`) VALUES
('inspector', 'inspector-send-to-client', 'english', 'Send inspector to Customer', 'inspector # {inspector_number} created', '<span style=\"font-size: 12pt;\">Dear {contact_firstname} {contact_lastname}</span><br /><br /><span style=\"font-size: 12pt;\">Please find the attached inspector <strong># {inspector_number}</strong></span><br /><br /><span style=\"font-size: 12pt;\"><strong>inspector state:</strong> {inspector_state}</span><br /><br /><span style=\"font-size: 12pt;\">You can view the inspector on the following link: <a href=\"{inspector_link}\">{inspector_number}</a></span><br /><br /><span style=\"font-size: 12pt;\">We look forward to your communication.</span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}<br /></span>', '{companyname} | CRM', '', 0, 1, 0),
('inspector', 'inspector-already-send', 'english', 'inspector Already Sent to Customer', 'inspector # {inspector_number} ', '<span style=\"font-size: 12pt;\">Dear {contact_firstname} {contact_lastname}</span><br /> <br /><span style=\"font-size: 12pt;\">Thank you for your inspector request.</span><br /> <br /><span style=\"font-size: 12pt;\">You can view the inspector on the following link: <a href=\"{inspector_link}\">{inspector_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">Please contact us for more information.</span><br /> <br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('inspector', 'inspector-declined-to-staff', 'english', 'inspector Declined (Sent to Staff)', 'Customer Declined inspector', '<span style=\"font-size: 12pt;\">Hi</span><br /> <br /><span style=\"font-size: 12pt;\">Customer ({client_company}) declined inspector with number <strong># {inspector_number}</strong></span><br /> <br /><span style=\"font-size: 12pt;\">You can view the inspector on the following link: <a href=\"{inspector_link}\">{inspector_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('inspector', 'inspector-accepted-to-staff', 'english', 'inspector Accepted (Sent to Staff)', 'Customer Accepted inspector', '<span style=\"font-size: 12pt;\">Hi</span><br /> <br /><span style=\"font-size: 12pt;\">Customer ({client_company}) accepted inspector with number <strong># {inspector_number}</strong></span><br /> <br /><span style=\"font-size: 12pt;\">You can view the inspector on the following link: <a href=\"{inspector_link}\">{inspector_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('inspector', 'inspector-thank-you-to-customer', 'english', 'Thank You Email (Sent to Customer After Accept)', 'Thank for you accepting inspector', '<span style=\"font-size: 12pt;\">Dear {contact_firstname} {contact_lastname}</span><br /> <br /><span style=\"font-size: 12pt;\">Thank for for accepting the inspector.</span><br /> <br /><span style=\"font-size: 12pt;\">We look forward to doing business with you.</span><br /> <br /><span style=\"font-size: 12pt;\">We will contact you as soon as possible.</span><br /> <br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('inspector', 'inspector-expiry-reminder', 'english', 'inspector Expiration Reminder', 'inspector Expiration Reminder', '<p><span style=\"font-size: 12pt;\">Hello {contact_firstname} {contact_lastname}</span><br /><br /><span style=\"font-size: 12pt;\">The inspector with <strong># {inspector_number}</strong> will expire on <strong>{inspector_expirydate}</strong></span><br /><br /><span style=\"font-size: 12pt;\">You can view the inspector on the following link: <a href=\"{inspector_link}\">{inspector_number}</a></span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}</span></p>', '{companyname} | CRM', '', 0, 1, 0),
('inspector', 'inspector-send-to-client', 'english', 'Send inspector to Customer', 'inspector # {inspector_number} created', '<span style=\"font-size: 12pt;\">Dear {contact_firstname} {contact_lastname}</span><br /><br /><span style=\"font-size: 12pt;\">Please find the attached inspector <strong># {inspector_number}</strong></span><br /><br /><span style=\"font-size: 12pt;\"><strong>inspector state:</strong> {inspector_state}</span><br /><br /><span style=\"font-size: 12pt;\">You can view the inspector on the following link: <a href=\"{inspector_link}\">{inspector_number}</a></span><br /><br /><span style=\"font-size: 12pt;\">We look forward to your communication.</span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}<br /></span>', '{companyname} | CRM', '', 0, 1, 0),
('inspector', 'inspector-already-send', 'english', 'inspector Already Sent to Customer', 'inspector # {inspector_number} ', '<span style=\"font-size: 12pt;\">Dear {contact_firstname} {contact_lastname}</span><br /> <br /><span style=\"font-size: 12pt;\">Thank you for your inspector request.</span><br /> <br /><span style=\"font-size: 12pt;\">You can view the inspector on the following link: <a href=\"{inspector_link}\">{inspector_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">Please contact us for more information.</span><br /> <br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('inspector', 'inspector-declined-to-staff', 'english', 'inspector Declined (Sent to Staff)', 'Customer Declined inspector', '<span style=\"font-size: 12pt;\">Hi</span><br /> <br /><span style=\"font-size: 12pt;\">Customer ({client_company}) declined inspector with number <strong># {inspector_number}</strong></span><br /> <br /><span style=\"font-size: 12pt;\">You can view the inspector on the following link: <a href=\"{inspector_link}\">{inspector_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('inspector', 'inspector-accepted-to-staff', 'english', 'inspector Accepted (Sent to Staff)', 'Customer Accepted inspector', '<span style=\"font-size: 12pt;\">Hi</span><br /> <br /><span style=\"font-size: 12pt;\">Customer ({client_company}) accepted inspector with number <strong># {inspector_number}</strong></span><br /> <br /><span style=\"font-size: 12pt;\">You can view the inspector on the following link: <a href=\"{inspector_link}\">{inspector_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('inspector', 'staff-added-as-program-member', 'english', 'Staff Added as Program Member', 'New program assigned to you', '<p>Hi <br /><br />New inspector has been assigned to you.<br /><br />You can view the inspector on the following link <a href=\"{inspector_link}\">inspector__number</a><br /><br />{email_signature}</p>', '{companyname} | CRM', '', 0, 1, 0),
('inspector', 'inspector-accepted-to-staff', 'english', 'inspector Accepted (Sent to Staff)', 'Customer Accepted inspector', '<span style=\"font-size: 12pt;\">Hi</span><br /> <br /><span style=\"font-size: 12pt;\">Customer ({client_company}) accepted inspector with number <strong># {inspector_number}</strong></span><br /> <br /><span style=\"font-size: 12pt;\">You can view the inspector on the following link: <a href=\"{inspector_link}\">{inspector_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0);
");
/*
 *
 */

// Add options for inspectors
add_option('delete_only_on_last_inspector', 1);
add_option('inspector_prefix', 'SCH-');
add_option('next_inspector_number', 1);
add_option('default_inspector_assigned', 9);
add_option('inspector_number_decrement_on_delete', 0);
add_option('inspector_number_format', 4);
add_option('inspector_year', date('Y'));
add_option('exclude_inspector_from_client_area_with_draft_state', 1);
add_option('predefined_clientnote_inspector', '- Staf diatas untuk melakukan riksa uji pada peralatan tersebut.
- Staf diatas untuk membuat dokumentasi riksa uji sesuai kebutuhan.');
add_option('predefined_terms_inspector', '- Pelaksanaan riksa uji harus mengikuti prosedur yang ditetapkan perusahaan pemilik alat.
- Dilarang membuat dokumentasi tanpa seizin perusahaan pemilik alat.
- Dokumen ini diterbitkan dari sistem CRM, tidak memerlukan tanda tangan dari PT. Cipta Mas Jaya');
add_option('inspector_due_after', 1);
add_option('allow_staff_view_inspectors_assigned', 1);
add_option('show_assigned_on_inspectors', 1);
add_option('require_client_logged_in_to_view_inspector', 0);

add_option('show_program_on_inspector', 1);
add_option('inspectors_pipeline_limit', 1);
add_option('default_inspectors_pipeline_sort', 1);
add_option('inspector_accept_identity_confirmation', 1);
add_option('inspector_qrcode_size', '160');
add_option('inspector_send_telegram_message', 0);

add_option('allow_inspector_staff_view_inspectors_in_same_institution', 1);

/*

DROP TABLE `tblinspectors`;
DROP TABLE `tblinspector_activity`, `tblinspector_items`, `tblinspector_members`;
delete FROM `tbloptions` WHERE `name` LIKE '%inspector%';
DELETE FROM `tblemailtemplates` WHERE `type` LIKE 'inspector';



*/