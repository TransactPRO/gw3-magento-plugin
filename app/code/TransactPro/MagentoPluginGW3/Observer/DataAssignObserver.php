<?php

namespace TransactPro\MagentoPluginGW3\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;

class DataAssignObserver extends AbstractDataAssignObserver
{
    private $logger;

    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {

        $this->logger->critical('9999 Observer called');
        $method = $this->readMethodArgument($observer);
        $data = $this->readDataArgument($observer);
        $paymentInfo = $method->getInfoInstance();

        $this->logger->critical('22111 OBSERVER DATA: ', $data->getData());
        // todo Its unsafe to save card data to additional information
        $cardData = [
            'cc_holder' => $data->getDataByKey('additional_data')['cc_holder'],
            'cc_exp_month' => $data->getDataByKey('additional_data')['cc_exp_month'],
            'cc_exp_year' => $data->getDataByKey('additional_data')['cc_exp_year'],
            'cc_number' => $data->getDataByKey('additional_data')['cc_number'],
            'cc_cid' => $data->getDataByKey('additional_data')['cc_cid'],
        ];

        foreach ($cardData as $key => $val) {
            $paymentInfo->setAdditionalInformation($key, $val);
        }
    }
}