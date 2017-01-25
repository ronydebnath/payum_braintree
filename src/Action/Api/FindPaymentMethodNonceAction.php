<?php
namespace Payum\Braintree\Action\Api;

use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Braintree\Request\Api\FindPaymentMethodNonce;

class FindPaymentMethodNonceAction extends BaseApiAwareAction
{
    /**
     * {@inheritDoc}
     *
     * @throws \Payum\Core\Exception\LogicException if the token not set in the instruction.
     */
    public function execute($request)
    {
        /** @var $request GenerateClientToken */
        RequestNotSupportedException::assertSupports($this, $request);
        
        $paymentMethodNonce = $this->api->findPaymentMethodNonce($request->getNonceString());
        
        $request->setResponse($paymentMethodNonce);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof FindPaymentMethodNonce;
    }
}
