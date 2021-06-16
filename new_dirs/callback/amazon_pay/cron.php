<?php
/**
 * @package AlkimMediaAmazonPayCV2Global
 */
chdir('../../');
require_once 'includes/application_top.php';

if (!defined('APC_CRON_STATUS') || APC_CRON_STATUS !== 'True') {
    http_response_code(403);
    return;
}

$blockFile = DIR_FS_CATALOG . 'cache/amazon_pay_cron.block';
if (file_exists($blockFile) && filemtime($blockFile) > time() - 300) {
    http_response_code(429);
    return;
}
file_put_contents($blockFile, date('Y-m-d H:i:s'));

require_once 'includes/modules/payment/amazon_pay/amazon_pay.php';
$transactionHelper = new \AlkimAmazonPay\Helpers\TransactionHelper();
$transactionHelper->doCron();
