<?php
    if ($order->info['payment_method'] === 'amazon_pay') {
        ?>
        <tr>
            <td class="main" colspan="2">
                <?php require_once DIR_FS_CATALOG.'includes/modules/payment/amazon_pay/includes/admin_order.inc.php'; ?>
            </td>
        </tr>
        <?php
    }
