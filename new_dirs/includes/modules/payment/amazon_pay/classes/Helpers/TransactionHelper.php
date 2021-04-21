<?php

namespace AlkimAmazonPay\Helpers;

use AlkimAmazonPay\AmazonPayHelper;
use AlkimAmazonPay\ConfigHelper;
use AlkimAmazonPay\GeneralHelper;
use AlkimAmazonPay\Models\Transaction;
use AlkimAmazonPay\OrderHelper;
use AmazonPayExtendedSdk\Struct\CaptureAmount;
use AmazonPayExtendedSdk\Struct\Charge;
use AmazonPayExtendedSdk\Struct\ChargePermission;
use AmazonPayExtendedSdk\Struct\CheckoutSession;
use AmazonPayExtendedSdk\Struct\Refund;
use AmazonPayExtendedSdk\Struct\StatusDetails;
use Exception;

class TransactionHelper
{

    public function updateRefund(Refund $refund, $updateCharge = true)
    {
        try {
            $transaction         = new Transaction();
            $transaction->status = $refund->getStatusDetails()->getState();
            if ($updateCharge) {
                $amazonPayHelper = new AmazonPayHelper();
                $charge          = $amazonPayHelper->getClient()->getCharge($refund->getChargeId());
                $this->updateCharge($charge);
            }
            xtc_db_perform('amazon_pay_transactions', $transaction->toArray(), 'update', ' reference = \'' . xtc_db_input($refund->getRefundId()) . '\'');
        } catch (\Exception $e) {
            GeneralHelper::log('error', 'updateRefund failed', [$e->getMessage(), $refund]);
        }

        return null;
    }

    public function updateCharge(Charge $charge)
    {
        try {
            $transaction = new Transaction();
            if ($charge->getCaptureAmount()) {
                $transaction->captured_amount = (float)$charge->getCaptureAmount()->getAmount();
            }
            if ($charge->getRefundedAmount()) {
                $transaction->refunded_amount = (float)$charge->getRefundedAmount()->getAmount();
            }
            $transaction->status = $charge->getStatusDetails()->getState();
            xtc_db_perform('amazon_pay_transactions', $transaction->toArray(), 'update', ' reference = \'' . xtc_db_input($charge->getChargeId()) . '\'');

            $originalChargeTransaction = $this->getTransaction($charge->getChargeId());
            if ($originalChargeTransaction->order_id) {
                $orderHelper = new OrderHelper();
                if ($transaction->status === StatusDetails::AUTHORIZED) {
                    $orderHelper->setOrderStatusAuthorized($originalChargeTransaction->order_id);
                    if (APC_CAPTURE_MODE === 'after_auth') {
                        $this->capture($charge->getChargeId());
                    }
                } elseif ($transaction->status === StatusDetails::DECLINED) {
                    $orderHelper->setOrderStatusDeclined($originalChargeTransaction->order_id);
                } elseif ($transaction->status === StatusDetails::CAPTURED) {
                    $orderHelper->setOrderStatusCaptured($originalChargeTransaction->order_id);
                }
            }
            
        } catch (\Exception $e) {
            GeneralHelper::log('error', 'updateCharge failed', [$e->getMessage(), $charge]);
        }

        return null;
    }

    public function updateChargePermission(ChargePermission $chargePermission)
    {
        try {
            $transaction = new Transaction();
            $transaction->status = $chargePermission->getStatusDetails()->getState();
            xtc_db_perform('amazon_pay_transactions', $transaction->toArray(), 'update', ' reference = \'' . xtc_db_input($chargePermission->getChargePermissionId()) . '\'');
        } catch (\Exception $e) {
            GeneralHelper::log('error', 'updateChargePermission failed', [$e->getMessage(), $chargePermission]);
        }
        return null;
    }

    public function getTransaction($reference)
    {
        $q  = "SELECT * FROM amazon_pay_transactions WHERE reference = '" . xtc_db_input($reference) . "'";
        $rs = xtc_db_query($q);
        if ($r = xtc_db_fetch_array($rs)) {
            return new Transaction($r);
        }

        return null;
    }

    public function capture($chargeId, $amount = null)
    {
        try {
            $amazonPayHelper = new AmazonPayHelper();
            $apiClient       = $amazonPayHelper->getClient();
            $originalCharge  = $apiClient->getCharge($chargeId);
            if ($originalCharge->getStatusDetails()->getState() === StatusDetails::AUTHORIZED) {
                $captureCharge = new Charge();
                $captureAmount = new CaptureAmount($originalCharge->getChargeAmount()->toArray());
                if ($amount !== null) {
                    $captureAmount->setAmount($amount);
                }
                $captureCharge->setCaptureAmount($captureAmount);
                $captureCharge = $apiClient->captureCharge($originalCharge->getChargeId(), $captureCharge);
                $this->updateCharge($captureCharge);
            }
        } catch (\Exception $e) {
            GeneralHelper::log('error', 'capture failed', [$e->getMessage(), $chargeId, $amount]);
        }

        return null;
    }

    public function saveNewCharge(Charge $charge, $orderId = null)
    {

        $transaction                = new Transaction();
        $transaction->type          = 'Charge';
        $transaction->reference     = $charge->getChargeId();
        $transaction->time          = date('Y-m-d H:i:s', strtotime($charge->getCreationTimestamp()));
        $transaction->expiration    = date('Y-m-d H:i:s', strtotime($charge->getExpirationTimestamp()));
        $transaction->charge_amount = $charge->getChargeAmount()->getAmount();
        $transaction->currency      = $charge->getChargeAmount()->getCurrencyCode();
        $transaction->mode          = strtolower($charge->getReleaseEnvironment());
        $transaction->merchant_id   = (new ConfigHelper())->getMerchantId();
        $transaction->status        = $charge->getStatusDetails()->getState();
        if ($orderId !== null) {
            $transaction->order_id = $orderId;
        }
        xtc_db_perform('amazon_pay_transactions', $transaction->toArray());
        return $transaction;
    }

    public function saveNewChargePermission(ChargePermission $chargePermission, $orderId = null)
    {
        $transaction                = new Transaction();
        $transaction->type          = 'ChargePermission';
        $transaction->reference     = $chargePermission->getChargePermissionId();
        $transaction->time          = date('Y-m-d H:i:s', strtotime($chargePermission->getCreationTimestamp()));
        $transaction->expiration    = date('Y-m-d H:i:s', strtotime($chargePermission->getExpirationTimestamp()));
        $transaction->charge_amount = $chargePermission->getLimits()->getAmountLimit()->getAmount();
        $transaction->currency      = $chargePermission->getLimits()->getAmountLimit()->getCurrencyCode();
        $transaction->mode          = strtolower($chargePermission->getReleaseEnvironment());
        $transaction->merchant_id   = (new ConfigHelper())->getMerchantId();
        $transaction->status        = $chargePermission->getStatusDetails()->getState();
        if ($orderId !== null) {
            $transaction->order_id = $orderId;
        }
        xtc_db_perform('amazon_pay_transactions', $transaction->toArray());
        return $transaction;
    }

    public function saveNewCheckoutSession(CheckoutSession $checkoutSession, $total, $currency, $orderId = null)
    {
        $configHelper               = new ConfigHelper();
        $transaction                = new Transaction();
        $transaction->type          = 'CheckoutSession';
        $transaction->reference     = $checkoutSession->getCheckoutSessionId();
        $transaction->charge_amount = $total;
        $transaction->currency      = $currency;
        $transaction->mode          = $configHelper->isSandbox() ? 'sandbox' : 'live';
        $transaction->merchant_id   = $configHelper->getMerchantId();
        $transaction->status        = $checkoutSession->getStatusDetails()->getState();
        if ($orderId !== null) {
            $transaction->order_id = $orderId;
        }

        xtc_db_perform('amazon_pay_transactions', $transaction->toArray());
        return $transaction;
    }

    /**
     * @return Transaction[]
     */
    public function getOpenTransactions($orderId = null)
    {
        $q      = "SELECT * FROM amazon_pay_transactions WHERE type != 'CheckoutSession' AND status IN ('" . implode("', '", [
                StatusDetails::REFUND_INITIATED,
                StatusDetails::OPEN,
                StatusDetails::AUTHORIZATION_INITIATED,
                StatusDetails::AUTHORIZED,
                StatusDetails::NON_CHARGEABLE,
                StatusDetails::CHARGEABLE,
            ]) . "')".
            ($orderId !== null?' AND order_id = '.(int)$orderId:'');
        $rs     = xtc_db_query($q);
        $return = [];
        while ($r = xtc_db_fetch_array($rs)) {
            $return[] = new Transaction($r);
        }
        return $return;
    }



    public function doCron()
    {
        foreach ($this->getOpenTransactions() as $transaction) {
            try {
                $this->refreshTransaction($transaction);
            } catch (Exception $e) {
                GeneralHelper::log('error', 'Unable to update transaction in cron',  ['msg'=>$e->getMessage(), 'trace' => $e->getTrace(), 'transaction' => $transaction->toArray()]);
            }
        }
    }

    public function refreshOrder($orderId)
    {
        foreach ($this->getOpenTransactions($orderId) as $transaction) {
            try {
                $this->refreshTransaction($transaction);
            } catch (Exception $e) {
                GeneralHelper::log('error', 'Unable to update transaction in cron',  ['msg'=>$e->getMessage(), 'trace' => $e->getTrace(), 'transaction' => $transaction->toArray()]);
            }
        }
    }

    public function refreshTransaction(Transaction $transaction)
    {
        $apiClient       = (new AmazonPayHelper())->getClient();
        if ($transaction->type === Transaction::TRANSACTION_TYPE_REFUND) {
            $refund = $apiClient->getRefund($transaction->reference);
            $this->updateRefund($refund);
        } elseif ($transaction->type === Transaction::TRANSACTION_TYPE_CHARGE) {
            $charge = $apiClient->getCharge($transaction->reference);
            $this->updateCharge($charge);
        } elseif ($transaction->type === Transaction::TRANSACTION_TYPE_CHARGE_PERMISSION) {
            $chargePermission = $apiClient->getChargePermission($transaction->reference);
            $this->updateChargePermission($chargePermission);
        }
    }

}
