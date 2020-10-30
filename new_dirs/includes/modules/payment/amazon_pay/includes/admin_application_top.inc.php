<?php
include __DIR__ . '/../amazon_pay.php';

if (strpos($_SERVER['PHP_SELF'], 'modules.php') !== false && isset($_GET['set']) && $_GET['set'] === 'payment' && isset($_GET['action']) && $_GET['action'] === 'edit') {

    if (empty($_GET['module'])) {
        $module_directory = DIR_FS_CATALOG_MODULES . 'payment/';

        $directory_array = [];
        if ($dir = @dir($module_directory)) {
            while ($file = $dir->read()) {
                if (!is_dir($module_directory . $file)) {
                    if (pathinfo($file)['extension'] === 'php') {
                        $directory_array[] = $file;
                    }
                }
            }
            sort($directory_array);
            $dir->close();
        }

        if (pathinfo(reset($directory_array))['filename'] === 'amazon_pay') {
            $_GET['module'] = 'amazon_pay';
        }
    }

    if (isset($_GET['module']) && $_GET['module'] === 'amazon_pay') {
        xtc_redirect(xtc_href_link('amazon_pay_configuration.php'));
    }
}

if (!empty($_GET['amazon_pay_action'])) {

    $orderId           = (int)$_GET['oID'];
    $amazonPayHelper   = new \AlkimAmazonPay\AmazonPayHelper();
    $transactionHelper = new \AlkimAmazonPay\Helpers\TransactionHelper();
    $apiClient         = $amazonPayHelper->getClient();
    $orderHelper       = new \AlkimAmazonPay\OrderHelper();
    $configHelper      = new \AlkimAmazonPay\ConfigHelper();
    switch ($_GET['amazon_pay_action']) {
        case 'capture':
            $transactionHelper->capture($_GET['charge_id'], (float)$_POST['amount']);
            break;
        case 'refund':
            $originalCharge = $apiClient->getCharge($_GET['charge_id']);
            if ($originalCharge->getStatusDetails()->getState() !== \AmazonPayExtendedSdk\Struct\StatusDetails::CAPTURED) {
                $transactionHelper->updateCharge($originalCharge);
            } else {
                $chargeTransaction = $transactionHelper->getTransaction($originalCharge->getChargeId());
                $refund            = new \AmazonPayExtendedSdk\Struct\Refund();
                $refund->setChargeId($originalCharge->getChargeId());
                $amount = new \AmazonPayExtendedSdk\Struct\RefundAmount($originalCharge->getCaptureAmount()->toArray());
                $amount->setAmount((float)$_POST['amount']);
                $refund->setRefundAmount($amount);
                $refund                     = $apiClient->createRefund($refund);
                $transaction                = new \AlkimAmazonPay\Models\Transaction();
                $transaction->type          = 'Refund';
                $transaction->reference     = $refund->getRefundId();
                $transaction->time          = date('Y-m-d H:i:s', strtotime($refund->getCreationTimestamp()));
                $transaction->charge_amount = $refund->getRefundAmount()->getAmount();
                $transaction->currency      = $refund->getRefundAmount()->getCurrencyCode();
                $transaction->mode          = strtolower($refund->getReleaseEnvironment());
                $transaction->merchant_id   = $configHelper->getMerchantId();
                $transaction->status        = $refund->getStatusDetails()->getState();
                $transaction->order_id      = $chargeTransaction->order_id;
                xtc_db_perform('amazon_pay_transactions', $transaction->toArray());
            }
            break;
        case 'create_charge':
            $chargePermissionTransaction = $transactionHelper->getTransaction($_GET['charge_permission_id']);

            $amount = new \AmazonPayExtendedSdk\Struct\ChargeAmount();
            $amount->setAmount((float)$_POST['amount'])->setCurrencyCode($chargePermissionTransaction->currency);

            $charge = new \AmazonPayExtendedSdk\Struct\Charge();
            $charge->setChargePermissionId($_GET['charge_permission_id'])
                   ->setCanHandlePendingAuthorization(true)
                   ->setChargeAmount($amount);
            $charge                     = $apiClient->createCharge($charge);
            $transaction                = new \AlkimAmazonPay\Models\Transaction();
            $transaction->type          = 'Charge';
            $transaction->reference     = $charge->getChargeId();
            $transaction->time          = date('Y-m-d H:i:s', strtotime($charge->getCreationTimestamp()));
            $transaction->expiration    = date('Y-m-d H:i:s', strtotime($charge->getExpirationTimestamp()));
            $transaction->charge_amount = $charge->getChargeAmount()->getAmount();
            $transaction->currency      = $charge->getChargeAmount()->getCurrencyCode();
            $transaction->mode          = strtolower($charge->getReleaseEnvironment());
            $transaction->merchant_id   = $configHelper->getMerchantId();
            $transaction->status        = $charge->getStatusDetails()->getState();
            $transaction->order_id      = $chargePermissionTransaction->order_id;
            xtc_db_perform('amazon_pay_transactions', $transaction->toArray());

            break;
    }
    xtc_redirect(xtc_href_link('orders.php', xtc_get_all_get_params(['amazon_pay_action']), 'SSL'));
}

$orderHelper = new \AlkimAmazonPay\OrderHelper();
$orderHelper->doShippingCapture();
