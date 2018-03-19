<?php

namespace TransactPro\MagentoPluginGW3\Model\Adminhtml\Source;

use Magento\Framework\Option\ArrayInterface;

class PaymentMethod implements ArrayInterface
{
    const SMS = 'Sms';
    const DMS = 'Dms';
    const CREDIT = 'Credit';
    const P2P = 'P2P';

    public function toOptionArray()
    {
        return [
            [
                'value' => self::SMS,
                'label' => __('Sms')
            ],
            [
                'value' => self::DMS,
                'label' => __('Dms')
            ],
            [
                'value' => self::CREDIT,
                'label' => __('Credit')
            ],
            [
                'value' => self::P2P,
                'label' => __('P2P')
            ],
        ];
    }
}