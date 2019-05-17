<?php
$this->startSetup();
if (!$this->getConnection()->isTableExists($this->getTable('sorter/sorter'))) {
    $table = $this->getConnection()
        ->newTable($this->getTable('sorter/sorter'))
        ->addColumn(
            'editea_report_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'nullable' => false,
                'primary' => true,
            ],
            'Report ID'
        )
        ->addColumn(
            'report_type',
            Varien_Db_Ddl_Table::TYPE_TEXT, 255,
            [
                'nullable' => true,
            ],
            'Report Type'
        )
        ->addColumn(
            'report_level',
            Varien_Db_Ddl_Table::TYPE_TEXT, 255,
            [
                'nullable' => true,
            ],
            'Report Level'
        )
        ->addColumn(
            'report_item_id',
            Varien_Db_Ddl_Table::TYPE_TEXT, 255,
            [
                'nullable' => true,
            ],
            'Report Item ID'
        )
        ->addColumn(
            'report_message',
            Varien_Db_Ddl_Table::TYPE_TEXT, '2M',
            [
                'nullable' => true,
            ],
            'Report Message'
        )
        ->addColumn(
            'session_id',
            Varien_Db_Ddl_Table::TYPE_TEXT, 255,
            [
                'nullable' => true,
            ],
            'Session ID'
        )
        ->addColumn(
            'created_at',
            Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
            null,
            [],
            'Report Creation Time'
        )
        ->setComment('Editea Report');
    $this->getConnection()->createTable($table);
}

$this->endSetup();
