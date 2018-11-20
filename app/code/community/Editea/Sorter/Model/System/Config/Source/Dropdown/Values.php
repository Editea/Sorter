<?php

class Editea_Sorter_Model_System_Config_Source_Dropdown_Values
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 0,
                'label' => 'Disable',
            ),
            array(
                'value' => 1,
                'label' => 'Enable (debug level: low)',
            ),
            array(
                'value' => 2,
                'label' => 'Enable (debug level: high)',
            )
        );
    }
}