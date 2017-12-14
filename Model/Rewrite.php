<?php

namespace SavvyCube\Connector\Model;

class Rewrite extends Report
{
    public function report($params)
    {
        $query = $this->getSelect()
            ->from(array('ent' => $this->getTable('url_rewrite')))
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->order('ent.url_rewrite_id')
            ->columns($this->getColumns());

        $this->applyParams($query, $params, 'ent.url_rewrite_id');

        $data = $this->fetchAll($query);

        foreach ($data as $key => $record) {
            if ($record['metadata']) {
                $meta = json_decode($record['metadata'], true);
                if (isset($meta['category_id'])) {
                    $data[$key]['category_id'] = $meta['category_id'];
                }
            }
            unset($data[$key]['metadata']);
        }

        return $data;

    }

    protected function getColumns()
    {
        return array_merge(
            $this->prepareColumns(
                array(
                    'url_rewrite_id',
                    'store_id',
                    'id_path',
                    'request_path',
                    'target_path',
                    'metadata'
                ),
                'url_rewrite',
                'ent'
            ),
            array(
                'category_id' => "IF(ent.entity_type = 'category', ent.entity_id, NULL)",
                'product_id' => "IF(ent.entity_type = 'product', ent.entity_id, NULL)",
                'id_path' => new \Zend_Db_Expr("''")
            )
        );
    }
}
