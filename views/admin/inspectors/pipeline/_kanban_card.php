<?php defined('BASEPATH') or exit('No direct script access allowed');
   if ($inspector['status'] == $status) { ?>
<li data-inspector-id="<?php echo $inspector['id']; ?>" class="<?php if($inspector['invoiceid'] != NULL){echo 'not-sortable';} ?>">
   <div class="panel-body">
      <div class="row">
         <div class="col-md-12">
            <h4 class="bold pipeline-heading"><a href="<?php echo admin_url('inspectors/list_inspectors/'.$inspector['id']); ?>" onclick="inspector_pipeline_open(<?php echo $inspector['id']; ?>); return false;"><?php echo format_inspector_number($inspector['id']); ?></a>
               <?php if(has_permission('inspectors','','edit')){ ?>
               <a href="<?php echo admin_url('inspectors/inspector/'.$inspector['id']); ?>" target="_blank" class="pull-right"><small><i class="fa fa-pencil-square-o" aria-hidden="true"></i></small></a>
               <?php } ?>
            </h4>
            <span class="inline-block full-width mbot10">
            <a href="<?php echo admin_url('clients/client/'.$inspector['clientid']); ?>" target="_blank">
            <?php echo $inspector['company']; ?>
            </a>
            </span>
         </div>
         <div class="col-md-12">
            <div class="row">
               <div class="col-md-8">
                  <span class="bold">
                  <?php echo _l('inspector_total') . ':' . app_format_money($inspector['total'], $inspector['currency_name']); ?>
                  </span>
                  <br />
                  <?php echo _l('inspector_data_date') . ': ' . _d($inspector['date']); ?>
                  <?php if(is_date($inspector['expirydate']) || !empty($inspector['expirydate'])){
                     echo '<br />';
                     echo _l('inspector_data_expiry_date') . ': ' . _d($inspector['expirydate']);
                     } ?>
               </div>
               <div class="col-md-4 text-right">
                  <small><i class="fa fa-paperclip"></i> <?php echo _l('inspector_notes'); ?>: <?php echo total_rows(db_prefix().'notes', array(
                     'rel_id' => $inspector['id'],
                     'rel_type' => 'inspector',
                     )); ?></small>
               </div>
               <?php $tags = get_tags_in($inspector['id'],'inspector');
                  if(count($tags) > 0){ ?>
               <div class="col-md-12">
                  <div class="mtop5 kanban-tags">
                     <?php echo render_tags($tags); ?>
                  </div>
               </div>
               <?php } ?>
            </div>
         </div>
      </div>
   </div>
</li>
<?php } ?>
