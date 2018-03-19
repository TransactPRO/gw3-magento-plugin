<?php

namespace TransactPro\MagentoPluginGW3\Gateway\Http\Client;

use TransactPro\MagentoPluginGW3\Model\Adapter\TransactProAdapter;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use Psr\Log\LoggerInterface;

abstract class AbstractTransaction implements ClientInterface
{
    protected $logger;

    protected $customLogger;

    protected $adapter;

    public function __construct(
        LoggerInterface $logger,
        Logger $customLogger,
        TransactProAdapter $adapter
    )
    {
        $this->logger = $logger;
        $this->customLogger = $customLogger;
        $this->adapter = $adapter;
    }

    public function placeRequest(
        TransferInterface $transferObject
    )
    {
        $this->logger->critical(__METHOD__);
        $data = $transferObject->getBody();
        $log = [
            'request' => $data,
            'client' => static::class
        ];
        $response['object'] = [];

        try {
            $response['object'] = $this->process($data);
        } catch (\Exception $e) {
            $message = __($e->getMessage() ?: 'Sorry, but something went wrong.');
            $this->logger->critical('44444' . $message);
            throw new ClientException($message);
        } finally {
            $log['response'] = (array)$response['object'];
            $this->customLogger->debug($log);
            $this->logger->critical('after placeRequest 222:', $log);
        }

        return $response;
    }

    abstract protected function process(array $data);
}