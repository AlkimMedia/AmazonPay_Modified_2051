<?php

namespace AmazonPayExtendedSdk\Struct;

class Buyer extends StructBase
{
    /**
     * @var string
     */
    protected $buyerId;
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $email;

    /**
     * @return string
     */
    public function getBuyerId()
    {
        return $this->buyerId;
    }

    /**
     * @param string $buyerId
     *
     * @return Buyer
     */
    public function setBuyerId($buyerId)
    {
        $this->buyerId = $buyerId;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Buyer
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return Buyer
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }


}
