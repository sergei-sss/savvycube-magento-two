<?php
namespace SavvyCube\Connector\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use SavvyCube\Connector\Helper;

class InstallData implements InstallDataInterface
{
    protected $helper;

    public function __construct(Helper\Data $helper) {
        $this->helper = $helper;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $data = [
            'scope' => 'default',
            'scope_id' => 0,
            'path' => 'savvycube/settings/base_url',
            'value' => $this->helper->getDefaultStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_WEB
            )
        ];
        $setup->getConnection()
            ->insertOnDuplicate($setup->getTable('core_config_data'), $data, ['value']);
        $setup->endSetup();
    }
}
