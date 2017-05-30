<?php

class Koan_Jadlog_Model_Source_PagarDestino
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'S', 'label' => Mage::helper('adminhtml')->__('Sim')),
            array('value' => 'N', 'label' => Mage::helper('adminhtml')->__('Não'))
        );
    }
}
