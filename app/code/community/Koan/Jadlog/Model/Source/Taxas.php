<?php

class Koan_Jadlog_Model_Source_Taxas
{
    public function toOptionArray()
    {
        return array(
            array('value' => '0', 'label' => Mage::helper('adminhtml')->__('Nenhum')),
            array('value' => '1', 'label' => Mage::helper('adminhtml')->__('Valor Fixo')),
            array('value' => '2', 'label' => Mage::helper('adminhtml')->__('Porcentagem')),
        );
    }
}
