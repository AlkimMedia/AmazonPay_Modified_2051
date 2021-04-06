<?php
/**
 * @var \AlkimAmazonPay\ConfigHelper $configHelper
 */
$_SESSION['amazon_pay_delivery_zip']     = null;
$_SESSION['amazon_pay_delivery_country'] = null;

if ($_SESSION['sendto'] === false) {
    unset($_SESSION['sendto']);
}

if (!empty($_SESSION['sendto'])) {
    $q  = "SELECT entry_postcode, entry_country_id FROM " . TABLE_ADDRESS_BOOK . " WHERE address_book_id = " . (int)$_SESSION['sendto'];
    $rs = xtc_db_query($q);
    if ($r = xtc_db_fetch_array($rs)) {
        $_SESSION['amazon_pay_delivery_zip']     = $r['entry_postcode'];
        $_SESSION['amazon_pay_delivery_country'] = $r['entry_country_id'];
    }
}

if (isset($_GET['_action']) && $_GET['_action'] === 'reset_payment') {
    unset($_SESSION['payment']);
} elseif (!empty($_SESSION['payment']) && $_SESSION['payment'] === $configHelper->getPaymentMethodName()) {
    \AlkimAmazonPay\GeneralHelper::log('debug', 'skip checkout_payment');
    xtc_redirect(xtc_href_link('checkout_confirmation.php', '', 'SSL'));
}