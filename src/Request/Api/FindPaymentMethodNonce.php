<?php
namespace Payum\Braintree\Request\Api;

use Payum\Core\Request\Generic;
use Braintree\PaymentMethodNonce;

class FindPaymentMethodNonce
{
    private $nonceString;

    private $response;

    /**
     * @param string $nonceString
     */
    public function __construct($nonceString)
    {
        $this->nonceString = $nonceString;
    }

    /** 
     * @return string
     */
    public function getNonceString()
    {
        return $this->nonceString;
    }

    /**
     * @return PaymentMethodNonce
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param PaymentMethodNonce $response
     */
    public function setResponse(PaymentMethodNonce $response)
    {
        $this->response = $response;
    }
}
