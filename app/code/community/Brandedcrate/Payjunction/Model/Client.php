<?php
class Brandedcrate_Payjunction_Model_Client extends BrandedCrate\PayJunction\Client
{

    private $_data = array();




    public function request()
    {
        switch ($this->_data['x_type']){
            case Brandedcrate_Payjunction_Model_CreditCard::REQUEST_TYPE_AUTH_CAPTURE:
                //@todo figure out what it means to auth and capture from payjunction
                $response = $this->transaction()->create(
                  array(
                      'cardNumber' => $this->getData('x_card_num'),
                      'cardExpMonth' => $this->getData('x_exp_month'),
                      'cardExpYear' => $this->getData('x_exp_year'),
                      'cardCvv' => $this->getData('x_card_code'),
                      'amountBase' => $this->getData('x_amount')
                  )
                );
                return $response;
            case Brandedcrate_Payjunction_Model_CreditCard::REQUEST_TYPE_AUTH_ONLY:
                //@todo figure out how to do an auth only
                $response = $this->transaction()->authorize(
                    array(
                        'cardNumber' => $this->getData('x_card_num'),
                        'cardExpMonth' => $this->getData('x_exp_month'),
                        'cardExpYear' => $this->getData('x_exp_year'),
                        'cardCvv' => $this->getData('x_card_code'),
                        'amountBase' => $this->getData('x_amount')
                    )
                );
                return $response;
            case Brandedcrate_Payjunction_Model_CreditCard::REQUEST_TYPE_CAPTURE_ONLY:
                //@todo figure out what it means to only capture without an authorization
                $response = $this->transaction()->create(
                    array(
                        'cardNumber' => $this->getData('x_card_num'),
                        'cardExpMonth' => $this->getData('x_exp_month'),
                        'cardExpYear' => $this->getData('x_exp_year'),
                        'cardCvv' => $this->getData('x_card_code'),
                        'amountBase' => $this->getData('x_amount')
                    )
                );
                return $response;
            case Brandedcrate_Payjunction_Model_CreditCard::REQUEST_TYPE_AUTH_CREDIT:
                //@todo figure out how to process a credit "refund" through payjunction
                return;
            case Brandedcrate_Payjunction_Model_CreditCard::REQUEST_TYPE_AUTH_VOID:
                //@todo figure out how to connect the mage transaction with a transaction id from payjunction
                return;

        }
    }


    public function getData($key)
    {
        return isset($this->_data[$key]) ? $this->_data[$key] : null;
    }



    /**
     * @description sets the transaction type
     * @param $type
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXType($type)
    {
        $this->_data['x_type'] = $type;
        return $this;
    }


    /**
     * @description sets the transaction method
     * @param $method
     * @return Brandedcrate_Payjunction_Model_Client
     */
    public function setXMethod($method)
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
    public function setXAmount(decimal $amount)
    {
        $this->_data['x_amount'] = $amount;
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


}