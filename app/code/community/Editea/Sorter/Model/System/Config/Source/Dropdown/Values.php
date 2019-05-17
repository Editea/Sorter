<?php

class Editea_Sorter_Model_System_Config_Source_Dropdown_Values
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 0,
                'label' => 'Disable',
            ],
            [
                'value' => 1,
                'label' => 'Enable (debug level: low)',
            ],
            [
                'value' => 2,
                'label' => 'Enable (debug level: high)',
            ],
        ];
    }
}