<?php

namespace SavvyCube\Connector\Controller\Api;

class Auth extends \Magento\Framework\App\Action\Action
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
            $this->auth->cleanSession();
            $this->auth->cleanNonce();
            $key = $this->getRequest()->getParam('key');
            $session = $this->auth->createSession($key);
            $key = $this->auth->getKeyBySession($session);
            $key = base64_encode($this->auth->getScRsa()->encrypt($key));
            $this->getResponse()->setHeader('Sc-Session', $session);
            $this->getResponse()->setHeader('Sc-Key', $key);
            $this->getResponse()->setBody('ok');
        }

        return $this->getResponse();
    }
}
