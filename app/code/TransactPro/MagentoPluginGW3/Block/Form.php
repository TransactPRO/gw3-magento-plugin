<?php

namespace TransactPro\MagentoPluginGW3\Block;

use TransactPro\MagentoPluginGW3\Gateway\Config\Config as GatewayConfig;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Block\Form\Cc;
use Magento\Payment\Model\Config;
use Magento\Payment\Helper\Data as Helper;
use TransactPro\MagentoPluginGW3\Model\Ui\ConfigProvider;

class Form extends Cc
{
    /** @var GatewayConfig $gatewayConfig */
    protected $gatewayConfig;

    /** @var Helper $paymentDataHelper */
    private $paymentDataHelper;

    private $logger;

    public function __construct(
        Context $context,
        Config $paymentConfig,
        GatewayConfig $gatewayConfig,
        Helper $helper,
        array $data = [],
        \Psr\Log\LoggerInterface $logger
    )
    {
        parent::__construct($context, $paymentConfig, $data);
        $this->gatewayConfig = $gatewayConfig;
        $this->paymentDataHelper = $helper;
        $this->logger = $logger;
    }

    public function useCcv()
    {
        $this->logger->critical(__METHOD__);
        return $this->gatewayConfig->isCcvEnabled();
    }

    /**
     * Check if vault enabled
     * @return bool
     */
    public function isVaultEnabled()
    {
        $this->logger->critical(__METHOD__);
        return false;
    }
}