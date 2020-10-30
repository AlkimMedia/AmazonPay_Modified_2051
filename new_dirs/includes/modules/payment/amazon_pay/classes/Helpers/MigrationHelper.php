<?php

namespace AlkimAmazonPay;

class MigrationHelper
{
    protected $mapping = [
        'APC_MERCHANT_ID'                      => 'MODULE_PAYMENT_AM_APA_MERCHANTID',
        'APC_CLIENT_ID'                        => 'MODULE_PAYMENT_AM_APA_CLIENTID',
        'MODULE_PAYMENT_AMAZON_PAY_ALLOWED'    => 'MODULE_PAYMENT_AM_APA_ALLOWED',
        'MODULE_PAYMENT_AMAZON_PAY_SORT_ORDER' => 'MODULE_PAYMENT_AM_APA_SORT_ORDER',
        'APC_IS_DEBUG'                         => 'AMZ_DEBUG_MODE',
        'APC_ORDER_STATUS_AUTHORIZED'          => 'MODULE_PAYMENT_AM_APA_ORDER_STATUS_OK',
        'APC_ORDER_STATUS_DECLINED'            => 'AMZ_STATUS_HARDDECLINE',
        'APC_ORDER_STATUS_CAPTURED'            => 'AMZ_ORDER_STATUS_CAPTURED',
        'APC_CAPTURE_MODE'                     => 'AMZ_CAPTURE_MODE',
        'APC_ORDER_STATUS_SHIPPED'             => 'AMZ_SHIPPED_STATUS',

    ];

    public function getLegacyValue($field)
    {
        if (isset($this->mapping[$field]) && defined($this->mapping[$field])) {
            return constant($this->mapping[$field]);
        }
        if ($field === 'APC_IS_LIVE' && defined('MODULE_PAYMENT_AM_APA_MODE')) {
            return (MODULE_PAYMENT_AM_APA_MODE === 'live' ? 'True' : 'False');
        }

        return null;
    }
}