<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade modal-assignment assignment-modal-<?php echo $name . '-' . $id; ?>" tabindex="-1" role="dialog"
    aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php echo form_open('admin/inspectors/add_assignment/' . $id . '/' . $name, ['id' => 'form-assignment-' . $name]); ?>
            <div class="modal-header">
                <button type="button" class="close close-assignment-modal" data-rel-id="<?php echo $id; ?>"
                    data-rel-type="<?php echo $name; ?>" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"><i class="fa-regular fa-circle-question" data-toggle="tooltip"
                        title="<?php echo _l('set_assignment_tooltip'); ?>" data-placement="bottom"></i>
                    <?php echo _l('inspector_set_assignment_title'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?php $this->load->view('admin/includes/assignment_fields'); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default close-assignment-modal" data-rel-id="<?php echo $id; ?>"
                    data-rel-type="<?php echo $name; ?>"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
