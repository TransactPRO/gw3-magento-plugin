<?php

namespace TransactPro\MagentoPluginGW3\Gateway\Request;

use Magento\Braintree\Gateway\Request\PaymentDataBuilder;
use Magento\Framework\Exception\LocalizedException;
use TransactPro\MagentoPluginGW3\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use TransactPro\MagentoPluginGW3\Helper\Payment\Formatter;
use TransactPro\Stripe\Model\Payment;

class CaptureDataBuilder implements BuilderInterface
{
    use Formatter;

    const TRANSACTION_ID = 'transaction_id';
    const GW_TRANSACTION_ID = 'gateway-transaction-id';

    private $subjectReader;
    private $logger;

    public function __construct(
        SubjectReader $subjectReader,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->subjectReader = $subjectReader;
        $this->logger = $logger;
        $this->logger->critical('CaptureDataBuilder');
    }

    public function build(array $subject)
    {
        $this->logger->critical(__METHOD__, $subject);
        $paymentDataObject = $this->subjectReader->readPayment($subject);
        $payment = $paymentDataObject->getPayment();
        $payment_info = $payment->getAdditionalInformation();

        if (key_exists(self::GW_TRANSACTION_ID, $payment_info) === false) {
            throw new LocalizedException(__('No Transaction ID in Payment information to capture'));
        }

        $transactionId = $payment->getAdditionalInformation(self::GW_TRANSACTION_ID);

        if (!$transactionId) {
            throw new LocalizedException(__('No Authorization Transaction to capture'));
        }

        if (!isset($subject[PaymentDataBuilder::AMOUNT])) {
            $subject[PaymentDataBuilder::AMOUNT] = 0;
            if (key_exists(PaymentDataBuilder::AMOUNT, $payment_info)) {
                $subject[PaymentDataBuilder::AMOUNT] = $payment->getAdditionalInformation(PaymentDataBuilder::AMOUNT);
            }
        }

        $data = [
            self::TRANSACTION_ID => $transactionId,
            PaymentDataBuilder::AMOUNT => $this->formatPrice($this->subjectReader->readAmount($subject))
        ];

        $this->logger->critical('CaptureDataBuilder ----- data:', $data);
        return $data;
    }
}