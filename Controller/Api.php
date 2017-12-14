<?php

namespace SavvyCube\Connector\Controller;

use Magento\Framework\App\Action\Context;

abstract class Api extends \Magento\Framework\App\Action\Action
{
    protected $auth;


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \SavvyCube\Connector\Helper\Authorization $auth
    ) {
        parent::__construct($context);
        $this->auth = $auth;
    }

    public function execute()
    {
        if (!$this->auth->auth($this->getRequest())) {
            $this->getResponse()
                ->setHttpResponseCode(401)
                ->setBody('401');
        } else {
            $response = $this->getModel()->report($this->getRequest()->getParams());
            $this->getResponse()->setHeader('sc-report-count', count($response));
            $data = json_encode($response);
            $session = $this->getRequest()->getHeader('Sc-Session');
            $key = $this->auth->getKeyBySession($session);
            list($iv, $encryptedData) = $this->auth->encrypt($key, $data);
            $signature = $this->auth->getRsa()->sign($encryptedData);
            $this->getResponse()->setHeader('Sc-Sig', base64_encode($signature));
            $this->getResponse()->setHeader('Sc-Iv', base64_encode($iv));
            $this->getResponse()->setBody($encryptedData);
        }
        return $this->getResponse();
    }

    protected function getModel()
    {
        $actionName = ucfirst(strtolower(
            $this->getRequest()->getActionName()));
        $modelClass = "SavvyCube\\Connector\\Model\\{$actionName}";
        $model = $this->_objectManager->create($modelClass);
        return $model;
    }
}
