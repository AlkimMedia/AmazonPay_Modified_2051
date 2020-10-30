<?php
require_once DIR_FS_CATALOG . 'includes/modules/payment/amazon_pay/amazon_pay.php';

use AlkimAmazonPay\AmazonPayHelper;
use AlkimAmazonPay\CheckoutHelper;
use AlkimAmazonPay\ConfigHelper;
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

        //complete checkout session
        $amazonPayHelper = new AmazonPayHelper();
        $configHelper    = new ConfigHelper();
        $checkoutHelper  = new CheckoutHelper();
        $transactionHelper = new \AlkimAmazonPay\Helpers\TransactionHelper();

        $paymentDetails = new PaymentDetails();

        $orderTotalRs = xtc_db_query("SELECT `value` FROM " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$insert_id . "' and class = 'ot_total'");
        $orderTotal   = xtc_db_fetch_array($orderTotalRs);
        $order        = new order($insert_id);

        $paymentDetails->setChargeAmount(new Price(['amount' => round($orderTotal['value'], 2), 'currencyCode' => $order->info['currency']]));
        //TODO handle errors
        try {
            $checkoutSession = $amazonPayHelper->getClient()->completeCheckoutSession($_SESSION['amazon_checkout_session'], $paymentDetails);

            $transaction                = new \AlkimAmazonPay\Models\Transaction();
            $transaction->type          = 'CheckoutSession';
            $transaction->reference     = $checkoutSession->getCheckoutSessionId();
            $transaction->charge_amount = $orderTotal['value'];
            $transaction->currency      = $order->info['currency'];
            $transaction->mode          = $configHelper->isSandbox() ? 'sandbox' : 'live';
            $transaction->merchant_id   = $configHelper->getMerchantId();
            $transaction->status        = $checkoutSession->getStatusDetails()->getState();
            $transaction->order_id      = $insert_id;

            xtc_db_perform('amazon_pay_transactions', $transaction->toArray());

            if ($checkoutSession->getChargePermissionId()) {
                $chargePermission           = $amazonPayHelper->getClient()->getChargePermission($checkoutSession->getChargePermissionId());
                $transaction                = new \AlkimAmazonPay\Models\Transaction();
                $transaction->type          = 'ChargePermission';
                $transaction->reference     = $chargePermission->getChargePermissionId();
                $transaction->time          = date('Y-m-d H:i:s', strtotime($chargePermission->getCreationTimestamp()));
                $transaction->expiration    = date('Y-m-d H:i:s', strtotime($chargePermission->getExpirationTimestamp()));
                $transaction->charge_amount = $chargePermission->getLimits()->getAmountLimit()->getAmount();
                $transaction->currency      = $chargePermission->getLimits()->getAmountLimit()->getCurrencyCode();
                $transaction->mode          = strtolower($chargePermission->getReleaseEnvironment());
                $transaction->merchant_id   = $configHelper->getMerchantId();
                $transaction->status        = $chargePermission->getStatusDetails()->getState();
                $transaction->order_id      = $insert_id;
                xtc_db_perform('amazon_pay_transactions', $transaction->toArray());
            }

            if ($checkoutSession->getChargeId()) {
                $charge                     = $amazonPayHelper->getClient()->getCharge($checkoutSession->getChargeId());
                $transaction                = new \AlkimAmazonPay\Models\Transaction();
                $transaction->type          = 'Charge';
                $transaction->reference     = $charge->getChargeId();
                $transaction->time          = date('Y-m-d H:i:s', strtotime($charge->getCreationTimestamp()));
                $transaction->expiration    = date('Y-m-d H:i:s', strtotime($charge->getExpirationTimestamp()));
                $transaction->charge_amount = $charge->getChargeAmount()->getAmount();
                $transaction->currency      = $charge->getChargeAmount()->getCurrencyCode();
                $transaction->mode          = strtolower($charge->getReleaseEnvironment());
                $transaction->merchant_id   = $configHelper->getMerchantId();
                $transaction->status        = $charge->getStatusDetails()->getState();
                $transaction->order_id      = $insert_id;
                xtc_db_perform('amazon_pay_transactions', $transaction->toArray());
                if ($transaction->status === StatusDetails::AUTHORIZED && APC_CAPTURE_MODE === 'after_auth') {
                    $transactionHelper->capture($charge->getChargeId());
                }
            }

            $checkoutHelper->setOrderIdToChargePermission($checkoutSession->getChargePermissionId(), $insert_id);
        } catch (Exception $e) {
            //TODO handle
            //xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART));
            $checkoutSession = $amazonPayHelper->getClient()->getCheckoutSession($_SESSION['amazon_checkout_session']);
            var_dump($e->getMessage(), $orderTotal['value'], $checkoutSession, $insert_id);
            die;
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