<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php echo form_hidden('rel_id',$id); ?>
<?php echo form_hidden('rel_type',$name); ?>
<?php //echo render_datetime_input('date','set_assignment_date','',array('data-date-min-date'=>_d(date('Y-m-d')), 'data-step'=>30)); ?>
<?php echo render_input('assignment_number', 'inspector_assignment_number',); ?>
<?php echo render_date_input('date_issued','assignment_date_issued'); ?>
<?php echo render_date_input('date_expired','assignment_date_expired'); ?>
<?php echo render_select('category_id',$categories,array('id',array('name')),'assignment_category','category_id',array('data-current-staff'=>get_staff_user_id())); ?>
<?php echo render_select('staff',$members,array('staffid',array('firstname','lastname')),'assignment_set_to',get_staff_user_id(),array('data-current-staff'=>get_staff_user_id())); ?>
<?php echo render_textarea('description','assignment_description'); ?>
<?php if(is_email_template_active('assignment-email-staff')) { ?>
  <div class="form-group">
    <div class="checkbox checkbox-primary">
      <input type="checkbox" name="notify_by_email" id="notify_by_email">
      <label for="notify_by_email"><?php echo _l('assignment_notify_me_by_email'); ?></label>
    </div>
  </div>
<?php } ?>
