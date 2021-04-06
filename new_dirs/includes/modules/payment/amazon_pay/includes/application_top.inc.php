<?php

require_once __DIR__ . '/../amazon_pay.php';
$configHelper = new \AlkimAmazonPay\ConfigHelper();
if (strpos($PHP_SELF, 'shopping_cart.php') !== false && !empty($_SESSION['payment']) && $_SESSION['payment'] === 'amazon_pay') {
    unset($_SESSION['payment']);
}


if (strpos($PHP_SELF, 'address_book.php') !== false) {
    include __DIR__.'/actions/address_book.php';
}

if (strpos($PHP_SELF, 'account.php') !== false) {
    include __DIR__.'/actions/account.php';
}

if (strpos($PHP_SELF, 'checkout_shipping.php') !== false) {
    include __DIR__.'/actions/checkout_shipping.php';
}

if (strpos($PHP_SELF, 'checkout_payment.php') !== false) {
    include __DIR__.'/actions/checkout_payment.php';
}

if (strpos($PHP_SELF, 'checkout_confirmation.php') !== false && !empty($_SESSION['payment']) && $_SESSION['payment'] === $configHelper->getPaymentMethodName()) {
    $_POST['conditions'] = 1; //TODO
}

if (strpos($PHP_SELF, 'checkout_process.php') !== false && !empty($_SESSION['payment']) && $_SESSION['payment'] === $configHelper->getPaymentMethodName()) {
    include __DIR__.'/actions/checkout_process.php';
}

if (strpos($PHP_SELF, 'checkout_success.php') !== false) {
    include __DIR__.'/actions/checkout_success.php';
}
