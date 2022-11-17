<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if(isset($client)){ ?>
	<h4 class="customer-profile-group-heading"><?php echo _l('inspectors'); ?></h4>
	<?php if(has_permission('inspectors','','create')){ ?>
		<a href="<?php echo admin_url('inspectors/inspector?customer_id='.$client->userid); ?>" class="btn btn-info mbot15<?php if($client->active == 0){echo ' disabled';} ?>"><?php echo _l('create_new_inspector'); ?></a>
	<?php } ?>
	<?php if(has_permission('inspectors','','view') || has_permission('inspectors','','view_own') || get_option('allow_staff_view_inspectors_assigned') == '1'){ ?>
		<a href="#" class="btn btn-info mbot15" data-toggle="modal" data-target="#client_zip_inspectors"><?php echo _l('zip_inspectors'); ?></a>
	<?php } ?>
	<div id="inspectors_total"></div>
	<?php
	$this->load->view('admin/inspectors/table_html', array('class'=>'inspectors-single-client'));
	//$this->load->view('admin/clients/modals/zip_inspectors');
	?>
<?php } ?>
