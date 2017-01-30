<?php
namespace Payum\Braintree\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Authorize;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Braintree\Request\ObtainPaymentMethodNonce;
use Payum\Braintree\Request\ObtainCardholderAuthentication;
use Payum\Braintree\Request\Api\FindPaymentMethodNonce;
use Payum\Braintree\Request\Api\DoSale;
use Payum\Braintree\Reply\Api\PaymentMethodNonceArray;
use Payum\Braintree\Reply\Api\TransactionResultArray;
use Braintree\Transaction;

class AuthorizeAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    protected $cardholderAuthenticationRequired;

    public function __construct()
    {
        $this->cardholderAuthenticationRequired = true;
    }

    public function setCardholderAuthenticationRequired($value)
    {
        $this->cardholderAuthenticationRequired = $value;
    }

    /**
     * {@inheritDoc}
     *
     * @param Authorize $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if (true == $details->offsetExists('status')) {
            return;
        }

        if (false == $details->offsetExists('paymentMethodNonce')) {
            $this->obtainPaymentMethodNonce($details);
        }

        $details->validateNotEmpty(['paymentMethodNonceInfo']);

        $paymentMethodNonceInfo = $details['paymentMethodNonceInfo'];

        if ($paymentMethodNonceInfo['type'] === 'CreditCard') {

            if (true == $this->cardholderAuthenticationRequired && empty($paymentMethodNonceInfo['threeDSecureInfo'])) {
                $this->obtainCardholderAuthentication($details);
            }
        }

        if (false == $details->offsetExists('sale')) {

            $this->doSaleTransaction($details);
        }

        $details->validateNotEmpty(['sale']);

        $sale = $details['sale'];

        if (true == $sale['success']) {

            switch($sale['transaction']['status']) {
                
                case Transaction::AUTHORIZED:
                case Transaction::AUTHORIZING:

                    $details['status'] = 'authorized';
                    break;

                case Transaction::SUBMITTED_FOR_SETTLEMENT:
                case Transaction::SETTLING:
                case Transaction::SETTLED:
                case Transaction::SETTLEMENT_PENDING:
                case Transaction::SETTLEMENT_CONFIRMED:

                    $details['status'] = 'captured';
                    break;
            }
        }
        else {

            $details['status'] = 'failed';
        }
    }

    protected function obtainPaymentMethodNonce($details)
    {
        $this->gateway->execute($request = new ObtainPaymentMethodNonce($details));

        $paymentMethodNonce = $request->getResponse();

        $details['paymentMethodNonce'] = $paymentMethodNonce;

        if (false == $details->offsetExists('paymentMethodInfo')) {
            $this->findPaymentMethodNonceInfo($details);
        }
    }

    protected function findPaymentMethodNonceInfo($details)
    {
        $this->gateway->execute($request = new FindPaymentMethodNonce($details['paymentMethodNonce']));

        $paymentMethodInfo = $request->getResponse();

        $details['paymentMethodNonceInfo'] = PaymentMethodNonceArray::toArray($paymentMethodInfo);
    }

    protected function obtainCardholderAuthentication($details)
    {
        $this->gateway->execute($request = new ObtainCardholderAuthentication($details));

        $paymentMethodNonce = $request->getResponse();

        $details['paymentMethodNonce'] = $paymentMethodNonce;

        $this->findPaymentMethodNonceInfo($details);
    }

    protected function getPaymentMethodNonceInfo($nonceString)
    {
        $this->gateway->execute($request = new FindPaymentMethodNonce($details['paymentMethodNonce']));
        return $request->getPaymentMethodNonceArray();
    }

    protected function doSaleTransaction($details) 
    {
        $saleOptions = [
            'submitForSettlement' => false
        ];

        if ($details->offsetExists('paymentMethodNonce')) {

            $saleOptions['threeDSecure'] = [
                'required' => $this->cardholderAuthenticationRequired
            ];
        }

        $details['saleOptions'] = $saleOptions;        

        $this->gateway->execute($request = new DoSale($details));

        $transaction = $request->getResponse();

        $details['sale'] = TransactionResultArray::toArray($transaction);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Authorize &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
