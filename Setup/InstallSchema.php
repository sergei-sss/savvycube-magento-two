<?php

namespace SavvyCube\Connector\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $tableName = $setup->getTable('savvycube_nonce');
        if (!$setup->getConnection()->isTableExists($tableName)) {
            $table = $setup->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'nonce',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'nonce'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['default' => Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->setComment('SavvyCube Nonce Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $setup->getConnection()->createTable($table);
        }

        $tableName = $setup->getTable('savvycube_session');
        if (!$setup->getConnection()->isTableExists($tableName)) {
            $table = $setup->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'session',
                    Table::TYPE_TEXT,
                    40,
                    ['nullable' => false],
                    'session name'
                )
                ->addColumn(
                    'key',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'encryption key'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['default' => Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->setComment('SavvyCube Session Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $setup->getConnection()->createTable($table);
        }

        $setup->endSetup();
    }
}
