<?php

namespace SavvyCube\Connector\Model;

class Invoiceitem extends Report
{
    public function report($params)
    {
        $query = $this->getSelect()
            ->from(array('ent' => $this->getTable('sales_invoice_item')))
            ->join(
                array('parent' => $this->getTable('sales_invoice')),
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
        return $this->ensureColumns($this->prepareColumns(
            array(
                'base_discount_amount',
                'base_hidden_tax_amount',
                'base_tax_amount',
                'qty',
                'base_price',
                'base_row_total',
                'base_cost',
                'entity_id',
                'order_item_id',
                'parent_id'
            ),
            'sales_invoice_item',
            'ent',
            array(
                'base_discount_amount' => 'discount_amount',
                'base_hidden_tax_amount' => 'hidden_tax_amount',
                'base_tax_amount' => 'tax_amount',
                'base_price' => 'price',
                'base_row_total' => 'row_total',
                'base_cost' => 'cost',
            )
        ), array('hidden_tax_amount'));
    }
}
