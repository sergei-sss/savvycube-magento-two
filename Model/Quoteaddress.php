<?php

namespace SavvyCube\Connector\Model;

class Quoteaddress extends Report
{
    public function report($params)
    {
        $dateColumn = 'IF(ent.updated_at = 0 or ent.updated_at is NULL, parent.updated_at, ent.updated_at)';

        $query = $this->getSelect()
            ->from(array('ent' => $this->getTable('quote_address')))
            ->join(
                array('parent' => $this->getTable('quote')),
                'parent.entity_id = ent.quote_id',
                array()
            )
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->order('ent.address_id')
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
                'base_shipping_discount_amount',
                'base_hidden_tax_amount',
                'base_shipping_hidden_tax_amnt',
                'base_shipping_hidden_tax_amount',
                'base_tax_amount',
                'base_shipping_tax_amount',
                'base_shipping_amount',
                'base_subtotal',
                'base_grand_total',
                'weight',
                'created_at',
                'customer_id',
                'quote_id',
                'address_id',
                'address_type',
                'city',
                'country_id',
                'discount_description',
                'email',
                'firstname',
                'free_shipping',
                'lastname',
                'middlename',
                'postcode',
                'prefix',
                'region',
                'region_id',
                'shipping_description',
                'shipping_method',
                'suffix'
            ),
            'quote_address',
            'ent',
            array(
                'base_discount_amount' => 'discount_amount',
                'base_shipping_discount_amount' => 'shipping_discount_amount',
                'base_hidden_tax_amount' => 'hidden_tax_amount',
                'base_shipping_hidden_tax_amnt' => 'shipping_hidden_tax_amnt',
                'base_shipping_hidden_tax_amount' => 'shipping_hidden_tax_amnt',
                'base_tax_amount' => 'tax_amount',
                'base_shipping_tax_amount' => 'shipping_tax_amount',
                'base_shipping_amount' => 'shipping_amount',
                'base_subtotal' => 'subtotal',
                'base_grand_total' => 'grand_total',
            )
        ), array(
            'hidden_tax_amount',
            'shipping_hidden_tax_amnt'
        ));
    }
}
