<?php

namespace TransactPro\MagentoPluginGW3\Gateway\Validator\ResponseValidator;

use TransactPro\MagentoPluginGW3\Gateway\Validator\ResponseValidator;

class Authorize extends ResponseValidator
{
    protected function getResponseValidators()
    {
        return array_merge(
            parent::getResponseValidators(),
            [
                function ($response) {
                    return [
                        true,
                        [__('Transaction has been declined')]
                    ];
                }
            ]
        );
    }
}