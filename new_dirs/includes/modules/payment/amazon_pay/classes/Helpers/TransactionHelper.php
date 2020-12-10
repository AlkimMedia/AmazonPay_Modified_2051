<?php

namespace AlkimAmazonPay\Helpers;

use AlkimAmazonPay\AmazonPayHelper;
use AlkimAmazonPay\GeneralHelper;
use AlkimAmazonPay\Models\Transaction;
use AlkimAmazonPay\OrderHelper;
use AmazonPayExtendedSdk\Struct\CaptureAmount;
use AmazonPayExtendedSdk\Struct\Charge;
use AmazonPayExtendedSdk\Struct\Refund;
use AmazonPayExtendedSdk\Struct\StatusDetails;

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
                } elseif ($transaction->status === StatusDetails::DECLINED) {
                    $orderHelper->setOrderStatusDeclined($originalChargeTransaction->order_id);
                } elseif ($transaction->status === StatusDetails::CAPTURED) {
                    $orderHelper->setOrderStatusCaptured($originalChargeTransaction->order_id);
                }
            }

            if ($transaction->status === StatusDetails::AUTHORIZED && APC_CAPTURE_MODE === 'after_auth') {
                $this->capture($charge->getChargeId());
            }
        } catch (\Exception $e) {
            GeneralHelper::log('error', 'updateCharge failed', [$e->getMessage(), $charge]);
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
}
