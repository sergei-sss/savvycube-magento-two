<?php
namespace SavvyCube\Connector\Controller\Adminhtml\Index;

class Index extends \Magento\Backend\App\Action
{
    protected $auth;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \SavvyCube\Connector\Helper\Authorization $auth
    ) {
        parent::__construct($context);
        $this->auth = $auth;
    }

    public function execute()
    {
        $this->auth->generateKeys();
        $this->auth->cleanCache();
        $this->_redirect($this->auth->getActivationUrl());
    }
}
