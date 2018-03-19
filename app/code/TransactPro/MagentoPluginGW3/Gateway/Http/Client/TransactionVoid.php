<?php

namespace TransactPro\MagentoPluginGW3\Gateway\Http\Client;

class TransactionVoid extends AbstractTransaction
{
    protected function process(array $data)
    {
        $this->logger->critical('TransactionVoid process', $data);
        return $this->adapter->void($data['transaction_id']);
    }
}