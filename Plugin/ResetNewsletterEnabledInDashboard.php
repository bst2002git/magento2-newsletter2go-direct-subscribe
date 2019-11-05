<?php

namespace IMI\Newsletter2GoDirectSubscribe\Plugin;

use Magento\Newsletter\Observer\PredispatchNewsletterObserver;

class ResetNewsletterEnabledInDashboard
{
    /** @var \IMI\Newsletter2GoDirectSubscribe\Helper\Config */
    protected $config;

    /** @var \Magento\Framework\Module\Manager */
    protected $_moduleManager;

    public function __construct(
        \IMI\Newsletter2GoDirectSubscribe\Helper\Config $config,
        \Magento\Framework\Module\Manager $_moduleManager)
    {
        $this->config = $config;
        $this->_moduleManager = $_moduleManager;
    }

    /**
     * Reset to standard enabled / disable, because this block calls the plugged in class
     *
     * @see \IMI\Newsletter2GoDirectSubscribe\Plugin\EnableNewsletterCheckboxInRegisterForm::aroundIsNewsletterEnabled
     *
     * @see \Magento\Customer\Block\Account\Dashboard\Info::isNewsletterEnabled
     *
     * @return bool
     */
    public function aroundIsNewsletterEnabled(\Magento\Customer\Block\Account\Dashboard\Info $subject, callable $proceed)
    {
        if ($this->config->isEnabled()) {
            // logic from @see \Magento\Customer\Block\Account\Dashboard\Info::isNewsletterEnabled
            $registerBlock = $subject->getLayout()
                ->getBlockSingleton(\Magento\Customer\Block\Form\Register::class);

            return $this->_moduleManager->isOutputEnabled('Magento_Newsletter')
                && $registerBlock->getConfig(PredispatchNewsletterObserver::XML_PATH_NEWSLETTER_ACTIVE);
        } else {
            return $proceed();
        }
    }
}