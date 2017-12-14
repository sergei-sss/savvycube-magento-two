<?php

namespace Inchoo\SetupTest\Setup;

use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class Uninstall implements UninstallInterface
{
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        foreach (['savvycube_nonce', 'savvycube_session'] as $table) {
            $tableName = $setup->getTable($table);
            if ($setup->getConnection()->isTableExists($tableName)) {
                $setup->getConnection()->dropTable($tableName);
            }
        }

        $setup->endSetup();
    }
}
