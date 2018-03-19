<?php

namespace TransactPro\MagentoPluginGW3\Helper;

use TransactPro\MagentoPluginGW3\Model\Adminhtml\Source\Cctype as CcTypeSource;

class CcType
{
    private $ccTypes = [];
    private $ccTypeSource;

    public function __construct(
        CcTypeSource $ccTypeSource
    )
    {
        $this->ccTypeSource = $ccTypeSource;
    }

    public function getCcTypes()
    {
        if (!$this->ccTypes) {
            $this->ccTypes = $this->ccTypeSource->toOptionArray();
        }
        return $this->ccTypes;
    }
}