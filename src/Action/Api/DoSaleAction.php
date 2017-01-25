<?php
namespace Payum\Braintree\Action\Api;

use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Braintree\Request\Api\DoSale;

class DoSaleAction extends BaseApiAwareAction
{
    /**
     * {@inheritDoc}
     *
     * @throws \Payum\Core\Exception\LogicException if the token not set in the instruction.
     */
    public function execute($request)
    {
        /** @var $request DoSale */
        RequestNotSupportedException::assertSupports($this, $request);

        $requestParams = $this->getSaleRequestParams($request);

        $transactionResult = $this->api->sale($requestParams);

        $request->setResponse($transactionResult);
    }

    private function getSaleRequestParams($request)
    {
        $details = ArrayObject::ensureArrayObject($request->getModel());

        $details->validateNotEmpty(['amount']);

        $requestParams = [];

        $forwardParams = [
            'amount', 
            'paymentMethodNonce',
            'paymentMethodToken',
            'orderId',
        ];

        foreach($forwardParams as $paramName) {
            if ($details->offsetExists($paramName)) {
                $requestParams[$paramName] = $details[$paramName];
            }
        }

        return $requestParams;
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof DoSale &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
