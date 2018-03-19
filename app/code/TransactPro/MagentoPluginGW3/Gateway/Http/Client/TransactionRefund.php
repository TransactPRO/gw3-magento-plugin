<?php

namespace TransactPro\MagentoPluginGW3\Gateway\Http\Client;

use TransactPro\MagentoPluginGW3\Gateway\Request\PaymentDataBuilder;

class TransactionRefund extends AbstractTransaction
{
    protected function process(array $data)
    {
        return $this->adapter->refund(
            $data['transaction_id'],
            $data[PaymentDataBuilder::AMOUNT]
        );
    }
}