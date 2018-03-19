<?php

namespace TransactPro\MagentoPluginGW3\Gateway\Validator;

class ResponseValidator extends GeneralResponseValidator
{
    protected function getResponseValidators()
    {
        return array_merge(
            parent::getResponseValidators(),
            [
                function ($response) {
                    if (isset($response['error']) && is_array($response['error']) && count($response['error'])) {
                        return [false, [$response['error']['message']]];
                    }
                    return [
                        true,
                        [__('Wrong transaction status')]
                    ];
                }
            ]
        );
    }
}