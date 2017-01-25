<?php
namespace Payum\Braintree\Tests\Action\Api;

use Payum\Braintree\Action\Api\DoSaleAction;
use Payum\Braintree\Request\Api\DoSale;
use Payum\Braintree\Api;
use Braintree\Transaction;
use Braintree\Result;

class DoSaleActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldRespondWithBraintreeTransaction()
    {
        $apiMock = $this->createApiMock();

        $apiMock
            ->expects($this->once())
            ->method('sale')
            ->will($this->returnValue(new Result\Successful(Transaction::factory(array(
                'id' => 'aTransactionId'
            )))))
        ;

        $action = new DoSaleAction();
        $action->setApi($apiMock);

        $action->execute($request = new DoSale(array(
            'amount' => 10,
            'paymentMethodNonce' => 'first_nonce'
        )));

        $transactionResult = $request->getResponse();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Api
     */
    protected function createApiMock()
    {
        return $this->createMock(Api::class);
    }
}
