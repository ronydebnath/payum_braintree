<?php
namespace Payum\Braintree\Request;

use Payum\Core\Request\Generic;

class ObtainPaymentMethodNonce extends Generic
{
    protected $response;

    public function getResponse()
    {
        return $this->response;
    }
    
    public function setResponse($value)
    {
        $this->response = $value;
    }
}
