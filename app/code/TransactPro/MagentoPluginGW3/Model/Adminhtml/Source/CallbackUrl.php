<?php

namespace TransactPro\MagentoPluginGW3\Model\Adminhtml\Source;

use Magento\Framework\Data\Form\Element\AbstractElement;

class CallbackUrl extends \Magento\Config\Block\System\Config\Form\Field
{
    protected function _getElementHtml(AbstractElement $element)
    {

        $element->setValue($this->getCallbackUrl());
        $element->setDisabled('disabled');
        return $element->getElementHtml();
    }

    protected function getCallbackUrl()
    {
        $url = 'transactpro';
        return $this->_storeManager->getStore()->getBaseUrl() . $url;
    }
}