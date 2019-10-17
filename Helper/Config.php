<?php
namespace IMI\Newsletter2GoDirectSubscribe\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Config extends AbstractHelper
{
    const XML_PATH_FORM_CODE = 'newsletter/general/newsletter2go_form_code';

    public function getFormCode()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_FORM_CODE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function isEnabled()
    {
        return !empty($this->getFormCode());
    }

}