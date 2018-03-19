<?php

namespace TransactPro\MagentoPluginGW3\Model\Adminhtml\Source;

use Magento\Framework\Data\Form\Element\AbstractElement;

class ReturnUrl extends \Magento\Config\Block\System\Config\Form\Field
{
    protected function _getElementHtml(AbstractElement $element)
    {

        $element->setValue($this->getReturnUrl());
        $element->setDisabled('disabled');
        return $element->getElementHtml();
    }

    protected function getReturnUrl()
    {
        $url = 'transactpro/index/result';
        return $this->_storeManager->getStore()->getBaseUrl() . $url;
    }
}