<?php

namespace TransactPro\MagentoPluginGW3\Model\Adminhtml\Source;

use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Framework\Option\ArrayInterface;

class PaymentAction implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => AbstractMethod::ACTION_AUTHORIZE,
                'label' => __('Authorize Only')
            ],
            [
                'value' => AbstractMethod::ACTION_AUTHORIZE_CAPTURE,
                'label' => __('Authorize and Capture')
            ]
        ];
    }
}