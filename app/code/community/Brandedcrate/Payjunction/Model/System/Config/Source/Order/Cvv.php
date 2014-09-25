<?php
class Brandedcrate_Payjunction_Model_System_Config_Source_Order_Cvv
{
    // set null to enable all possible
    protected $_orderCvv = array(
        'ON' => 'On',
        'OFF' => 'Off',
    );

    public function toOptionArray()
    {
        $options = array();
        $options[] = array(
            'value' => '',
            'label' => Mage::helper('adminhtml')->__('-- Please Select --')
        );
        foreach ($this->_orderCvv as $code=>$label) {
            $options[] = array(
                'value' => $code,
                'label' => $label
            );
        }
        return $options;
    }
}
