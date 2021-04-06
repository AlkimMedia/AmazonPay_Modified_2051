<?php
namespace AmazonPayExtendedSdk\Client;

use AmazonPayExtendedSdk\Struct\Charge;
use AmazonPayExtendedSdk\Struct\ChargePermission;
use AmazonPayExtendedSdk\Struct\CheckoutSession;
use AmazonPayExtendedSdk\Struct\PaymentDetails;
use AmazonPayExtendedSdk\Struct\Refund;

class Client extends \Amazon\Pay\API\Client{
    /**
     * @param $checkoutSession
     * @param $headers
     *
     * @return \AmazonPayExtendedSdk\Struct\CheckoutSession|array|string
     * @throws \Exception
     */
    public function createCheckoutSession($checkoutSession, $headers = null)
    {
        if($checkoutSession instanceof CheckoutSession){
            $checkoutSession = $checkoutSession->toArray();
        }
        if($headers === null){
           $headers =  ['x-amz-pay-Idempotency-Key' => uniqid()];
        }
        $result = parent::createCheckoutSession($checkoutSession, $headers);
        $response = json_decode($result['response'], true);
        if((int)$result['status']!==201){
            throw new \AmazonPayException('createCheckoutSession failed: '.$response['message'].' - '.$response['reasonCode']);
        }

        return new CheckoutSession($response);
    }

    /**
     * @param string $checkoutSessionId
     * @param null $headers
     *
     * @return \AmazonPayExtendedSdk\Struct\CheckoutSession|array|bool|string
     * @throws \Exception
     */
    public function getCheckoutSession($checkoutSessionId, $headers = null)
    {
        $result = parent::getCheckoutSession($checkoutSessionId, $headers);
        //$result['status']
        return new CheckoutSession(json_decode($result['response'], true));
    }

    /**
     * @param string $checkoutSessionId
     * @param \AmazonPayExtendedSdk\Struct\CheckoutSession|array|string$checkoutSession
     * @param null $headers
     *
     * @return \AmazonPayExtendedSdk\Struct\CheckoutSession|array|bool|string
     * @throws \Exception
     */
    public function updateCheckoutSession($checkoutSessionId, $checkoutSession, $headers = null)
    {
        if($checkoutSession instanceof CheckoutSession){
            $checkoutSession = $checkoutSession->toArray();
        }
        $result = parent::updateCheckoutSession($checkoutSessionId, $checkoutSession, $headers);
        //$result['status']
        return new CheckoutSession(json_decode($result['response'], true));
    }

    /**
     * @param string $checkoutSessionId
     * @param \AmazonPayExtendedSdk\Struct\PaymentDetails|array|string $paymentDetails
     * @param null $headers
     *
     * @return \AmazonPayExtendedSdk\Struct\CheckoutSession|array|bool|string
     * @throws \Exception
     */
    public function completeCheckoutSession($checkoutSessionId, $paymentDetails, $headers = null)
    {
        if($paymentDetails instanceof PaymentDetails){
            $paymentDetails = $paymentDetails->toArray();
        }
        $result = parent::completeCheckoutSession($checkoutSessionId, $paymentDetails, $headers);
        $response = json_decode($result['response'], true);
        if((int)$result['status']!==200 && (int)$result['status']!==202){
            throw new \AmazonPayException('completeCheckoutSession failed: '.$response['message'].' - '.$response['reasonCode']);
        }
        return new CheckoutSession($response);
    }

    /**
     * @param string $chargeId
     * @param null $headers
     *
     * @return \AmazonPayExtendedSdk\Struct\Charge|array|bool|string
     */
    public function getCharge($chargeId, $headers = null)
    {
        $result = parent::getCharge($chargeId, $headers);
        if($result['status'] < 200 || $result['status'] > 299) {
            $response = json_decode($result['response'], true);
            throw new \AmazonPayException('getCharge failed: '.$response['message']);
        }
        return new Charge(json_decode($result['response'], true));
    }

    public function captureCharge($chargeId, $charge, $headers = null)
    {
        if($charge instanceof Charge){
            $charge = $charge->toArray();
        }
        if($headers === null){
            $headers =  ['x-amz-pay-Idempotency-Key' => uniqid()];
        }
        $result = parent::captureCharge($chargeId, $charge, $headers);
        $response = json_decode($result['response'], true);
        if($result['status'] < 200 || $result['status'] > 299) {
            throw new \AmazonPayException('captureCharge failed: '.$response['message'].' - '.$response['reasonCode']);
        }
        return new Charge($response);
    }

    /**
     * @param Refund $refund
     * @param null|array $headers
     *
     * @return \AmazonPayExtendedSdk\Struct\Refund
     * @throws \AmazonPayException
     */
    public function createRefund($refund, $headers = null)
    {
        if($refund instanceof Refund){
            $refund = $refund->toArray();
        }
        if($headers === null){
            $headers =  ['x-amz-pay-Idempotency-Key' => uniqid()];
        }
        $result = parent::createRefund($refund, $headers);
        $response = json_decode($result['response'], true);
        if((int)$result['status']!==201){
            throw new \AmazonPayException('createRefund failed: '.$response['message'].' - '.$response['reasonCode']);
        }
        return new Refund($response);
    }

    /**
     * @param Charge $charge
     * @param null|array $headers
     *
     * @return \AmazonPayExtendedSdk\Struct\Charge
     * @throws \AmazonPayException
     */
    public function createCharge($charge, $headers = null)
    {
        if($charge instanceof Charge){
            $charge = $charge->toArray();
        }
        if($headers === null){
            $headers =  ['x-amz-pay-Idempotency-Key' => uniqid()];
        }
        $result = parent::createCharge($charge, $headers);
        $response = json_decode($result['response'], true);
        if($result['status'] < 200 || $result['status'] > 299) {
            throw new \AmazonPayException('createCharge failed: '.$response['message'].' - '.$response['reasonCode']);
        }
        return new Charge($response);
    }


    public function getRefund($refundId, $headers = null)
    {
        $result = parent::getRefund($refundId, $headers);
        return new Refund(json_decode($result['response'], true));
    }

    public function getBuyer($buyerToken, $headers = null)
    {
        $result = parent::getBuyer($buyerToken, $headers);
        return json_decode($result['response'], true);
    }

    /**
     * @param string $chargePermissionId
     * @param null|array $headers
     *
     * @return \AmazonPayExtendedSdk\Struct\ChargePermission
     * @throws \AmazonPayException
     */
    public function getChargePermission($chargePermissionId, $headers = null)
    {
        $result = parent::getChargePermission($chargePermissionId, $headers);
        $response = json_decode($result['response'], true);
        if($result['status'] < 200 || $result['status'] > 299) {
            throw new \AmazonPayException('getChargePermission failed: '.$response['message'].' - '.$response['reasonCode']);
        }
        return new ChargePermission($response);
    }
}