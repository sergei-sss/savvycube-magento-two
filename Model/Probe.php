<?php

namespace SavvyCube\Connector\Model;

class Probe extends Api
{
    public function report($params)
    {
        return array(
            'module_version' => $this->helper->getConnectorVersion(),
            'magento_version'=> $this->helper->getMagentoVersion(),
            'source_bottom' => $this->getBottom(),
            'utc_timestamp' => $this->helper->getConnection()
                ->fetchOne('SELECT UTC_TIMESTAMP();'),
            'timezone' => $this->helper->getDefaultStore()
                ->getConfig('general/locale/timezone'),
            'stores' => $this->getStores(),
            'store_limits' => $this->getStoreLimits(),
            'limits' => $this->getLimits()
        );
    }

    public function getBottom()
    {
        $bottoms = array($this->getSelect()
            ->from(array('ent' => $this->getTable('sales_order')))
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->columns('MIN(created_at) AS bottom_date')
            ->where('created_at > 0')
            ->where('created_at IS NOT NULL'),
            $this->getSelect()
            ->from(array('ent' => $this->getTable('quote')))
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->columns('MIN(created_at) AS bottom_date')
            ->where('created_at > 0')
            ->where('created_at IS NOT NULL'),
            $this->getSelect()
            ->from(array('ent' => $this->getTable('customer_entity')))
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->columns('MIN(created_at) AS bottom_date')
            ->where('created_at > 0')
            ->where('created_at IS NOT NULL'),
            $this->getSelect()
            ->from(array('ent' => $this->getTable('catalog_product_entity')))
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->columns('MIN(created_at) AS bottom_date')
            ->where('created_at > ?', '2018-01-01 00:00:01')
            ->where('created_at IS NOT NULL'),
            'SELECT UTC_TIMESTAMP();');
        return min(array_filter(array_map(
            array($this->helper->getConnection(), 'fetchOne'), $bottoms)));
    }

    public function getStores()
    {
        $result = array();
        foreach ($this->helper->getStores() as $code => $store) {
            $result[$code] = array(
                'store_id' => $store->getId(),
                'store_code' => $store->getCode(),
                'store_name' => $store->getName(),
                'is_default_store' => (bool)$store->isDefault(),
                'website_id' => $store->getWebsiteId(),
                'website_code' => $store->getWebsite()->getCode(),
                'website_name' => $store->getWebsite()->getName(),
                'is_default_website' => (bool)$store->getWebsite()->getIsDefault(),
                'root_category_id' => $store->getRootCategoryId(),
                'root_category' => $this->helper->getFullCategoryPath($store->getRootCategoryId()),
                'base_url' => $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB, false),
                'secure_base_url' => $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB, true),
                'ga_property_id' => $store->getConfig('google/analytics/account'),
                'ga_active' => $store->getConfig('google/analytics/active'),
                'ga_ip_anonymization' => $store->getConfig('google/analytics/anonymize'),
                'ga_type' => 'universal'
            );
        }
        return $result;
    }

    public function getStoreLimits()
    {
        $result = array();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        foreach ($this->helper->getStores() as $code => $store) {
            $this->helper->emulateStore($store->getId());
            $factory = $objectManager
                ->create('SavvyCube\Connector\Model\EavCategoryFactory');
            $treeRoot = \Magento\Catalog\Model\Category::TREE_ROOT_ID;
            $storeRoot = $store->getRootCategoryId();
            $query = $factory->create()->getCollection()
                ->addAttributeToFilter('path',
                    array('like' => "$treeRoot/{$storeRoot}%")
                )
                ->getSelect()
                ->reset(\Magento\Framework\DB\Select::COLUMNS)
                ->where('updated_at > 0')
                ->where('updated_at IS NOT NULL')
                ->columns(array('max(updated_at) as max', 'min(updated_at) as min'));
            $result[$store->getCode()]['category'] = $this->helper->getConnection()->fetchRow($query);

            # product
            $treeRoot = \Magento\Catalog\Model\Category::TREE_ROOT_ID;
            $storeRoot = $store->getRootCategoryId();
            $catSubquery = $this->getSelect()
                ->from(array('cat_prod' => $this->getTable('catalog_category_product')))
                ->joinLeft(
                    array('cat' => $this->getTable('catalog_category_entity')),
                    'cat.entity_id = cat_prod.category_id'
                )
                ->reset(\Magento\Framework\DB\Select::COLUMNS)
                ->where('cat.updated_at > 0')
                ->where('cat.updated_at IS NOT NULL')
                ->columns(
                    array(
                        'updated_at' => 'max(cat.updated_at)',
                        'product_id' => 'cat_prod.product_id'
                    )
                )
                ->where('path like ?', "{$treeRoot}/{$storeRoot}%")
                ->group('cat_prod.product_id');
            $collection = $objectManager
                ->create('Magento\Catalog\Model\ResourceModel\Product\Collection');
            if ($store->getWebsiteId() != 0) {
                $collection->addWebsiteFilter();
            }
            $query = $collection
                ->getSelect()
                ->where('e.updated_at > 0')
                ->where('e.updated_at IS NOT NULL')
                ->joinLeft(
                    array('cat_sum' => $catSubquery),
                    'cat_sum.product_id = e.entity_id',
                    array(
                        'max_cat_updated_at' => 'cat_sum.updated_at',
                    )
                )
                ->reset(\Magento\Framework\DB\Select::COLUMNS)
                ->columns(array(
                    'max(GREATEST(COALESCE(cat_sum.updated_at, 0), e.updated_at)) as max',
                    'min(LEAST(COALESCE(cat_sum.updated_at, e.updated_at), e.updated_at)) as min'
                ));

            $result[$store->getCode()]['product']
                = $this->helper->getConnection()->fetchRow($query);
            $this->helper->stopStoreEmulation();
        }

        return $result;
    }

    public function getLimits()
    {
        return array(
            'customer' => $this->helper->getLimit('customer_entity', 'updated_at'),
            'order' => $this->helper->getLimit('sales_order', 'updated_at'),
            'quote' => $this->getQuoteLimit(),
            'invoice' => $this->helper->getLimit('sales_invoice', 'updated_at'),
            'refund' => $this->helper->getLimit('sales_creditmemo', 'updated_at'),
            'shipment' => $this->helper->getLimit('sales_shipment', 'updated_at'),
            'rewrite' => $this->helper->getLimit('url_rewrite', 'entity_id'),
            'transaction' => $this->helper->getLimit('sales_payment_transaction', 'created_at'));
    }

    public function getQuoteLimit()
    {
        $result = array('max' => null, 'min' => null);
        foreach (array('quote', 'quote_item', 'quote_address') as $table) {
            $tableResult = $this->helper->getConnection()->fetchRow(
                $this->helper->getConnection()->select()
                ->from($this->getTable($table))
                ->reset(\Magento\Framework\DB\Select::COLUMNS)
                ->where('updated_at > 0')
                ->where('updated_at is not Null')
                ->columns(array(
                    'max(updated_at) as max',
                    'min(updated_at) as min',
                ))
            );

            if ($tableResult['max']
                && (is_null($result['max'])
                    || $tableResult['max'] > $result['max'])) {
                $result['max'] = $tableResult['max'];
            }

            if ($tableResult['min']
                && (is_null($result['min'])
                    || $tableResult['min'] < $result['min'])) {
                $result['min'] = $tableResult['min'];
            }

        }
        return $result;
    }

}
