<?php

namespace TransactPro\MagentoPluginGW3\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\CustomerRepositoryInterface;
use TransactPro\MagentoPluginGW3\Gateway\Helper\SubjectReader;
use Stripe\Customer;

class CustomerDataBuilder implements BuilderInterface
{
    const CUSTOMER = 'customer';
    const FIRST_NAME = 'firstName';
    const LAST_NAME = 'lastName';
    const COMPANY = 'company';
    const EMAIL = 'email';
    const PHONE = 'phone';

    private $subjectReader;
    private $customerSession;
    private $logger;

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    public function __construct(
        SubjectReader $subjectReader,
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->subjectReader = $subjectReader;
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
    }

    public function build(array $subject)
    {
        $this->logger->critical(__METHOD__);
        $paymentDataObject = $this->subjectReader->readPayment($subject);
        $order = $paymentDataObject->getOrder();
        $billingAddress = $order->getBillingAddress();
        $customerData = [
            self::FIRST_NAME => $billingAddress->getFirstname() ?? ' ',
            self::LAST_NAME => $billingAddress->getLastname() ?? ' ',
            self::EMAIL => $billingAddress->getEmail() ?? ' ',
            'tel' => $billingAddress->getTelephone() ?? ' ',
            'country' => $billingAddress->getCountryId() ?? ' ',
            'city' => $billingAddress->getCity() ?? ' ',
            'street' => $billingAddress->getStreetLine1() ?? ' ',
            'house' => $billingAddress->getStreetLine2() ?? ' ',
            'zip' => $billingAddress->getPostcode() ?? ' ',
            'region' => $billingAddress->getRegionCode() ?? ' ',
        ];
        $this->logger->critical('22444 CustomerDataBuilder', $customerData);

        return $customerData;
    }

    //todo we have stored cards or something like that?
    protected function isSavePaymentInformation($paymentDataObject)
    {
        return false;
    }
}