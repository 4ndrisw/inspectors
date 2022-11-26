<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
    $CI = &get_instance();
    $CI->load->model('inspectors/inspectors_model');
    $inspectors = $CI->inspectors_model->get_program_not_scheduled(get_staff_user_id());
?>

<div class="widget" id="widget-<?php echo create_widget_id(); ?>" data-name="<?php echo _l('program_not_scheduled'); ?>">
    <?php if(staff_can('view', 'inspectors') || staff_can('view_own', 'inspectors')) { ?>
    <div class="panel_s inspectors-expiring">
        <div class="panel-body padding-10">
            <p class="padding-5"><?php echo _l('program_not_scheduled'); ?></p>
            <hr class="hr-panel-heading-dashboard">
            <?php if (!empty($inspectors)) { ?>
                <div class="table-vertical-scroll">
                    <a href="<?php echo admin_url('inspectors'); ?>" class="mbot20 inline-block full-width"><?php echo _l('home_widget_view_all'); ?></a>
                    <table id="widget-<?php echo create_widget_id(); ?>" class="table dt-table" data-order-col="2" data-order-type="desc">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th class="<?php echo (isset($client) ? 'not_visible' : ''); ?>"><?php echo _l('inspector_list_program'); ?></th>
                                <th><?php echo _l('inspector_list_client'); ?></th>
                                <th><?php echo _l('inspector_list_date'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; ?>
                            <?php foreach ($inspectors as $inspector) { ?>
                                <tr>
                                    <td> <?php echo $i; ?>
                                    </td>
                                    <td>
                                        <?php //echo $inspector['name']; ?>
                                        <?php echo '<a href="' . admin_url("programs/view/" . $inspector["id"]) . '">' . $inspector['name'] . '</a>'; ?>
                                    </td>
                                    <td>
                                        <?php echo '<a href="' . admin_url("clients/client/" . $inspector["userid"]) . '">' . $inspector["company"] . '</a>'; ?>
                                    </td>
                                    <td>
                                        <?php echo _d($inspector['start_date']); ?>
                                    </td>
                                </tr>
                            <?php $i++; ?>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } else { ?>
                <div class="text-center padding-5">
                    <i class="fa fa-check fa-5x" aria-hidden="true"></i>
                    <h4><?php echo _l('no_program_not_scheduled',["7"]) ; ?> </h4>
                </div>
            <?php } ?>
        </div>
    </div>
    <?php } ?>
</div>
