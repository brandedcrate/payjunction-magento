<?php

class Brandedcrate_Payjunction_Helper_Data extends Mage_Core_Helper_Abstract {

    /**
     * Return message for gateway transaction request
     *
     * @param Mage_Payment_Model_Info $payment
     * @param string $requestType
     * @param string $lastTransactionId
     * @param Varien_Object $card
     * @param float $amount
     * @param string $exception
     * @return bool|string
     */
    public function getTransactionMessage($payment, $requestType, $lastTransactionId, $card = false, $amount = false,
                                          $exception = false
    ) {
        return $this->getExtendedTransactionMessage(
            $payment, $requestType, $lastTransactionId, $card, $amount, $exception
        );
    }

    /**
     * Return message for gateway transaction request
     *
     * @param  Mage_Payment_Model_Info $payment
     * @param  string $requestType
     * @param  string $lastTransactionId
     * @param  Varien_Object $card
     * @param float $amount
     * @param string $exception
     * @param string $additionalMessage Custom message, which will be added to the end of generated message
     * @return bool|string
     */
    public function getExtendedTransactionMessage($payment, $requestType, $lastTransactionId, $card = false, $amount = false,
                                                  $exception = false, $additionalMessage = false
    ) {
        $operation = $this->_getOperation($requestType);

        if (!$operation) {
            return false;
        }

        if ($amount) {
            $amount = $this->__('amount %s', $this->_formatPrice($payment, $amount));
        }

        if ($exception) {
            $result = $this->__('failed');
        } else {
            $result = $this->__('successful');
        }

        if ($card) {
            $card = $this->__('Credit Card: xxxx-%s', $card->getCcLast4());
            $pattern = '%s %s %s - %s.';
            $texts = array($card, $amount, $operation, $result);
        } else {
            $pattern = '%s %s - %s.';
            $texts = array($amount, $operation, $result);
        }

        if (!is_null($lastTransactionId)) {
            $pattern .= ' %s.';
            $texts[] = $this->__('Payjunction.com Transaction ID %s', $lastTransactionId);
        }

        if ($additionalMessage) {
            $pattern .= ' %s.';
            $texts[] = $additionalMessage;
        }
        $pattern .= ' %s';
        $texts[] = $exception;

        return call_user_func_array(array($this, '__'), array_merge(array($pattern), $texts));
    }

    /**
     * Return operation name for request type
     *
     * @param  string $requestType
     * @return bool|string
     */
    protected function _getOperation($requestType)
    {
        switch ($requestType) {
        case Brandedcrate_Payjunction_Model_CreditCard::REQUEST_TYPE_AUTH_ONLY:
            return $this->__('authorize');
        case Brandedcrate_Payjunction_Model_CreditCard::REQUEST_TYPE_AUTH_CAPTURE:
            return $this->__('authorize and capture');
        case Brandedcrate_Payjunction_Model_CreditCard::REQUEST_TYPE_PRIOR_AUTH_CAPTURE:
            return $this->__('capture');
        case Brandedcrate_Payjunction_Model_CreditCard::REQUEST_TYPE_CREDIT:
            return $this->__('refund');
        case Brandedcrate_Payjunction_Model_CreditCard::REQUEST_TYPE_VOID:
            return $this->__('void');
        default:
            return false;
        }
    }

    /**
     * Format price with currency sign
     * @param  Mage_Payment_Model_Info $payment
     * @param float $amount
     * @return string
     */
    protected function _formatPrice($payment, $amount)
    {
        return $payment->getOrder()->getBaseCurrency()->formatTxt($amount);
    }
}
