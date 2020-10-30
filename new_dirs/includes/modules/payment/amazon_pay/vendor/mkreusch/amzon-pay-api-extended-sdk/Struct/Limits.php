<?php

namespace AmazonPayExtendedSdk\Struct;

class Limits extends StructBase {

    /**
     * @var \AmazonPayExtendedSdk\Struct\AmountLimit
     */
    protected $amountLimit;

    /**
     * @var \AmazonPayExtendedSdk\Struct\AmountBalance
     */
    protected $amountBalance;

    /**
     * @return \AmazonPayExtendedSdk\Struct\AmountLimit
     */
    public function getAmountLimit()
    {
        return $this->amountLimit;
    }

    /**
     * @param \AmazonPayExtendedSdk\Struct\AmountLimit $amountLimit
     *
     * @return Limits
     */
    public function setAmountLimit($amountLimit)
    {
        $this->amountLimit = $amountLimit;

        return $this;
    }

    /**
     * @return \AmazonPayExtendedSdk\Struct\AmountBalance
     */
    public function getAmountBalance()
    {
        return $this->amountBalance;
    }

    /**
     * @param \AmazonPayExtendedSdk\Struct\AmountBalance $amountBalance
     *
     * @return Limits
     */
    public function setAmountBalance($amountBalance)
    {
        $this->amountBalance = $amountBalance;

        return $this;
    }


}