<?php

namespace AlkimAmazonPay;

use AlkimAmazonPay\Helpers\TransactionHelper;
use AlkimAmazonPay\Models\Transaction;
use AmazonPayExtendedSdk\Struct\StatusDetails;

class OrderHelper
{
    /**
     * @var \AlkimAmazonPay\AmazonPayHelper
     */
    private $amazonPayHelper;

    /**
     * @var \AlkimAmazonPay\ConfigHelper
     */
    private $configHelper;

    public function __construct()
    {
        $this->amazonPayHelper = new AmazonPayHelper();
        $this->configHelper    = new ConfigHelper();
    }

    public function doShippingCapture()
    {
        if (defined('APC_CAPTURE_MODE') && APC_CAPTURE_MODE === 'after_shipping' && defined('MODULE_PAYMENT_AMAZON_PAY_STATUS') && MODULE_PAYMENT_AMAZON_PAY_STATUS === 'True') {
            $q                 = "SELECT DISTINCT a.* FROM " . TABLE_ORDERS . " o
                            JOIN amazon_pay_transactions AS a ON (o.orders_id = a.order_id AND a.type = 'Charge' AND a.status = '" . StatusDetails::AUTHORIZED . "')
                    WHERE
                        o.payment_method = 'amazon_pay'
                            AND
                        o.orders_status = '" . APC_ORDER_STATUS_SHIPPED . "'";
            $rs                = xtc_db_query($q);
            $amazonPayHelper   = new AmazonPayHelper();
            $transactionHelper = new TransactionHelper();
            $apiClient         = $amazonPayHelper->getClient();
            while ($r = xtc_db_fetch_array($rs)) {
                $chargeTransaction = new Transaction($r);
                $originalCharge    = $apiClient->getCharge($chargeTransaction->reference);

                $captureCharge = new \AmazonPayExtendedSdk\Struct\Charge();
                $amount        = new \AmazonPayExtendedSdk\Struct\CaptureAmount($originalCharge->getChargeAmount()->toArray());
                $captureCharge->setCaptureAmount($amount);
                $captureCharge = $apiClient->captureCharge($originalCharge->getChargeId(), $captureCharge);
                $transactionHelper->updateCharge($captureCharge);
            }
        }
    }

    public function setOrderStatusAuthorized($orderId)
    {
        /*
                if (APC_CAPTURE_MODE == 'after_shipping' && MODULE_PAYMENT_AM_APA_STATUS == 'True' && self::getOrderStatus($oid) == AMZ_SHIPPED_STATUS) {
                    $q  = "SELECT * FROM amz_transactions
                                WHERE
                                    amz_tx_order_reference = '" . xtc_db_input($orderRef) . "'
                                        AND
                                    amz_tx_type = 'auth'
                                        AND
                                    amz_tx_status = 'Open'
                                ORDER BY amz_tx_id DESC";
                    $rs = xtc_db_query($q);
                    if ($r = xtc_db_fetch_array($rs)) {
                        if (AlkimAmazonHandler::isFullAuth($r["amz_tx_amz_id"])) {
                            AlkimAmazonTransactions::capture($r["amz_tx_amz_id"], AlkimAmazonHandler::getAuthAmount($r["amz_tx_amz_id"]));
                        }
                    }
                }*/

        $newStatus = APC_ORDER_STATUS_AUTHORIZED;
        $comment   = 'Amazon Pay - authorize';
        self::setOrderStatus($orderId, $newStatus, $comment);
    }

    public function setOrderStatus($orderId, $status, $comment = '')
    {
        $orderId = (int)$orderId;
        $status  = (int)$status;
        if ($status <= 0) {
            $q  = "SELECT orders_status FROM " . TABLE_ORDERS . " WHERE orders_id = " . $orderId;
            $rs = xtc_db_query($q);
            if ($r = xtc_db_fetch_array($rs)) {
                $status = (int)$r["orders_status"];
            } else {
                return;
            }
        } else {
            $q  = "SELECT * FROM " . TABLE_ORDERS_STATUS_HISTORY . " WHERE orders_id = " . $orderId . " AND orders_status_id = " . $status;
            $rs = xtc_db_query($q);
            if (xtc_db_num_rows($rs)) {
                return;
            }
        }
        $data = [
            'orders_id'         => $orderId,
            'orders_status_id'  => $status,
            'date_added'        => 'now()',
            'customer_notified' => 0,
            'comments'          => $comment
        ];
        xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $data);
        $q = "UPDATE " . TABLE_ORDERS . " SET orders_status = " . $status . " WHERE orders_id = " . $orderId;
        xtc_db_query($q);
    }

    public function setOrderStatusDeclined($orderId)
    {
        self::setOrderStatus($orderId, APC_ORDER_STATUS_DECLINED, 'Amazon Pay - declined');
    }

    public function setOrderStatusCaptured($orderId)
    {
        self::setOrderStatus($orderId, APC_ORDER_STATUS_CAPTURED, 'Amazon Pay - captured');
    }

    public function connectAmazonPaySessionToOrder($checkoutSessionId, $orderId)
    {
        xtc_db_perform('amazon_pay_transactions', [
            'amazon_pay_session_id' => $checkoutSessionId,
            'order_id'              => $orderId
        ]);
    }

    public function getAmazonPaySessionIdFromOrder($orderId)
    {
        $q  = "SELECT amazon_pay_session_id FROM amazon_pay_transactions WHERE order_id = " . (int)$orderId;
        $rs = xtc_db_query($q);
        $r  = xtc_db_fetch_array($rs);

        return $r["amazon_pay_session_id"];
    }

}