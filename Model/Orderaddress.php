<?php

namespace SavvyCube\Connector\Model;

class Orderaddress extends Report
{
    public function report($params)
    {
        $query = $this->getSelect()
            ->from(array('ent' => $this->getTable('sales_order_address')))
            ->join(
                array('parent' => $this->getTable('sales_order')),
                "ent.parent_id = parent.entity_id"
            )
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->order('ent.entity_id')
            ->columns($this->getColumns());


        $this->applyParams($query, $params, 'parent.updated_at');

        return $this->fetchAll($query);
    }

    protected function getColumns()
    {
        return $this->prepareColumns(
            array(
                'entity_id',
                'parent_id',
                'address_type',
                'city',
                'country_id',
                'customer_id',
                'email',
                'firstname',
                'lastname',
                'middlename',
                'postcode',
                'prefix',
                'region',
                'region_id',
                'suffix',
            ),
            'sales_order_address',
            'ent'
        );
    }
}
