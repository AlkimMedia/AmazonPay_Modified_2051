<?php
chdir('../../');
require_once 'includes/application_top.php';
require_once 'includes/modules/payment/amazon_pay/amazon_pay.php';
$transactionHelper = new \AlkimAmazonPay\Helpers\TransactionHelper();
$transactionHelper->doCron();