<?php

namespace TransactPro\MagentoPluginGW3\Gateway\Request;

use TransactPro\MagentoPluginGW3\Gateway\Config\Config;
use TransactPro\MagentoPluginGW3\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use TransactPro\MagentoPluginGW3\Helper\Payment\Formatter;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\CustomerRepositoryInterface;
use TransactPro\Gateway\DataSets\Customer;

class PaymentDataBuilder implements BuilderInterface
{
    use Formatter;

    const AMOUNT = 'amount';
    const SOURCE = 'source';
    const ORDER_ID = 'description';
    const CURRENCY = 'currency';
    const CAPTURE = 'capture';
    const CUSTOMER = 'customer';
    const SAVE_IN_VAULT = 'save_in_vault';

    /** @var Config */
    protected $config;

    /** @var SubjectReader */
    protected $subjectReader;

    /** @var Session */
    protected $customerSession;

    /** @var CustomerRepositoryInterface */
    protected $customerRepository;
    private $logger;

    /**
     * PaymentDataBuilder constructor.
     * @param Config $config
     * @param SubjectReader $subjectReader
     * @param Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        Config $config,
        SubjectReader $subjectReader,
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->config = $config;
        $this->subjectReader = $subjectReader;
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
    }

    /**
     * @param array $subject
     * @return array
     * @throws \Magento\Framework\Validator\Exception
     */
    public function build(array $subject)
    {
        $this->logger->critical(__METHOD__, $subject);
        $paymentDataObject = $this->subjectReader->readPayment($subject);
        $payment = $paymentDataObject->getPayment();
        $order = $paymentDataObject->getOrder();

        $result = [
            self::AMOUNT => $this->formatPrice($this->subjectReader->readAmount($subject)),
            self::ORDER_ID => $order->getOrderIncrementId(),
            self::CURRENCY => $this->config->getCurrency(),
            self::SOURCE => $this->getPaymentSource($payment),
            self::CAPTURE => 'false'
        ];


        return $result;
    }

    /**
     * @param $payment
     * @return array
     */
    protected function getPaymentSource($payment)
    {
        $this->logger->critical(__METHOD__);

        //todo add card holder field
        $data = [
            'cc_holder' => $payment->getAdditionalInformation('cc_holder'),
            'exp_month' => $payment->getAdditionalInformation('cc_exp_month'),
            'exp_year' => $payment->getAdditionalInformation('cc_exp_year'),
            'number' => $payment->getAdditionalInformation('cc_number'),
            'cvc' => $payment->getAdditionalInformation('cc_cid'),
            'last4' => $payment->getAdditionalInformation('cc_last4'),
        ];
        // If month = 1 (January) then this code will convert it to 01
        $data['exp_month'] = !empty($data['exp_month']) ? sprintf("%02d", $data['exp_month']) : '';

        $this->logger->critical('1144 Payment Source: ', $data);

        return $data;
    }
}