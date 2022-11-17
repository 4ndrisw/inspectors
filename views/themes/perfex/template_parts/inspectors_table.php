<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<table class="table dt-table table-inspectors" data-order-col="1" data-order-type="desc">
    <thead>
        <tr>
            <th><?php echo _l('inspector_number'); ?> #</th>
            <th><?php echo _l('inspector_list_project'); ?></th>
            <th><?php echo _l('inspector_list_date'); ?></th>
            <th><?php echo _l('inspector_list_status'); ?></th>

        </tr>
    </thead>
    <tbody>
        <?php foreach($inspectors as $inspector){ ?>
            <tr>
                <td><?php echo '<a href="' . site_url("inspectors/show/" . $inspector["id"] . '/' . $inspector["hash"]) . '">' . format_inspector_number($inspector["id"]) . '</a>'; ?></td>
                <td><?php echo $inspector['name']; ?></td>
                <td><?php echo _d($inspector['date']); ?></td>
                <td><?php echo format_inspector_status($inspector['status']); ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>
