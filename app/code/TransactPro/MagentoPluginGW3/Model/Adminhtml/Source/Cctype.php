<?php

namespace TransactPro\MagentoPluginGW3\Model\Adminhtml\Source;

use Magento\Payment\Model\Source\Cctype as PaymentCctype;

class Cctype extends PaymentCctype
{
    public function getAllowedTypes()
    {
        return ['VI', 'MC'];
    }
}