<?php

namespace TransactPro\MagentoPluginGW3\Gateway\Request;

use Magento\Framework\Exception\LocalizedException;
use TransactPro\MagentoPluginGW3\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Framework\App\ObjectManager;

class VoidDataBuilder implements BuilderInterface
{
    private $subjectReader;

    public function __construct(
        SubjectReader $subjectReader
    )
    {
        $this->subjectReader = $subjectReader;

    }

    public function build(array $subject)
    {
        $objectManager = ObjectManager::getInstance();
        $logger = $objectManager->get('\Psr\Log\LoggerInterface');
        $logger->critical('VoidDataBuilder build', $subject);

        $paymentDataObject = $this->subjectReader->readPayment($subject);
        $payment = $paymentDataObject->getPayment();

        if (key_exists('gateway-transaction-id', $payment->getAdditionalInformation()) === false) {
            throw new LocalizedException(__('No Transaction ID in Payment information to cancel'));
        }

        $transactionId = $payment->getAdditionalInformation()['gateway-transaction-id'];

        if (!$transactionId) {
            throw new LocalizedException(__('No Transaction to cancel'));
        }

        $logger->critical('VoidDataBuilder $transactionId:', [$transactionId]);

        return [
            'transaction_id' => $transactionId
        ];
    }
}