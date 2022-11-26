<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div id="stats-top" class="hide">
    <div id="inspectors_total"></div>
    <div class="panel_s">
        <div class="panel-body">
         <div class="_filters _hidden_inputs hidden">
            <?php
            if(isset($inspectors_sale_agents)){
                foreach($inspectors_sale_agents as $agent){
                    echo form_hidden('sale_agent_'.$agent['sale_agent']);
                }
            }
            if(isset($inspector_states)){
                foreach($inspector_states as $_state){
                    $val = '';
                    if($_state == $this->input->get('state')){
                        $val = $_state;
                    }
                    echo form_hidden('inspectors_'.$_state,$val);
                }
            }
            if(isset($inspectors_years)){
                foreach($inspectors_years as $year){
                    echo form_hidden('year_'.$year['year'],$year['year']);
                }
            }
            echo form_hidden('not_sent',$this->input->get('filter'));
            echo form_hidden('program_id');
            echo form_hidden('invoiced');
            echo form_hidden('not_invoiced');
            ?>
        </div>
        <div class="row text-left quick-top-stats">
            <?php foreach($inspector_states as $state){
              $percent_data = get_inspectors_percent_by_state($state, (isset($program) ? $program->id : null));
              ?>
              <div class="col-md-5ths col-xs-12">
                <div class="row">
                    <div class="col-md-7">
                        <a href="#" data-cview="inspectors_<?php echo $state; ?>" onclick="dt_custom_view('inspectors_<?php echo $state; ?>','.table-inspectors','inspectors_<?php echo $state; ?>',true); return false;">
                            <h5><?php echo format_inspector_state($state,'',false); ?></h5>
                        </a>
                    </div>
                    <div class="col-md-5 text-right">
                        <?php echo $percent_data['total_by_state']; ?> / <?php echo $percent_data['total']; ?>
                    </div>
                    <div class="col-md-12">
                        <div class="progress no-margin">
                            <div class="progress-bar progress-bar-<?php echo inspector_state_color_class($state); ?>" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 0%" data-percent="<?php echo $percent_data['percent']; ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</div>
<hr />
</div>
