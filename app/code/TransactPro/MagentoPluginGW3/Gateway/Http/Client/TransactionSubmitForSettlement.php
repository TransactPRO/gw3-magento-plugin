<?php

namespace TransactPro\MagentoPluginGW3\Gateway\Http\Client;

use Magento\Sales\Model\Order\Payment;
use TransactPro\MagentoPluginGW3\Gateway\Request\CaptureDataBuilder;
use TransactPro\MagentoPluginGW3\Gateway\Request\PaymentDataBuilder;

class TransactionSubmitForSettlement extends AbstractTransaction
{
    protected function process(array $data)
    {
        $this->logger->critical(__METHOD__, $data);

        return $this->adapter->submitForSettlement(
            $data[CaptureDataBuilder::TRANSACTION_ID],
            $data[PaymentDataBuilder::AMOUNT]
        );
    }
}