<?php

namespace SavvyCube\Connector\Model;

abstract class Api
{
    protected $helper;

    public function __construct(
        \SavvyCube\Connector\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    abstract public function report($params);

    protected function parseParams($params)
    {
        return array();
    }

    protected function getParam($params, $key, $default=false, $predicate=false)
    {
        if (is_array($params)
            && array_key_exists($key, $params)) {
            if ($predicate) {
                return $predicate($params[$key]);
            }
            return $params[$key];
        }

        return $default;
    }

    protected function getSelect()
    {
        return $this->helper->getConnection()->select();
    }

    protected function fetchAll($query)
    {
        return $this->helper->getConnection()->fetchAll($query);
    }

    protected function getTable($table)
    {
        return $this->helper->getTableName($table);
    }

    protected function prepareColumns($columns, $table, $tableAlias = false, $aliases = array())
    {
        $result = array();
        $columns = array_flip($columns);
        if ($this->helper->getConnection()->isTableExists($this->getTable($table))) {
            $tableDescription = $this->helper->getConnection()->describeTable(
                $this->getTable($table)
            );
            foreach ($tableDescription as $column) {
                if (isset($columns[$column['COLUMN_NAME']])) {
                    $alias = isset($aliases[$column['COLUMN_NAME']])
                        ? $aliases[$column['COLUMN_NAME']]
                        :  $column['COLUMN_NAME'];
                    $column = $tableAlias
                        ? "{$tableAlias}.{$column['COLUMN_NAME']}"
                        : $column['COLUMN_NAME'];
                    $result[$alias] = $column;
                }
            }
        }

        return $result;
    }
}
