<?php
/**
 * @var \AlkimAmazonPay\ConfigHelper $configHelper
 */
$accountHelper = new \AlkimAmazonPay\AccountHelper();
if (!empty($_GET['amazonCheckoutSessionId'])) {
    \AlkimAmazonPay\GeneralHelper::log('debug', 'start checkout_shipping');
    $checkoutHelper                      = new \AlkimAmazonPay\CheckoutHelper();
    $checkoutSessionId                   = $_GET['amazonCheckoutSessionId'];
    $_SESSION['amazon_checkout_session'] = $checkoutSessionId;
    $checkoutSession                     = $checkoutHelper->getCheckoutSession($checkoutSessionId);
    $needsMainAddress                    = false;
    if (!$accountHelper->isLoggedIn()) {
        if(!$checkoutSession->getBuyer()){
            xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, 'amazon_pay_error=1'));
        }
        $name        = $checkoutSession->getBuyer()->getName();
        $t           = explode(' ', $name);
        $lastNameKey = max(array_keys($t));
        $lastName    = $t[$lastNameKey];
        unset($t[$lastNameKey]);
        $firstName = implode(' ', $t);
        require_once DIR_FS_INC . 'xtc_create_password.inc.php';
        $password       = xtc_create_password(32);
        $sql_data_array = [
            'customers_status' => DEFAULT_CUSTOMERS_STATUS_ID_GUEST,
            'customers_gender' => '',
            'customers_firstname' => $firstName,
            'customers_lastname' => $lastName,
            'customers_dob' => '0000-00-00 00:00:00',
            'customers_email_address' => $checkoutSession->getBuyer()->getEmail(),
            'customers_default_address_id' => '0',
            'customers_telephone' => '',
            'customers_password' => $password,
            'customers_newsletter' => 0,
            'customers_newsletter_mode' => 0,
            'member_flag' => 0,
            'delete_user' => 1,
            'account_type' => 1,
            'customers_date_added'=>'now()',
        ];

        xtc_db_perform(TABLE_CUSTOMERS, $sql_data_array);
        $_SESSION['customer_id'] = xtc_db_insert_id();
        xtc_db_perform(TABLE_CUSTOMERS_INFO, [
            'customers_info_id' => $_SESSION['customer_id'],
        ]);
        $needsMainAddress        = true;
    }
    if ($shippingAddress = $checkoutSession->getShippingAddress()) {
        if ($shippingAddressId = $accountHelper->getAddressId($shippingAddress)) {
            $_SESSION["sendto"] = $shippingAddressId;
        } else {
            $_SESSION["sendto"] = $accountHelper->createAddress($shippingAddress);
        }
    } else {
        $_SESSION["sendto"] = false;
    }

    if ($billingAddressId = $accountHelper->getAddressId($checkoutSession->getBillingAddress())) {
        $_SESSION["billto"] = $billingAddressId;
    } else {
        $_SESSION["billto"] = $accountHelper->createAddress($checkoutSession->getBillingAddress());
    }

    if ($needsMainAddress) {
        xtc_db_perform(TABLE_CUSTOMERS, ['customers_default_address_id' => $_SESSION['billto']], 'update', 'customers_id = ' . (int)$_SESSION['customer_id']);
        $accountHelper->doLogin($_SESSION['customer_id']);
    }

    $_SESSION['payment'] = $configHelper->getPaymentMethodName();

    if (!empty($_SESSION['shipping']) && !empty($_SESSION['shipping']['id'])) {
        $q  = "SELECT entry_postcode, entry_country_id FROM " . TABLE_ADDRESS_BOOK . " WHERE address_book_id = " . (int)$_SESSION['sendto'];
        $rs = xtc_db_query($q);
        if ($r = xtc_db_fetch_array($rs)) {
            if(isset($_SESSION['amazon_pay_delivery_zip']) && isset($_SESSION['amazon_pay_delivery_country'])){
                if ($_SESSION['amazon_pay_delivery_zip'] === $r['entry_postcode'] && $_SESSION['amazon_pay_delivery_country'] === $r['entry_country_id']) {
                    xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT));
                }
            }
        }
    }
} else {
    if ($accountHelper->isLoggedIn() && $accountHelper->getStatusId() !== (int)DEFAULT_CUSTOMERS_STATUS_ID_GUEST) {
        if ($accountHelper->isAccountComplete($_SESSION['customer_id']) === false) {
            $_SESSION['checkout_with_incomplete_account_started'] = true;
            xtc_redirect(xtc_href_link(FILENAME_ACCOUNT_EDIT, 'amazon_pay_error=1'));
        }
        if ($accountHelper->hasAddress($_SESSION['customer_id']) === false) {
            $_SESSION['checkout_with_incomplete_account_started'] = true;
            xtc_redirect(xtc_href_link(FILENAME_ADDRESS_BOOK_PROCESS, 'amazon_pay_error=1'));
        }
    }
}
