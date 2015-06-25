<?php

class Brandedcrate_Payjunction_Model_CreditCard extends Mage_Payment_Model_Method_Cc
{
    const RESPONSE_CODE_APPROVED = '00';
    const RESPONSE_CODE_DECLINED = 2; //@todo not real
    const RESPONSE_CODE_ERROR    = 3; //@todo not real
    const RESPONSE_CODE_HELD     = 4; //@todo not real

    const RESPONSE_REASON_CODE_APPROVED = 00;
    const RESPONSE_REASON_CODE_NOT_FOUND = 16;
    const RESPONSE_REASON_CODE_PARTIAL_APPROVE = 295;
    const RESPONSE_REASON_CODE_PENDING_REVIEW_AUTHORIZED = 252;
    const RESPONSE_REASON_CODE_PENDING_REVIEW = 253;
    const RESPONSE_REASON_CODE_PENDING_REVIEW_DECLINED = 254;

    const REQUEST_METHOD_CC     = 'CC';
    const REQUEST_METHOD_ECHECK = 'ECHECK';
    const REQUEST_TYPE_AUTH_CAPTURE = 'AUTH_CAPTURE';
    const REQUEST_TYPE_AUTH_ONLY    = 'AUTH_ONLY';
    const REQUEST_TYPE_CAPTURE_ONLY = 'CAPTURE_ONLY';
    const REQUEST_TYPE_CREDIT       = 'CREDIT';
    const REQUEST_TYPE_VOID         = 'VOID';
    const REQUEST_TYPE_PRIOR_AUTH_CAPTURE = 'PRIOR_AUTH_CAPTURE';

    protected $_client;
    protected $_code = 'payjunction';
    protected $_isGateway = true;
    protected $_canAuthorize = false;
    protected $_canCapture = true;
    protected $_canCapturePartial = false;
    protected $_canRefund = true;
    protected $_canVoid = true;
    protected $_canUseInternal = true; //can use payment method on admin
    protected $_canUseCheckout = true; //can use payment metod on frontend
    protected $_canUseForMultishipping = true; //suitable for multi-shipping
    protected $_canSaveCC = false;

    /**
     * Void the payment through gateway
     *
     * @param  Mage_Payment_Model_Info $payment
     * @return Brandedcrate_Payjunction_Model_CreditCard
     */
    public function void(Varien_Object $payment)
    {
        $cardsStorage = $this->getCardsStorage($payment);

        $messages = array();
        $isSuccessful = false;
        $isFiled = false;

        foreach ($cardsStorage->getCards() as $card) {
            try {
                $newTransaction = $this->_voidCardTransaction($payment, $card);

                $messages[] = $newTransaction->getMessage();
                $isSuccessful = true;
            } catch (Exception $e) {
                Mage::throwException($e->getMessage()); //@todo debug code remove this line
                $messages[] = $e->getMessage();
                $isFiled = true;
                continue;
            }
            $cardsStorage->updateCard($card);
        }

        if ($isFiled) {
            $this->_processFailureMultitransactionAction($payment, $messages, $isSuccessful);
        }

        $payment->setSkipTransactionCreation(true);
        return $this;
    }

    /**
     * Cancel the payment through gateway
     *
     * @param Mage_Payment_Model_Info $payment Payment object
     *
     * @return Brandedcrate_Payjunction_Model_CreditCard
     */
    public function cancel(Varien_Object $payment)
    {
        return $this->void($payment);
    }

    /**
     * Void the card transaction through gateway
     *
     * @param Mage_Payment_Model_Info $payment
     * @param Varien_Object $card
     * @return Mage_Sales_Model_Order_Payment_Transaction
     */
    protected function _voidCardTransaction($payment, $card)
    {
        $authTransactionId = $card->getLastTransId();
        $payment->setPayjunctionTransType(self::REQUEST_TYPE_VOID);
        $payment->setXTransId($authTransactionId);

        $request = $this->_buildRequest($payment);
        $result = $this->_postRequest($request);

        switch ($result->getResponseCode()) {
        case self::RESPONSE_CODE_APPROVED:
            if ($result->getResponseReasonCode() == self::RESPONSE_REASON_CODE_APPROVED) {
                Mage::log('approved', true); //@todo remove debug code
                $voidTransactionId = $result->getTransactionId() . '-void';
                $card->setLastTransId($voidTransactionId);
                return $this->_addTransaction(
                    $payment,
                    $voidTransactionId,
                    Mage_Sales_Model_Order_Payment_Transaction::TYPE_VOID,
                    array(
                        'is_transaction_closed' => 1,
                        'should_close_parent_transaction' => 1,
                        'parent_transaction_id' => $authTransactionId //@todo not sure if this is correct , mage uses $authTransactionId I think they have been flip flopped
                    ),
                    array($this->_realTransactionIdKey => $result->getTransactionId()),
                    Mage::helper('payjunction')->getTransactionMessage(
                        $payment, self::REQUEST_TYPE_VOID, $result->getTransactionId(), $card
                    )
                );

            }
            $exceptionMessage = $result->getResponseReasonText();
            break;
        case self::RESPONSE_CODE_DECLINED:
            // @todo handle this
        default:
            $exceptionMessage = Mage::helper('payjunction')->__('Payment voiding error.');
            break;
        }

        $exceptionMessage = Mage::helper('payjunction')->getTransactionMessage(
            $payment, self::REQUEST_TYPE_VOID, $realAuthTransactionId, $card, false, $exceptionMessage
        );
        Mage::throwException($exceptionMessage);
    }

    /**
     * Send capture request to gateway
     *
     * @param Mage_Payment_Model_Info $payment
     * @param decimal $amount
     * @return Brandedcrate_Payjunction_Model_CreditCard
     */
    public function capture(Varien_Object $payment, $amount)
    {
        //If the amount is less than or equal to 0 then it cannot be captured
        if ($amount <= 0) {
            Mage::throwException(Mage::helper('payjunction')->__('Invalid amount for capture.'));
        }
        $this->_place($payment, $amount, self::REQUEST_TYPE_AUTH_CAPTURE);

        $payment->setSkipTransactionCreation(true);
        return $this;
    }

    /**
     * Send authorize request to gateway
     *
     * @param  Mage_Payment_Model_Info $payment
     * @param  decimal $amount
     * @return Mage_Payjunction_Model_Authorizenet
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        if ($amount <= 0) {
            Mage::throwException(Mage::helper('payjunction')->__('Invalid amount for authorization.'));
        }

        $this->_initCardsStorage($payment);
        $this->_place($payment, $amount, self::REQUEST_TYPE_AUTH_ONLY);
        $payment->setSkipTransactionCreation(true);
        return $this;
    }

    private function _getClientOptions()
    {
        $options = array(
            'username' => Mage::getStoreConfig('payment/payjunction/username'),
            'password' => Mage::getStoreConfig('payment/payjunction/password'),
            'appkey'   => Mage::getStoreConfig('payment/payjunction/appkey'),
            'endpoint' => Mage::getStoreConfig('payment/payjunction/endpoint') // 'test' or 'live'
        );
        return $options;
    }

    private function _getClient()
    {
        //If client is not set or it is not an object then set it and return it
        if (!isset($this->_client) || !is_object($this->_client)) {
            $this->_client = Mage::getModel('payjunction/client', $this->_getClientOptions());
        }
        return $this->_client;
    }

    /**
     * Init cards storage model
     *
     * @param Mage_Payment_Model_Info $payment
     * @return true
     */
    protected function _initCardsStorage($payment)
    {
        $this->_cardsStorage = Mage::getModel('payjunction/cards')->setPayment($payment);
        return true;
    }

    /**
     * Prepare request to gateway
     * @param Mage_Payment_Model_Info $payment
     * @return Brandedcrate_Payjunction_Model_Client
     */
    protected function _buildRequest(Varien_Object $payment)
    {
        $order = $payment->getOrder();
        $this->setStore($order->getStoreId());

        $client = $this->_getClient()
            ->setXType($payment->getPayjunctionTransType())
            ->setXMethod(self::REQUEST_METHOD_CC);

        if ($order && $order->getIncrementId()) {
            $client->setXInvoiceNum($order->getIncrementId());
        }

        if ($payment->getAmount()) {
            $client->setXAmount($payment->getAmount(), 2);
            $client->setXCurrencyCode($order->getBaseCurrencyCode());
        }

        switch ($payment->getPayjunctionTransType()) {
        case self::REQUEST_TYPE_AUTH_CAPTURE:
            $client->setXTransId($payment->parent_transaction_id);
            $client->setXAllowPartialAuth($this->getConfigData('allow_partial_authorization') ? 'True' : 'False');
            if ($payment->getAdditionalInformation($this->_splitTenderIdKey)) {
                $client->setXSplitTenderId($payment->getAdditionalInformation($this->_splitTenderIdKey));
            }
            break;
        case self::REQUEST_TYPE_AUTH_ONLY:
            $client->setXAllowPartialAuth($this->getConfigData('allow_partial_authorization') ? 'True' : 'False');
            if ($payment->getAdditionalInformation($this->_splitTenderIdKey)) {
                $client->setXSplitTenderId($payment->getAdditionalInformation($this->_splitTenderIdKey));
            }
            break;
        case self::REQUEST_TYPE_CREDIT:
            /**
              * Send last 4 digits of credit card number
              * otherwise it will give an error
              */
            $client->setXCardNum($payment->getCcLast4());
            $client->setXTransId($payment->getXTransId());
            break;
        case self::REQUEST_TYPE_VOID:
            $client->setXTransId($payment->getXTransId());
            break;
        case self::REQUEST_TYPE_PRIOR_AUTH_CAPTURE:
            $client->setXTransId($payment->getXTransId());
            break;
        case self::REQUEST_TYPE_CAPTURE_ONLY:
            $client->setXAuthCode($payment->getCcAuthCode());
            break;
        }

        if (!empty($order)) {
            //Set the customer id for the order
            if (Mage::getSingleton('core/session')->getVisitorData() == null) {
                $customer_data = Mage::getSingleton('core/session')->getVisitorData();
                $client->setXCustomerId($customer_data['visitor_id']);
            } else {
                $client->setXCustomerId(Mage::getSingleton('customer/session')->getId());
            }

            // @todo test and make sure that a guest id will also work
            // Mage::throwException($client->getData('x_customer_id'));

            $billing = $order->getBillingAddress();
            if (!empty($billing)) {
                $client->setXFirstName($billing->getFirstname())
                    ->setXLastName($billing->getLastname())
                    ->setXCompany($billing->getCompany())
                    ->setXAddress($billing->getStreet(1))
                    ->setXCity($billing->getCity())
                    ->setXState($billing->getRegion())
                    ->setXZip($billing->getPostcode())
                    ->setXCountry($billing->getCountry())
                    ->setXPhone($billing->getTelephone())
                    ->setXFax($billing->getFax())
                    ->setXCustId($order->getCustomerId())
                    ->setXCustomerIp($order->getRemoteIp())
                    ->setXCustomerTaxId($billing->getTaxId())
                    ->setXEmail($order->getCustomerEmail())
                    ->setXEmailCustomer($this->getConfigData('email_customer'))
                    ->setXMerchantEmail($this->getConfigData('merchant_email'));
            }

            $shipping = $order->getShippingAddress();
            if (!empty($shipping)) {
                $client->setXShipToFirstName($shipping->getFirstname())
                    ->setXShipToLastName($shipping->getLastname())
                    ->setXShipToCompany($shipping->getCompany())
                    ->setXShipToAddress($shipping->getStreet(1))
                    ->setXShipToCity($shipping->getCity())
                    ->setXShipToState($shipping->getRegion())
                    ->setXShipToZip($shipping->getPostcode())
                    ->setXShipToCountry($shipping->getCountry());
            }

            $client->setXPoNum($payment->getPoNumber())
                ->setXTax($order->getBaseTaxAmount())
                ->setXFreight($order->getBaseShippingAmount());
        }

        if ($payment->getCcNumber()) {
            $client->setXCardNum($payment->getCcNumber())
                ->setXExpDate(sprintf('%02d-%04d', $payment->getCcExpMonth(), $payment->getCcExpYear()))
                ->setXExpMonth($payment->getCcExpMonth())
                ->setXExpYear($payment->getCcExpYear())
                ->setXCardCode($payment->getCcCid());
        }

        return $client;
    }

    /**
     * Post request to gateway and return responce
     *
     * @param Mage_Payjunction_Model_Authorizenet_Request $request)
     * @return Mage_Payjunction_Model_Authorizenet_Result
     */
    protected function _postRequest(Brandedcrate_Payjunction_Model_Client $client)
    {
        $result = Mage::getModel('payjunction/result');
        $client->setEndpoint(Mage::getStoreConfig('payment/payjunction/endpoint'));
        $response = $client->request();

        if ($response->response->code != '00') {
            //If Transaction was declined

            $result->setResponseCode(-1)
                ->setResponseReasonCode($response->response->code)
                ->setResponseReasonText($response->response->message);

            //@todo try to come up with a more elegant way to throw error
            Mage::throwException($response->response->message);
        }

        if (isset($response)) {
            $result->setResponseCode(isset($response->response->code)?$response->response->code:null);
            $result->setResponseReasonCode(isset($response->response->code) ? $response->response->code : null);
            $result->setResponseReasonText(isset($response->response->message) ? $response->response->message : null);
            $result->setApprovalCode(isset($response->response->processor->approvalCode) ? $response->response->processor->approvalCode : null);
            $result->setAvsResultCode(isset($response->response->processor->avs->status) ? $response->response->processor->avs->status : null); //@todo not sure if this is correct
            $result->setTransactionId(isset($response->transactionId) ? $response->transactionId : null);
            $result->setInvoiceNumber($response->invoiceNumber);
            $result->setAmount(isset($response->amountTotal)?$response->amountTotal:null);
            $result->setMethod(isset($response->status)?$response->status:null); //@todo I dont know if this and the next one should be flipped
            $result->setTransactionType(isset($response->action)?$response->action:null);
            $result->setCustomerId(isset($response->billing->identifier)?$response->billing->identifier:null);
            $result->setCAVVResponseCode((isset($response->response->processor->cvv->status) ? $response->response->processor->cvv->status : null)); //@todo not sure if this is correct
            $result->setCardType(isset($response->vault->accountType)?$response->vault->accountType:null);
            $result->setRequestedAmount(isset($response->amountTotal)?$response->amountTotal:null);//@todo decide if this is necessary

            // $result->setBalanceOnCard(0);//@todo not sure if this is correct
            // $result->setResponseSubcode();//@todo not sure what this is
            // $result->setDescription(); //@todo Not sure what this is
            // $result->setCardCodeResponseCode() //@todo not sure what this is
            // $result->setSplitTenderId(); //@todo decide how to handle this
            // $result->setAccNumber(); //@todo decide how to handle this
        } else {
            Mage::throwException(
                Mage::helper('payjunction')->__('Error in payment gateway.')
            );
        }

        $this->logResult($result);

        return $result;
    }

    /**
     * Log the result of a transaction in the Mage log
     *
     * @param Brandedcrate_Payjunction_Model_Result $result Transaction result
     *
     * @return true
     */
    protected function logResult($result)
    {
        $params = implode(
            ', ', array(
                "id: {$result->getTransactionId()}",
                "code: {$result->getResponseCode()}",
                "reason: {$result->getResponseReasonText()}",
                "amount: {$result->getAmount()}",
                "type: {$result->getTransactionType()}",
                "method: {$result->getMethod()}",
                "approvalCode: {$result->getApprovalCode()}",
            )
        );

        Mage::log("Payjunction result ($params)");

        return true;
    }

    /**
     * Send request with new payment to gateway
     *
     * @param Mage_Payment_Model_Info $payment
     * @param decimal $amount
     * @param string $requestType
     * @return Mage_Payjunction_Model_Authorizenet
     * @throws Mage_Core_Exception
     */
    protected function _place($payment, $amount, $requestType)
    {
        $payment->setPayjunctionTransType($requestType);
        $payment->setAmount($amount);

        $client = $this->_buildRequest($payment);
        $result = $this->_postRequest($client);

        switch ($requestType) {
        case self::REQUEST_TYPE_AUTH_ONLY:
            $newTransactionType = Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH;
            $defaultExceptionMessage = Mage::helper('payjunction')->__('Payment authorization error.');
            break;
        case self::REQUEST_TYPE_AUTH_CAPTURE:
            $newTransactionType = Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE;
            $defaultExceptionMessage = Mage::helper('payjunction')->__('Payment capturing error.');
            break;
        }

        switch ($result->getResponseCode()) {
        case self::RESPONSE_CODE_APPROVED:
            $this->getCardsStorage($payment)->flushCards();
            $card = $this->_registerCard($result, $payment);

            $this->_addTransaction(
                $payment,
                $card->getLastTransId(),
                $newTransactionType,
                array('is_transaction_closed' => 0),
                array($this->_realTransactionIdKey => $card->getLastTransId()),
                Mage::helper('payjunction')->getTransactionMessage(
                    $payment, $requestType, $card->getLastTransId(), $card, $amount
                )
            );

            if ($requestType == self::REQUEST_TYPE_AUTH_CAPTURE) {
                $card->setCapturedAmount($card->getProcessedAmount());
                $this->getCardsStorage($payment)->updateCard($card);
            }
            return $this;

        case self::RESPONSE_CODE_HELD:
            // @todo: handle held
        case self::RESPONSE_CODE_DECLINED:
        case self::RESPONSE_CODE_ERROR:
            Mage::throwException($result->getResponseReasonText());
        default:
            Mage::throwException($defaultExceptionMessage);
        }
        Mage::throwException('continue');
        return $this;
    }

    /**
     * Return cards storage model
     *
     * @param Mage_Payment_Model_Info $payment
     * @return Brandedcrate_Payjunction_Model_Cards
     */
    public function getCardsStorage($payment = null)
    {
        if (is_null($payment)) {
            $payment = $this->getInfoInstance();
        }
        if (is_null($this->_cardsStorage)) {
            $this->_initCardsStorage($payment);
        }
        return $this->_cardsStorage;
    }

    /**
     * It sets card`s data into additional information of payment model
     *
     * @param Brandedcrate_Payjunction_Model_Result $response
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return Varien_Object
     */
    protected function _registerCard(Varien_Object $response, Mage_Sales_Model_Order_Payment $payment)
    {
        $cardsStorage = $this->getCardsStorage($payment);
        $card = $cardsStorage->registerCard();

        $card
            ->setRequestedAmount($response->getRequestedAmount())
            ->setBalanceOnCard($response->getBalanceOnCard())
            ->setLastTransId($response->getTransactionId())
            ->setProcessedAmount($response->getAmount())
            ->setCcType($payment->getCcType())
            ->setCcOwner($payment->getCcOwner())
            ->setCcLast4($payment->getCcLast4())
            ->setCcExpMonth($payment->getCcExpMonth())
            ->setCcExpYear($payment->getCcExpYear())
            ->setCcSsIssue($payment->getCcSsIssue())
            ->setCcSsStartMonth($payment->getCcSsStartMonth())
            ->setCcSsStartYear($payment->getCcSsStartYear());

        $cardsStorage->updateCard($card);
        $this->_clearAssignedData($payment);
        return $card;
    }

    /**
     * Reset assigned data in payment info model
     *
     * @param Mage_Payment_Model_Info
     * @return Brandedcrate_Payjunction_Model_CreditCard
     */
    private function _clearAssignedData($payment)
    {

        //@todo uncomment these lines
//        $payment->setCcType(null)
//            ->setCcOwner(null)
//            ->setCcLast4(null)
//            ->setCcNumber(null)
//            ->setCcCid(null)
//            ->setCcExpMonth(null)
//            ->setCcExpYear(null)
//            ->setCcSsIssue(null)
//            ->setCcSsStartMonth(null)
//            ->setCcSsStartYear(null)
//        ;
        return $this;
    }

    /**
     * Add payment transaction
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param string $transactionId
     * @param string $transactionType
     * @param array $transactionDetails
     * @param array $transactionAdditionalInfo
     * @return null|Mage_Sales_Model_Order_Payment_Transaction
     */
    protected function _addTransaction(Mage_Sales_Model_Order_Payment $payment, $transactionId, $transactionType,
                                       array $transactionDetails = array(), array $transactionAdditionalInfo = array(), $message = false
    ) {
        $payment->setTransactionId($transactionId);
        $payment->resetTransactionAdditionalInfo();
        foreach ($transactionDetails as $key => $value) {
            $payment->setData($key, $value);
        }
        foreach ($transactionAdditionalInfo as $key => $value) {
            $payment->setTransactionAdditionalInfo($key, $value);
        }
        $transaction = $payment->addTransaction($transactionType, null, false , $message);
        foreach ($transactionDetails as $key => $value) {
            $payment->unsetData($key);
        }
        $payment->unsLastTransId();

        /**
         * It for self using
         */
        $transaction->setMessage($message);

        return $transaction;
    }
}
