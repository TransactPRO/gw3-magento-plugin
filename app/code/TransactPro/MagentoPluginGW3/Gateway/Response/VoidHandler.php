<?php

namespace TransactPro\MagentoPluginGW3\Gateway\Response;

use Magento\Sales\Model\Order\Payment;
use TransactPro\MagentoPluginGW3\Gateway\Response\TransactionIdHandler;

class VoidHandler extends TransactionIdHandler
{
    public function handle(array $subject, array $response)
    {
        $paymentDataObject = $this->subjectReader->readPayment($subject);
        $this->logger->critical(__METHOD__, [$response]);

        if ($paymentDataObject->getPayment() instanceof Payment) {
            /**
             * @var $orderPayment Payment
             */
            $orderPayment = $paymentDataObject->getPayment();
            $orderPayment->setIsTransactionClosed(true);
            $orderPayment->setShouldCloseParentTransaction(true);
        }
    }

    protected function shouldCloseTransaction()
    {
        return true;
    }

    protected function shouldCloseParentTransaction(Payment $orderPayment)
    {
        return true;
    }
}