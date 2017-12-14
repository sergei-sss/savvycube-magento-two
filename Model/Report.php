<?php

namespace SavvyCube\Connector\Model;

abstract class Report extends Api
{

    protected function applyParams($query, $params, $dateColumn = false)
    {
        list($offset, $count, $from, $to) = $this->parseParams($params);
        $query->limit($count, $offset);
        if ($dateColumn && $from) {
            $query->where("{$dateColumn} >= ?", $from);
        }
        if ($dateColumn && $to) {
            $query->where("{$dateColumn} <= ?", $to);
        }
    }

    protected function parseParams($params)
    {
        return array(
            $this->getParam(
                $params,
                'offset',
                0,
                function ($v) { return (int) $v; }),
            $this->getParam(
                $params,
                'count',
                100,
                function ($v) { return (int) $v; }),
            $this->getParam(
                $params,
                'from',
                false,
                function ($v) { return urldecode($v); }),
            $this->getParam(
                $params,
                'to',
                false,
                function ($v) { return urldecode($v); }));
    }


    protected function ensureColumns($columns, $toEnsure)
    {
        foreach($toEnsure as $column) {
            if (!array_key_exists($column, $columns)) {
                $columns[$column] = new \Zend_Db_Expr('0.0000');
            }
        }
        return $columns;
    }

}
