<?php

namespace TransactPro\MagentoPluginGW3\Helper\Payment;

use Magento\Payment\Helper\Formatter as PaymentFormatter;

trait Formatter
{
    public function formatPrice($price)
    {
        $price = sprintf('%.2F', $price);

        return str_replace('.', '', $price);
    }
}