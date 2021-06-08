<?php

namespace AlkimAmazonPay;

use AmazonPayExtendedSdk\Struct\AddressRestrictions;
use AmazonPayExtendedSdk\Struct\CheckoutSession;
use AmazonPayExtendedSdk\Struct\DeliverySpecifications;
use AmazonPayExtendedSdk\Struct\MerchantMetadata;
use AmazonPayExtendedSdk\Struct\PaymentDetails;
use AmazonPayExtendedSdk\Struct\Price;
use AmazonPayExtendedSdk\Struct\WebCheckoutDetails;
use order;
use order_total;
use shipping;

class CheckoutHelper
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

    public function createCheckoutSession()
    {
        try {

            $storeName    = (strlen(STORE_NAME) <= 50) ? STORE_NAME : (substr(STORE_NAME, 0, 47) . '...');
            $merchantData = new MerchantMetadata();
            $merchantData->setMerchantStoreName($storeName);
            $merchantData->setCustomInformation($this->configHelper->getCustomInformationString());

            $webCheckoutDetails = new WebCheckoutDetails();
            $webCheckoutDetails->setCheckoutReviewReturnUrl(xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));

            $addressRestrictions = new AddressRestrictions();
            $addressRestrictions->setType('Allowed')
                ->setRestrictions($this->configHelper->getAllowedCountries());
            $deliverySpecifications = new DeliverySpecifications();
            $deliverySpecifications->setAddressRestrictions($addressRestrictions);

            $checkoutSession = new CheckoutSession();
            $checkoutSession->setMerchantMetadata($merchantData)
                ->setWebCheckoutDetails($webCheckoutDetails)
                ->setStoreId($this->configHelper->getClientId())
                ->setPlatformId($this->configHelper->getPlatformId())
                ->setDeliverySpecifications($deliverySpecifications);

            return $this->amazonPayHelper->getClient()->createCheckoutSession($checkoutSession);
        } catch (\Exception $e) {
            GeneralHelper::log('error', 'createCheckoutSession failed', $e->getMessage());
        }
        return null;
    }

    public function getCheckoutSession($checkoutSessionId)
    {
        try {
            return $this->amazonPayHelper->getClient()->getCheckoutSession($checkoutSessionId);
        } catch (\Exception $e) {
            GeneralHelper::log('error', 'getCheckoutSession failed', [$e->getMessage(), $checkoutSessionId]);
        }
        return null;
    }

    public function updateCheckoutSession($checkoutSessionId, CheckoutSession $checkoutSession)
    {
        try {
            return $this->amazonPayHelper->getClient()->updateCheckoutSession($checkoutSessionId, $checkoutSession);
        } catch (\Exception $e) {
            GeneralHelper::log('error', 'updateCheckoutSession failed', [$e->getMessage(), $checkoutSessionId, $checkoutSession]);
        }
        return null;
    }

    public function setOrderIdToChargePermission($chargePermissionId, $orderId)
    {

        $this->amazonPayHelper->getClient()->updateChargePermission(
            $chargePermissionId,
            ['merchantMetadata' => ['merchantReferenceId' => $orderId]]
        );
    }

    public function getJs($placement = 'Cart')
    {
        if (!$this->configHelper->isActive()) {
            return '';
        }
        $merchantId               = $this->configHelper->getMerchantId();
        $createCheckoutSessionUrl = $this->configHelper->getCheckoutSessionAjaxUrl();
        $isSandbox                = $this->configHelper->isSandbox() ? 'true' : 'false';
        $language                 = $this->configHelper->getLanguage();
        $currency                 = $this->configHelper->getCurrency();
        $checkoutSessionId        = (!empty($_SESSION['amazon_checkout_session']) ? $_SESSION['amazon_checkout_session'] : '');
        $jsPath                   = DIR_WS_CATALOG . 'includes/modules/payment/amazon_pay/js/amazon-pay.js';
        $checkoutButtonColor      = APC_CHECKOUT_BUTTON_COLOR;
        $loginButtonColor         = APC_LOGIN_BUTTON_COLOR;

        $client       = $this->amazonPayHelper->getClient();
        $loginPayload = json_encode([
            'signInReturnUrl' => xtc_href_link('amazon_pay_login.php'),
            'storeId' => $this->configHelper->getClientId(),
            'signInScopes' => ["name", "email", "postalCode"],
        ]);

        $productType = 'PayAndShip';
        if ($_SESSION['cart']->count_contents() > 0) {
            if ($_SESSION['cart']->get_content_type() === 'virtual' || $_SESSION['cart']->count_contents_virtual() == 0) {
                $productType = 'PayOnly';
            }
        }

        $loginSignature = $client->generateButtonSignature($loginPayload);
        $publicKeyId    = $this->configHelper->getPublicKeyId();

        $return = <<<EOT
                <script src="https://static-eu.payments-amazon.com/checkout.js"></script>
                <script src="$jsPath"></script>
                <script type="text/javascript" charset="utf-8">
                
                    try{
                        amazon.Pay.bindChangeAction('#amz-change-address', {
                            amazonCheckoutSessionId: '$checkoutSessionId',
                            changeAction: 'changeAddress'
                        });
                    }catch(e){
                        //console.warn(e);
                    }
                    try{
                        amazon.Pay.bindChangeAction('#amz-change-payment', {
                            amazonCheckoutSessionId: '$checkoutSessionId',
                            changeAction: 'changePayment'
                        });
                    }catch(e){
                        //console.warn(e);
                    }
                    try{
                        var buttons = document.querySelectorAll('.amazon-pay-button');
                        for (var i = 0; i < buttons.length; i++) {
                            var button = buttons[i];
                            var id  = 'amazon-pay-button-' + alkimAmazonPay.payButtonCount++;
                            button.id = id;
                            amazon.Pay.renderButton('#' + id, {
                                merchantId: '$merchantId',
                                createCheckoutSession: {
                                    url: '$createCheckoutSessionUrl'
                                },
                                sandbox: $isSandbox,
                                ledgerCurrency: '$currency',
                                checkoutLanguage: '$language',
                                productType: '$productType',
                                placement: '$placement',
                                buttonColor: '$checkoutButtonColor'
                            });
                         }
                    }catch(e){
                        //console.warn(e);
                    }
                    
                    try{
                        var btn = amazon.Pay.renderButton('#amazon-pay-button-manual', {
                            merchantId: '$merchantId',
                            sandbox: $isSandbox,
                            ledgerCurrency: '$currency',
                            checkoutLanguage: '$language',
                            productType: '$productType',
                            placement: '$placement',
                            buttonColor: '$checkoutButtonColor'
                        });
                        alkimAmazonPay.initCheckout = function(){
                            btn.initCheckout({
                                createCheckoutSession: {
                                    url: '$createCheckoutSessionUrl'
                                }
                            });
                        }
                    }catch(e){
                        //console.warn(e);
                    }
                    
                    try{
                        var btn = amazon.Pay.renderButton('#amazon-pay-button-product-info', {
                            merchantId: '$merchantId',
                            sandbox: $isSandbox,
                            ledgerCurrency: '$currency',
                            checkoutLanguage: '$language',
                            productType: '$productType',
                            placement: '$placement',
                            buttonColor: '$checkoutButtonColor'
                        });
                        
                        btn.onClick(function(){
                            alkimAmazonPay.ajaxPost(document.getElementById('cart_quantity'), function(){
                                btn.initCheckout({
                                    createCheckoutSession: {
                                        url: '$createCheckoutSessionUrl'
                                    }
                                });
                            });
                        });
                    }catch(e){
                        //console.warn(e);
                    }
                    
                    try{
                        var buttons = document.querySelectorAll('.amazon-login-button');
                        for (var i = 0; i < buttons.length; i++) {
                            var button = buttons[i];
                            var id  = 'amazon-login-button-' + alkimAmazonPay.payButtonCount++;
                            button.id = id;
                            amazon.Pay.renderButton('#' + id, {
                                merchantId: '$merchantId',
                                sandbox: $isSandbox,
                                ledgerCurrency: '$currency',
                                checkoutLanguage: '$language',
                                productType: 'SignIn',
                                placement: '$placement',
                                buttonColor: '$loginButtonColor',
                                signInConfig: {                     
                                    payloadJSON: '$loginPayload',
                                    signature: '$loginSignature',
                                    publicKeyId: '$publicKeyId' 
                                }
                            });
                         }
                    }catch(e){
                        //console.warn(e);
                    }
                    
                    
              
                    
                    
                </script>
EOT;
        if ($this->configHelper->isDebugMode()) {
            $return .= '<style>.amazon-login-button, .amazon-pay-button, #amazon-pay-button-manual, #amazon-pay-button-product-info{display:none;}</style>';
        }

        return $return;
    }

    /**
     * @param $checkoutSession
     */
    public function doUpdateCheckoutSessionBeforeCheckoutProcess($checkoutSession)
    {
        global $order, $order_totals, $shipping_modules, $order_total_modules;
        require_once DIR_WS_CLASSES . 'payment.php';
        require_once DIR_WS_CLASSES . 'shipping.php';
        $shipping_modules = new shipping($_SESSION['shipping']);
        require_once DIR_WS_CLASSES . 'order.php';
        $order = new order();
        require_once DIR_WS_CLASSES . 'order_total.php';
        $order_total_modules = new order_total();
        $order_totals        = $order_total_modules->process();

        $checkoutSessionUpdate = new CheckoutSession();

        $webCheckoutDetails = new WebCheckoutDetails();
        $webCheckoutDetails->setCheckoutResultReturnUrl($this->configHelper->getCheckoutResultReturnUrl());

        $paymentDetails = new PaymentDetails();
        $paymentDetails
            ->setPaymentIntent('Authorize')
            ->setCanHandlePendingAuthorization(true)
            ->setChargeAmount(new Price(['amount' => $order->info['total'], 'currencyCode' => $order->info['currency']]));

        $checkoutSessionUpdate
            ->setWebCheckoutDetails($webCheckoutDetails)
            ->setPaymentDetails($paymentDetails);
        $updatedCheckoutSession = $this->updateCheckoutSession($checkoutSession->getCheckoutSessionId(), $checkoutSessionUpdate);

        if ($redirectUrl = $updatedCheckoutSession->getWebCheckoutDetails()->getAmazonPayRedirectUrl()) {
            xtc_redirect($redirectUrl);
        } else {
            GeneralHelper::log('warning', 'updateCheckoutSession failed', $checkoutSessionUpdate);
            xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_CONFIRMATION, 'amazon_pay_error', 'SSL'));
        }
    }

    public function defaultErrorHandling()
    {
        unset($_SESSION['payment']);
        xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->configHelper->getPaymentMethodName()));
    }
}
