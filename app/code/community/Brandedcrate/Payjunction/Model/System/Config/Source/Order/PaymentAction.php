<?php

class Brandedcrate_Payjunction_Model_System_Config_Source_Order_PaymentAction
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE,
                'label' => 'Authorize Only'
            ),
            array(
                'value' => Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE,
                'label' => 'Authorize and Capture'
            ),
        );
    }
}

