<?php

class Koan_Jadlog_Model_Source_Entrega
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'D', 'label' => Mage::helper('adminhtml')->__('Domicilio')),
            array('value' => 'R', 'label' => Mage::helper('adminhtml')->__('Retira unidade JADLOG')),
        );
    }
}
