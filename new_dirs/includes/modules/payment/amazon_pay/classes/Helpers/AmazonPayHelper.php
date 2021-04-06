<?php

namespace AlkimAmazonPay;



use AmazonPayExtendedSdk\Client\Client;

class AmazonPayHelper
{
    private static $client;
    /**
     * @var \AlkimAmazonPay\ConfigHelper
     */
    private $configHelper;

    public function __construct()
    {
        $this->configHelper = new ConfigHelper();
    }

    /**
     * @return \AmazonPayExtendedSdk\Client\Client
     */
    public function getClient()
    {
        if (!isset(self::$client)) {
            try {
                self::$client = new Client($this->configHelper->getMainConfig());
            } catch (\Exception $e) {
                GeneralHelper::log('error', 'Unable to get client', $e->getMessage());
            }
        }

        return self::$client;
    }

    public function getHeaders()
    {
        return ['x-amz-pay-Idempotency-Key' => uniqid()];
    }
}