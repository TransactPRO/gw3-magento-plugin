<?php

namespace TransactPro\MagentoPluginGW3\Gateway\Config;

use TransactPro\MagentoPluginGW3\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Config\ValueHandlerInterface;

class CanReviewPaymentHandler implements ValueHandlerInterface
{
    private $subjectReader;

    public function __construct(
        SubjectReader $subjectReader
    )
    {
        $this->subjectReader = $subjectReader;
    }

    public function handle(array $subject, $storeId = NULL)
    {
        return true;
    }
}