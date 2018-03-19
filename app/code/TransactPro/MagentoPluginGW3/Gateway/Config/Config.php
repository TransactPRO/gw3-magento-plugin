<?php

namespace TransactPro\MagentoPluginGW3\Gateway\Config;

class Config extends \Magento\Payment\Gateway\Config\Config
{
    const KEY_ENVIRONMENT = 'test_mode';
    const KEY_ACTIVE = 'active';
    const KEY_LIVE_SECRET_KEY = 'live_secret_key';
    const KEY_TEST_SECRET_KEY = 'test_secret_key';
    const KEY_CURRENCY = 'currency';
    const KEY_CC_TYPES = 'cctypes';
    const KEY_CC_TYPES_STRIPE_MAPPER = 'cctypes_transactpro_mapper';
    const KEY_USE_CCV = 'useccv';
    const KEY_ALLOW_SPECIFIC = 'allowspecific';
    const KEY_SPECIFIC_COUNTRY = 'specificcountry';
    const KEY_ACCOUNT_ID = 'account_id';
    const KEY_PAYMENT_METHOD = 'payment_method';
    const KEY_SHOW_CC_FORM = 'show_cc_form';
    const KEY_P2P_NAME = 'recipient_name';
    const KEY_P2P_BIRTHDATE = 'recipient_birthdate';

    public function getRecipientName()
    {
        return $this->getValue(self::KEY_P2P_NAME);
    }

    public function getRecipientBirthdate()
    {
        return $this->getValue(self::KEY_P2P_BIRTHDATE);
    }

    public function getPaymentMethod()
    {
        return $this->getValue(self::KEY_PAYMENT_METHOD);
    }

    public function showCcForm()
    {
        return $this->getValue(self::KEY_SHOW_CC_FORM);
    }

    /**
     * @return array
     */
    public function getAvailableCardTypes()
    {
        $ccTypes = $this->getValue(self::KEY_CC_TYPES);

        return !empty($ccTypes) ? explode(',', $ccTypes) : [];
    }

    /**
     * @return array|mixed
     */
    public function getCcTypesMapper()
    {
        $result = json_decode(
            $this->getValue(self::KEY_CC_TYPES_STRIPE_MAPPER),
            true
        );

        return is_array($result) ? $result : [];
    }

    /**
     * @return mixed
     */
    public function getCurrency()
    {
        return $this->getValue(self::KEY_CURRENCY);
    }

    /**
     * @return bool
     */
    public function isCcvEnabled()
    {
        return (bool)$this->getValue(self::KEY_USE_CCV);
    }

    /**
     * @return mixed
     */
    public function getEnvironment()
    {
        return $this->getValue(Config::KEY_ENVIRONMENT);
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return (bool)$this->getValue(self::KEY_ACTIVE);
    }

    /**
     * @return mixed
     */
    public function getSecretKey()
    {
        if ($this->isTestMode()) {
            return $this->getValue(self::KEY_TEST_SECRET_KEY);
        }
        return $this->getValue(self::KEY_LIVE_SECRET_KEY);
    }

    /**
     * @return bool
     */
    public function isTestMode()
    {
        return (bool)$this->getEnvironment();
    }

    public function getAccountId()
    {
        return $this->getValue(self::KEY_ACCOUNT_ID);
    }
}