<?php
namespace Payum\Braintree\Reply\Api;

use Braintree\Instance;
use Payum\Braintree\Util\ArrayUtils;

class TransactionResultArray
{
    public static function toArray(Instance $object)
    {
        if (null == $object) {
            return;
        }

        $array = ArrayUtils::extractPropertiesToArray($object, [
            'success', 'transaction', 'errors'
        ]);

        if (array_key_exists('transaction', $array)) {
            $array['transaction'] = TransactionArray::toArray($array['transaction']);
        }

        return $array;
    }
}
