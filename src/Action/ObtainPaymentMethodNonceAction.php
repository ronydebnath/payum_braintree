<?php
namespace Payum\Braintree\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\RenderTemplate;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Reply\HttpResponse;

use Payum\Braintree\Request\ObtainPaymentMethodNonce;
use Payum\Braintree\Request\Api\GenerateClientToken;

class ObtainPaymentMethodNonceAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    private $templateName;

    protected $cardholderAuthenticationRequired;

    public function __construct($templateName)
    {
        $this->templateName = $templateName;
        
        $this->cardholderAuthenticationRequired = true;
    }

    public function setCardholderAuthenticationRequired($value)
    {
        $this->cardholderAuthenticationRequired = $value;
    }

    /**
     * {@inheritDoc}
     *
     * @param ObtainPaymentMethodNonce $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());
        
        if (true == $details->offsetExists('paymentMethodNonce')) {

            $request->setResponse($details['paymentMethodNonce']);
            return;
        }

        $this->gateway->execute($clientHttpRequest = new GetHttpRequest());
        
        if (array_key_exists('payment_method_nonce', $clientHttpRequest->request)) {

            $paymentMethodNonce = $clientHttpRequest->request['payment_method_nonce'];

            $request->setResponse($paymentMethodNonce);
            return;
        }
        
        if (false == $details->offsetExists('clientToken')) {
            $this->generateClientToken($details);
        }

        $details->validateNotEmpty(['clientToken']);

        $this->gateway->execute($template = new RenderTemplate($this->templateName, [
            'formAction' => $clientHttpRequest->uri,
            'clientToken' => $details['clientToken'],
            'amount' => $details['amount'],
            'details' => $details,
            'obtainCardholderAuthentication' => $this->cardholderAuthenticationRequired
        ]));

        throw new HttpResponse($template->getResult());
    }

    protected function generateClientToken($details)
    {
        $request = new GenerateClientToken();

        $this->gateway->execute($request);

        $details['clientToken'] = $request->getResponse();
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof ObtainPaymentMethodNonce &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
