<?php

namespace AlkimAmazonPay\Models;

class Transaction
{
    const TRANSACTION_TYPE_CHARGE = 'Charge';
    const TRANSACTION_TYPE_CHARGE_PERMISSION = 'ChargePermission';
    const TRANSACTION_TYPE_REFUND = 'Refund';

    public $id;
    public $reference;
    public $merchant_id;
    public $mode;
    public $type;
    public $time;
    public $expiration;
    public $charge_amount;
    public $captured_amount;
    public $refunded_amount;
    public $currency;
    public $status;
    public $last_change;
    public $last_update;
    public $order_id;
    public $customer_informed;
    public $admin_informed;


    public function __construct($dataArray = null)
    {
        if(is_array($dataArray)){
            $this->setFromArray($dataArray);
        }
        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $return = [];
        foreach (array_keys(get_object_vars($this)) as $property) {
            if (isset($this->{$property})) {
                $return[$property] = $this->{$property};
            }
        }
        return $return;
    }



    public function setFromArray($dataArray){
        foreach($dataArray as $fieldName=>$fieldValue){
            if(property_exists($this, $fieldName)){
                $this->{$fieldName} = $fieldValue;
            }
        }
    }
}