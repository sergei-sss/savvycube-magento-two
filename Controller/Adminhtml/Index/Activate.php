<?php
namespace SavvyCube\Connector\Controller\Adminhtml\Index;

class Activate extends \Magento\Backend\App\Action
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
        $token = base64_decode($this->getRequest()->getParam('token'));
        $session = (int)$this->getRequest()->getParam('session');
        if ($this->auth->promoteCandidateKeys($session)) {
            $this->auth->setToken($token);
            $this->auth->cleanCache();
            $this->_redirect($this->getUrl(
                'adminhtml/system_config/edit',
                array('section' => 'savvycube')
            ));
        } else {
            $this->getResponse()
                ->setHttpResponseCode(401)
                ->setBody('401');
            return $this->getResponse();
        }
    }
}
