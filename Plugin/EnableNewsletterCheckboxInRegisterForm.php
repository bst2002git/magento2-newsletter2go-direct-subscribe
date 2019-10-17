<?php

namespace IMI\Newsletter2GoDirectSubscribe\Plugin;

class EnableNewsletterCheckboxInRegisterForm
{
    /** @var \IMI\Newsletter2GoDirectSubscribe\Helper\Config */
    protected $config;

    public function __construct(\IMI\Newsletter2GoDirectSubscribe\Helper\Config $config)
    {
        $this->config = $config;
    }

    /**
     * Always show the register box, even if newsletter is disabled
     *
     * @see \Magento\Customer\Block\Form\Register::isNewsletterEnabled
     *
     * @return bool
     */
    public function aroundIsNewsletterEnabled(\Magento\Customer\Block\Form\Register $subject, callable $proceed)
    {
        if ($this->config->isEnabled()) {
            return true;
        } else {
            return $proceed();
        }
    }
}