<?php

namespace SavvyCube\Connector\Model;

class Orderitem extends Report
{
    public function report($params)
    {
        $query = $this->getSelect()
            ->from(array('ent' => $this->getTable('sales_order_item')))
            ->join(
                array('parent' => $this->getTable('sales_order')),
                "ent.order_id = parent.entity_id"
            )
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->order('ent.item_id')
            ->columns($this->getColumns());


        $this->applyParams($query, $params, 'parent.updated_at');

        return $this->fetchAll($query);
    }

    protected function getColumns()
    {
        $updated = 'IF(ent.updated_at = 0 or ent.updated_at is NULL, ent.created_at, ent.updated_at)';
        return array_merge(
            array('updated_at' => $updated),
            $this->ensureColumns($this->prepareColumns(
            array(
                'base_discount_amount',
                'base_discount_invoiced',
                'base_discount_refunded',
                'discount_percent',
                'base_hidden_tax_amount',
                'base_hidden_tax_invoiced',
                'base_hidden_tax_refunded',
                'base_tax_amount',
                'base_tax_invoiced',
                'base_tax_refunded',
                'qty_canceled',
                'qty_invoiced',
                'qty_ordered',
                'qty_refunded',
                'qty_returned',
                'qty_shipped',
                'base_row_invoiced',
                'base_row_total',
                'base_price',
                'base_cost',
                'base_original_price',
                'weight',
                'row_weight',
                'created_at',
                'item_id',
                'order_id',
                'parent_item_id',
                'product_id',
                'quote_item_id',
                'description',
                'free_shipping',
                'is_virtual',
                'name',
                'product_type',
                'sku'
            ),
            'sales_order_item',
            'ent',
            array(
                'base_discount_amount' => 'discount_amount',
                'base_discount_invoiced' => 'discount_invoiced',
                'base_discount_refunded' => 'discount_refunded',
                'base_hidden_tax_amount' => 'hidden_tax_amount',
                'base_hidden_tax_invoiced' => 'hidden_tax_invoiced',
                'base_hidden_tax_refunded' => 'hidden_tax_refunded',
                'base_tax_amount' => 'tax_amount',
                'base_tax_invoiced' => 'tax_invoiced',
                'base_tax_refunded' => 'tax_refunded',
                'base_row_invoiced' => 'row_invoiced',
                'base_row_total' => 'row_total',
                'base_price' => 'price',
                'base_cost' => 'cost',
                'base_original_price' => 'original_price',
            )
        ), array('hidden_tax_amount', 'hidden_tax_invoiced', 'hidden_tax_refunded')));
    }
}
