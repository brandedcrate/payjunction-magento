<?php
class Brandedcrate_Payjunction_Model_Client extends BrandedCrate\PayJunction\Client
{

    private $_data = array();




    private function afterProcessResponse($response)
    {
        //@todo remove logging
        Mage::log(print_r($response, true));
        return $response;
    }

    public function request()
    {

        switch ($this->_data['x_type']){
            case Brandedcrate_Payjunction_Model_CreditCard::REQUEST_TYPE_AUTH_CAPTURE:
                Mage::log('AUTH CAPTURE TRANSACTION', true); //@todo remove debug code
                //@todo figure out what it means to auth and capture from payjunction
                $response = $this->transaction()->create(
                  array(
                      'amountBase' => 0.99, //@todo change this to $this->getData('x_amount')
                      'status' => 'CAPTURE',
                      'action' => 'CHARGE',
                      'cardNumber' => $this->getData('x_card_num'),
                      'cardExpMonth' => $this->getData('x_exp_month'),
                      'cardExpYear' => $this->getData('x_exp_year'),
                      'cardCvv' => $this->getData('x_card_code') != null ? $this->getData('x_card_code') : $this->getData('x_auth_code'),
                      'invoiceNumber' => $this->getData('x_invoice_num'),
//                        'amountShipping' => $this->getData('x_freight'),
//                        'amountTax' => $this->getData('x_tax'),
                        'purchaseOrderNumber' => $this->getData('x_po_num'),

                        'avs' => Mage::getStoreConfig('payment/payjunction/avs'),
                        'cvv' => Mage::getStoreConfig('payment/payjunction/cvv'),

                        'billingIdentifier' => $this->getData('x_customer_id'),
                        'billingFirstName' => $this->getData('x_first_name'),
                        'billingLastName' => $this->getData('x_last_name'),
                        'billingCompanyName' => $this->getData('x_company'),
                        'billingPhone' => $this->getData('x_phone'),
                        'billingAddress' => $this->getData('x_address'),
                        'billingCity' => $this->getData('x_city'),
                        'billingState' => $this->getData('x_state'),
                        'billingZip' => $this->getData('x_zip'),
                        'billingCountry' => $this->getData('x_country'),
                        'billingEmail' => $this->getData('x_email'),

                        'shippingIdentifier' => $this->getData('x_customer_id'),
                        'shippingFirstName' => $this->getData('x_ship_to_first_name'),
                        'shippingLastName' => $this->getData('x_ship_to_last_name'),
                        'shippingCompanyName' => $this->getData('x_ship_to_company'),
                        'shippingPhone' => $this->getData('x_ship_to_phone'),
                        'shippingAddress' => $this->getData('x_ship_to_address'),
                        'shippingCity' => $this->getData('x_ship_to_city'),
                        'shippingState' => $this->getData('x_ship_to_state'),
                        'shippingZip' => $this->getData('x_ship_to_zip'),
                        'shippingCountry' => $this->getData('x_ship_to_country')
                  )
                );
                return $this->afterProcessResponse($response);
            case Brandedcrate_Payjunction_Model_CreditCard::REQUEST_TYPE_AUTH_ONLY:
                Mage::log('AUTH ONLY', true); //@todo remove debug code
                //@todo figure out how to do an auth only
                $response = $this->transaction()->create(
                    array(
                        'amountBase' => 0.99, //@todo change this to $this->getData('x_amount')
                        'status' => 'HOLD',
                        'action' => 'CHARGE',
                        'cardNumber' => $this->getData('x_card_num'),
                        'cardExpMonth' => $this->getData('x_exp_month'),
                        'cardExpYear' => $this->getData('x_exp_year'),
                        'cardCvv' => $this->getData('x_card_code') != null ? $this->getData('x_card_code') : $this->getData('x_auth_code'),
                        'invoiceNumber' => $this->getData('x_invoice_num'),
//                        'amountShipping' => $this->getData('x_freight'),
//                        'amountTax' => $this->getData('x_tax'),
                        'purchaseOrderNumber' => $this->getData('x_po_num'),

                        'avs' => Mage::getStoreConfig('payment/payjunction/avs'),
                        'cvv' => Mage::getStoreConfig('payment/payjunction/cvv'),

                        'billingIdentifier' => $this->getData('x_customer_id'),
                        'billingFirstName' => $this->getData('x_first_name'),
                        'billingLastName' => $this->getData('x_last_name'),
                        'billingCompanyName' => $this->getData('x_company'),
                        'billingPhone' => $this->getData('x_phone'),
                        'billingAddress' => $this->getData('x_address'),
                        'billingCity' => $this->getData('x_city'),
                        'billingState' => $this->getData('x_state'),
                        'billingZip' => $this->getData('x_zip'),
                        'billingCountry' => $this->getData('x_country'),
                        'billingEmail' => $this->getData('x_email'),

                        'shippingIdentifier' => $this->getData('x_customer_id'),
                        'shippingFirstName' => $this->getData('x_ship_to_first_name'),
                        'shippingLastName' => $this->getData('x_ship_to_last_name'),
                        'shippingCompanyName' => $this->getData('x_ship_to_company'),
                        'shippingPhone' => $this->getData('x_ship_to_phone'),
                        'shippingAddress' => $this->getData('x_ship_to_address'),
                        'shippingCity' => $this->getData('x_ship_to_city'),
                        'shippingState' => $this->getData('x_ship_to_state'),
                        'shippingZip' => $this->getData('x_ship_to_zip'),
                        'shippingCountry' => $this->getData('x_ship_to_country')
                    )
                );

                return $this->afterProcessResponse($response);
            case Brandedcrate_Payjunction_Model_CreditCard::REQUEST_TYPE_CAPTURE_ONLY:
                Mage::log('CAPTURE ONLY', true); //@todo remove debug code
                //@todo figure out what it means to only capture without an authorization
                $response = $this->transaction()->create(
                    array(
                        'amountBase' => 0.99, //@todo change this to $this->getData('x_amount')
                        'status' => 'CAPTURE',
                        'action' => 'CHARGE',
                        'cardNumber' => $this->getData('x_card_num'),
                        'cardExpMonth' => $this->getData('x_exp_month'),
                        'cardExpYear' => $this->getData('x_exp_year'),
                        'cardCvv' => $this->getData('x_card_code') != null ? $this->getData('x_card_code') : $this->getData('x_auth_code'),
                        'invoiceNumber' => $this->getData('x_invoice_num'),
//                        'amountShipping' => $this->getData('x_freight'),
//                        'amountTax' => $this->getData('x_tax'),
                        'purchaseOrderNumber' => $this->getData('x_po_num'),

                        'avs' => Mage::getStoreConfig('payment/payjunction/avs'),
                        'cvv' => Mage::getStoreConfig('payment/payjunction/cvv'),

                        'billingIdentifier' => $this->getData('x_customer_id'),
                        'billingFirstName' => $this->getData('x_first_name'),
                        'billingLastName' => $this->getData('x_last_name'),
                        'billingCompanyName' => $this->getData('x_company'),
                        'billingPhone' => $this->getData('x_phone'),
                        'billingAddress' => $this->getData('x_address'),
                        'billingCity' => $this->getData('x_city'),
                        'billingState' => $this->getData('x_state'),
                        'billingZip' => $this->getData('x_zip'),
                        'billingCountry' => $this->getData('x_country'),
                        'billingEmail' => $this->getData('x_email'),

                        'shippingIdentifier' => $this->getData('x_customer_id'),
                        'shippingFirstName' => $this->getData('x_ship_to_first_name'),
                        'shippingLastName' => $this->getData('x_ship_to_last_name'),
                        'shippingCompanyName' => $this->getData('x_ship_to_company'),
                        'shippingPhone' => $this->getData('x_ship_to_phone'),
                        'shippingAddress' => $this->getData('x_ship_to_address'),
                        'shippingCity' => $this->getData('x_ship_to_city'),
                        'shippingState' => $this->getData('x_ship_to_state'),
                        'shippingZip' => $this->getData('x_ship_to_zip'),
                        'shippingCountry' => $this->getData('x_ship_to_country')
                    )
                );
                return $this->afterProcessResponse($response);
            case Brandedcrate_Payjunction_Model_CreditCard::REQUEST_TYPE_CREDIT:
                Mage::log('REFUND TRANSACTION', true); //@todo remove debug code
                //@todo debug and make sure this is working
                $response = $this->transaction()->update($this->getData('x_trans_id'),
                    array(
                        'amountBase' => 0.99, //@todo change this to $this->getData('x_amount')
                        'action' => 'REFUND',
                    )
                );

                return $this->afterProcessResponse($response);
            case Brandedcrate_Payjunction_Model_CreditCard::REQUEST_TYPE_VOID:
                Mage::log('VOIDING TRANSACTION', true); //@todo remove debug code
//                @todo debug and make sure this is working
                $response = $this->transaction()->update($this->getData('x_trans_id'),
                    array(
                        'status' => 'VOID',
                    )
                );
                return $this->afterProcessResponse($response);

        }
    }


    public function getData($key='', $index=null)
    {
        if (''===$key) {
            return $this->_data;
        }

        $default = null;

        // accept a/b/c as ['a']['b']['c']
        if (strpos($key,'/')) {
            $keyArr = explode('/', $key);
            $data = $this->_data;
            foreach ($keyArr as $i=>$k) {
                if ($k==='') {
                    return $default;
                }
                if (is_array($data)) {
                    if (!isset($data[$k])) {
                        return $default;
                    }
                    $data = $data[$k];
                } elseif ($data instanceof Varien_Object) {
                    $data = $data->getData($k);
                } else {
                    return $default;
                }
            }
            return $data;
        }

        // legacy functionality for $index
        if (isset($this->_data[$key])) {
            if (is_null($index)) {
                return $this->_data[$key];
            }

            $value = $this->_data[$key];
            if (is_array($value)) {
                //if (isset($value[$index]) && (!empty($value[$index]) || strlen($value[$index]) > 0)) {
                /**
                 * If we have any data, even if it empty - we should use it, anyway
                 */
                if (isset($value[$index])) {
                    return $value[$index];
                }
                return null;
            } elseif (is_string($value)) {
                $arr = explode("\n", $value);
                return (isset($arr[$index]) && (!empty($arr[$index]) || strlen($arr[$index]) > 0))
                    ? $arr[$index] : null;
            } elseif ($value instanceof Varien_Object) {
                return $value->getData($index);
            }
            return $default;
        }
        return $default;
    }



    /**
     * @description sets the transaction type
     * @param $type
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXType($type = null)
    {
        $this->_data['x_type'] = $type;

        return $this;

    }


    /**
     * @description sets the transaction method
     * @param $method
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXMethod($method = null)
    {
        $this->_data['x_method'] = $method;
        return $this;
    }

    /**
     * @description sets the transaction method
     * @param $number
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXInvoiceNum($number)
    {
        $this->_data['x_invoice_num'] = $number;
        return $this;
    }


    /**
     * @description sets the transaction amount
     * @param decimal $amount
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXAmount($amount,$decimals = 2)
    {
        $this->_data['x_amount'] = number_format($amount,$decimals);
        return $this;
    }


    /**
     * @description sets the transaction currency code
     * @param $code
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXCurrencyCode($code)
    {
        $this->_data['x_currency_code'] = $code;
        return $this;
    }

    /**
     * @description sets bool allow partial authorization
     * @param $allow
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXAllowPartialAuth($allow)
    {
        $this->_data['x_allow_partial_auth'] = $allow;
        return $this;
    }


    /**
     * @description sets the split tendered id
     * @param $split_tender_id
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXSplitTenderId($split_tender_id)
    {
        $this->_data['x_split_tender_id'] = $split_tender_id;
        return $this;
    }


    /**
     * @description sets the transaction card number
     * @param $card_number
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXCardNum($card_number)
    {
        $this->_data['x_card_num'] = $card_number;
        return $this;
    }


    /**
     * @description sets the transaction credit card cvv or auth code
     * @param $auth_code
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXAuthCode($auth_code)
    {
        $this->_data['x_auth_code'] = $auth_code;
        return $this;
    }

    /**
     * @description sets the transaction first name from billing address
     * @param $first_name
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXFirstName($first_name)
    {
        $this->_data['x_first_name'] = $first_name;
        return $this;
    }

    /**
     * @description sets the transaction last name from billing address
     * @param $last_name
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXLastName($last_name)
    {
        $this->_data['x_last_name'] = $last_name;
        return $this;
    }

    /**
     * @description sets the transaction company name from the billing address
     * @param $last_name
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXCompany($company)
    {
        $this->_data['x_company'] = $company;
        return $this;
    }

    /**
     * @description sets the transaction address from the billing address street 1
     * @param $address
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXAddress($address)
    {
        $this->_data['x_address'] = $address;
        return $this;
    }

    /**
     * @description sets the transaction city from the billing address
     * @param $city
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXCity($city)
    {
        $this->_data['x_city'] = $city;
        return $this;
    }

    /**
     * @description sets the transaction state from the billing address
     * @param $state
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXState($state)
    {
        $this->_data['x_state'] = $state;
        return $this;
    }


    /**
     * @description sets the transaction zip from the billing address
     * @param $zip
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXZip($zip)
    {
        $this->_data['x_zip'] = $zip;
        return $this;
    }


    /**
     * @description sets the transaction country from the billing address
     * @param $country
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXCountry($country)
    {
        $this->_data['x_country'] = $country;
        return $this;
    }

    /**
     * @description sets the transaction telephone from the billing address
     * @param $telephone
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXPhone($telephone)
    {
        $this->_data['x_telephone'] = $telephone;
        return $this;
    }


    /**
     * @description sets the transaction fax from the billing address
     * @param $fax
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXFax($fax)
    {
        $this->_data['x_fax'] = $fax;
        return $this;
    }

    /**
     * @description sets the transaction customer id
     * @param $customer_id
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXCustId($customer_id)
    {
        $this->_data['x_cust_id'] = $customer_id;
        return $this;
    }

    /**
     * @description sets the transaction customer ip
     * @param $ip_number
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXCustomerIp($ip_number)
    {
        $this->_data['x_customer_ip'] = $ip_number;
        return $this;
    }


    /**
     * @description sets the transaction customer tax id number
     * @param $tax_id
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXCustomerTaxId($tax_id)
    {
        $this->_data['x_customer_tax_id'] = $tax_id;
        return $this;
    }



    /**
     * @description sets the transaction customer's email address
     * @param $email
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXEmail($email)
    {
        $this->_data['x_email'] = $email;
        return $this;
    }


    /**
     * @description sets bool to email customer or not
     * @param $email_customer
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXEmailCustomer($email_customer = FALSE)
    {
        $this->_data['x_email_customer'] = $email_customer;
        return $this;
    }



    /**
     * @description sets merchant email
     * @param $merchant_email
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXMerchantEmail($merchant_email)
    {
        $this->_data['x_merchant_email'] = $merchant_email;
        return $this;
    }



    /**
     * @description sets ship to first name from order shipping address
     * @param $first_name
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXShipToFirstName($first_name)
    {
        $this->_data['x_ship_to_first_name'] = $first_name;
        return $this;
    }


    /**
     * @description sets ship to last name from order shipping address
     * @param $last_name
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXShipToLastName($last_name)
    {
        $this->_data['x_ship_to_last_name'] = $last_name;
        return $this;
    }


    /**
     * @description sets ship to company from order shipping address
     * @param $company
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXShipToCompany($company)
    {
        $this->_data['x_ship_to_company'] = $company;
        return $this;
    }


    /**
     * @description sets ship to address from order shipping address
     * @param $address
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXShipToAddress($address)
    {
        $this->_data['x_ship_to_address'] = $address;
        return $this;
    }

    /**
     * @description sets ship to city from order shipping address
     * @param $city
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXShipToCity($city)
    {
        $this->_data['x_ship_to_city'] = $city;
        return $this;
    }

    /**
     * @description sets ship to state from order shipping address
     * @param $state
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXShipToState($state)
    {
        $this->_data['x_ship_to_state'] = $state;
        return $this;
    }

    /**
     * @description sets ship to zip from order shipping address
     * @param $zip
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXShipToZip($zip)
    {
        $this->_data['x_ship_to_zip'] = $zip;
        return $this;
    }

    /**
     * @description sets ship to country from order shipping address
     * @param $country
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXShipToCountry($country)
    {
        $this->_data['x_ship_to_country'] = $country;
        return $this;
    }

    /**
     * @description sets the po number from the order
     * @param $po_number
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXPoNum($po_number)
    {
        $this->_data['x_po_num'] = $po_number;
        return $this;
    }


    /**
     * @description sets the tax amount from the order
     * @param decimal $tax_amount
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXTax($tax_amount)
    {
        $this->_data['x_tax'] = $tax_amount;
        return $this;
    }


    /**
     * @description sets the freight amount from the order
     * @param decimal $freight_amount
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXFreight($freight_amount)
    {
        $this->_data['x_freight'] = $freight_amount;
        return $this;
    }


    /**
     * @description sets the card expiration date
     * @param $expiration_date
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXExpDate($expiration_date)
    {
        $this->_data['x_exp_date'] = $expiration_date;
        return $this;
    }


    /**
     * @description sets the card expiration month
     * @param $expiration_month
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXExpMonth($expiration_month)
    {
        $this->_data['x_exp_month'] = $expiration_month;
        return $this;
    }



    /**
     * @description sets the card expiration year
     * @param $expiration_year
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXExpYear($expiration_year)
    {
        $this->_data['x_exp_year'] = $expiration_year;
        return $this;
    }



    /**
     * @description sets the credit card cvv number
     * @param $card_code
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXCardCode($card_code)
    {
        $this->_data['x_card_code'] = $card_code;
        return $this;
    }


    /**
     * @description sets the customer id for the order
     * @param $customer_id
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXCustomerId($customer_id)
    {
        $this->_data['x_customer_id'] = $customer_id;
        return $this;
    }



    /**
     * @description sets the transaction id
     * @param $transaction_id
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXTransId($transaction_id)
    {
        $this->_data['x_trans_id'] = $transaction_id;
        return $this;
    }

}