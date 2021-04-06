<?php
/**
 * @var \AlkimAmazonPay\ConfigHelper $configHelper
 */
$orders_query = xtc_db_query("SELECT o.payment_class, a.status
                                        FROM 
                                            " . TABLE_ORDERS . " o
                                            LEFT JOIN amazon_pay_transactions AS a ON (a.order_id = o.orders_id AND a.type = 'Charge')
                                        WHERE 
                                            customers_id = " . (int)$_SESSION['customer_id'] . "                                             
                                        ORDER BY
                                            orders_id DESC 
                                        LIMIT 1");

if ($order = xtc_db_fetch_array($orders_query)) {
    if ($order["payment_class"] === $configHelper->getPaymentMethodName()
        &&
        $order['status'] === \AmazonPayExtendedSdk\Struct\StatusDetails::AUTHORIZATION_INITIATED
    ) {
        define('AMAZON_PAY_CHECKOUT_SUCCESS_INFORMATION', TEXT_AMAZON_PAY_PENDING);
    }
}
if (!defined('AMAZON_PAY_CHECKOUT_SUCCESS_INFORMATION')) {
    define('AMAZON_PAY_CHECKOUT_SUCCESS_INFORMATION', '');
}