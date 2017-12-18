<?php

namespace SavvyCube\Connector\Model;

class Category extends StoreReport
{
    public function report($params)
    {
        list($store, $offset, $count, $from, $to) = $this->parseParams($params);
        $store = $this->helper->getStore($store);
        if ($store->getId() != null) {
            $this->helper->emulateStore($store->getId());
            $data = array();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $factory = $objectManager
                ->create('SavvyCube\Connector\Model\EavCategoryFactory');
            $treeRoot = \Magento\Catalog\Model\Category::TREE_ROOT_ID;
            $storeRoot = $store->getRootCategoryId();
            $collection = $factory->create()->getCollection()
                ->addAttributeToSelect('entity_id')
                ->addAttributeToSelect('created_at')
                ->addAttributeToSelect('updated_at')
                ->addAttributeToFilter('path',
                    array('like' => "$treeRoot/{$storeRoot}%")
                )
                ->setOrder('entity_id', 'ASC');
            $collection->getSelect()->limit($count, $offset);
            if ($from) {
                $collection->getSelect()
                    ->where("e.updated_at >= ?", $from);
            }
            if ($to) {
                $collection->getSelect()
                    ->where("e.updated_at <= ?", $to);
            }
            foreach($collection as $id => $category) {
                $data[$id] = array(
                    'entity_id' => $category->getId(),
                    'store_id' => $store->getId(),
                    'name' => $this->helper
                        ->getRelativeCategoryPath($category->getId(), $store),
                    'full_name' => $this->helper
                        ->getFullCategoryPath($category->getId()),
                    'root' => $store->getRootCategoryId(),
                    'created_at' => $category->getCreatedAt(),
                    'updated_at' => $category->getUpdatedAt()
                );
            }
            $this->helper->stopStoreEmulation();
            return $data;
        }
    }
}
