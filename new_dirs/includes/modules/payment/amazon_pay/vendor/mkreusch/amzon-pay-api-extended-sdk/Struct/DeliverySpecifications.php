<?php

namespace AmazonPayExtendedSdk\Struct;

class DeliverySpecifications extends StructBase
{
    /**
     * @var array
     */
    protected $specialRestrictions;
    /**
     * @var AddressRestrictions
     */
    protected $addressRestrictions;

    /**
     * @return array
     */
    public function getSpecialRestrictions()
    {
        return $this->specialRestrictions;
    }

    /**
     * @param array $specialRestrictions
     *
     * @return DeliverySpecifications
     */
    public function setSpecialRestrictions($specialRestrictions)
    {
        $this->specialRestrictions = $specialRestrictions;

        return $this;
    }

    /**
     * @return \AmazonPayExtendedSdk\Struct\AddressRestrictions
     */
    public function getAddressRestrictions()
    {
        return $this->addressRestrictions;
    }

    /**
     * @param \AmazonPayExtendedSdk\Struct\AddressRestrictions $addressRestrictions
     *
     * @return DeliverySpecifications
     */
    public function setAddressRestrictions($addressRestrictions)
    {
        $this->addressRestrictions = $addressRestrictions;

        return $this;
    }


}
