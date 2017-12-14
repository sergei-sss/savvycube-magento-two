<?php

namespace SavvyCube\Connector\Block\Adminhtml;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use SavvyCube\Connector\Helper;

class Version extends Field
{

    protected $helper;

    public function __construct(
        Context $context,
        Helper\Data $scHelper,
        array $data = []
    ) {
        $this->helper = $scHelper;
        parent::__construct($context, $data);
    }


    protected function _getElementHtml(AbstractElement $element)
    {
        $element->setValue($this->helper->getConnectorVersion());
        return parent::_getElementHtml($element);
    }
}
