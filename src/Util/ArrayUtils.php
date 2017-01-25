<?php
namespace Payum\Braintree\Util;

class ArrayUtils
{
    public static function extractPropertiesToArray($object, $properties)
    {
        $array = array();

        foreach ($properties as $propertyName) {
            if (isset($object->{$propertyName})) {
                $array[$propertyName] = $object->{$propertyName};
            }
        }

        return $array;
    }
}
