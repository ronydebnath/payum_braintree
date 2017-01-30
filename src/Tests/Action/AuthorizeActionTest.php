<?php
namespace Payum\Braintree\Tests\Action;

use Payum\Core\Request\Authorize;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Braintree\Action\AuthorizeAction;
use Payum\Braintree\Request\ObtainPaymentMethodNonce;
use Payum\Braintree\Request\ObtainCardholderAuthentication;
use Payum\Braintree\Request\Api\FindPaymentMethodNonce;
use Payum\Braintree\Request\Api\DoSale;

use Braintree\PaymentMethodNonce;
use Braintree\Transaction;
use Braintree\Result;

class AuthorizeActionTest extends GenericActionTest
{
    protected $actionClass = AuthorizeAction::class;
    
    protected $requestClass = Authorize::class;

    /**
     * @test
     */
    public function shouldImplementSetCardholderAuthenticationRequiredMethod()
    {
        $this->action->setCardholderAuthenticationRequired(true);
        $this->action->setCardholderAuthenticationRequired(false);   
    }

    /**
     * @test
     */
    public function shouldAuthorizeWithObtainedCreditCard()
    {
        $gatewayMock = $this->createGatwayMockWithExecutes([
            
            [
                'Payum\Braintree\Request\ObtainPaymentMethodNonce',

                function(ObtainPaymentMethodNonce $request) {

                    $request->setResponse('first_nonce');
                }
            ],

            [
                'Payum\Braintree\Request\Api\FindPaymentMethodNonce',

                function(FindPaymentMethodNonce $request) {

                    $request->setResponse(PaymentMethodNonce::factory([
                        'nonce' => 'first_nonce',
                        'consumed' => false,
                        'default' => false,
                        'type' => 'CreditCard',
                        'details' => [
                            'cardType' => 'Visa',
                            'last2' => '11',
                            'bin' => '123456'
                        ]
                    ]));
                }
            ],

            [
                'Payum\Braintree\Request\ObtainCardholderAuthentication',

                function(ObtainCardholderAuthentication $request) {

                    $request->setResponse('second_nonce');
                }
            ],
            
            [
                'Payum\Braintree\Request\Api\FindPaymentMethodNonce',

                function(FindPaymentMethodNonce $request) {

                    $request->setResponse(PaymentMethodNonce::factory([
                            'nonce' => 'second_nonce',
                            'consumed' => false,
                            'default' => false,
                            'type' => 'CreditCard',
                            'details' => [
                                'cardType' => 'Visa',
                                'last2' => '11'
                            ],
                            'threeDSecureInfo' => [
                                'enrolled' => 'Y',
                                'liabilityShiftPossible' => true,
                                'liabilityShifted' => false,
                                'status' => 'authenticate_successful'
                            ]
                    ]));
                }
            ],

            [
                'Payum\Braintree\Request\Api\DoSale',

                function(DoSale $request) {

                    $request->setResponse(new Result\Successful(Transaction::factory([
                        'id' => 'transaction_id',
                        'status' => 'authorized',
                        'type' => 'sale',
                        'currencyIsoCode' => 'EUR',
                        'amount' => 10,
                        'merchantAccountId' => '',
                        'paymentInstrumentType' => 'credit_card',
                        'creditCard' => [
                            'token' => '9662jd',
                            'bin' => '510510',
                            'last4' => '5100',
                            'cardType' => 'MasterCard',
                            'expirationMonth' => '12',
                            'expirationYear' => '2020',
                            'cardholderName' => null,
                            'issuingBank' => 'Unknown',
                            'countryOfIssuance' => 'Unknown',
                            'productId' => 'Unknown',
                            'uniqueNumberIdentifier' => '0658f55519a57e295d5e5f485559e405'
                        ]
                    ])));
                }
            ]
        ]);

        $action = new AuthorizeAction();
        $action->setGateway($gatewayMock);

        $request = new Authorize(array(
            'amount' => 10
        ));

        $action->execute($request);

        $model = iterator_to_array($request->getModel());

        $this->assertEquals('authorized', $model['status']);
        
        $this->assertEquals('second_nonce', $model['paymentMethodNonce']);
        $this->assertArrayHasKey('paymentMethodNonceInfo', $model);

        $this->assertArrayHasKey('sale', $model);
        $this->assertEquals('transaction_id', $model['sale']['transaction']['id']);
    }

    /**
     * @test
     */
    public function shouldNotMakeSubRequestsIfStatusResolved()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock->expects($this->never())->method('execute');

        $action = new AuthorizeAction();
        $action->setGateway($gatewayMock);

        $request = new Authorize(array(
            'status' => 'failed'
        ));

        $action->execute($request);

        $model = iterator_to_array($request->getModel());

        $this->assertEquals('failed', $model['status']);
    }

    /**
     * @test
     */
    public function shouldResolveFailedStatusForUnsuccessfulTransaction()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock->expects($this->never())->method('execute');

        $action = new AuthorizeAction();
        $action->setGateway($gatewayMock);

        $request = new Authorize(array(
            'paymentMethodNonce' => 'first_nonce',
            'paymentMethodNonceInfo' => array(
                'nonce' => 'first_nonce',
                'type' => 'unknown'
            ),
            'sale' => array(
                'success' => false,
                'transaction' => array(),
                'errors' => array()
            )
        ));

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldSetSaleOptions()
    {
        $gatewayMock = $this->createGatewayMock();

        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(DoSale::class))
            ->will($this->returnCallback(function($request) {

                $details = ArrayObject::ensureArrayObject($request->getModel());

                $this->assertArrayHasKey('saleOptions', $details);

                $this->assertTrue($details['saleOptions']['threeDSecure']['required']);
                $this->assertFalse($details['saleOptions']['submitForSettlement']);

                $request->setResponse(new Result\Successful(Transaction::factory([
                    'id' => 1,
                    'status' => 'authorized'
                ])));
            }))
        ;

        $action = new AuthorizeAction();
        $action->setGateway($gatewayMock);

        $request = new Authorize(array(
            'amount' => 123,
            'paymentMethodNonce' => 'first_nonce',
            'paymentMethodNonceInfo' => array(
                'nonce' => 'first_nonce',
                'type' => 'creditCard',
                'threeDSecureInfo' => array(
                    'enrolled' => 'Y',
                    'status' => 'authenticate_successful',
                    'liabilityShifted' => true,
                    'liabilityShiftPossible' => true
                )
            )
        ));

        $action->execute($request);
    }

    protected function createGatwayMockWithExecutes($executeSequence)
    {
        $gatewayMock = $this->createGatewayMock();

        foreach ($executeSequence as $index => $executeAction) {

            $requestClass = $executeAction[0];
            $action = $executeAction[1];

            $gatewayMock
                ->expects($this->at($index))
                ->method('execute')
                ->with($this->isInstanceOf($requestClass))
                ->will($this->returnCallback($action));
        }

        return $gatewayMock;
    }
}
