<?php
namespace Payum\Braintree\Reply\Api;

use Braintree\PaymentMethodNonce;
use Payum\Braintree\Util\ArrayUtils;

class PaymentMethodNonceArray
{
    public static function toArray(PaymentMethodNonce $object)
    {
        if (null == $object) {
            return;
        }

        $array = ArrayUtils::extractPropertiesToArray($object, [
            'nonce', 'consumed', 'default', 'type', 'threeDSecureInfo', 'details'
        ]);

        if (array_key_exists('threeDSecureInfo', $array)) {

            $array['threeDSecureInfo'] = ArrayUtils::extractPropertiesToArray($array['threeDSecureInfo'], [
                'enrolled', 'liabilityShiftPossible', 'liabilityShifted', 'status'
            ]);
        }
        
        if (array_key_exists('details', $array)) {

            $array['details'] = ArrayUtils::extractPropertiesToArray($array['details'], [
                'cardType', 'lastTwo', 'correlationId', 'email', 'payerInfo', 'username'
            ]);
        }

        return $array;
    }
}
