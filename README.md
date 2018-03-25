# Payum_Braintree

A Payum extension for Braintree gateway integration

## Configuration

Register a gateway factory to the payum's builder and create a gateway:

```php
<?php

use Payum\Core\PayumBuilder;

$defaultConfig = [];

$payum = (new PayumBuilder)
    ->addGatewayFactory('braintree', new Payum\Braintree\BraintreeGatewayFactory($defaultConfig))

    ->addGateway('braintree', [
        'factory' => 'braintree',
        'sandbox' => true,
        'merchantId' => '123123',
        'publicKey' => '999999',
        'privateKey' => '777888',
    ])

    ->getPayum()
;
```

Or, if your are working on the bases of Symfony, you can define it in a service that way :
```yml
    acme.braintree_gateway_factory:
        class: Payum\Core\Bridge\Symfony\Builder\GatewayFactoryBuilder
        arguments: [Payum\Braintree\BraintreeGatewayFactory]
        tags:
            - { name: payum.gateway_factory_builder, factory: braintree }
```
and in config.yml

```yml
payum:
    gateways:
        braintree:
            factory: braintree
            payum.http_client: '@payum.http_client'
            merchantId: 123123
            publicKey: 999999
            privateKey: 777888
```



Using the gateway:

```php
<?php

use Payum\Core\Request\Capture;

/** @var \Payum\Core\Payum $payum */
$paypal = $payum->getGateway('braintree');

$model = new \ArrayObject([
  // ...
]);

$paypal->execute(new Capture($model));
