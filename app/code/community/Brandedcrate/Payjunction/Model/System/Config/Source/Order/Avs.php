<?php
class Brandedcrate_Payjunction_Model_System_Config_Source_Order_Avs
{
    // set null to enable all possible
    protected $_orderAvs = array(
        'ADDRESS' => 'Address',
        'ZIP' => 'Zip',
        'ADDRESS_AND_ZIP' => 'Address and Zip',
        'ADDRESS_OR_ZIP' => 'Address Or Zip',
        'BYPASS' => 'Bypass',
        'OFF' => 'Off',
    );

    public function toOptionArray()
    {
        $options = array();
        $options[] = array(
            'value' => '',
            'label' => Mage::helper('adminhtml')->__('-- Please Select --')
        );
        foreach ($this->_orderAvs as $code=>$label) {
            $options[] = array(
                'value' => $code,
                'label' => $label
            );
        }
        return $options;
    }
}
