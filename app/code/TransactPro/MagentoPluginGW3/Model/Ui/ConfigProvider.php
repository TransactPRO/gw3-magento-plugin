<?php

namespace TransactPro\MagentoPluginGW3\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;
use TransactPro\MagentoPluginGW3\Gateway\Config\Config;

class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'transactpro';
    const CC_VAULT_CODE = 'transactpro_vault';

    protected $_config;
    protected $_encryptor;

    public function __construct(
        ScopeConfigInterface $configInterface,
        EncryptorInterface $encryptorInterface
    )
    {
        $this->_config = $configInterface;
        $this->_encryptor = $encryptorInterface;
    }

    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                    'payment_method' => $this->getPaymentMethod(),
                    'show_cc_form' => $this->showCcForm(),
                ]
            ]
        ];
    }

    protected function getPaymentMethod()
    {
        return $this->_getConfig(Config::KEY_PAYMENT_METHOD);
    }

    protected function showCcForm()
    {
        return (bool)$this->_getConfig(Config::KEY_SHOW_CC_FORM);
    }

    protected function _isTestMode()
    {
        return (bool)$this->_getConfig(Config::KEY_ENVIRONMENT);
    }

    protected function _getEncryptedConfig($value)
    {
        $config = $this->_getConfig($value);
        return $this->_encryptor->decrypt($config);
    }

    protected function _getConfig($value)
    {
        return $this->_config->getValue('payment/transactpro/' . $value, ScopeInterface::SCOPE_STORE);
    }
}