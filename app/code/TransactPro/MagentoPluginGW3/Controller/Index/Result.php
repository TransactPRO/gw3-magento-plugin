<?php

namespace TransactPro\MagentoPluginGW3\Controller\Index;

use Magento\Framework\Api\FilterBuilder;

use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Psr\Log\LoggerInterface;

class Result extends Action
{
    protected $pageFactory;
    /**
     * @var $session \Magento\Checkout\Model\Session
     */
    protected $session;
    protected $customerSession;
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
        \Magento\Customer\Model\Session $customerSession,
        LoggerInterface $logger
    )
    {
        $this->messageManager = $context->getMessageManager();
        $this->orderRepository = $orderRepository;
        $this->session = $checkoutSession;
        $this->transactionRepository = $transactionRepository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->customerSession = $customerSession;
        $this->logger = $logger;
        return parent::__construct($context);
    }

    public function execute()
    {
        $this->logger->critical('0912222 RESULT execute');
        $order = $this->session->getLastRealOrder();
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($order->getState() !== $order::STATE_PENDING_PAYMENT) {
            $this->messageManager->addSuccessMessage(__('Order has been paid.'));
        } else {
            $this->messageManager->addErrorMessage(__('Order pending payment.'));
        }

        return $resultRedirect->setUrl('/checkout/onepage/success');
    }
}