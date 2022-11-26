<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="mtop15 preview-top-wrapper">
   <div class="row">
      <div class="col-md-3">
         <div class="mbot30">
            <div class="inspector-html-logo">
               <?php echo get_dark_company_logo(); ?>
            </div>
         </div>
      </div>
      <div class="clearfix"></div>
   </div>
   <div class="top" data-sticky data-sticky-class="preview-sticky-header">
      <div class="container preview-sticky-container">
         <div class="row">
            <div class="col-md-12">
               <div class="col-md-3">
                  <h3 class="bold no-mtop inspector-html-number no-mbot">
                     <span class="sticky-visible hide">
                     <?php echo format_inspector_number($inspector->id); ?>
                     </span>
                  </h3>
                  <h4 class="inspector-html-state mtop7">
                     <?php echo format_inspector_state($inspector->state,'',true); ?>
                  </h4>
               </div>
               <div class="col-md-9">         
                  <?php
                     // Is not accepted, declined and expired
                     if ($inspector->state != 4 && $inspector->state != 3 && $inspector->state != 5) {
                       $can_be_accepted = true;
                       if($identity_confirmation_enabled == '0'){
                         echo form_open($this->uri->uri_string(), array('class'=>'pull-right mtop7 action-button'));
                         echo form_hidden('inspector_action', 4);
                         echo '<button type="submit" data-loading-text="'._l('wait_text').'" autocomplete="off" class="btn btn-success action-button accept"><i class="fa fa-check"></i> '._l('clients_accept_inspector').'</button>';
                         echo form_close();
                       } else {
                         echo '<button type="button" id="accept_action" class="btn btn-success mright5 mtop7 pull-right action-button accept"><i class="fa fa-check"></i> '._l('clients_accept_inspector').'</button>';
                       }
                     } else if($inspector->state == 3){
                       if (($inspector->expirydate >= date('Y-m-d') || !$inspector->expirydate) && $inspector->state != 5) {
                         $can_be_accepted = true;
                         if($identity_confirmation_enabled == '0'){
                           echo form_open($this->uri->uri_string(),array('class'=>'pull-right mtop7 action-button'));
                           echo form_hidden('inspector_action', 4);
                           echo '<button type="submit" data-loading-text="'._l('wait_text').'" autocomplete="off" class="btn btn-success action-button accept"><i class="fa fa-check"></i> '._l('clients_accept_inspector').'</button>';
                           echo form_close();
                         } else {
                           echo '<button type="button" id="accept_action" class="btn btn-success mright5 mtop7 pull-right action-button accept"><i class="fa fa-check"></i> '._l('clients_accept_inspector').'</button>';
                         }
                       }
                     }
                     // Is not accepted, declined and expired
                     if ($inspector->state != 4 && $inspector->state != 3 && $inspector->state != 5) {
                       echo form_open($this->uri->uri_string(), array('class'=>'pull-right action-button mright5 mtop7'));
                       echo form_hidden('inspector_action', 3);
                       echo '<button type="submit" data-loading-text="'._l('wait_text').'" autocomplete="off" class="btn btn-default action-button accept"><i class="fa fa-remove"></i> '._l('clients_decline_inspector').'</button>';
                       echo form_close();
                     }
                     ?>
                  <?php echo form_open(site_url('inspectors/pdf/'.$inspector->id), array('class'=>'pull-right action-button')); ?>
                  <button type="submit" name="inspectorpdf" class="btn btn-default action-button download mright5 mtop7" value="inspectorpdf">
                  <i class="fa fa-file-pdf-o"></i>
                  <?php echo _l('clients_invoice_html_btn_download'); ?>
                  </button>
                  <?php echo form_close(); ?>
                  <?php if((is_client_logged_in() && has_contact_permission('inspectors'))  || is_staff_member()){ ?>
                  <a href="<?php echo site_url('clients/inspectors/'); ?>" class="btn btn-default pull-right mright5 mtop7 action-button go-to-portal">
                  <?php echo _l('client_go_to_dashboard'); ?>
                  </a>
                  <?php } ?>
               </div>
            </div>
            <div class="clearfix"></div>
         </div>
      </div>
   </div>
</div>
<div class="clearfix"></div>
<div class="panel_s mtop20">
   <div class="panel-body">
      <div class="col-md-10 col-md-offset-1">
         <div class="row mtop20">
            <div class="col-md-6 col-sm-6 transaction-html-info-col-left">
               <h4 class="bold inspector-html-number"><?php echo format_inspector_number($inspector->id); ?></h4>
               <address class="inspector-html-company-info">
                  <?php echo format_organization_info(); ?>
               </address>
            </div>
            <div class="col-sm-6 text-right transaction-html-info-col-right">
               <span class="bold inspector_to"><?php echo _l('inspector_to'); ?>:</span>
               <address class="inspector-html-customer-billing-info">
                  <?php echo format_customer_info($inspector, 'inspector', 'billing'); ?>
               </address>
               <!-- shipping details -->
               <?php if($inspector->include_shipping == 1 && $inspector->show_shipping_on_inspector == 1){ ?>
               <span class="bold inspector_ship_to"><?php echo _l('ship_to'); ?>:</span>
               <address class="inspector-html-customer-shipping-info">
                  <?php echo format_customer_info($inspector, 'inspector', 'shipping'); ?>
               </address>
               <?php } ?>
            </div>
         </div>
         <div class="row">
            <div class="col-md-6">
               <div class="container-fluid">
                  <?php if(!empty($inspector_members)){ ?>
                     <strong><?= _l('inspector_members') ?></strong>
                     <ul class="inspector_members">
                     <?php 
                        foreach($inspector_members as $member){
                          echo ('<li style="list-style:auto" class="member">' . $member['firstname'] .' '. $member['lastname'] .'</li>');
                         }
                     ?>
                     </ul>
                  <?php } ?>
               </div>
            </div>
            <div class="col-md-6 text-right">
               <p class="no-mbot inspector-html-date">
                  <span class="bold">
                  <?php echo _l('inspector_data_date'); ?>:
                  </span>
                  <?php echo _d($inspector->date); ?>
               </p>
               <?php if(!empty($inspector->expirydate)){ ?>
               <p class="no-mbot inspector-html-expiry-date">
                  <span class="bold"><?php echo _l('inspector_data_expiry_date'); ?></span>:
                  <?php echo _d($inspector->expirydate); ?>
               </p>
               <?php } ?>
               <?php if(!empty($inspector->reference_no)){ ?>
               <p class="no-mbot inspector-html-reference-no">
                  <span class="bold"><?php echo _l('reference_no'); ?>:</span>
                  <?php echo $inspector->reference_no; ?>
               </p>
               <?php } ?>
               <?php if($inspector->program_id != 0 && get_option('show_program_on_inspector') == 1){ ?>
               <p class="no-mbot inspector-html-program">
                  <span class="bold"><?php echo _l('program'); ?>:</span>
                  <?php echo get_program_name_by_id($inspector->program_id); ?>
               </p>
               <?php } ?>
               <?php $pdf_custom_fields = get_custom_fields('inspector',array('show_on_pdf'=>1,'show_on_client_portal'=>1));
                  foreach($pdf_custom_fields as $field){
                    $value = get_custom_field_value($inspector->id,$field['id'],'inspector');
                    if($value == ''){continue;} ?>
               <p class="no-mbot">
                  <span class="bold"><?php echo $field['name']; ?>: </span>
                  <?php echo $value; ?>
               </p>
               <?php } ?>
            </div>
         </div>
         <div class="row">
            <div class="col-md-12">
               <div class="table-responsive">
                  <?php
                     $items = get_inspector_items_table_data($inspector, 'inspector');
                     echo $items->table();
                  ?>
               </div>
            </div>


            <div class="row mtop25">
               <div class="col-md-12">
                  <div class="col-md-6 text-center">
                     <div class="bold"><?php echo get_option('invoice_company_name'); ?></div>
                     <div class="qrcode text-center">
                        <img src="<?php echo site_url('download/preview_image?path='.protected_file_url_by_path(get_inspector_upload_path('inspector').$inspector->id.'/assigned-'.$inspector_number.'.png')); ?>" class="img-responsive center-block inspector-assigned" alt="inspector-<?= $inspector->id ?>">
                     </div>
                     <div class="assigned">
                     <?php if($inspector->assigned != 0 && get_option('show_assigned_on_inspectors') == 1){ ?>
                        <?php echo get_staff_full_name($inspector->assigned); ?>
                     <?php } ?>

                     </div>
                  </div>
                     <div class="col-md-6 text-center">
                       <div class="bold"><?php echo $client_company; ?></div>
                       <?php if(!empty($inspector->signature)) { ?>
                           <div class="bold">
                              <p class="no-mbot"><?php echo _l('inspector_signed_by') . ": {$inspector->acceptance_firstname} {$inspector->acceptance_lastname}"?></p>
                              <p class="no-mbot"><?php echo _l('inspector_signed_date') . ': ' . _dt($inspector->acceptance_date) ?></p>
                              <p class="no-mbot"><?php echo _l('inspector_signed_ip') . ": {$inspector->acceptance_ip}"?></p>
                           </div>
                           <p class="bold"><?php echo _l('document_customer_signature_text'); ?>
                           <?php if($inspector->signed == 1 && has_permission('inspectors','','delete')){ ?>
                              <a href="<?php echo admin_url('inspectors/clear_signature/'.$inspector->id); ?>" data-toggle="tooltip" title="<?php echo _l('clear_signature'); ?>" class="_delete text-danger">
                                 <i class="fa fa-remove"></i>
                              </a>
                           <?php } ?>
                           </p>
                           <div class="customer_signature text-center">
                              <img src="<?php echo site_url('download/preview_image?path='.protected_file_url_by_path(get_inspector_upload_path('inspector').$inspector->id.'/'.$inspector->signature)); ?>" class="img-responsive center-block inspector-signature" alt="inspector-<?= $inspector->id ?>">
                           </div>
                       <?php } ?>
                     </div>
               </div>
            </div>




            <?php if(!empty($inspector->clientnote)){ ?>
            <div class="col-md-12 inspector-html-note">
            <hr />
               <b><?php echo _l('inspector_order'); ?></b><br /><?php echo $inspector->clientnote; ?>
            </div>
            <?php } ?>
            <?php if(!empty($inspector->terms)){ ?>
            <div class="col-md-12 inspector-html-terms-and-conditions">
               <b><?php echo _l('terms_and_conditions'); ?>:</b><br /><?php echo $inspector->terms; ?>
            </div>
            <?php } ?>

         </div>
      </div>
   </div>
</div>
<?php
   if($identity_confirmation_enabled == '1' && $can_be_accepted){
    get_template_part('identity_confirmation_form',array('formData'=>form_hidden('inspector_action',4)));
   }
   ?>
<script>
   $(function(){
     new Sticky('[data-sticky]');
   })
</script>
