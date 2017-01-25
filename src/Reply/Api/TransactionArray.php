<?php
namespace Payum\Braintree\Reply\Api;

use Braintree\Transaction;
use Payum\Braintree\Util\ArrayUtils;

class TransactionArray
{
    public static function toArray(Transaction $object)
    {
        if (null == $object) {
            return;
        }

        $array = ArrayUtils::extractPropertiesToArray($object, [
            'id', 'status', 'type', 'currentIsoCode', 'amount',
            'merchantAccountId', 'subMerchantAccountId', 'masterMerchantAccountId',
            'orderId', 'createdAt', 'updatedAt', 'customer', 'billing', 'refundId',
            'refundIds', 'refundedTransactionId', 'partialSettlementTransactionIds',
            'authorizedTransactionId', 'settlementBatchId', 'shipping', 'customFields',
            'avsErrorResponseCode', 'avsPostalCodeResponseCode', 'avsStreetAddressResponseCode',
            'cvvResponseCode', 'gatewayRejectionReason', 'processorAuthorizationCode', 
            'processorResponseCode', 'processorResponseText', 'additionalProcessorResponse',
            'voiceReferralNumber', 'purchaseOrderNumber', 'taxAmount', 'taxExempt', 'creditCard',
            'planId', 'subscriptionId', 'subscription', 'addOns', 'discounts', 'recurring',
            'channel', 'serviceFeeAmount', 'escrowStatus', /*disbursementDetails,*/
            'paymentInstrumentType', 'processorSettlementResponseCode', 
            'processorSettlementResponseText', 'threeDSecureInfo' /*, creditCardDetails */
        ]);

        return $array;
    }
}
