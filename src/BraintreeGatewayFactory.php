<?php
namespace Payum\Braintree;

use Payum\Braintree\Action\ConvertPaymentAction;
use Payum\Braintree\Action\AuthorizeAction;
use Payum\Braintree\Action\ObtainPaymentMethodNonceAction;
use Payum\Braintree\Action\ObtainCardholderAuthenticationAction;
use Payum\Braintree\Action\StatusAction;
use Payum\Braintree\Action\Api\GenerateClientTokenAction;
use Payum\Braintree\Action\Api\FindPaymentMethodNonceAction;
use Payum\Braintree\Action\Api\DoSaleAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

class BraintreeGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([

            'payum.factory_name' => 'braintree',
            'payum.factory_title' => 'braintree',

            'payum.template.obtain_payment_method_nonce' => '@PayumBraintree/Action/obtain_payment_method_nonce.html.twig',
            'payum.template.obtain_cardholder_authentication' => '@PayumBraintree/Action/obtain_cardholder_authentication.html.twig',

            'payum.action.convert_payment' => new ConvertPaymentAction(),

            'payum.action.authorize' => function(ArrayObject $config) {
                return new AuthorizeAction($config['cardholder_authentication_enabled']);
            },

            'payum.action.obtain_payment_method_nonce' => function(ArrayObject $config) {
                return new ObtainPaymentMethodNonceAction($config['payum.template.obtain_payment_method_nonce']);
            },

            'payum.action.obtain_cardholder_authentication' => function(ArrayObject $config) {
                return new ObtainCardholderAuthenticationAction($config['payum.template.obtain_cardholder_authentication']);
            },

            'payum.action.status' => new StatusAction(),

            'payum.action.api.generate_client_token' => new GenerateClientTokenAction(),
            'payum.action.api.find_payment_method_nonce' => new FindPaymentMethodNonceAction(),
            'payum.action.api.do_sale' => new DoSaleAction(),

            'cardholder_authentication_enabled' => true
        ]);
        
        if (false == $config['payum.api']) {
            $config['payum.default_options'] = array(
                'sandbox' => true,
            );
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = [];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new Api((array) $config, $config['payum.http_client'], $config['httplug.message_factory']);
            };
        }

        $config['payum.paths'] = array(
            'PayumBraintree' => __DIR__ . '/Resources/views'
        );
    }
}
