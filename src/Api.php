<?php
namespace Payum\Braintree;

use Braintree\Configuration;
use Braintree\ClientToken;
use Braintree\PaymentMethodNonce;
use Braintree\Transaction;

class Api
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * @param array               $options
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException if an option is invalid
     */
    public function __construct(array $options)
    {
        $this->options = $options;

        $this->initialise();
    }

    private function initialise()
    {
        Configuration::reset();

        Configuration::environment($this->options['sandbox'] ? 'sandbox' : 'production');

        Configuration::merchantId($this->options['merchantId']);
        Configuration::publicKey($this->options['publicKey']);
        Configuration::privateKey($this->options['privateKey']);
    }

    public function generateClientToken($params)
    {
        return ClientToken::generate($params);
    }
    
    public function findPaymentMethodNonce($nonceString)
    {
        return PaymentMethodNonce::find($nonceString);
    }
    
    public function sale($params)
    {
        return Transaction::sale($params);
    }
}
