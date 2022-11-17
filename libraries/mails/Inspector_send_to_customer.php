<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Inspector_send_to_customer extends App_mail_template
{
    protected $for = 'customer';

    protected $inspector;

    protected $contact;

    public $slug = 'inspector-send-to-client';

    public $rel_type = 'inspector';

    public function __construct($inspector, $contact, $cc = '')
    {
        parent::__construct();

        $this->inspector = $inspector;
        $this->contact = $contact;
        $this->cc      = $cc;
    }

    public function build()
    {
        if ($this->ci->input->post('email_attachments')) {
            $_other_attachments = $this->ci->input->post('email_attachments');
            foreach ($_other_attachments as $attachment) {
                $_attachment = $this->ci->inspectors_model->get_attachments($this->inspector->id, $attachment);
                $this->add_attachment([
                                'attachment' => get_upload_path_by_type('inspector') . $this->inspector->id . '/' . $_attachment->file_name,
                                'filename'   => $_attachment->file_name,
                                'type'       => $_attachment->filetype,
                                'read'       => true,
                            ]);
            }
        }

        $this->to($this->contact->email)
        ->set_rel_id($this->inspector->id)
        ->set_merge_fields('client_merge_fields', $this->inspector->clientid, $this->contact->id)
        ->set_merge_fields('inspector_merge_fields', $this->inspector->id);
    }
}
