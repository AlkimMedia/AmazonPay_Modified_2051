<?php

chdir('../../');

require_once 'includes/application_top.php';
require_once 'includes/modules/payment/amazon_pay/amazon_pay.php';

$checkoutHelper = new \AlkimAmazonPay\CheckoutHelper();
$checkoutSession = $checkoutHelper->createCheckoutSession();
echo json_encode(['amazonCheckoutSessionId'=>$checkoutSession->getCheckoutSessionId()]);
