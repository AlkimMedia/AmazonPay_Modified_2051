<?php
\AlkimAmazonPay\GeneralHelper::log('debug', 'start checkout_confirmation');

$checkoutHelper = new \AlkimAmazonPay\CheckoutHelper();
$configHelper   = new \AlkimAmazonPay\ConfigHelper();

if (empty($_SESSION['amazon_checkout_session'])) {
    \AlkimAmazonPay\GeneralHelper::log('warning', 'lost amazon checkout session id', $_SESSION);
    xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART));
}

$checkoutSession = $checkoutHelper->getCheckoutSession($_SESSION['amazon_checkout_session']);

if (!$checkoutSession || !$checkoutSession->getCheckoutSessionId()) {
    \AlkimAmazonPay\GeneralHelper::log('warning', 'invalid amazon checkout session id', [$_SESSION['amazon_checkout_session'], $checkoutSession]);
    xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART));
}

\AlkimAmazonPay\GeneralHelper::log('debug', 'checkout_confirmation CheckoutSession', [$checkoutSession->toArray()]);

if (is_array($checkoutSession->getPaymentPreferences())) {
    foreach ($checkoutSession->getPaymentPreferences() as $paymentPreference) {
        if (is_array($paymentPreference) && isset($paymentPreference['paymentDescriptor'])) {
            define('AMAZON_PAY_PAYMENT_DESCRIPTOR', $paymentPreference['paymentDescriptor']);
            break;
        }
    }
}
