<?php

namespace TransactPro\MagentoPluginGW3\Model\Adapter;

use TransactPro\Gateway\Gateway;
use TransactPro\Gateway\Operations\Transactions\Credit;
use TransactPro\Gateway\Operations\Transactions\DmsHold;
use TransactPro\Gateway\Operations\Transactions\Sms;
use TransactPro\MagentoPluginGW3\Gateway\Config\Config;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use TransactPro\MagentoPluginGW3\Model\Adminhtml\Source\PaymentMethod;

class TransactProAdapter
{
    const TEST_GATEWAY_URL = 'https://api.sandbox.transactpro.io/v3.0';
    const PROD_GATEWAY_URL = 'https://api.transactpro.io/v3.0';

    private $config;
    private $logger;
    protected $remoteAddress;
    protected $encryptor;

    /**
     * @var $gw Gateway
     */
    protected $gw;

    /**
     * @var $operation Sms|DmsHold|Credit
     */
    protected $operation;


    public function __construct(
        Config $config,
        \Psr\Log\LoggerInterface $logger,
        RemoteAddress $remoteAddress
    )
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->remoteAddress = $remoteAddress;
        $this->initCredentials();
    }

    protected function initCredentials()
    {
        $this->logger->critical(__METHOD__);

        $gatewayUrl = $this->config->isTestMode() ? self::TEST_GATEWAY_URL : self::PROD_GATEWAY_URL;
        $this->gw = new Gateway($gatewayUrl);

        $account_id = (int)$this->config->getAccountId();
        $secret_key = $this->config->getSecretKey();

        $this->gw->auth()
            ->setAccountID($account_id)
            ->setSecretKey($secret_key);

        switch ($this->config->getPaymentMethod()) {
            case PaymentMethod::DMS:
                $this->operation = $this->gw->createDmsHold();
                break;

            case PaymentMethod::SMS:
                $this->operation = $this->gw->createSms();
                break;

            case PaymentMethod::CREDIT:
                $this->operation = $this->gw->createCredit();
                break;

            case PaymentMethod::P2P:
                $this->operation = $this->gw->createP2P();
                $this->operation->order()->setRecipientName($this->config->getRecipientName());
                $this->operation->customer()->setBirthDate($this->config->getRecipientBirthdate());
                break;
        }
    }

    public function refund(string $transactionId, int $amount)
    {
        $this->logger->critical(__METHOD__, [$transactionId, $amount]);

        $operation = $this->gw->createRefund();
        $operation->command()->setGatewayTransactionID($transactionId);
        $operation->money()->setAmount( (int) $amount );
        $request  = $this->gw->generateRequest($operation);
        $response = $this->gw->process($request);

        if ( 200 !== $response->getStatusCode() ) {
            throw new \Exception( $response->getBody(), $response->getStatusCode() );
        }
        $json = json_decode($response->getBody(), true);
        $this->logger->critical('11199900 REFUND RESPONSE', $json);
        $this->logger->critical('11199900 REFUND GW ID', [$transactionId]);

        return $json;
    }

    public function sale($attributes)
    {
        $this->logger->critical(__METHOD__, $attributes);
        $amount = (int)$attributes['amount'];

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');

        $this->operation->order()
            // order id on store side
            ->setDescription($attributes['description']) // order id on store side
            ->setMerchantSideUrl($storeManager->getStore()->getBaseUrl());

        #$ip = $this->remoteAddress->getRemoteAddress() == '127.0.0.1' ? '194.44.253.141' : $this->remoteAddress->getRemoteAddress();
        $ip = $this->remoteAddress->getRemoteAddress();
        $this->operation->system()
            ->setUserIP($ip); // user ip

        $shipping = $attributes['shipping'];

        $this->operation->customer()
            ->setEmail($attributes['email'])
            ->setPhone($attributes['tel'])
            ->setBillingAddressCountry($attributes['country'])
            ->setBillingAddressState($attributes['region'])
            ->setBillingAddressCity($attributes['city'])
            ->setBillingAddressStreet($attributes['street'])
            ->setBillingAddressHouse($attributes['house'])
            ->setBillingAddressFlat(' ')
            ->setBillingAddressZIP($attributes['zip'] ?? 'N/A')
            // Shipping data
            ->setShippingAddressCountry($shipping['address']['country'])
            ->setShippingAddressState($shipping['address']['state'] ?? 'N/A')
            ->setShippingAddressCity($shipping['address']['city'] ?? 'N/A')
            ->setShippingAddressStreet($shipping['address']['line1'] ?? 'N/A')
            ->setShippingAddressHouse($shipping['address']['line2'] ?? 'N/A')
            ->setShippingAddressFlat(' ')
            ->setShippingAddressZIP($shipping['address']['postal_code'] ?? 'N/A');

        $card = $attributes['source'];
        if (!empty($card['exp_month']) && !empty($card['exp_year'])) {
            $expire = $card['exp_month'] . '/' . substr($card['exp_year'], 2, 2); // like mm/yy
        } else {
            $expire = '';
        }

        $this->operation->paymentMethod()
            ->setPAN($card['number'])
            ->setExpire($expire)
            ->setCVV($card['cvc'])
            ->setCardHolderName($card['cc_holder']);

        $this->operation->money()
            ->setAmount($amount)
            ->setCurrency($this->config->getCurrency());

        try {
            $gwRequest = $this->gw->generateRequest($this->operation);
            $response = $this->gw->process($gwRequest);

            $this->logger->critical('SALE Response BODY: ', [$response->getBody()]);

            $json = json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            $this->logger->critical('5555 ERROR MSG: ' . $e->getMessage());
            $this->logger->critical('5556 ERROR FILE: ' . $e->getFile());
            $this->logger->critical('5557 ERROR LINE: ' . $e->getLine());
            throw new \Magento\Framework\Validator\Exception(__($e->getMessage()));
        }

        $this->logger->critical('1122 RESPONSE JSON', $json);

        return $json;
    }

    public function submitForSettlement(string $transactionId, int $amount = null)
    {
        $this->logger->critical('submitForSettlement', [$transactionId, $amount]);
        $this->operation = $this->gw->createDmsCharge();
        $this->operation->command()->setGatewayTransactionID($transactionId);
        if ($amount) {
            $this->operation->money()->setAmount($amount)->setCurrency($this->config->getCurrency());;
        }

        try {
            $gwRequest = $this->gw->generateRequest($this->operation);
            $response = $this->gw->process($gwRequest);

            $json = json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            $this->logger->critical('5555 ERROR MSG: ' . $e->getMessage());
            $this->logger->critical('5556 ERROR FILE: ' . $e->getFile());
            $this->logger->critical('5557 ERROR LINE: ' . $e->getLine());
            throw new \Magento\Framework\Validator\Exception(__($e->getMessage()));
        }

        return $json;
    }

    public function void($transactionId)
    {
        $this->logger->critical('void', [$transactionId]);

        $this->operation = $this->gw->createCancel();
        $this->operation->command()->setGatewayTransactionID($transactionId);

        try {
            $gwRequest = $this->gw->generateRequest($this->operation);
            $response = $this->gw->process($gwRequest);

            $json = json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            $this->logger->critical('5555 ERROR MSG: ' . $e->getMessage());
            $this->logger->critical('5556 ERROR FILE: ' . $e->getFile());
            $this->logger->critical('5557 ERROR LINE: ' . $e->getLine());
            throw new \Magento\Framework\Validator\Exception(__($e->getMessage()));
        }

        $this->logger->critical('Cancel/Void RESPONSE: ', $json);

        return $json;
    }

    /**
     * @param $attributes
     * @return array
     */
    protected function _saveCustomerCard($attributes)
    {
        $this->logger->critical(__METHOD__);

        return [];
    }
}