<?php

namespace TransactPro\MagentoPluginGW3\Gateway\Response;

use Magento\Sales\Model\Order\Payment;

class RefundHandler extends VoidHandler
{
    protected function shouldCloseParentTransaction(Payment $orderPayment)
    {
        return !(bool)$orderPayment->getCreditmemo()->getInvoice()->canRefund();
    }
}