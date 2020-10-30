<?php
$body = file_get_contents('php://input');

if ($data = json_decode($body, true)) {
    if ($message = json_decode($data['Message'], true)) {
        chdir('../../');
        require_once 'includes/application_top.php';
        require_once 'includes/modules/payment/amazon_pay/amazon_pay.php';
        $amazonPayHelper   = new \AlkimAmazonPay\AmazonPayHelper();
        $transactionHelper = new \AlkimAmazonPay\Helpers\TransactionHelper();
        $apiClient         = $amazonPayHelper->getClient();

        switch ($message['ObjectType']) {
            case 'CHARGE':
                $charge = $apiClient->getCharge($message['ObjectId']);
                $transactionHelper->updateCharge($charge);
                break;
            case 'REFUND':
                $refund = $apiClient->getRefund($message['ObjectId']);
                $transactionHelper->updateRefund($refund);
                break;
            default:
                break;

        }

    }
}