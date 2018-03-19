<?php

namespace TransactPro\MagentoPluginGW3\Gateway\Response;

use TransactPro\MagentoPluginGW3\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Checkout\Model\Session;
use TransactPro\MagentoPluginGW3\Gateway\Request\PaymentDataBuilder;

class TransactionIdHandler implements HandlerInterface
{
    protected $subjectReader;
    protected $logger;
    protected $session;

    public function __construct(
        SubjectReader $subjectReader,
        Session $session,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->subjectReader = $subjectReader;
        $this->logger = $logger;
        $this->session = $session;
    }

    public function handle(array $subject, array $response)
    {
        $this->logger->critical(__METHOD__, [$response]);

        $paymentDataObject = $this->subjectReader->readPayment($subject);

        if ($paymentDataObject->getPayment() instanceof Payment) {
            /**
             * @var $orderPayment Payment
             */
            $orderPayment = $paymentDataObject->getPayment();
            $order = $orderPayment->getOrder();
            $gateway = $response['object']['gw'];

            if ($gateway['status-code'] == 3 && $order->getState() != $order::STATE_PENDING_PAYMENT) {
                $orderPayment->setAdditionalInformation('dmsHoldSuccess', true);
                $order->setState($order::STATE_PENDING_PAYMENT);
                #$order->addStatusToHistory(false, __('Pending payment.'));
            }

            if (isset($gateway['redirect-url'])) {
                $this->logger->critical('9821222 gatewayUrl set');
                $orderPayment->setAdditionalInformation('gatewayUrl', $gateway['redirect-url']);
            }

            if (isset($gateway['gateway-transaction-id'])) {
                $this->logger->critical('9821222 transaction id set');
                $orderPayment->setTransactionId($gateway['gateway-transaction-id']);
                $orderPayment->setCcTransId($gateway['gateway-transaction-id']);
                $orderPayment->setLastTransId($gateway['gateway-transaction-id']);
                // use line below instead
                $orderPayment->setAdditionalInformation(
                    'gateway-transaction-id',
                    $gateway['gateway-transaction-id']
                );
            }

            if (isset($subject[PaymentDataBuilder::AMOUNT])) {
                $orderPayment->setAdditionalInformation(
                    PaymentDataBuilder::AMOUNT,
                    $subject[PaymentDataBuilder::AMOUNT]
                );
            }

            $orderPayment->setIsTransactionClosed($this->shouldCloseTransaction());
            $closed = $this->shouldCloseParentTransaction($orderPayment);
            $orderPayment->setShouldCloseParentTransaction($closed);
        } else {
            $this->logger->critical('77777 $paymentDataObject isnt instanceof Payment');
        }
    }

    protected function shouldCloseTransaction()
    {
        return false;
    }

    protected function shouldCloseParentTransaction(Payment $orderPayment)
    {
        return false;
    }
}