<?php

namespace AmazonPayExtendedSdk\Struct;

class ChargePermission extends StructBase
{
    const CHARGE_PERMISSION_TYPE_ONETIME = 'Onetime';
    const CHARGE_PERMISSION_TYPE_RECURRING = 'Recurring';

    /*
recurringMetadata
Type: recurringMetadata
Metadata about how the recurring Charge Permission will be used. Amazon Pay only uses this information to calculate the Charge Permission expiration date and in buyer communication
Note that it is still your responsibility to call Create Charge to charge the buyer for each billing cycle

paymentPreferences
Type: list<paymentPreference>
List of payment instruments selected by the buyer

*/

    /**
     * @var string
     */
    protected $chargePermissionId;

    /**
     * @var string
     */
    protected $chargePermissionType;

    /**
     * @var string
     */
    protected $releaseEnvironment;

    /**
     * @var Limits
     */
    protected $limits;

    /**
     * @var $buyer
     */
    protected $buyer;

    /**
     * @var \AmazonPayExtendedSdk\Struct\ShippingAddress
     */
    protected $shippingAddress;

    /**
     * @var \AmazonPayExtendedSdk\Struct\BillingAddress
     */
    protected $billingAddress;

    /**
     * @var \AmazonPayExtendedSdk\Struct\MerchantMetadata
     */
    protected $merchantMetaData;

    /**
     * @var string
     */
    protected $platformId;

    /**
     * @var string
     */
    protected $creationTimestamp;

    /**
     * @var string
     */
    protected $expirationTimestamp;

    /**
     * @var \AmazonPayExtendedSdk\Struct\StatusDetails
     */
    protected $statusDetails;

    /**
     * @var string
     */
    protected $presentmentCurrency;

    /**
     * @return \AmazonPayExtendedSdk\Struct\Limits
     */
    public function getLimits()
    {
        return $this->limits;
    }

    /**
     * @param \AmazonPayExtendedSdk\Struct\Limits $limits
     *
     * @return ChargePermission
     */
    public function setLimits($limits)
    {
        $this->limits = $limits;

        return $this;
    }



    /**
     * @return string
     */
    public function getChargePermissionId()
    {
        return $this->chargePermissionId;
    }

    /**
     * @param string $chargePermissionId
     *
     * @return ChargePermission
     */
    public function setChargePermissionId($chargePermissionId)
    {
        $this->chargePermissionId = $chargePermissionId;

        return $this;
    }

    /**
     * @return string
     */
    public function getChargePermissionType()
    {
        return $this->chargePermissionType;
    }

    /**
     * @param string $chargePermissionType
     *
     * @return ChargePermission
     */
    public function setChargePermissionType($chargePermissionType)
    {
        $this->chargePermissionType = $chargePermissionType;

        return $this;
    }

    /**
     * @return string
     */
    public function getReleaseEnvironment()
    {
        return $this->releaseEnvironment;
    }

    /**
     * @param string $releaseEnvironment
     *
     * @return ChargePermission
     */
    public function setReleaseEnvironment($releaseEnvironment)
    {
        $this->releaseEnvironment = $releaseEnvironment;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBuyer()
    {
        return $this->buyer;
    }

    /**
     * @param mixed $buyer
     *
     * @return ChargePermission
     */
    public function setBuyer($buyer)
    {
        $this->buyer = $buyer;

        return $this;
    }

    /**
     * @return \AmazonPayExtendedSdk\Struct\ShippingAddress
     */
    public function getShippingAddress()
    {
        return $this->shippingAddress;
    }

    /**
     * @param \AmazonPayExtendedSdk\Struct\ShippingAddress $shippingAddress
     *
     * @return ChargePermission
     */
    public function setShippingAddress($shippingAddress)
    {
        $this->shippingAddress = $shippingAddress;

        return $this;
    }

    /**
     * @return \AmazonPayExtendedSdk\Struct\BillingAddress
     */
    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    /**
     * @param \AmazonPayExtendedSdk\Struct\BillingAddress $billingAddress
     *
     * @return ChargePermission
     */
    public function setBillingAddress($billingAddress)
    {
        $this->billingAddress = $billingAddress;

        return $this;
    }

    /**
     * @return \AmazonPayExtendedSdk\Struct\MerchantMetadata
     */
    public function getMerchantMetaData()
    {
        return $this->merchantMetaData;
    }

    /**
     * @param \AmazonPayExtendedSdk\Struct\MerchantMetadata $merchantMetaData
     *
     * @return ChargePermission
     */
    public function setMerchantMetaData($merchantMetaData)
    {
        $this->merchantMetaData = $merchantMetaData;

        return $this;
    }

    /**
     * @return string
     */
    public function getPlatformId()
    {
        return $this->platformId;
    }

    /**
     * @param string $platformId
     *
     * @return ChargePermission
     */
    public function setPlatformId($platformId)
    {
        $this->platformId = $platformId;

        return $this;
    }

    /**
     * @return string
     */
    public function getCreationTimestamp()
    {
        return $this->creationTimestamp;
    }

    /**
     * @param string $creationTimestamp
     *
     * @return ChargePermission
     */
    public function setCreationTimestamp($creationTimestamp)
    {
        $this->creationTimestamp = $creationTimestamp;

        return $this;
    }

    /**
     * @return string
     */
    public function getExpirationTimestamp()
    {
        return $this->expirationTimestamp;
    }

    /**
     * @param string $expirationTimestamp
     *
     * @return ChargePermission
     */
    public function setExpirationTimestamp($expirationTimestamp)
    {
        $this->expirationTimestamp = $expirationTimestamp;

        return $this;
    }

    /**
     * @return \AmazonPayExtendedSdk\Struct\StatusDetails
     */
    public function getStatusDetails()
    {
        return $this->statusDetails;
    }

    /**
     * @param \AmazonPayExtendedSdk\Struct\StatusDetails $statusDetails
     *
     * @return ChargePermission
     */
    public function setStatusDetails($statusDetails)
    {
        $this->statusDetails = $statusDetails;

        return $this;
    }

    /**
     * @return string
     */
    public function getPresentmentCurrency()
    {
        return $this->presentmentCurrency;
    }

    /**
     * @param string $presentmentCurrency
     *
     * @return ChargePermission
     */
    public function setPresentmentCurrency($presentmentCurrency)
    {
        $this->presentmentCurrency = $presentmentCurrency;

        return $this;
    }


}


