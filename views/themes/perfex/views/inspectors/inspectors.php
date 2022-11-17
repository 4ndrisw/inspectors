<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel_s section-heading section-inspectors">
    <div class="panel-body">
        <h4 class="no-margin section-text"><?php echo _l('clients_my_inspectors'); ?></h4>
    </div>
</div>
<div class="panel_s">
    <div class="panel-body">
        <?php get_template_part('inspectors_stats'); ?>
        <hr />
        <?php get_template_part('inspectors_table'); ?>
    </div>
</div>
