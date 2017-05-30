<?php

class Koan_Jadlog_Model_Source_Seguro
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'A', 'label' => Mage::helper('adminhtml')->__('Apólice própria')),
            array('value' => 'N', 'label' => Mage::helper('adminhtml')->__('Normal'))
        );
    }
}
