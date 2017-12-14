<?php

namespace SavvyCube\Connector\Model;

class Shipment extends Report
{
    public function report($params)
    {
        $query = $this->getSelect()
            ->from(array('ent' => $this->getTable('sales_shipment')))
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->order('ent.entity_id')
            ->columns($this->getColumns());

        $this->applyParams($query, $params, 'ent.updated_at');

        return $this->fetchAll($query);
    }

    protected function getColumns()
    {
        return $this->prepareColumns(
            array(
                'total_qty',
                'total_weight',
                'store_id',
                'customer_id',
                'entity_id',
                'increment_id',
                'order_id',
                'created_at',
                'updated_at',
            ),
            'sales_shipment',
            'ent'
        );
    }
}
