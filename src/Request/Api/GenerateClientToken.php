<?php
namespace Payum\Braintree\Request\Api;

class GenerateClientToken
{
    protected $customerId;

    protected $merchantAccountId;

    protected $response;

    public function getCustomerId()
    {
        return $this->customerId;
    }
    
    public function setCustomerId($value)
    {
        $this->customerId = $value;
    }
    
    public function getMerchantAccountId()
    {
        return $this->merchantAccountId;
    }
    
    public function setMerchantAccountId($value)
    {
        $this->merchantAccountId = $value;
    }

    public function getResponse()
    {
        return $this->response;
    }
    
    public function setResponse($value)
    {
        $this->response = $value;
    }
}
