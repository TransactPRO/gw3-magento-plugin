<?php

namespace TransactPro\MagentoPluginGW3\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use TransactPro\MagentoPluginGW3\Gateway\Helper\SubjectReader;

class AddressDataBuilder implements BuilderInterface
{
    const SHIPPING_ADDRESS = 'shipping';
    const STREET_ADDRESS = 'line1';
    const EXTENDED_ADDRESS = 'line2';
    const LOCALITY = 'city';
    const REGION = 'state';
    const POSTAL_CODE = 'postal_code';
    const COUNTRY_CODE = 'country';
    const NAME = 'name';
    const PHONE = 'phone';

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

    public function build(array $buildSubject)
    {
        $this->logger->critical(__METHOD__);
        $paymentDataObject = $this->subjectReader->readPayment($buildSubject);

        $order = $paymentDataObject->getOrder();
        $result = [];

        $shippingAddress = $order->getShippingAddress();
        if ($shippingAddress) {
            $result[self::SHIPPING_ADDRESS] = [
                'address' => [
                    self::STREET_ADDRESS => $shippingAddress->getStreetLine1() ?? ' ',
                    self::EXTENDED_ADDRESS => $shippingAddress->getStreetLine2() ?? ' ',
                    self::LOCALITY => $shippingAddress->getCity() ?? ' ',
                    self::REGION => $shippingAddress->getRegionCode() ?? ' ',
                    self::POSTAL_CODE => $shippingAddress->getPostcode() ?? ' ',
                    self::COUNTRY_CODE => $shippingAddress->getCountryId()
                ],
                self::NAME => $shippingAddress->getFirstname() . ' ' . $shippingAddress->getLastname(),
                self::PHONE => $shippingAddress->getTelephone()
            ];
        }

        $this->logger->critical('3562111 $result', $result);
        return $result;
    }
}