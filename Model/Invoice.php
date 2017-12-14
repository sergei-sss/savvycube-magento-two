<?php

namespace SavvyCube\Connector\Model;

class Invoice extends Report
{
    public function report($params)
    {
        $query = $this->getSelect()
            ->from(array('ent' => $this->getTable('sales_invoice')))
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->order('ent.entity_id')
            ->columns($this->getColumns());

        $this->applyParams($query, $params, 'ent.updated_at');

        return $this->fetchAll($query);
    }

    protected function getColumns()
    {
        return $this->ensureColumns($this->prepareColumns(
            array(
                'base_discount_amount',
                'base_hidden_tax_amount',
                'base_shipping_hidden_tax_amnt',
                'base_shipping_hidden_tax_amount',
                'base_tax_amount',
                'base_shipping_tax_amount',
                'base_shipping_amount',
                'base_subtotal',
                'base_grand_total',
                'base_currency_code',
                'base_to_global_rate',
                'base_to_order_rate',
                'global_currency_code',
                'order_currency_code',
                'store_currency_code',
                'store_to_base_rate',
                'store_to_order_rate',
                'entity_id',
                'increment_id',
                'order_id',
                'transaction_id',
                'created_at',
                'updated_at',
            ),
            'sales_invoice',
            'ent',
            array(
                'base_discount_amount' => 'discount_amount',
                'base_hidden_tax_amount' => 'hidden_tax_amount',
                'base_shipping_hidden_tax_amnt' => 'shipping_hidden_tax_amnt',
                'base_shipping_hidden_tax_amount' => 'shipping_hidden_tax_amnt',
                'base_tax_amount' => 'tax_amount',
                'base_shipping_tax_amount' => 'shipping_tax_amount',
                'base_shipping_amount' => 'shipping_amount',
                'base_subtotal' => 'subtotal',
                'base_grand_total' => 'grand_total',
            )
        ), array('hidden_tax_amount', 'shipping_hidden_tax_amnt'));
    }
}
