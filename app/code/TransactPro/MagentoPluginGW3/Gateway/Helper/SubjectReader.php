<?php

namespace TransactPro\MagentoPluginGW3\Gateway\Helper;

use Magento\Payment\Gateway\Helper;

class SubjectReader
{
    /**
     * @param array $subject
     * @return array
     */
    public function readResponseObject(array $subject)
    {
        $response = Helper\SubjectReader::readResponse($subject);
        $response = $response['object'];

        if (!isset($response['gw'])) {
            throw new \InvalidArgumentException($response['error']['message']);
        }

        return $response;
    }

    public function readPayment(array $subject)
    {
        return Helper\SubjectReader::readPayment($subject);
    }

    public function readTransaction(array $subject)
    {
        if (!isset($subject['object']['acquirer-details'])) {
            throw new \InvalidArgumentException('Response object does not contain transaction information.');
        }

        return $subject['object']['acquirer-details'];
    }

    public function readAmount(array $subject)
    {
        return Helper\SubjectReader::readAmount($subject);
    }

    public function readCustomerId(array $subject)
    {
        if (!isset($subject['customer_id'])) {
            throw new \InvalidArgumentException('The customerId field does not exist');
        }

        return (int)$subject['customer_id'];
    }
}