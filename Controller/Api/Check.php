<?php

namespace SavvyCube\Connector\Controller\Api;

class Check extends \Magento\Framework\App\Action\Action
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
        $session = (int)$this->getRequest()->getParam('session');
        $result = $this->auth->candidateSignature($session);
        if ($result) {
            list($iv, $signature) = $result;
            $this->getResponse()->setHeader('Sc-Sig', $signature);
            $this->getResponse()->setHeader('Sc-Iv', $iv);
            $this->getResponse()->setBody('ok');
        } else {
            $this->getResponse()
                ->setHttpResponseCode(401)
                ->setBody('401');
        }
        return $this->getResponse();
    }
}
