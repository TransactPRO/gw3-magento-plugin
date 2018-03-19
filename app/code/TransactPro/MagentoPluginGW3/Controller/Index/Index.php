<?php

namespace TransactPro\MagentoPluginGW3\Controller\Index;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Checkout\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Psr\Log\LoggerInterface;

class Index extends Action
{
    protected $pageFactory;
    /**
     * @var $session \Magento\Checkout\Model\Session
     */
    protected $session;
    protected $messageManager;
    protected $orderRepository;
    protected $transactionRepository;
    protected $filterBuilder;
    protected $searchCriteriaBuilder;
    protected $logger;

    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        OrderRepositoryInterface $orderRepository,
        Session $checkoutSession,
        TransactionRepositoryInterface $transactionRepository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        LoggerInterface $logger
    )
    {
        $this->messageManager = $context->getMessageManager();
        $this->orderRepository = $orderRepository;
        $this->session = $checkoutSession;
        $this->transactionRepository = $transactionRepository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
        return parent::__construct($context);
    }

    public function execute()
    {
        $this->logger->critical('5646671 JSON:', [$this->getRequest()->getPostValue('json')]);

        $json = $this->getRequest()->getPostValue('json');
        $json = json_decode(html_entity_decode($json), true);

        $transaction_guid = $json['result-data']['gw']['gateway-transaction-id'];
        $transaction_status = (int)$json['result-data']['gw']['status-code'];

        $this->logger->critical('5646671 transaction status & guid:', [$transaction_status, $transaction_guid]);

        if ($transaction_status === 3) {
            $this->dms($transaction_guid);
            return;
        }

        if ($transaction_status === 7) {
            $this->sms($transaction_guid);
            return;
        }

        return;
    }

    protected function sms($transaction_guid)
    {
        $txn = $this->getTxnById($transaction_guid);

        if ($txn === null) {
            $this->logger->critical('5646432 thx === null', [$transaction_guid]);
            return;
        }

        $order = $this->getOrderById($txn->getOrderId());

        if ($order === null) {
            $this->logger->critical('5646432 $order === null', [$txn->getOrderId()]);
            return;
        }

        $payment = $order->getPayment();
        $payment->setLastTransId($transaction_guid);
        $payment->setCcTransId($transaction_guid);
        $payment->setTransactionId($transaction_guid);
        $payment->registerCaptureNotification(
            $payment->getAmountAuthorized(),
            true
        );
        #$payment->setIsTransactionClosed(true);
        $invoice = $payment->getCreatedInvoice();

        $orderItems = $order->getItems();

        foreach ($orderItems as $orderItem) {
            $orderItem->setQtyInvoiced($orderItem->getQtyOrdered());
            #$orderItem->setRowTotal(100);

            if (method_exists($orderItem, 'save')) {
                $this->logger->critical('564643211 OrderItem SAVED');
                $orderItem->save();
            }
        }

        if ($invoice) {
            /**
             * @var $invoice Order\Invoice
             */
            $this->logger->critical('5646555 Invoice exists');
            $invoice->setTransactionId($transaction_guid);
            $invoice->setState($invoice::STATE_PAID);
            $invoice->save();
        }

        $order->setState($order::STATE_PROCESSING);
        $notifyCustomer = false;
        $order->addStatusToHistory(false, __('Order has been paid.'), $notifyCustomer); // Maybe here we should notify customer?
        // Or at least add field in admin config: "Notify customers about order payment?"
        $order->setTotalPaid($payment->getAmountAuthorized());
        $this->orderRepository->save($order);
    }

    protected function dms($transaction_guid)
    {
        $txn = $this->getTxnById($transaction_guid);

        if ($txn === null) {
            return;
        }

        $order = $this->getOrderById($txn->getOrderId());

        if ($order === null) {
            return;
        }

        $payment = $order->getPayment();
        $payment->setLastTransId($transaction_guid);
        $payment->setCcTransId($transaction_guid);
        $payment->setTransactionId($transaction_guid);
        /**
         * @var $order Order
         */
        $order->setState($order::STATE_PAYMENT_REVIEW);
        $notifyCustomer = false;
        $order->addStatusToHistory(false, __('Transaction accepted - you can charge or cancel payment.'), $notifyCustomer);
        $this->orderRepository->save($order);
    }

    protected function getTxnById($txn_id)
    {
        $txn = null;
        $transactionSearchCriteriaBuilder = $this->searchCriteriaBuilder;
        $transactionSearchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('txn_id')
                    ->setValue($txn_id)
                    ->create()
            ]
        );

        $transactionSearchCriteria = $transactionSearchCriteriaBuilder->create();
        $transactionsCount = $this->transactionRepository->getList($transactionSearchCriteria)->getTotalCount();
        $this->logger->critical('5646671 $transactionsCount:', [$transactionsCount]);
        if (!$transactionsCount) {
            return $txn;
        }

        $transactions = $this->transactionRepository->getList($transactionSearchCriteria)->getItems();
        // todo find how to get first item from repository, $txn = $transactions[0]; doesn't work
        foreach ($transactions as $transaction) {
            $txn = $transaction;
            break;
        }

        return $txn;
    }

    protected function getOrderById($order_id)
    {
        $order = null;
        $orderSearchCriteriaBuilder = $this->searchCriteriaBuilder;
        $orderSearchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('entity_id')
                    ->setValue($order_id)
                    ->create()
            ]
        );

        $orderSearchCriteria = $orderSearchCriteriaBuilder->create();

        $ordersList = $this->orderRepository->getList($orderSearchCriteria)->getItems();

        foreach ($ordersList as $orderItem) {
            $this->logger->critical('5646671 First order id:', [$orderItem->getEntityId()]);
            $order = $orderItem;
            break;
        }

        return $order;
    }
}