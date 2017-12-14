<?php

namespace SavvyCube\Connector\Model;

class Quoteitem extends Report
{
    public function report($params)
    {
        $dateColumn = 'IF(ent.updated_at = 0 or ent.updated_at is NULL, parent.updated_at, ent.updated_at)';
        $query = $this->getSelect()
            ->from(array('ent' => $this->getTable('quote_item')))
            ->join(
                array('parent' => $this->getTable('quote')),
                'parent.entity_id = ent.quote_id',
                array()
            )
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->order('ent.item_id')
            ->columns($this->getColumns())
            ->columns(array('updated_at' => $dateColumn));

        $this->applyParams($query, $params, $dateColumn);

        return $this->fetchAll($query);
    }

    protected function getColumns()
    {
        return $this->ensureColumns($this->prepareColumns(
            array(
                'base_discount_amount',
                'discount_percent',
                'base_hidden_tax_amount',
                'base_tax_amount',
                'qty',
                'quote_id',
                'base_row_total',
                'base_price',
                'base_cost',
                'weight',
                'row_weight',
                'created_at',
                'description',
                'free_shipping',
                'is_virtual',
                'name',
                'product_type',
                'sku',
                'item_id',
                'parent_item_id',
                'product_id'
            ),
            'quote_item',
            'ent',
            array(
                'base_discount_amount' => 'discount_amount',
                'base_hidden_tax_amount' => 'hidden_tax_amount',
                'base_tax_amount' => 'tax_amount',
                'base_row_total' => 'row_total',
                'base_price' => 'price',
                'base_cost' => 'cost',
            )
        ), array('hidden_tax_amount'));
    }
}
