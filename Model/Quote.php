<?php

namespace SavvyCube\Connector\Model;

class Quote extends Report
{
    public function report($params)
    {
        $query = $this->getSelect()
            ->from(array('ent' => $this->getTable('quote')))
            # to eliminate empty quotes
            ->join(
                array('with_items' => $this->generateSubQuery()),
                "ent.entity_id = with_items.quote_id"
            )
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->order('ent.entity_id')
            ->columns($this->getColumns());

        $this->applyParams($query, $params, 'ent.updated_at');

        return $this->fetchAll($query);
    }

    public function generateSubQuery()
    {
        $query = $this->getSelect()
            ->distinct()
            ->from($this->getTable('quote_item'))
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->columns('quote_id');

        return $query;
    }

    public function getColumns()
    {
        return $this->prepareColumns(
            array(
                    'base_subtotal',
                    'base_grand_total',
                    'base_currency_code',
                    'base_to_global_rate',
                    'base_to_quote_rate',
                    'global_currency_code',
                    'quote_currency_code',
                    'store_currency_code',
                    'store_to_base_rate',
                    'store_to_quote_rate',
                    'checkout_method',
                    'coupon_code',
                    'customer_email',
                    'customer_firstname',
                    'customer_id',
                    'customer_group_id',
                    'customer_is_guest',
                    'customer_lastname',
                    'customer_middlename',
                    'customer_prefix',
                    'customer_suffix',
                    'customer_taxvat',
                    'is_active',
                    'is_changed',
                    'is_virtual',
                    'entity_id',
                    'reserved_order_id',
                    'store_id',
                    'created_at',
                    'updated_at'
                ),
            'quote',
            'ent',
            array(
                    'base_subtotal' => 'subtotal',
                    'base_grand_total' => 'grand_total'
                )
        );
    }
}
