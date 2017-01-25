<?php
namespace Payum\Braintree\Tests\Action\Api;

use Payum\Braintree\Action\Api\FindPaymentMethodNonceAction;
use Payum\Braintree\Request\Api\FindPaymentMethodNonce;
use Payum\Braintree\Api;
use Braintree\PaymentMethodNonce;

class FindPaymentMethodNonceActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldResponseWithBraintreePaymentMethodNonceObject()
    {
        $apiMock = $this->createApiMock();

        $apiMock
            ->expects($this->once())
            ->method('findPaymentMethodNonce')
            ->will($this->returnValue(PaymentMethodNonce::factory(array(
                'nonce' => 'first_nonce',
                'type' => 'aPaymentMethodType'
            ))))
        ;

        $action = new FindPaymentMethodNonceAction();
        $action->setApi($apiMock);

        $action->execute($request = new FindPaymentMethodNonce('first_nonce'));

        $paymentMethodNonceInfo = $request->getResponse();

        $this->assertEquals('first_nonce', $paymentMethodNonceInfo->nonce);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Api
     */
    protected function createApiMock()
    {
        return $this->createMock(Api::class);
    }
}
