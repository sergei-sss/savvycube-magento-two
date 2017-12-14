<?php

namespace SavvyCube\Connector\Model;

abstract class StoreReport extends Api
{

    protected function parseParams($params)
    {
        return array(
            $this->getParam(
                $params,
                'store',
                0,
                function ($v) { return (int) $v; }),
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

}
