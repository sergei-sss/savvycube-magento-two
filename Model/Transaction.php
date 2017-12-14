<?php

namespace SavvyCube\Connector\Model;

class Transaction extends Report
{
    public function report($params)
    {
        $query = $this->getSelect()
            ->from(array('ent' => $this->getTable('sales_payment_transaction')))
            ->joinLeft(
                array('payment' => $this->getTable('sales_order_payment')),
                "ent.payment_id = payment.entity_id"
            )
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->order('payment.entity_id')
            ->columns($this->getColumns());

        $this->applyParams($query, $params, 'ent.created_at');

        return $this->fetchAll($query);
    }

    protected function getColumns()
    {
        return array_merge(
            $this->prepareColumns(
                array(
                    'parent_id',
                    'entity_id',
                    'method',
                    'last_trans_id'
                ),
                'sales_order_payment',
                'payment',
                array(
                    'parent_id' => 'order_id'
                )
            ),
            $this->prepareColumns(
                array(
                    'transaction_id',
                    'txn_id',
                    'parent_txn_id',
                    'txn_type',
                    'is_closed',
                    'created_at'
                ),
                'sales_payment_transaction',
                'ent'
            )
        );
    }
}
