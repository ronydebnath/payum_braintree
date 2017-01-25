<?php
namespace Payum\Braintree\Request;

interface RequestInterface
{
    public function getResponse();

    public function setResponse();
}
