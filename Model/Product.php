<?php

namespace SavvyCube\Connector\Model;

class Product extends StoreReport
{
    public function report($params)
    {
        list($store, $offset, $count, $from, $to) = $this->parseParams($params);
        $store = $this->helper->getStore($store);
        if ($store->getId() != null) {
            $this->helper->emulateStore($store->getId());
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $collection = $objectManager->create('Magento\Catalog\Model\Product')
                ->getCollection();

            if ($store->getWebsiteId() != 0) {
                $collection->addWebsiteFilter();
            }

            $treeRoot = \Magento\Catalog\Model\Category::TREE_ROOT_ID;
            $storeRoot = $store->getRootCategoryId();
            $catSubquery = $this->getSelect()
                ->from(array('cat_prod' => $this->getTable('catalog_category_product')))
                ->joinLeft(
                    array('cat' => $this->getTable('catalog_category_entity')),
                    'cat.entity_id = cat_prod.category_id'
                )
                ->reset(\Magento\Framework\DB\Select::COLUMNS)
                ->columns(
                    array(
                        'created_at' => 'max(cat.created_at)',
                        'updated_at' => 'max(cat.updated_at)',
                        'categories' => 'group_concat(cat_prod.category_id separator ",")',
                        'product_id' => 'cat_prod.product_id'
                    )
                )
                ->where('path like ?', "${treeRoot}/${storeRoot}%")
                ->group('cat_prod.product_id');

            $collection
                ->addAttributeToSelect('entity_id')
                ->addAttributeToSelect('sku')
                ->addAttributeToSelect('type_id')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('status')
                ->addAttributeToSelect('url')
                ->addAttributeToSelect('msrp')
                ->addAttributeToSelect('visibility')
                ->addAttributeToSelect('url_key')
                ->joinField(
                    'attribute_set_name',
                    $this->getTable('eav_attribute_set'),
                    'attribute_set_name',
                    'attribute_set_id=attribute_set_id'
                )->setOrder('entity_id', 'ASC');
            $collection->getSelect()->limit($count, $offset);
            $collection->getSelect()->joinLeft(
                array('cat_sum' => $catSubquery),
                'cat_sum.product_id = e.entity_id',
                array(
                    'max_cat_created_at' => 'cat_sum.created_at',
                    'max_cat_updated_at' => 'cat_sum.updated_at',
                    'categories' => 'cat_sum.categories'
                )
            );
            $collection->getSelect()->columns(array(
                'greatest_created' => 'GREATEST(COALESCE(cat_sum.created_at, 0), e.created_at)',
                'greatest_updated' => 'GREATEST(COALESCE(cat_sum.updated_at, 0), e.updated_at)'));
            if ($from) {
                $collection->getSelect()
                ->where("GREATEST(COALESCE(cat_sum.updated_at, 0), e.updated_at) >= ?", $from);
            }
            if ($to) {
                $collection->getSelect()
                ->where("GREATEST(COALESCE(cat_sum.updated_at, 0), e.updated_at) <= ?", $to);
            }
            $data = array();
            foreach($collection->getItems() as $id => $product) {
                $result = array(
                    'entity_id' => $product->getEntityId(),
                    'store_id' => $store->getId(),
                    'attribute_set' => $product->getAttributeSetName(),
                    'type_id' => $product->getTypeId(),
                    'sku' => $product->getSku(),
                    'name' => $product->getName(),
                    'status' => $product->getAttributeText('status'),
                    'visibility' => $product->getAttributeText('visibility'),
                    'url_key' => $product->getUrlKey(),
                    'msrp' => $product->getMsrp(),
                    'created_at' => $product->getGreatestCreated(),
                    'updated_at' => $product->getGreatestUpdated(),

                );
                if ($product->getCategories()) {
                    foreach(explode(',', $product->getCategories()) as $category)
                        $result['categories'][$category] = $this->helper
                            ->getRelativeCategoryPath(
                                $category,
                                $store
                            );
                } else {
                    $result['categories'] = array();
                }
                $data[$id] = $result;
            }

            $this->helper->stopStoreEmulation();
            return $data;
        }
    }
}
