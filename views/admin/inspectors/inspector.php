<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
	<div class="content">
		<div class="row">
			<?php
			echo form_open($this->uri->uri_string(),array('id'=>'inspector-form','class'=>'_transaction_form'));
			if(isset($inspector)){
				echo form_hidden('isedit');
			}
			?>
			<div class="col-md-12">
				<?php $this->load->view('admin/inspectors/inspector_template'); ?>
			</div>
			<?php echo form_close(); ?>
		</div>
	</div>
</div>
</div>
<?php init_tail(); ?>
<script>
	$(function(){
		validate_inspector_form();
		// Init accountacy currency symbol
		//init_currency();
		// Program ajax search
		//init_ajax_program_search_by_customer_id();
		// Maybe items ajax search
	    //init_ajax_search('items','#item_select.ajax-search',undefined,admin_url+'items/search');
	});
</script>
</body>
</html>
