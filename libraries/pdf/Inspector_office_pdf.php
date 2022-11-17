<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once(LIBSPATH . 'pdf/App_pdf.php');

class Inspector_office_pdf extends App_pdf
{
    protected $inspector;

    private $inspector_number;

    public function __construct($inspector, $tag = '')
    {
        $this->load_language($inspector->clientid);

        $inspector                = hooks()->apply_filters('inspector_html_pdf_data', $inspector);
        $GLOBALS['inspector_pdf'] = $inspector;

        parent::__construct();

        $this->tag             = $tag;
        $this->inspector        = $inspector;
        $this->inspector_number = format_inspector_number($this->inspector->id);

        $this->SetTitle(str_replace("SCH", "SCH-UPT", $this->inspector_number));
    }

    public function prepare()
    {

        $this->set_view_vars([
            'status'          => $this->inspector->status,
            'inspector_number' => str_replace("SCH", "SCH-UPT", $this->inspector_number),
            'inspector'        => $this->inspector,
        ]);

        return $this->build();
    }

    protected function type()
    {
        return 'inspector';
    }

    protected function file_path()
    {
        $customPath = APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_inspector_office_pdf.php';
        $actualPath = module_views_path('inspectors','themes/' . active_clients_theme() . '/views/inspectors/inspector_office_pdf.php');

        if (file_exists($customPath)) {
            $actualPath = $customPath;
        }

        return $actualPath;
    }
}
