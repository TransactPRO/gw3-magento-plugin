<?php

namespace TransactPro\MagentoPluginGW3\Gateway\Request;

use TransactPro\MagentoPluginGW3\Gateway\Request\PaymentDataBuilder;
use TransactPro\MagentoPluginGW3\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use TransactPro\MagentoPluginGW3\Helper\Payment\Formatter;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Model\Order\Payment;

class RefundDataBuilder implements BuilderInterface
{
    use Formatter;

    private $subjectReader;
    private $logger;

    public function __construct(
        SubjectReader $subjectReader,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->subjectReader = $subjectReader;
        $this->logger = $logger;
    }

    public function build(array $subject)
    {
        $this->logger->critical(__METHOD__);
        $paymentDataObject = $this->subjectReader->readPayment($subject);
        $payment = $paymentDataObject->getPayment();
        $amount = null;
        $transactionId = $payment->getTransactionId();

        if (key_exists(CaptureDataBuilder::GW_TRANSACTION_ID, $payment->getAdditionalInformation())) {
            $transactionId = $payment->getAdditionalInformation(CaptureDataBuilder::GW_TRANSACTION_ID);
        }

        try {
            $amount = $this->formatPrice($this->subjectReader->readAmount($subject));
        } catch (\InvalidArgumentException $e) {
            //nothing
        }

        $txnId = str_replace(
            '-' . TransactionInterface::TYPE_REFUND,
            '',
            $transactionId
        );

        return [
            'transaction_id' => $txnId,
            PaymentDataBuilder::AMOUNT => $amount
        ];
    }
}