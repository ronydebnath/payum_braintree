<?php
namespace Payum\Braintree\Tests\Action;

use Payum\Core\Request\Generic;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\RenderTemplate;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\GatewayInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Braintree\Action\ObtainCardholderAuthenticationAction;
use Payum\Braintree\Request\ObtainCardholderAuthentication;
use Payum\Braintree\Request\Api\GenerateClientToken;

class ObtainCardholderAuthenticationActionTest extends \PHPUnit_Framework_TestCase
{
    protected $action;

    public function setUp()
    {
        $this->action = new ObtainCardholderAuthenticationAction('aTemplateName');
    }

    /**
     * @test
     */
    public function shouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(ObtainCardholderAuthenticationAction::class);
        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function provideSupportedRequests()
    {
        return array(
            array(new ObtainCardholderAuthentication(array())),
            array(new ObtainCardholderAuthentication(new \ArrayObject())),
        );
    }

    public function provideNotSupportedRequests()
    {
        return array(
            array('foo'),
            array(array('foo')),
            array(new \stdClass()),
            array(new ObtainCardholderAuthentication('foo')),
            array(new ObtainCardholderAuthentication(new \stdClass())),
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
        $templateName = '';
        $templateParams = array();
        
        $gatewayMock = $this->createGatewayMock();

        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->will($this->returnCallback(function(GetHttpRequest $request) {
                $request->method = 'GET';
                $request->uri = 'pageUri';
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
            ->will($this->returnCallback(function(RenderTemplate $request) use(&$templateName, &$templateParams) {
                $templateName = $request->getTemplateName();
                $templateParams = $request->getParameters();
                $request->setResult('renderedTemplate');
            }))
        ;

        $this->action->setGateway($gatewayMock);

        try {
            $this->action->execute(new ObtainCardholderAuthentication(array(
                'paymentMethodNonce' => 'first_nonce',
                'paymentMethodNonceInfo' => array('nonce' => 'first_nonce'),
                'amount' => 10
            )));
        }
        catch(HttpResponse $reply) {

            $this->assertEquals('renderedTemplate', $reply->getContent());

            $this->assertEquals('aTemplateName', $templateName);

            $this->assertEquals('aClientToken', $templateParams['clientToken']);
            $this->assertEquals('first_nonce', $templateParams['creditCard']);
            $this->assertEquals(10, $templateParams['amount']);
            $this->assertEquals('pageUri', $templateParams['formAction']);

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
                $request->request['threeDSecure_payment_method_nonce'] = 'second_nonce';
            }))
        ;
     
        $this->action->setGateway($gatewayMock);

        $this->action->execute($request = new ObtainCardholderAuthentication(array(
            'paymentMethodNonce' => 'first_nonce',
            'paymentMethodNonceInfo' => array('nonce' => 'first_nonce')
        )));

        $this->assertEquals('second_nonce', $request->getResponse());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
