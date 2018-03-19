<?php

namespace TransactPro\MagentoPluginGW3\Gateway\Command;

use TransactPro\MagentoPluginGW3\Model\Adapter\TransactProAdapter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use TransactPro\MagentoPluginGW3\Gateway\Helper\SubjectReader;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Api\Data\TransactionInterface;

class CaptureStrategyCommand implements CommandInterface
{
    const SALE = 'sale';
    const CAPTURE = 'settlement';

    private $commandPool;
    private $transactionRepository;
    private $filterBuilder;
    private $searchCriteriaBuilder;
    private $subjectReader;
    private $stripeAdapter;
    private $logger;

    public function __construct(
        CommandPoolInterface $commandPool,
        TransactionRepositoryInterface $repository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SubjectReader $subjectReader,
        TransactProAdapter $stripeAdapter,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->commandPool = $commandPool;
        $this->transactionRepository = $repository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->subjectReader = $subjectReader;
        $this->stripeAdapter = $stripeAdapter;
        $this->logger = $logger;
        $this->logger->critical('CaptureStrategyCommand');
    }

    public function execute(array $commandSubject)
    {
        $this->logger->critical(__METHOD__);

        $paymentDataObject = $this->subjectReader->readPayment($commandSubject);
        $paymentInfo = $paymentDataObject->getPayment();
        ContextHelper::assertOrderPayment($paymentInfo);

        $command = $this->getCommand($paymentInfo);
        $this->commandPool->get($command)->execute($commandSubject);
    }

    private function getCommand(OrderPaymentInterface $payment)
    {
        $this->logger->critical(__METHOD__);
        $existsCapture = $this->isExistsCaptureTransaction($payment);
        if (!$payment->getAuthorizationTransaction() && !$existsCapture) {
            return self::SALE;
        }

        if (!$existsCapture) {
            return self::CAPTURE;
        }
    }

    private function isExistsCaptureTransaction(OrderPaymentInterface $payment)
    {
        $this->logger->critical(__METHOD__);
        $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('payment_id')
                    ->setValue($payment->getId())
                    ->create()
            ]
        );

        $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('txn_type')
                    ->setValue(TransactionInterface::TYPE_CAPTURE)
                    ->create()
            ]
        );

        $searchCriteria = $this->searchCriteriaBuilder->create();

        $count = $this->transactionRepository->getList($searchCriteria)->getTotalCount();
        return (boolean)$count;
    }
}