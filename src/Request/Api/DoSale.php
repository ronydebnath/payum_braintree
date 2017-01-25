<?php
namespace Payum\Braintree\Request\Api;

use Payum\Core\Request\Generic;
use Braintree\Instance as BraintreeInstance;

class DoSale extends Generic
{
    protected $response;

    public function getResponse()
    {
        return $this->response;
    }
    
    public function setResponse(BraintreeInstance $value)
    {
        $this->response = $value;
    }
}
