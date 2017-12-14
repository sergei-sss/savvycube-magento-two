<?php

namespace SavvyCube\Connector\Model;

class Shipmentitem extends Report
{
    public function report($params)
    {
        $query = $this->getSelect()
            ->from(array('ent' => $this->getTable('sales_shipment_item')))
            ->join(
                array('parent' => $this->getTable('sales_shipment')),
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
                'order_item_id',
                'parent_id',
                'product_id',
                'qty',
                'weight',
            ),
            'sales_shipment_item',
            'ent'
        );
    }
}
