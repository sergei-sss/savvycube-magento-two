<?php

namespace SavvyCube\Connector\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
    * @var \Magento\Framework\Module\ModuleListInterface
    */
    protected $_moduleList;
    protected $_productMeta;
    protected $_db;
    protected $_storeEmulation;
    protected $_storeManager;
    protected $_categories;
    protected $_emulation;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\App\ProductMetadataInterface $productMeta,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Model\App\Emulation $emulation,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_moduleList = $moduleList;
        $this->_productMeta = $productMeta;
        $this->_db = $resource;
        $this->_emulation = $emulation;
        $this->_storeManager = $storeManager;
        $this->_storeEmulation = $emulation;
        parent::__construct($context);
    }

    public function getConnectorVersion()
    {
        $moduleCode = 'SavvyCube_Connector';
        $moduleInfo = $this->_moduleList->getOne($moduleCode);
        return $moduleInfo['setup_version'];
    }

    public function getMagentoVersion()
    {
        return $this->_productMeta->getVersion();
    }

    public function getConnection()
    {
        return $this->_db->getConnection(
            \Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
    }

    public function emulateStore($storeId)
    {
        $this->_storeEmulation->startEnvironmentEmulation($storeId);
    }

    public function stopStoreEmulation()
    {
        $this->_storeEmulation->stopEnvironmentEmulation();
    }

    public function getTableName($table)
    {
        return $this->_db->getTableName($table);
    }

    public function getStores()
    {
        return $this->_storeManager->getStores(true, true);
    }

    public function getStore($id)
    {
        return $this->_storeManager->getStore($id);
    }

    public function getDefaultStore()
    {
        return $this->_storeManager->getDefaultStoreView();
    }

    public function getFullCategoryPath($categoryId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $category = $objectManager
            ->create('SavvyCube\Connector\Model\EavCategory')
            ->load($categoryId);
        $result = "";
        if ($category->getId()) {
            $categoryFactory = $objectManager
                ->create('SavvyCube\Connector\Model\EavCategoryFactory');
            $categories = $categoryFactory->create()->getCollection()
                ->addAttributeToSelect('name')
                ->addAttributeToFilter('entity_id', array('in' => $category->getPathIds()))
                ->getItems();
            foreach ($category->getPathIds() as $id) {
                if (isset($categories[$id])) {
                    $result .= $categories[$id]['name'] . "/";
                } else {
                    $result .= 'Unknown' . "/";
                }
            };
        }

        return $result;
    }

    public function getRelativeCategoryPath($catId, $store)
    {
        $result = array();
        if (!isset($this->_categories)) {
             $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
             $factory = $objectManager
                 ->create('SavvyCube\Connector\Model\EavCategoryFactory');
             $collection = $factory->create()->getCollection()
                 ->addAttributeToSelect('name');
             $this->_categories = $collection->getItems();
        }

        $categories = $this->_categories;
        if (isset($categories[$catId])) {
            foreach ($categories[$catId]->getPathIds() as $id) {
                if (isset($categories[$id])) {
                    $result[] = $categories[$id]->getName();
                } else {
                    $result[] = 'Unknown';
                }
            }
        }

        if (isset($categories[$store->getRootCategoryId()])) {
            $rootCategory = $categories[$store->getRootCategoryId()];
            foreach ($rootCategory->getPathIds() as $id) {
                if (isset($categories[$id])) {
                    $prefix = $categories[$id]->getName();
                } else {
                    $prefix = 'Unknown';
                }

                if (!empty($result) && $result[0] == $prefix) {
                    array_shift($result);
                } else {
                    break;
                }
            }
        }

        return implode('/', $result);
    }

    public function getLimit($table, $column)
    {
        $query = $this->getConnection()->select()
            ->from($this->getTableName($table))
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->where("{$column} > 0")
            ->where("{$column} IS NOT NULL")
            ->columns(array("max({$column}) as max", "min({$column}) as min"));
        $result = $this->getConnection()->fetchRow($query);
        return $result;
    }

}
