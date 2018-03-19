<?php

namespace TransactPro\MagentoPluginGW3\Gateway\Config;

use TransactPro\MagentoPluginGW3\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Config\ValueHandlerInterface;
use Magento\Sales\Model\Order\Payment;

class CanVoidHandler implements ValueHandlerInterface
{
    private $subjectReader;

    public function __construct(
        SubjectReader $subjectReader
    )
    {
        $this->subjectReader = $subjectReader;
    }

    public function handle(array $subject, $storeId = NULL)
    {
        $paymentDataObject = $this->subjectReader->readPayment($subject);
        $payment = $paymentDataObject->getPayment();

        return $payment instanceof Payment && !(bool)$payment->getAmountPaid();
    }
}