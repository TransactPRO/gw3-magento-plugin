<?php

namespace TransactPro\MagentoPluginGW3\Gateway\Http;

use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Http\TransferInterface;

class TransferFactory implements TransferFactoryInterface
{
    private $transferBuilder;

    public function __construct(
        TransferBuilder $transferBuilder
    )
    {
        $this->transferBuilder = $transferBuilder;
    }

    public function create(array $request)
    {
        return $this->transferBuilder
            ->setBody($request)
            ->build();
    }
}
