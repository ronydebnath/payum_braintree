<?php
namespace Payum\Braintree\Reply\Api;

use Braintree\Instance;
use Payum\Braintree\Util\ArrayUtils;

class TransactionResultArray
{
    public static function toArray($object)
    {
        if (null == $object) {
            return;
        }

        $array = ArrayUtils::extractPropertiesToArray($object, [
            'success', 'transaction', 'errors'
        ]);

        if (array_key_exists('transaction', $array) && null !== $array['transaction']) {
            $array['transaction'] = TransactionArray::toArray($array['transaction']);
        }

        return $array;
    }
}
