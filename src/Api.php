<?php
namespace Payum\Braintree;

use Payum\Core\Bridge\Spl\ArrayObject;
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

        $environment = 'sandbox';

        if (array_key_exists('environment', $this->options) && null !== $this->options['environment']) {
            $environment = $this->options['environment'];
        }
        else if (array_key_exists('sandbox', $this->options) && null !== $this->options['sandbox']) {
            $environment = !$this->options['sandbox'] ? 'production' : 'sandbox';
        }

        Configuration::environment($environment);

        Configuration::merchantId($this->options['merchantId']);
        Configuration::publicKey($this->options['publicKey']);
        Configuration::privateKey($this->options['privateKey']);
    }

    public function generateClientToken($params)
    {
        if (array_key_exists('merchantAccountId', $this->options) && null !== $this->options['merchantAccountId']) {
            $params['merchantAccountId'] = $this->options['merchantAccountId'];
        }

        return ClientToken::generate($params);
    }
    
    public function findPaymentMethodNonce($nonceString)
    {
        return PaymentMethodNonce::find($nonceString);
    }
    
    public function sale(ArrayObject $params)
    {
        $options = $params->offsetExists('options') ? $params['options'] : array();

        if (null !== $this->options['storeInVault'] && !isset($options['storeInVault'])) {
            $options['storeInVault'] = $this->options['storeInVault'];
        }

        if (null !== $this->options['storeInVaultOnSuccess'] && !isset($options['storeInVaultOnSuccess'])) {
            $options['storeInVaultOnSuccess'] = $this->options['storeInVaultOnSuccess'];
        }

        if (null !== $this->options['addBillingAddressToPaymentMethod'] && 
            !isset($options['addBillingAddressToPaymentMethod']) && 
            $params->offsetExists('billing')) {

            $options['addBillingAddressToPaymentMethod'] = $this->options['addBillingAddressToPaymentMethod'];
        }

        if (null !== $this->options['storeShippingAddressInVault'] && 
            !isset($options['storeShippingAddressInVault']) && 
            $params->offsetExists('shipping')) {

            $options['storeShippingAddressInVault'] = $this->options['storeShippingAddressInVault'];
        }

        $params['options'] = $options;

        if (array_key_exists('merchantAccountId', $this->options) && null !== $this->options['merchantAccountId']) {
            $params['merchantAccountId'] = $this->options['merchantAccountId'];
        }

        return Transaction::sale((array)$params);
    }
}
