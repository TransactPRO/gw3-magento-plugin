<?php

namespace TransactPro\MagentoPluginGW3\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

class SettlementDataBuilder implements BuilderInterface
{
    const SUBMIT_FOR_SETTLEMENT = 'capture';

    public function build(array $subject)
    {
        return [self::SUBMIT_FOR_SETTLEMENT => true];
    }
}