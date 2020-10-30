<?php

namespace AlkimAmazonPay;

use AmazonPayExtendedSdk\Struct\AddressRestrictions;
use AmazonPayExtendedSdk\Struct\CheckoutSession;
use AmazonPayExtendedSdk\Struct\DeliverySpecifications;
use AmazonPayExtendedSdk\Struct\WebCheckoutDetails;

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
        //try {
        $storeName = (strlen(STORE_NAME) <= 50)?STORE_NAME:(substr(STORE_NAME, 0, 47).'...');
        $merchantData = new \AmazonPayExtendedSdk\Struct\MerchantMetadata();
        $merchantData->setMerchantStoreName($storeName);
        

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
        /* } catch (\Exception $e) {
             //TODO
             echo $e->getMessage() . "\n";
         }*/
    }

    public function getCheckoutSession($checkoutSessionId)
    {
        try {
            return $this->amazonPayHelper->getClient()->getCheckoutSession($checkoutSessionId);
        } catch (\Exception $e) {
            //TODO
            echo $e->getMessage() . "\n";
        }
    }

    public function updateCheckoutSession($checkoutSessionId, CheckoutSession $checkoutSession)
    {
        try {
            return $this->amazonPayHelper->getClient()->updateCheckoutSession($checkoutSessionId, $checkoutSession);
        } catch (\Exception $e) {
            //TODO
            echo $e->getMessage() . "\n";
        }
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

        $client         = $this->amazonPayHelper->getClient();
        $loginPayload   = json_encode([
            'signInReturnUrl' => xtc_href_link('amazon_pay_login.php'),
            'storeId'         => $this->configHelper->getClientId(),
            'signInScopes'    => ["name", "email", "postalCode"]
        ]);
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
                        console.warn(e);
                    }
                    try{
                        amazon.Pay.bindChangeAction('#amz-change-payment', {
                            amazonCheckoutSessionId: '$checkoutSessionId',
                            changeAction: 'changePayment'
                        });
                    }catch(e){
                        console.warn(e);
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
                                productType: 'PayAndShip',
                                placement: '$placement',
                                buttonColor: '$checkoutButtonColor'
                            });
                         }
                    }catch(e){
                        console.warn(e);
                    }
                    
                    try{
                        var btn = amazon.Pay.renderButton('#amazon-pay-button-manual', {
                            merchantId: '$merchantId',
                            sandbox: $isSandbox,
                            ledgerCurrency: '$currency',
                            checkoutLanguage: '$language',
                            productType: 'PayAndShip',
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
                        console.warn(e);
                    }
                    
                    try{
                        var btn = amazon.Pay.renderButton('#amazon-pay-button-product-info', {
                            merchantId: '$merchantId',
                            sandbox: $isSandbox,
                            ledgerCurrency: '$currency',
                            checkoutLanguage: '$language',
                            productType: 'PayAndShip',
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
                        console.warn(e);
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
                        console.warn(e);
                    }
                    
                    
              
                    
                    
                </script>';
EOT;
        if ($this->configHelper->isDebugMode()) {
            $return .= '<style>.amazon-pay-button, #amazon-pay-button-manual, #amazon-pay-button-product-info{display:none;}</style>';
        }

        return $return;
    }
}
