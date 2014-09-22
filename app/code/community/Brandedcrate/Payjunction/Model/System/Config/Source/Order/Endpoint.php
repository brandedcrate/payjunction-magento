<?php
class Brandedcrate_Payjunction_Model_System_Config_Source_Order_Endpoint
{
    // set null to enable all possible
    protected $_orderEnpoints = array(
        'live' => 'live',
        'test' => 'test'
    );

    public function toOptionArray()
    {
        $options = array();
        $options[] = array(
            'value' => '',
            'label' => Mage::helper('adminhtml')->__('-- Please Select --')
        );
        foreach ($this->_orderEnpoints as $code=>$label) {
            $options[] = array(
                'value' => $code,
                'label' => $label
            );
        }
        return $options;
    }
}
