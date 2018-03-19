<?php

namespace TransactPro\MagentoPluginGW3\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Api\StoreManagementInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\ObjectManager;

class AfterOrderObserver extends AbstractDataAssignObserver
{
    private $storeManager;
    protected $_checkoutSession;
    protected $logger;
    protected $orderRepository;
    protected $invoiceService;
    protected $objectManager;


    public function __construct(
        StoreManagementInterface $storeManager,
        Session $checkoutSession,
        \Psr\Log\LoggerInterface $logger,
        OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService
    ) {
        $this->storeManager = $storeManager;
        $this->_checkoutSession = $checkoutSession;
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->invoiceService = $invoiceService;
    }

    public function execute(Observer $observer)
    {
        $this->logger->critical(__METHOD__);

        $orderId = $observer->getEvent()->getOrderIds();
        $objectManager = ObjectManager::getInstance();
        $redirect = $objectManager->get('\Magento\Framework\App\Response\Http');
        /**
         * @var $order Order
         */
        $order = $this->orderRepository->get($orderId[0]);
        $payment = $order->getPayment();

        $this->logger->critical("09877 payment DATA", $payment->getAdditionalInformation());
        $this->logger->critical("09877 OrderId", $orderId);
        $this->logger->critical("09877 order state", [$order->getState()]);

        // We need to attach invoice to transaction or mb vise-versa?
        if (key_exists('dmsHoldSuccess', $payment->getAdditionalInformation())) {
            $this->logger->critical("09899 dmsHoldSuccess - STATE_PAYMENT_REVIEW");
            $invoice = $this->createInvoice($order);

            $order->setState($order::STATE_PAYMENT_REVIEW);
            $order->addStatusToHistory(false, __('Transaction accepted - you can charge or cancel payment.'));

            $this->orderRepository->save($order);
            if (isset($invoice) && $invoice !== null) $invoice->save();
        }

        if (key_exists('gatewayUrl', $payment->getAdditionalInformation())) {
            $this->logger->critical("09877 getData", [$payment->getAdditionalInformation()['gatewayUrl']]);
            $gatewayUrl = $payment->getAdditionalInformation()['gatewayUrl'];
            $invoice = $this->createInvoice($order);
            // we can move it (lines below) to TransactionIdHandler to prevent double save
            $order->setState($order::STATE_PENDING_PAYMENT);
            $order->addStatusToHistory(false, __('Pending payment.'));
            $this->orderRepository->save($order);
            if (isset($invoice) && $invoice !== null) $invoice->save();
            // we can move it ^^^ to TransactionIdHandler to prevent double save
            return $redirect->setRedirect($gatewayUrl);
        } else {
            $this->logger->critical("09879 after order redirect to /checkout/onepage/success");
            return $redirect->setRedirect('/checkout/onepage/success');
        }
    }

    protected function createInvoice($order)
    {
        $invoice = null;

        if ($order->canInvoice()) {
            $objectManager = ObjectManager::getInstance();
            $this->logger->critical("09879 prepareInvoice");
            #$invoice = $this->invoiceService->prepareInvoice($order);
            /**
             * @var $invoice \Magento\Sales\Model\Order\Invoice
             */
            $invoice = $objectManager->create('Magento\Sales\Model\Service\InvoiceService')->prepareInvoice($order);
            $this->logger->critical("09879 Invoice Items: ", $invoice->getAllItems());

            /**
             * @var $iitem \Magento\Sales\Model\Order\Invoice\Item
             */
            $iitem = $invoice->getAllItems()[0];

            $this->logger->critical("09879 Invoice Item QTY: ", [$iitem->getQty()]);

            if (!$invoice->getTotalQty()) {
                $this->logger->critical("09879 getTotalQty === 0 (BAD)");
            }

            $this->logger->critical("09879 setRequestedCaptureCase");
            $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
            $invoice->setState($invoice::STATE_OPEN);
            $this->logger->critical("09879 register");
        } else {
            $this->logger->critical("09879 canInvoice === false");
        }

        return $invoice;
    }
}