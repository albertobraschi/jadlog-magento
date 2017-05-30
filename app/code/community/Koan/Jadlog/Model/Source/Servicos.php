<?php

class Koan_Jadlog_Model_Source_Servicos
{
    public function toOptionArray()
    {
        return array(
            array('value' => '0', 'label' => Mage::helper('adminhtml')->__('EXPRESSO')),
            array('value' => '3', 'label' => Mage::helper('adminhtml')->__('.PACKAGE')),
            array('value' => '4', 'label' => Mage::helper('adminhtml')->__('RODOVIÁRIO')),
            array('value' => '5', 'label' => Mage::helper('adminhtml')->__('ECONÔMICO')),
            array('value' => '6', 'label' => Mage::helper('adminhtml')->__('DOC')),
            array('value' => '7', 'label' => Mage::helper('adminhtml')->__('CORPORATE')),
            array('value' => '9', 'label' => Mage::helper('adminhtml')->__('.COM')),
            array('value' => '10', 'label' => Mage::helper('adminhtml')->__('INTERNACIONAL')),
            array('value' => '12', 'label' => Mage::helper('adminhtml')->__('CARGO')),
            array('value' => '14', 'label' => Mage::helper('adminhtml')->__('EMERGÊNCIAL')),
        );
    }
}
