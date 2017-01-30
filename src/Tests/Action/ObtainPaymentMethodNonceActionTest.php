<?php
namespace Payum\Braintree\Tests\Action;

use Payum\Core\Request\Generic;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\RenderTemplate;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\GatewayInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Braintree\Action\ObtainPaymentMethodNonceAction;
use Payum\Braintree\Request\ObtainPaymentMethodNonce;
use Payum\Braintree\Request\Api\GenerateClientToken;

class ObtainPaymentMethodNonceActionTest extends \PHPUnit_Framework_TestCase
{
    protected $action;

    public function setUp()
    {
        $this->action = new ObtainPaymentMethodNonceAction('aTemplateName');
    }

    /**
     * @test
     */
    public function shouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(ObtainPaymentMethodNonceAction::class);
        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementSetCardholderAuthenticationRequiredMethod()
    {
        $this->action->setCardholderAuthenticationRequired(true);
        $this->action->setCardholderAuthenticationRequired(false);   
    }

    public function provideSupportedRequests()
    {
        return array(
            array(new ObtainPaymentMethodNonce(array())),
            array(new ObtainPaymentMethodNonce(new \ArrayObject())),
        );
    }

    public function provideNotSupportedRequests()
    {
        return array(
            array('foo'),
            array(array('foo')),
            array(new \stdClass()),
            array(new ObtainPaymentMethodNonce('foo')),
            array(new ObtainPaymentMethodNonce(new \stdClass())),
            array($this->getMockForAbstractClass(Generic::class, array(array()))),
        );
    }

    /**
     * @test
     *
     * @dataProvider provideSupportedRequests
     */
    public function shouldSupportRequest($request)
    {
        $this->assertTrue($this->action->supports($request));
    }

    /**
     * @test
     *
     * @dataProvider provideNotSupportedRequests
     */
    public function shouldNotSupportRequest($request)
    {
        $this->assertFalse($this->action->supports($request));
    }

    /**
     * @test
     */
    public function shouldThrowHttpResponseIfHttpRequestNotPost()
    {
        $gatewayMock = $this->createGatewayMock();

        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->will($this->returnCallback(function(GetHttpRequest $request) {
                $request->method = 'GET';
            }))
        ;

        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(GenerateClientToken::class))
            ->will($this->returnCallback(function(GenerateClientToken $request) {
                $request->setResponse('aClientToken');
            }))
        ;

        $gatewayMock
            ->expects($this->at(2))
            ->method('execute')
            ->with($this->isInstanceOf(RenderTemplate::class))
            ->will($this->returnCallback(function(RenderTemplate $request) {
                
                $this->assertEquals('aTemplateName', $request->getTemplateName());

                $templateParameters = $request->getParameters();

                $this->assertEquals('aClientToken', $templateParameters['clientToken']);
                $this->assertEquals(123, $templateParameters['amount']);
                $this->assertEquals(false, $templateParameters['obtainCardholderAuthentication']);

                $request->setResult('renderedTemplate');
            }))
        ;

        $this->action->setGateway($gatewayMock);

        $this->action->setCardholderAuthenticationRequired(false);

        try {
            $this->action->execute(new ObtainPaymentMethodNonce(array(
                'amount' => 123
            )));
        }
        catch(HttpResponse $reply) {

            $this->assertEquals('renderedTemplate', $reply->getContent());

            return;
        }

        $this->fail('HttpResponse reply was expected to be thrown.');
    }

    /**
     * @test
     */ 
    public function shouldSetResponseIfHttpRequestPost()
    {
        $gatewayMock = $this->createGatewayMock();

        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->will($this->returnCallback(function(GetHttpRequest $request) {
                $request->method = 'POST';
                $request->request['payment_method_nonce'] = 'aPaymentMethodNonce';
            }))
        ;
     
        $this->action->setGateway($gatewayMock);

        $this->action->execute($request = new ObtainPaymentMethodNonce(array()));

        $this->assertEquals('aPaymentMethodNonce', $request->getResponse());
    }

    /**
     * @test
     */
    public function shouldNotOperateIfPaymentMethodNoncePresent()
    {
        $gatewayMock = $this->createGatewayMock();

        $gatewayMock
            ->expects($this->never())
            ->method('execute')
        ;

        $this->action->setGateway($gatewayMock);

        $this->action->execute($request = new ObtainPaymentMethodNonce(array(
            'paymentMethodNonce' => 'first_nonce'
        )));

        $this->assertEquals('first_nonce', $request->getResponse());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
