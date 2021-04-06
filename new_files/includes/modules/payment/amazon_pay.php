<?php
require_once DIR_FS_CATALOG . 'includes/modules/payment/amazon_pay/amazon_pay.php';

use AlkimAmazonPay\AmazonPayHelper;
use AlkimAmazonPay\CheckoutHelper;
use AlkimAmazonPay\GeneralHelper;
use AlkimAmazonPay\Helpers\TransactionHelper;
use AlkimAmazonPay\InstallHelper;
use AmazonPayExtendedSdk\Struct\PaymentDetails;
use AmazonPayExtendedSdk\Struct\Price;
use AmazonPayExtendedSdk\Struct\StatusDetails;

class amazon_pay
{
    /**
     * @var string
     */
    public $code;
    /**
     * @var string
     */
    public $title;
    /**
     * @var string
     */
    public $description;
    /**
     * @var int
     */
    public $sort_order;
    /**
     * @var bool
     */
    public $enabled;
    /**
     * @var string
     */
    public $info;

    function __construct()
    {
        global $order;

        $this->code        = __CLASS__;
        $this->title       = MODULE_PAYMENT_AMAZON_PAY_TEXT_TITLE;
        $this->description = MODULE_PAYMENT_AMAZON_PAY_TEXT_DESCRIPTION;
        $this->sort_order  = MODULE_PAYMENT_AMAZON_PAY_SORT_ORDER;
        $this->enabled     = (MODULE_PAYMENT_AMAZON_PAY_STATUS == 'True');
        $this->info        = MODULE_PAYMENT_AMAZON_PAY_TEXT_INFO;
        if (is_object($order)) {
            $this->update_status();
        }
    }

    function update_status()
    {
        global $order;

    }

    function javascript_validation()
    {
        return 'if (payment_value === "amazon_pay") { alkimAmazonPay.initCheckout(); return false; }';
    }

    function selection()
    {
        return [
            'id'          => $this->code,
            'module'      => $this->title,
            'description' => $this->info . '<div style="display:none;"><div id="amazon-pay-button-manual"></div></div>'
        ];
    }

    function pre_confirmation_check()
    {
        return false;
    }

    function confirmation()
    {
        return [
            'title' => $this->description
        ];
    }

    function process_button()
    {
        return '<input type="hidden" name="checkout_confirmation_comments" id="checkout-confirmation-comments" />';
    }

    function before_process()
    {
        return false;
    }

    function after_process()
    {
        global $insert_id;

        //checkout session must be in status 'open'
        //this is taken care of in includes/modules/payment/amazon_pay/includes/actions/checkout_process.php

        //complete checkout session
        $amazonPayHelper = new AmazonPayHelper();
        $checkoutHelper  = new CheckoutHelper();
        $transactionHelper = new TransactionHelper();

        $paymentDetails = new PaymentDetails();

        $orderTotalRs = xtc_db_query("SELECT `value` FROM " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$insert_id . "' and class = 'ot_total'");
        $orderTotal   = xtc_db_fetch_array($orderTotalRs);
        $order        = new order($insert_id);

        $paymentDetails->setChargeAmount(new Price(['amount' => round($orderTotal['value'], 2), 'currencyCode' => $order->info['currency']]));

        try {
            $checkoutSession = $amazonPayHelper->getClient()->completeCheckoutSession($_SESSION['amazon_checkout_session'], $paymentDetails);
            $transactionHelper->saveNewCheckoutSession($checkoutSession, $orderTotal['value'], $order->info['currency'], $insert_id);

            if ($checkoutSession->getChargePermissionId()) {
                $chargePermission           = $amazonPayHelper->getClient()->getChargePermission($checkoutSession->getChargePermissionId());
                $transactionHelper->saveNewChargePermission($chargePermission, $insert_id);
            }

            if ($checkoutSession->getChargeId()) {
                $charge                     = $amazonPayHelper->getClient()->getCharge($checkoutSession->getChargeId());
                $transaction = $transactionHelper->saveNewCharge($charge, $insert_id);
                if ($transaction->status === StatusDetails::AUTHORIZED && APC_CAPTURE_MODE === 'after_auth') {
                    $transactionHelper->capture($charge->getChargeId());
                }
            }

            $checkoutHelper->setOrderIdToChargePermission($checkoutSession->getChargePermissionId(), $insert_id);
        } catch (Exception $e) {
            $checkoutSession = $amazonPayHelper->getClient()->getCheckoutSession($_SESSION['amazon_checkout_session']);
            GeneralHelper::log('error', 'unexpected exception during checkout', [$e->getMessage(), $checkoutSession->toArray()]);
            $checkoutHelper->defaultErrorHandling();
        }
    }

    function get_error()
    {
        return ['error' => TEXT_AMAZON_PAY_ERROR];
    }

    function check()
    {
        if (!isset ($this->_check)) {
            $check_query  = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_AMAZON_PAY_STATUS'");
            $this->_check = xtc_db_num_rows($check_query);
        }

        return $this->_check;
    }

    function install()
    {
        $values = [
            'MODULE_PAYMENT_AMAZON_PAY_STATUS'     => ['value' => 'False'],
            'MODULE_PAYMENT_AMAZON_PAY_ALLOWED'    => ['value' => ''],
            'MODULE_PAYMENT_AMAZON_PAY_SORT_ORDER' => ['value' => '0'],
            'MODULE_PAYMENT_AMAZON_PAY_ZONE'       => ['value' => '']
        ];

        foreach ($values as $key => $data) {
            xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . "
                            SET
                          configuration_key = '" . $key . "',
                          configuration_value = '" . $data['value'] . "',  
                          configuration_group_id = 6,
                          sort_order = 0, 
                          set_function = '',
                          date_added = now()");
        }
        $installHelper = new InstallHelper();
        $installHelper->process();

    }

    function remove()
    {
        xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key IN ('" . implode("', '", $this->keys()) . "')");
    }

    function keys()
    {
        return [
            'MODULE_PAYMENT_AMAZON_PAY_STATUS',
            'MODULE_PAYMENT_AMAZON_PAY_ALLOWED',
            'MODULE_PAYMENT_AMAZON_PAY_SORT_ORDER',
            'MODULE_PAYMENT_AMAZON_PAY_ZONE'
        ];
    }
}