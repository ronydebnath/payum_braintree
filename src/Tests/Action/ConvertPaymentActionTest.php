<?php
namespace Payum\Braintree\Tests\Action;

use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetCurrency;
use Payum\Core\Security\TokenInterface;
use Payum\Braintree\Action\ConvertPaymentAction;

class ConvertPaymentActionTest extends GenericActionTest
{
    protected $actionClass = ConvertPaymentAction::class;

    protected $requestClass = Convert::class;

    public function provideSupportedRequests()
    {
        return array(
            array(new $this->requestClass(new Payment(), 'array')),
            array(new $this->requestClass($this->getMock(PaymentInterface::class), 'array')),
            array(new $this->requestClass(new Payment(), 'array', $this->getMock(TokenInterface::class))),
        );
    }

    public function provideNotSupportedRequests()
    {
        return array(
            array('foo'),
            array(array('foo')),
            array(new \stdClass()),
            array($this->getMockForAbstractClass(Generic::class, array(array()))),
            array(new $this->requestClass(new \stdClass(), 'array')),
            array(new $this->requestClass(new Payment(), 'foobar')),
            array(new $this->requestClass($this->getMock(PaymentInterface::class), 'foobar')),
        );
    }

    /**
     * @test
     */
    public function shouldCorrectlyConvertOrderToDetailsAndSetItBack()
    {
        $gatewayMock = $this->createMock('Payum\Core\GatewayInterface');
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\GetCurrency'))
            ->willReturnCallback(function (GetCurrency $request) {
                $request->name = 'Pound Sterling';
                $request->alpha3 = 'GBP';
                $request->numeric = 826;
                $request->exp = 2;
                $request->country = 'GB';
            })
        ;

        $order = new Payment();
        $order->setCurrencyCode('GBP');
        $order->setTotalAmount(123);

        $action = new ConvertPaymentAction();
        $action->setGateway($gatewayMock);

        $action->execute($convert = new Convert($order, 'array'));

        $details = $convert->getResult();

        $this->assertNotEmpty($details);

        $this->assertArrayNotHasKey('card', $details);

        $this->assertArrayHasKey('amount', $details);
        $this->assertEquals(1.23, $details['amount']);
    }

    /**
     * @test
     */
    public function shouldNotOverwriteAlreadySetExtraDetails()
    {
        $gatewayMock = $this->createMock('Payum\Core\GatewayInterface');
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\GetCurrency'))
            ->willReturnCallback(function (GetCurrency $request) {
                $request->name = 'Pound Sterling';
                $request->alpha3 = 'GBP';
                $request->numeric = 826;
                $request->exp = 2;
                $request->country = 'GB';
            })
        ;

        $order = new Payment();
        $order->setCurrencyCode('GBP');
        $order->setTotalAmount(123);
        $order->setDescription('order description');
        $order->setDetails(array(
            'foo' => 'fooVal',
        ));

        $action = new ConvertPaymentAction();
        $action->setGateway($gatewayMock);

        $action->execute($convert = new Convert($order, 'array'));

        $details = $convert->getResult();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('foo', $details);
        $this->assertEquals('fooVal', $details['foo']);
    }
}
