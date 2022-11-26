<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel_s accounting-template inspector">
   <div class="panel-body">
      <?php if(isset($inspector)){ ?>
      <?php echo format_inspector_state($inspector->active); ?>
      <hr class="hr-panel-heading" />
      <?php } ?>
      <div class="row">
          <?php if (isset($inspector_request_id) && $inspector_request_id != '') {
              echo form_hidden('inspector_request_id',$inspector_request_id);
          }
          ?>
         <div class="col-md-6">
            <div class="f_client_id">
             <div class="form-group name-placeholder">
               <div class="row">
                 <div class="col-md-12">
                    <?php $value = (isset($inspector) ? $inspector->company : ''); ?>
                    <?php $attrs = (isset($inspector) ? array() : array('autofocus' => true)); ?>
                    <?php echo render_input('company', 'inspector', $value, 'text', $attrs); ?>
                    <div id="company_exists_info" class="hide"></div>
                  </div>
                 </div>
              </div>
            </div>

            <?php
               $next_inspector_number = get_option('next_inspector_number');
               $format = get_option('inspector_number_format');

                if(isset($inspector)){
                  $format = $inspector->number_format;
                }

               $prefix = get_option('inspector_prefix');

               if ($format == 1) {
                 $__number = $next_inspector_number;
                 if(isset($inspector)){
                   $__number = $inspector->number;
                   $prefix = '<span id="prefix">' . $inspector->prefix . '</span>';
                 }
               } else if($format == 2) {
                 if(isset($inspector)){
                   $__number = $inspector->number;
                   $prefix = $inspector->prefix;
                   $prefix = '<span id="prefix">'. $prefix . '</span><span id="prefix_year">' . date('Y',strtotime($inspector->dateactivated)).'</span>/';
                 } else {
                   $__number = $next_inspector_number;
                   $prefix = $prefix.'<span id="prefix_year">'.date('Y').'</span>/';
                 }
               } else if($format == 3) {
                  if(isset($inspector)){
                   $yy = date('y',strtotime($inspector->dateactivated));
                   $__number = $inspector->number;
                   $prefix = '<span id="prefix">'. $inspector->prefix . '</span>';
                 } else {
                  $yy = date('y');
                  $__number = $next_inspector_number;
                }
               } else if($format == 4) {
                  if(isset($inspector)){
                   $yyyy = date('Y',strtotime($inspector->dateactivated));
                   $mm = date('m',strtotime($inspector->dateactivated));
                   $__number = $inspector->number;
                   $prefix = '<span id="prefix">'. $inspector->prefix . '</span>';
                 } else {
                  $yyyy = date('Y');
                  $mm = date('m');
                  $__number = $next_inspector_number;
                }
               }
               
               $_inspector_number = str_pad($__number, get_option('number_padding_prefixes'), '0', STR_PAD_LEFT);
               $isedit = isset($inspector) ? 'true' : 'false';
               $data_original_number = isset($inspector) ? $inspector->number : 'false';
               ?>
            <div class="form-group">
               <label for="number"><?php echo _l('inspector_add_edit_number'); ?></label>
               <div class="input-group">
                  <span class="input-group-addon">
                  <?php if(isset($inspector)){ ?>
                  <a href="#" onclick="return false;" data-toggle="popover" data-container='._transaction_form' data-html="true" data-content="<label class='control-label'><?php echo _l('settings_sales_inspector_prefix'); ?></label><div class='input-group'><input name='s_prefix' type='text' class='form-control' value='<?php echo $inspector->prefix; ?>'></div><button type='button' onclick='save_sales_number_settings(this); return false;' data-url='<?php echo admin_url('inspectors/update_number_settings/'.$inspector->userid); ?>' class='btn btn-info btn-block mtop15'><?php echo _l('submit'); ?></button>"><i class="fa fa-cog"></i></a>
                   <?php }
                    echo $prefix;
                  ?>
                 </span>
                  <input type="text" name="number" class="form-control" value="<?php echo $_inspector_number; ?>" data-isedit="<?php echo $isedit; ?>" data-original-number="<?php echo $data_original_number; ?>">
                  <?php if($format == 3) { ?>
                  <span class="input-group-addon">
                     <span id="prefix_year" class="format-n-yy"><?php echo $yy; ?></span>
                  </span>
                  <?php } else if($format == 4) { ?>
                   <span class="input-group-addon">
                     <span id="prefix_month" class="format-mm-yyyy"><?php echo $mm; ?></span>
                     /
                     <span id="prefix_year" class="format-mm-yyyy"><?php echo $yyyy; ?></span>
                  </span>
                  <?php } ?>
               </div>
            </div>

            <div class="row">
               <div class="col-md-12">
                  <?php
                     $i = 0;
                     $selected = '';
                     foreach($institutions_sql as $institution){
                      if(isset($inspector)){
                        if($inspector->institution_id == $institution['userid']) {
                          $selected = $institution['userid'];
                        }
                      }
                      $i++;
                     }
                     echo render_select('institution_id',$institutions_sql,array('userid',array('company')),'institution',$selected);
                  ?>
               </div>
            </div>

            <div class="row">
               <div class="col-md-6">
                  <?php $value = (isset($inspector) ? $inspector->phone : ''); ?>
                  <?php echo render_input('phone', 'client_phonenumber', $value); ?>
               </div>
               <div class="col-md-6">
                  <?php if (get_option('disable_language') == 0) { ?>
                     <div class="form-group select-placeholder">
                        <label for="default_language" class="control-label"><?php echo _l('localization_default_language'); ?>
                        </label>
                        <select name="default_language" id="default_language" class="form-control selectpicker" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                           <option value=""><?php echo _l('system_default_string'); ?></option>
                           <?php foreach ($this->app->get_available_languages() as $availableLanguage) {
                                 $selected = '';
                                 if (isset($inspector)) {
                                    if ($inspector->default_language == $availableLanguage) {
                                       $selected = 'selected';
                                    }
                                 }
                                 ?>
                              <option value="<?php echo $availableLanguage; ?>" <?php echo $selected; ?>><?php echo ucfirst($availableLanguage); ?></option>
                           <?php } ?>
                        </select>
                     </div>
                  <?php } ?>
               </div>
             </div>

            <div class="row">

              <div class="col-md-6">
                 <div class="form-group select-placeholder">
                    <label class="control-label"><?php echo _l('inspector_state'); ?></label>
                    <select class="selectpicker display-block mbot15" name="state" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                       <?php foreach($inspector_states as $state){ ?>
                       <option value="<?php echo $state; ?>" <?php if(isset($inspector) && $inspector->state == $state){echo 'selected';} ?>><?php echo format_inspector_state($state,'',false); ?></option>
                       <?php } ?>
                    </select>
                 </div>
              </div>

            </div>
            <div class="clearfix mbot15"></div>
            <?php $rel_id = (isset($inspector) ? $inspector->userid : false); ?>
            <?php
                  if(isset($custom_fields_rel_transfer)) {
                      $rel_id = $custom_fields_rel_transfer;
                  }
             ?>
            <?php //echo render_custom_fields('inspector',$rel_id); ?>
         </div>
         <div class="col-md-6">
            <div class="no-shadow">
              <div class="row">
                 <div class="col-md-12">
                    <?php $value = (isset($inspector) ? $inspector->address : ''); ?>
                    <?php echo render_textarea('address', 'client_address', $value); ?>
                    <?php $value = (isset($inspector) ? $inspector->city : ''); ?>
                    <?php echo render_input('city', 'client_city', $value); ?>
                    <?php $value = (isset($inspector) ? $inspector->state : ''); ?>
                    <?php echo render_input('state', 'client_state', $value); ?>
                    <?php $value = (isset($inspector) ? $inspector->zip : ''); ?>
                    <?php echo render_input('zip', 'client_postal_code', $value); ?>
                    <?php $countries = get_all_countries();
                    $customer_default_country = get_option('customer_default_country');
                    $selected = (isset($inspector) ? $inspector->country : $customer_default_country);
                    echo render_select('country', $countries, array('country_id', array('short_name')), 'clients_country', $selected, array('data-none-selected-text' => _l('dropdown_non_selected_tex')));
                    ?>
                  
                 </div>
              </div>
            </div>
         </div>
      </div>
   </div>
   <div class="row">
    <div class="col-md-12 mtop5">
      <div class="panel-body bottom-transaction">
        <div class="btn-bottom-toolbar text-right">
          <div class="btn-group dropup">
            <button type="button" class="btn-tr btn btn-info inspector-form-submit transaction-submit">
              <?php echo _l('submit'); ?>
            </button>
          <button type="button"
            class="btn btn-info dropdown-toggle"
            data-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="false">
            <span class="caret"></span>
          </button>
          <ul class="dropdown-menu dropdown-menu-right width200">
            <li>
              <a href="#" class="inspector-form-submit save-and-send transaction-submit">
                <?php echo _l('save_and_send'); ?>
              </a>
            </li>
            <?php if(!isset($inspector)) { ?>
              <li>
                <a href="#" class="inspector-form-submit save-and-send-later transaction-submit">
                  <?php echo _l('save_and_send_later'); ?>
                </a>
              </li>
            <?php } ?>
          </ul>
        </div>
      </div>
    </div>
    <div class="btn-bottom-pusher"></div>
  </div>
</div>
</div>
