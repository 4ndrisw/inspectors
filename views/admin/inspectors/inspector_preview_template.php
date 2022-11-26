<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php echo form_hidden('_attachment_sale_id',$inspector->userid); ?>
<?php echo form_hidden('_attachment_sale_type','inspector'); ?>
<div class="col-md-12 no-padding">
   <div class="panel_s">
      <div class="panel-body">
         <div class="horizontal-scrollable-tabs preview-tabs-top">
            <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
            <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
            <div class="horizontal-tabs">
               <ul class="nav nav-tabs nav-tabs-horizontal mbot15" role="tablist">
                  <li role="presentation" class="active">
                     <a href="#tab_inspector" aria-controls="tab_inspector" role="tab" data-toggle="tab">
                     <?php echo _l('inspector'); ?>
                     </a>
                  </li>
                  <li role="presentation">
                     <a href="#tab_tasks" onclick="init_rel_tasks_table(<?php echo $inspector->userid; ?>,'inspector'); return false;" aria-controls="tab_tasks" role="tab" data-toggle="tab">
                     <?php echo _l('tasks'); ?>
                     </a>
                  </li>
                  <li role="presentation">
                     <a href="#tab_staffs" onclick="initDataTable('.table-staffs', admin_url + 'inspectors/table_staffs/' + <?php echo $inspector->userid ;?> + '/' + 'inspector', undefined, undefined, undefined,[1,'asc']); return false;" aria-controls="tab_staffs" role="tab" data-toggle="tab">
                     <?php echo _l('inspector_staffs'); ?>
                     <?php
                        $total_staffs = total_rows(db_prefix().'staff',
                          array(
                           'is_not_staff'=>0,
                           //'staff'=>get_staff_user_id(),
                           'client_type'=>'inspector',
                           'client_id'=>$inspector->userid
                           )
                          );
                        if($total_staffs > 0){
                          echo '<span class="badge">'.$total_staffs.'</span>';
                        }
                        ?>
                     </a>
                  </li>
                  <li role="presentation">
                     <a href="#tab_activity" aria-controls="tab_activity" role="tab" data-toggle="tab">
                     <?php echo _l('inspector_view_activity_tooltip'); ?>
                     </a>
                  </li>
                  <li role="presentation">
                     <a href="#tab_reminders" onclick="initDataTable('.table-reminders', admin_url + 'misc/get_reminders/' + <?php echo $inspector->userid ;?> + '/' + 'inspector', undefined, undefined, undefined,[1,'asc']); return false;" aria-controls="tab_reminders" role="tab" data-toggle="tab">
                     <?php echo _l('inspector_reminders'); ?>
                     <?php
                        $total_reminders = total_rows(db_prefix().'reminders',
                          array(
                           'isnotified'=>0,
                           'staff'=>get_staff_user_id(),
                           'rel_type'=>'inspector',
                           'rel_id'=>$inspector->userid
                           )
                          );
                        if($total_reminders > 0){
                          echo '<span class="badge">'.$total_reminders.'</span>';
                        }
                        ?>
                     </a>
                  </li>
                  <li role="presentation" class="tab-separator">
                     <a href="#tab_notes" onclick="get_sales_notes(<?php echo $inspector->userid; ?>,'inspectors'); return false" aria-controls="tab_notes" role="tab" data-toggle="tab">
                     <?php echo _l('inspector_notes'); ?>
                     <span class="notes-total">
                        <?php if($totalNotes > 0){ ?>
                           <span class="badge"><?php echo $totalNotes; ?></span>
                        <?php } ?>
                     </span>
                     </a>
                  </li>
                  <li role="presentation" data-toggle="tooltip" title="<?php echo _l('emails_tracking'); ?>" class="tab-separator">
                     <a href="#tab_emails_tracking" aria-controls="tab_emails_tracking" role="tab" data-toggle="tab">
                     <?php if(!is_mobile()){ ?>
                     <i class="fa fa-envelope-open-o" aria-hidden="true"></i>
                     <?php } else { ?>
                     <?php echo _l('emails_tracking'); ?>
                     <?php } ?>
                     </a>
                  </li>
                  <li role="presentation" data-toggle="tooltip" data-title="<?php echo _l('view_tracking'); ?>" class="tab-separator">
                     <a href="#tab_views" aria-controls="tab_views" role="tab" data-toggle="tab">
                     <?php if(!is_mobile()){ ?>
                     <i class="fa fa-eye"></i>
                     <?php } else { ?>
                     <?php echo _l('view_tracking'); ?>
                     <?php } ?>
                     </a>
                  </li>
                  <li role="presentation" data-toggle="tooltip" data-title="<?php echo _l('toggle_full_view'); ?>" class="tab-separator toggle_view">
                     <a href="#" onclick="small_table_full_view(); return false;">
                     <i class="fa fa-expand"></i></a>
                  </li>
               </ul>
            </div>
         </div>
         <div class="row mtop10">
            <div class="col-md-3">
               <?php echo format_inspector_state($inspector->active,'mtop5');  ?>
            </div>
            <div class="col-md-9">
               <div class="visible-xs">
                  <div class="mtop10"></div>
               </div>
               <div class="pull-right _buttons">
                  <?php if(staff_can('edit', 'inspectors') || staff_can('edit_own', 'inspectors')){ ?>
                  <a href="<?php echo admin_url('inspectors/inspector/'.$inspector->userid); ?>" class="btn btn-default btn-with-tooltip" data-toggle="tooltip" title="<?php echo _l('edit_inspector_tooltip'); ?>" data-placement="bottom"><i class="fa-solid fa-pen-to-square"></i></a>
                  <?php } ?>
                  <div class="btn-group">
                     <a href="#" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa-regular fa-file-pdf"></i><?php if(is_mobile()){echo ' PDF';} ?> <span class="caret"></span></a>
                     <ul class="dropdown-menu dropdown-menu-right">
                        <li class="hidden-xs"><a href="<?php echo admin_url('inspectors/pdf/'.$inspector->userid.'?output_type=I'); ?>"><?php echo _l('view_pdf'); ?></a></li>
                        <li class="hidden-xs"><a href="<?php echo admin_url('inspectors/pdf/'.$inspector->userid.'?output_type=I'); ?>" target="_blank"><?php echo _l('view_pdf_in_new_window'); ?></a></li>
                        <li><a href="<?php echo admin_url('inspectors/pdf/'.$inspector->userid); ?>"><?php echo _l('download'); ?></a></li>
                        <li>
                           <a href="<?php echo admin_url('inspectors/pdf/'.$inspector->userid.'?print=true'); ?>" target="_blank">
                           <?php echo _l('print'); ?>
                           </a>
                        </li>
                     </ul>
                  </div>
                  <?php
                     $_tooltip = _l('inspector_sent_to_email_tooltip');
                     $_tooltip_already_send = '';
                     if($inspector->active == 1){
                        $_tooltip_already_send = _l('inspector_already_send_to_client_tooltip', time_ago($inspector->dateactivated));
                     }
                     ?>

                  <div class="btn-group">
                     <button type="button" class="btn btn-default pull-left dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                     <?php echo _l('more'); ?> <span class="caret"></span>
                     </button>
                     <ul class="dropdown-menu dropdown-menu-right">
                        
                        <?php hooks()->do_action('after_inspector_view_as_client_link', $inspector); ?>
                        
                        <li>
                           <a href="#" data-toggle="modal" data-target="#sales_attach_file"><?php echo _l('invoice_attach_file'); ?></a>
                        </li>

                        <?php if(staff_can('create', 'inspectors')){ ?>
                        <li>
                           <a href="<?php echo admin_url('inspectors/copy/'.$inspector->userid); ?>">
                           <?php echo _l('copy_inspector'); ?>
                           </a>
                        </li>
                        <?php } ?>
                        <?php if(staff_can('delete', 'inspectors')){ ?>
                        <?php
                           if((get_option('delete_only_on_last_inspector') == 1 && is_last_inspector($inspector->userid)) || (get_option('delete_only_on_last_inspector') == 0)){ ?>
                        <li>
                           <a href="<?php echo admin_url('inspectors/delete/'.$inspector->userid); ?>" class="text-danger delete-text _delete"><?php echo _l('delete_inspector_tooltip'); ?></a>
                        </li>
                        <?php
                           }
                           }
                           ?>
                     </ul>
                  </div>
               </div>
            </div>
         </div>
         <div class="clearfix"></div>
         <hr class="hr-panel-heading" />
         <div class="tab-content">
            <div role="tabpanel" class="tab-pane ptop10 active" id="tab_inspector">
               <?php if(isset($inspector->scheduled_email) && $inspector->scheduled_email) { ?>
                     <div class="alert alert-warning">
                        <?php echo _l('invoice_will_be_sent_at', _dt($inspector->scheduled_email->scheduled_at)); ?>
                        <?php if(staff_can('edit', 'inspectors') || $inspector->addedfrom == get_staff_user_id()) { ?>
                           <a href="#"
                           onclick="edit_inspector_scheduled_email(<?php echo $inspector->scheduled_email->id; ?>); return false;">
                           <?php echo _l('edit'); ?>
                        </a>
                     <?php } ?>
                  </div>
               <?php } ?>
               <div id="inspector-preview">
                  <div class="row">
                     <?php if($inspector->active == 4 && !empty($inspector->acceptance_firstname) && !empty($inspector->acceptance_lastname) && !empty($inspector->acceptance_email)){ ?>
                     <div class="col-md-12">
                        <div class="alert alert-info mbot15">
                           <?php echo _l('accepted_identity_info',array(
                              _l('inspector_lowercase'),
                              '<b>'.$inspector->acceptance_firstname . ' ' . $inspector->acceptance_lastname . '</b> (<a href="mailto:'.$inspector->acceptance_email.'">'.$inspector->acceptance_email.'</a>)',
                              '<b>'. _dt($inspector->acceptance_date).'</b>',
                              '<b>'.$inspector->acceptance_ip.'</b>'.(is_admin() ? '&nbsp;<a href="'.admin_url('inspectors/clear_acceptance_info/'.$inspector->userid).'" class="_delete text-muted" data-toggle="tooltip" data-title="'._l('clear_this_information').'"><i class="fa fa-remove"></i></a>' : '')
                              )); ?>
                        </div>
                     </div>
                     <?php } ?>
                     <div class="col-md-6 col-sm-6">
                        <h4 class="bold">
                           <a href="<?php echo admin_url('inspectors/inspector/'.$inspector->userid); ?>">
                           <span id="inspector-number">
                           <?php echo format_inspector_number($inspector->userid); ?>
                           </span>
                           </a>
                        </h4>
                        <address>
                           <?php echo format_inspector_info($inspector); ?>
                        </address>
                     </div>
                     <div class="col-sm-6 text-right mtop15">
                        <address>
                           <?php echo format_institution_info($institution); ?>
                        </address>
                     </div>
                  </div>

               </div>
            </div>
            <div role="tabpanel" class="tab-pane" id="tab_tasks">
               <?php init_relation_tasks_table(array('data-new-rel-id'=>$inspector->userid,'data-new-rel-type'=>'inspector')); ?>
            </div>
            <div role="tabpanel" class="tab-pane" id="tab_staffs">
                <?php if (has_permission('pengguna', '', 'create')) { ?>
                <div class="tw-mb-2 sm:tw-mb-4">
                    <a href="<?php echo admin_url('inspectors/staff/add/'. $inspector->userid); ?>" class="btn btn-primary">
                        <i class="fa-regular fa-plus tw-mr-1"></i>
                        <?php echo _l('new_staff'); ?>
                    </a>
                    <?php echo $inspector->company; ?>
                </div>
                <?php } ?>
               <hr />
               <?php 
               //render_datatable(array( _l( 'staff_description'), _l( 'staff_date'), _l( 'staff_staff'), _l( 'staff_is_notified')), 'staffs'); 

                        $table_data = [
                            _l('staff_dt_name'),
                            _l('staff_dt_email'),
                            _l('staff_dt_last_Login'),
                            _l('staff_dt_active'),
                        ];
                        render_datatable($table_data, 'staffs');
               ?>
               <?php //$this->load->view('admin/includes/modals/staff',array('id'=>$inspector->userid,'name'=>'inspector','member'=>$member,'staff_title'=>_l('inspector_set_staff_title'))); ?>
            </div>
            <div role="tabpanel" class="tab-pane" id="tab_reminders">
               <a href="#" data-toggle="modal" class="btn btn-info" data-target=".reminder-modal-inspector-<?php echo $inspector->userid; ?>"><i class="fa fa-bell-o"></i> <?php echo _l('inspector_set_reminder_title'); ?></a>
               <hr />
               <?php render_datatable(array( _l( 'reminder_description'), _l( 'reminder_date'), _l( 'reminder_staff'), _l( 'reminder_is_notified')), 'reminders'); ?>
               <?php $this->load->view('admin/includes/modals/reminder',array('id'=>$inspector->userid,'name'=>'inspector','members'=>isset($members) ? $members : [],'reminder_title'=>_l('inspector_set_reminder_title'))); ?>
            </div>
            <div role="tabpanel" class="tab-pane" id="tab_emails_tracking">
               <?php
                  $this->load->view('admin/includes/emails_tracking',array(
                     'tracked_emails'=>
                     get_tracked_emails($inspector->userid, 'inspector'))
                  );
                  ?>
            </div>
            <div role="tabpanel" class="tab-pane" id="tab_notes">
               <?php echo form_open(admin_url('inspectors/add_note/'.$inspector->userid),array('id'=>'sales-notes','class'=>'inspector-notes-form')); ?>
               <?php echo render_textarea('description'); ?>
               <div class="text-right">
                  <button type="submit" class="btn btn-info mtop15 mbot15"><?php echo _l('inspector_add_note'); ?></button>
               </div>
               <?php echo form_close(); ?>
               <hr />
               <div class="panel_s mtop20 no-shadow" id="sales_notes_area">
               </div>
            </div>
            <div role="tabpanel" class="tab-pane" id="tab_activity">
               <div class="row">
                  <div class="col-md-12">
                     <div class="activity-feed">
                        <?php foreach($activity as $activity){
                           $_custom_data = false;
                           ?>
                        <div class="feed-item" data-sale-activity-id="<?php echo $activity['id']; ?>">
                           <div class="date">
                              <span class="text-has-action" data-toggle="tooltip" data-title="<?php echo _dt($activity['date']); ?>">
                              <?php echo time_ago($activity['date']); ?>
                              </span>
                           </div>
                           <div class="text">
                              <?php if(is_numeric($activity['staffid']) && $activity['staffid'] != 0){ ?>
                              <a href="<?php echo admin_url('profile/'.$activity["staffid"]); ?>">
                              <?php echo staff_profile_image($activity['staffid'],array('staff-profile-xs-image pull-left mright5'));
                                 ?>
                              </a>
                              <?php } ?>
                              <?php
                                 $additional_data = '';
                                 if(!empty($activity['additional_data'])){
                                  $additional_data = unserialize($activity['additional_data']);
                                  $i = 0;
                                  foreach($additional_data as $data){
                                    if(strpos($data,'<original_active>') !== false){
                                      $original_active = get_string_between($data, '<original_active>', '</original_active>');
                                      $additional_data[$i] = format_inspector_state($original_active,'',false);
                                    } else if(strpos($data,'<new_active>') !== false){
                                      $new_active = get_string_between($data, '<new_active>', '</new_active>');
                                      $additional_data[$i] = format_inspector_state($new_active,'',false);
                                    } else if(strpos($data,'<active>') !== false){
                                      $active = get_string_between($data, '<active>', '</active>');
                                      $additional_data[$i] = format_inspector_state($active,'',false);
                                    } else if(strpos($data,'<custom_data>') !== false){
                                      $_custom_data = get_string_between($data, '<custom_data>', '</custom_data>');
                                      unset($additional_data[$i]);
                                    }
                                    $i++;
                                  }
                                 }
                                 $_formatted_activity = _l($activity['description'],$additional_data);
                                 if($_custom_data !== false){
                                 $_formatted_activity .= '<br />';
                                 $_formatted_activity .= '<p>';
                                 $_formatted_activity .= $_custom_data;
                                 $_formatted_activity .= '</p>';
                                 }
                                 if(!empty($activity['full_name'])){
                                 $_formatted_activity = $activity['full_name'] . ' - ' . $_formatted_activity;
                                 }
                                 echo $_formatted_activity;
                                 if(is_admin()){
                                 echo '<a href="#" class="pull-right text-danger" onclick="delete_sale_activity('.$activity['id'].'); return false;"><i class="fa fa-remove"></i></a>';
                                 }
                                 ?>
                           </div>
                        </div>
                        <?php } ?>
                     </div>
                  </div>
               </div>
            </div>
            <div role="tabpanel" class="tab-pane" id="tab_views">
               <?php
                  $views_activity = get_views_tracking('inspector',$inspector->userid);
                  if(count($views_activity) === 0) {
                     echo '<h4 class="no-mbot">'._l('not_viewed_yet',_l('inspector_lowercase')).'</h4>';
                  }
                  foreach($views_activity as $activity){ ?>
               <p class="text-success no-margin">
                  <?php echo _l('view_date') . ': ' . _dt($activity['date']); ?>
               </p>
               <p class="text-muted">
                  <?php echo _l('view_ip') . ': ' . $activity['view_ip']; ?>
               </p>
               <hr />
               <?php } ?>
            </div>
         </div>
      </div>
   </div>
</div>
<script>
   init_items_sortable(true);
   init_btn_with_tooltips();
   init_datepicker();
   init_selectpicker();
   init_form_reminder();
   init_tabs_scrollable();
   <?php if($send_later) { ?>
      inspector_inspector_send(<?php echo $inspector->userid; ?>);
   <?php } ?>
</script>
<?php //$this->load->view('admin/inspectors/inspector_send_to_client'); ?>
