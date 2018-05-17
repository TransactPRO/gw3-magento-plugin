<?php

namespace TransactPro\MagentoPluginGW3\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use TransactPro\MagentoPluginGW3\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

class GeneralResponseValidator extends AbstractValidator
{
    protected $subjectReader;
    protected $logger;

    public function __construct(
        ResultInterfaceFactory $resultFactory,
        SubjectReader $subjectReader,
        \Psr\Log\LoggerInterface $logger
    )
    {
        parent::__construct($resultFactory);
        $this->subjectReader = $subjectReader;
        $this->logger = $logger;
    }

    public function validate(array $subject)
    {
        $this->logger->critical('11111 SUBJECT: ', $subject);
        $response = $this->subjectReader->readResponseObject($subject);

        $isValid = true;
        $errorMessages = [];

        foreach ($this->getResponseValidators() as $validator) {
            $validationResult = $validator($response);

            if (!$validationResult[0]) {
                $isValid = $validationResult[0];
                $errorMessages = array_merge($errorMessages, $validationResult[1]);
                break;
            }
        }

        return $this->createResult($isValid, $errorMessages);
    }

    protected function getResponseValidators()
    {
        $this->logger->critical(__METHOD__);

        return [
            function ($response) {
                if (!isset($response['gw']['status-code'])) {
                    return [
                        false,
                        [__($response['error']['message'])]
                    ];
                }

                if (in_array($response['gw']['status-code'], self::getValidStatusCodes()) === false) {
                    return [
                        false,
                        [__($response['error']['message'])]
                    ];
                }

                return [
                    true,
                    [__($response['gw']['status-text'])]
                ];
            }
        ];
    }

    public static function getValidStatusCodes()
    {
        // todo use constants instead of numbers
        return [
            7, # SMS/Credit SUCCESS
            3, # DMS Hold SUCCESS
            26, # MPI URL GENERATED
            30, # FORM URL
            13, #  REFUND SUCCESS
            15 #  CANCEL SUCCESS
        ];
    }
}