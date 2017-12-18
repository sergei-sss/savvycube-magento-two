<?php

namespace SavvyCube\Connector\Model;
use Magento\Catalog\Model;

class EavCategory extends Model\Category
{
    /**
     * Initialize resource mode
     *
     * @return void
     */
    protected function _construct()
    {
        # always use eav category resource
        $this->_init(\Magento\Catalog\Model\ResourceModel\Category::class);
    }
}
