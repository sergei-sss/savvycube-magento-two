<?php

namespace SavvyCube\Connector\Model;

class Order extends Report
{
    public function report($params)
    {
        $query = $this->getSelect()
            ->from(array('ent' => $this->getTable('sales_order')))
            ->joinLeft(
                array('st_label' => $this->getTable('sales_order_status')),
                "ent.status = st_label.status"
            )
            ->joinLeft(
                array('payment' => $this->getTable('sales_order_payment')),
                "ent.entity_id = payment.parent_id"
            )
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->order('ent.entity_id')
            ->columns($this->getColumns());

        $this->applyParams($query, $params, 'ent.updated_at');

        return $this->fetchAll($query);
    }

    protected function getColumns()
    {
        return $this->ensureColumns(array_merge(
            $this->prepareColumns(
                array('method'),
                'sales_order_payment',
                'payment',
                array('method' => 'payment_method')
            ),
            $this->prepareColumns(
                array('label'),
                'sales_order_status',
                'st_label',
                array('label' => 'status_label')
            ),
            $this->prepareColumns(
                array(
                    'base_adjustment_negative',
                    'base_adjustment_positive',
                    'base_currency_code',
                    'base_discount_amount',
                    'base_discount_canceled',
                    'base_discount_invoiced',
                    'base_discount_refunded',
                    'base_grand_total',
                    'base_hidden_tax_amount',
                    'base_hidden_tax_invoiced',
                    'base_hidden_tax_refunded',
                    'base_shipping_amount',
                    'base_shipping_canceled',
                    'base_shipping_discount_amount',
                    'base_shipping_hidden_tax_amnt',
                    'base_shipping_hidden_tax_amount',
                    'base_shipping_invoiced',
                    'base_shipping_refunded',
                    'base_shipping_tax_amount',
                    'base_shipping_tax_refunded',
                    'base_subtotal',
                    'base_subtotal_canceled',
                    'base_subtotal_invoiced',
                    'base_subtotal_refunded',
                    'base_tax_amount',
                    'base_tax_canceled',
                    'base_tax_invoiced',
                    'base_tax_refunded',
                    'base_to_global_rate',
                    'base_to_order_rate',
                    'base_total_canceled',
                    'base_total_due',
                    'base_total_invoiced',
                    'base_total_offline_refunded',
                    'base_total_online_refunded',
                    'base_total_paid',
                    'base_total_refunded',
                    'billing_address_id',
                    'coupon_code',
                    'coupon_rule_name',
                    'created_at',
                    'customer_email',
                    'customer_firstname',
                    'customer_gender',
                    'customer_group_id',
                    'customer_id',
                    'customer_is_guest',
                    'customer_lastname',
                    'customer_middlename',
                    'customer_prefix',
                    'customer_suffix',
                    'customer_taxvat',
                    'discount_description',
                    'entity_id',
                    'global_currency_code',
                    'increment_id',
                    'is_virtual',
                    'order_currency_code',
                    'quote_id',
                    'shipping_address_id',
                    'shipping_description',
                    'shipping_method',
                    'state',
                    'status',
                    'store_currency_code',
                    'store_id',
                    'store_name',
                    'store_to_base_rate',
                    'store_to_order_rate',
                    'updated_at',
                    'weight',
                ),
                'sales_order',
                'ent',
                array(
                    'base_discount_amount' => 'discount_amount',
                    'base_discount_canceled' => 'discount_canceled',
                    'base_discount_invoiced' => 'discount_invoiced',
                    'base_discount_refunded' => 'discount_refunded',
                    'base_shipping_discount_amount' => 'shipping_discount_amount',
                    'base_hidden_tax_amount' => 'hidden_tax_amount',
                    'base_hidden_tax_invoiced' => 'hidden_tax_invoiced',
                    'base_hidden_tax_refunded' => 'hidden_tax_refunded',
                    'base_shipping_hidden_tax_amnt' => 'shipping_hidden_tax_amnt',
                    'base_shipping_hidden_tax_amount' => 'shipping_hidden_tax_amnt',
                    'base_tax_amount' => 'tax_amount',
                    'base_tax_canceled' => 'tax_canceled',
                    'base_tax_invoiced' => 'tax_invoiced',
                    'base_tax_refunded' => 'tax_refunded',
                    'base_shipping_tax_amount' => 'shipping_tax_amount',
                    'base_shipping_tax_refunded' => 'shipping_tax_refunded',
                    'base_shipping_amount' => 'shipping_amount',
                    'base_shipping_canceled' => 'shipping_canceled',
                    'base_shipping_invoiced' => 'shipping_invoiced',
                    'base_shipping_refunded' => 'shipping_refunded',
                    'base_subtotal' => 'subtotal',
                    'base_subtotal_canceled' => 'subtotal_canceled',
                    'base_subtotal_invoiced' => 'subtotal_invoiced',
                    'base_subtotal_refunded' => 'subtotal_refunded',
                    'base_adjustment_negative' => 'adjustment_negative',
                    'base_adjustment_positive' => 'adjustment_positive',
                    'base_grand_total' => 'grand_total',
                    'base_total_canceled' => 'total_canceled',
                    'base_total_due' => 'total_due',
                    'base_total_invoiced' => 'total_invoiced',
                    'base_total_offline_refunded' => 'total_offline_refunded',
                    'base_total_online_refunded' => 'total_online_refunded',
                    'base_total_paid' => 'total_paid',
                    'base_total_refunded' => 'total_refunded'
                )
            )
        ),
        array(
            'hidden_tax_amount',
            'hidden_tax_invoiced',
            'hidden_tax_refunded',
            'shipping_hidden_tax_amnt'
        ));
    }
}
