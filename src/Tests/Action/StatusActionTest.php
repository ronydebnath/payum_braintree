<?php
namespace Payum\Braintree\Tests\Action;

use Payum\Core\Request\GetHumanStatus;
use Payum\Braintree\Action\StatusAction;

class StatusActionTest extends GenericActionTest
{
    protected $actionClass = StatusAction::class;

    protected $requestClass = GetHumanStatus::class;

    /**
     * @test
     */
    public function shouldMarkNewIfDetailsEmpty()
    {
        $action = new StatusAction();

        $action->execute($status = new GetHumanStatus(array()));

        $this->assertTrue($status->isNew());
    }

    /**
     * @test
     */
    public function shouldMarkPendingIfOnlyHasPaymentMethodNonce()
    {
        $action = new StatusAction();

        $action->execute($status = new GetHumanStatus(array(
            'paymentMethodNonce' => '1234'
        )));

        $this->assertTrue($status->isPending());
    }

    /**
     * @test
     */
    public function shouldMarkFailedIfHasFailedStatus()
    {
        $action = new StatusAction();

        $action->execute($status = new GetHumanStatus(array(
            'status' => 'failed'
        )));

        $this->assertTrue($status->isFailed());
    }

    /**
     * @test
     */
    public function shouldMarkAuthorized()
    {
        $action = new StatusAction();

        $action->execute($status = new GetHumanStatus(array(
            'status' => 'authorized',
            'sale' => array(
                'success' => true
            )
        )));

        $this->assertTrue($status->isAuthorized());
    }

    /**
     * @test
     */
    public function shouldMarkCaptured()
    {
        $action = new StatusAction();

        $action->execute($status = new GetHumanStatus(array(
            'status' => 'captured',
            'sale' => array(
                'success' => true
            )
        )));

        $this->assertTrue($status->isCaptured());
    }
    
    /**
     * @test
     */
    public function shouldMarkUnknownIfMissingTransactionSuccess()
    {
        $action = new StatusAction();

        $action->execute($status = new GetHumanStatus(array(
            'status' => 'captured'
        )));

        $this->assertTrue($status->isUnknown());
    }
    
    /**
     * @test
     */
    public function shouldMarkUnknownIfTransactionFalseSuccess()
    {
        $action = new StatusAction();

        $action->execute($status = new GetHumanStatus(array(
            'status' => 'captured',
            'sale' => array(
                'success' => false    
            )
        )));

        $this->assertTrue($status->isUnknown());
    }
}
