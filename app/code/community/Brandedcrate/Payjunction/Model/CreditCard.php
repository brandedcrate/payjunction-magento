<?php

class Brandedcrate_Payjunction_Model_CreditCard extends Mage_Payment_Model_Method_Cc
{

    const REQUEST_METHOD_CC     = 'CC';
    const REQUEST_METHOD_ECHECK = 'ECHECK';
    const REQUEST_TYPE_AUTH_CAPTURE = 'AUTH_CAPTURE';
    const REQUEST_TYPE_AUTH_ONLY    = 'AUTH_ONLY';
    const REQUEST_TYPE_CAPTURE_ONLY = 'CAPTURE_ONLY';
    const REQUEST_TYPE_CREDIT       = 'CREDIT';
    const REQUEST_TYPE_VOID         = 'VOID';


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
     * @return Mage_Paygate_Model_Authorizenet
     */
    public function authorize(Varien_Object $payment, $amount)
    {


        if ($amount <= 0) {
            Mage::throwException(Mage::helper('payjunction')->__('Invalid amount for authorization.'));
        }

        $this->_initCardsStorage($payment);


        //@todo add partial authorization functionality
//        if ($this->isPartialAuthorization($payment)) {
//            $this->_partialAuthorization($payment, $amount, self::REQUEST_TYPE_AUTH_ONLY);
//            $payment->setSkipTransactionCreation(true);
//            return $this;
//        }


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
        if(!isset($this->_client) || !is_object($this->_client))
        {
            $this->_client = Mage::getModel('payjunction/client',$this->_getClientOptions());
        }
        return $this->_client;
    }

    /**
     * Init cards storage model
     *
     * @param Mage_Payment_Model_Info $payment
     */
    protected function _initCardsStorage($payment)
    {
        $this->_cardsStorage = Mage::getModel('payjunction/cards')->setPayment($payment);
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

        if($payment->getAmount()){
            $client->setXAmount($payment->getAmount(),2);
            $client->setXCurrencyCode($order->getBaseCurrencyCode());
        }


        switch ($payment->getPayjunctionTransType()) {
            case self::REQUEST_TYPE_AUTH_CAPTURE:
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

//        @todo look into centinel
//        if ($this->getIsCentinelValidationEnabled()){
//            $params  = $this->getCentinelValidator()->exportCmpiData(array());
//            $client = Varien_Object_Mapper::accumulateByMap($params, $client, $this->_centinelFieldMap);
//        }

        if (!empty($order)) {
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

        if($payment->getCcNumber()){
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
     * @param Mage_Paygate_Model_Authorizenet_Request $request)
     * @return Mage_Paygate_Model_Authorizenet_Result
     */
    protected function _postRequest(Brandedcrate_Payjunction_Model_Client $client)
    {
        $debugData = array('client' => $client->getData());
        $result = Mage::getModel('payjunction/result');
        $client->setEndpoint(Mage::getStoreConfig('payment/payjunction/endpoint'));



        $response = $client->request();


        Mage::throwException($response->response->code);

        if($response->response->code != '00') //If Transaction was declined
        {
            $result->setResponseCode(-1)
                ->setResponseReasonCode($response->response->code)
                ->setResponseReasonText($response->response->message);

            $debugData['result'] = $result->getData();
            $this->_debug($debugData);
            Mage::throwException($result->response->message);
        }




        //@todo deal with the response object and set codes accordingly
//        $responseBody = $response->getBody();
//
//        $r = explode(self::RESPONSE_DELIM_CHAR, $responseBody);
//
        if (isset($response)) {

            $result->setResponseCode($response->response->code);
            $result->setResponseReasonCode($response->response->code);
            $result->setResponseReasonText($response->response->message);
            $result->setApprovalCode($response->response->processor->approvalCode);
            $result->setAvsResultCode($response->response->processor->avs->status);
            $result->setTransactionId($response->transactionId);
        }
//            $result->setResponseCode((int)str_replace('"','',$r[0]))
//                ->setResponseSubcode((int)str_replace('"','',$r[1]))
//                ->setResponseReasonCode((int)str_replace('"','',$r[2]))
//                ->setResponseReasonText($r[3])
//                ->setApprovalCode($r[4])
//                ->setAvsResultCode($r[5])
//                ->setTransactionId($r[6])
//                ->setInvoiceNumber($r[7])
//                ->setDescription($r[8])
//                ->setAmount($r[9])
//                ->setMethod($r[10])
//                ->setTransactionType($r[11])
//                ->setCustomerId($r[12])
//                ->setMd5Hash($r[37])
//                ->setCardCodeResponseCode($r[38])
//                ->setCAVVResponseCode( (isset($r[39])) ? $r[39] : null)
//                ->setSplitTenderId($r[52])
//                ->setAccNumber($r[50])
//                ->setCardType($r[51])
//                ->setRequestedAmount($r[53])
//                ->setBalanceOnCard($r[54])
//            ;
//        }
//        else {
//            Mage::throwException(
//                Mage::helper('paygate')->__('Error in payment gateway.')
//            );
//        }

        $debugData['result'] = $result->getData();
        $this->_debug($debugData);

        return $result;
    }















    
    

    /**
     * Send request with new payment to gateway
     *
     * @param Mage_Payment_Model_Info $payment
     * @param decimal $amount
     * @param string $requestType
     * @return Mage_Paygate_Model_Authorizenet
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
                $defaultExceptionMessage = Mage::helper('paygate')->__('Payment authorization error.');
                break;
            case self::REQUEST_TYPE_AUTH_CAPTURE:
                $newTransactionType = Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE;
                $defaultExceptionMessage = Mage::helper('paygate')->__('Payment capturing error.');
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
                    Mage::helper('paygate')->getTransactionMessage(
                        $payment, $requestType, $card->getLastTransId(), $card, $amount
                    )
                );
                if ($requestType == self::REQUEST_TYPE_AUTH_CAPTURE) {
                    $card->setCapturedAmount($card->getProcessedAmount());
                    $this->getCardsStorage($payment)->updateCard($card);
                }
                return $this;
            case self::RESPONSE_CODE_HELD:
                if ($result->getResponseReasonCode() == self::RESPONSE_REASON_CODE_PENDING_REVIEW_AUTHORIZED
                    || $result->getResponseReasonCode() == self::RESPONSE_REASON_CODE_PENDING_REVIEW
                ) {
                    $card = $this->_registerCard($result, $payment);
                    $this->_addTransaction(
                        $payment,
                        $card->getLastTransId(),
                        $newTransactionType,
                        array('is_transaction_closed' => 0),
                        array(
                            $this->_realTransactionIdKey => $card->getLastTransId(),
                            $this->_isTransactionFraud => true
                        ),
                        Mage::helper('paygate')->getTransactionMessage(
                            $payment, $requestType, $card->getLastTransId(), $card, $amount
                        )
                    );
                    if ($requestType == self::REQUEST_TYPE_AUTH_CAPTURE) {
                        $card->setCapturedAmount($card->getProcessedAmount());
                        $this->getCardsStorage()->updateCard($card);
                    }
                    $payment
                        ->setIsTransactionPending(true)
                        ->setIsFraudDetected(true);
                    return $this;
                }
                if ($result->getResponseReasonCode() == self::RESPONSE_REASON_CODE_PARTIAL_APPROVE) {
                    $checksum = $this->_generateChecksum($request, $this->_partialAuthorizationChecksumDataKeys);
                    $this->_getSession()->setData($this->_partialAuthorizationChecksumSessionKey, $checksum);
                    if ($this->_processPartialAuthorizationResponse($result, $payment)) {
                        return $this;
                    }
                }
                Mage::throwException($defaultExceptionMessage);
            case self::RESPONSE_CODE_DECLINED:
            case self::RESPONSE_CODE_ERROR:
                Mage::throwException($this->_wrapGatewayError($result->getResponseReasonText()));
            default:
                Mage::throwException($defaultExceptionMessage);
        }
        return $this;
    }








}