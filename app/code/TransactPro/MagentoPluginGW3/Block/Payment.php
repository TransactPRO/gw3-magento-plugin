<?php

namespace TransactPro\MagentoPluginGW3\Block;

use TransactPro\MagentoPluginGW3\Model\Ui\ConfigProvider;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Payment
 */
class Payment extends Template
{
    /**
     * @var ConfigProvider
     */
    private $config;

    /**
     * Constructor
     *
     * @param Context $context
     * @param ConfigProvider $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        ConfigProvider $config,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getPaymentConfig()
    {
        $payment = $this->config->getConfig()['payment'];
        $config = $payment[$this->getCode()];
        $config['code'] = $this->getCode();
        return json_encode($config, JSON_UNESCAPED_SLASHES);
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return ConfigProvider::CODE;
    }
}
