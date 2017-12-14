<?php

namespace SavvyCube\Connector\Model;

class Customer extends Report
{
    public function report($params)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $collection = $objectManager->create('Magento\Customer\Model\Customer')
                ->getCollection();

        $collection->joinTable(
                    array('group' => 'customer_group'),
                    'customer_group_id=group_id',
                    array('customer_group' => 'customer_group_code'),
                    null,
                    'left'
                );

        $query = $collection->getSelect()
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->order('e.entity_id')
            ->columns($this->getColumns());

        $this->applyParams($query, $params, 'e.updated_at');

        $genderSource = $objectManager->create('Magento\Customer\Model\Customer')
            ->getResource()
            ->getAttribute('gender')
            ->getSource();
        $data = array();
        foreach ($this->fetchAll($query) as $customer) {
            $data[$customer['entity_id']] = $customer;
            $data[$customer['entity_id']]['gender']
                = $genderSource->getOptionText($customer['gender']);
        }
        return $data;
    }

    protected function getColumns()
    {
        return array_merge(
            $this->prepareColumns(
                array(
                    'entity_id',
                    'gender',
                    'website_id',
                    'created_at',
                    'updated_at'
                ),
                'customer_entity',
                'e'
            ),
            $this->prepareColumns(
                array(
                    'customer_group_code',
                ),
                'customer_group',
                'group',
                array('customer_group_code' => 'customer_group')
            )
        );
    }
}
