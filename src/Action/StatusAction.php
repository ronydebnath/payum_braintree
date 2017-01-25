<?php
namespace Payum\Braintree\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;

class StatusAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $status = $details['status'];

        if (null != $status) {

            switch($status) {

                case 'failed':

                    $request->markFailed();
                    return;

                case 'authorized':

                    if ($this->hasSuccessfulTransaction($details)) {
                        $request->markAuthorized();
                    }
                    else {
                        $request->markUnknown();
                    }

                    return;

                case 'captured':

                    if ($this->hasSuccessfulTransaction($details)) {
                        $request->markCaptured();                        
                    }
                    else {
                        $request->markUnknown();
                    }

                    return;
                    
                case 'refunded':

                    if ($this->hasSuccessfulTransaction($details)) {
                        $request->markRefunded();                        
                    }
                    else {
                        $request->markUnknown();
                    }

                    return;
            }
        }

        if ($details['paymentMethodNonce']) {
            $request->markPending();
            return;
        }

        $request->markNew();
    }

    protected function hasSuccessfulTransaction($details) 
    {
        return $details['transaction'] && $details['transaction']['success'];
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
