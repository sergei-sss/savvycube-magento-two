<?php

namespace SavvyCube\Connector\Block\Adminhtml;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Activation extends Field
{
    protected $backendUrl;

    public function __construct(
        \Magento\Backend\Model\Url $backendUrlManager,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {

        $this->backendUrl = $backendUrlManager;
        parent::__construct($context, $data);

    }
    protected function _getElementHtml(AbstractElement $element)
    {

        $element->setValue(__('Connect'));
        $element->setOnclick("setLocation('" . $this->backendUrl->getUrl('savvyadmin/index/index') . "')");
        return parent::_getElementHtml($element);

    }
}