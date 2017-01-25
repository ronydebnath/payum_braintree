<?php
namespace Payum\Braintree\Tests\Action\Api;

use Payum\Braintree\Action\Api\GenerateClientTokenAction;
use Payum\Braintree\Request\Api\GenerateClientToken;
use Payum\Braintree\Api;

class GenerateClientTokenActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldRespondWithClientTokenString()
    {
        $apiMock = $this->createApiMock();

        $apiMock
            ->expects($this->once())
            ->method('generateClientToken')
            ->will($this->returnValue('aClientToken'))
        ;

        $action = new GenerateClientTokenAction();
        $action->setApi($apiMock);

        $action->execute($request = new GenerateClientToken(array()));

        $this->assertEquals('aClientToken', $request->getResponse());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Api
     */
    protected function createApiMock()
    {
        return $this->createMock(Api::class);
    }
}
