<?php

namespace IMI\Newsletter2GoDirectSubscribe\Plugin;

use NL2GO\Newsletter2Go_REST_Api;

class InterceptSubscription
{
    const FORM_ENDPOINT = 'https://api.newsletter2go.com/forms/submit/%s';

    /** @var \Magento\Customer\Model\CustomerFactory */
    protected $customerFactory;

    /** @var \Magento\Framework\Message\ManagerInterface */
    protected $messageManager;

    /** @var \Psr\Log\LoggerInterface */
    protected $logger;

    /** @var \IMI\Newsletter2GoDirectSubscribe\Helper\Config */
    protected $configHelper;

    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Psr\Log\LoggerInterface $logger,
        \IMI\Newsletter2GoDirectSubscribe\Helper\Config $configHelper)
    {
        $this->customerFactory = $customerFactory;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
        $this->configHelper = $configHelper;
    }

    /**
     * @see \Magento\Newsletter\Model\Subscriber::subscribeCustomerById
     */
    public function aroundSubscribeCustomerById(\Magento\Newsletter\Model\Subscriber $subject, callable $proceed, $customerId)
    {
        if (!$this->configHelper->isEnabled()) {
            return $proceed($customerId);
        }

        // Remark: use service contracts, but they just don't give us a customer object with getDefaultBillingAddress
        $customer = $this->customerFactory->create()->load($customerId);

        $subscriberData = [
            'first_name' => $customer->getFirstname(),
            'last_name' => $customer->getLastname(),
            'Firma' => $customer->getDefaultBillingAddress()->getCompany(), // FIXME: Do not hard code mapping
            'email' => $customer->getEmail(),
        ];

        $formCode = $this->configHelper->getFormCode();

        $this->sendToSubscriptionForm($subscriberData, $formCode);
    }

    protected function sendToSubscriptionForm($subscriberData, $formCode)
    {
        $endPoint = sprintf(self::FORM_ENDPOINT, $formCode);
        $result = $this->sendCurlPostRequest($endPoint, ['recipient' => $subscriberData]);

        if ($result->status == 201) {
            $this->logger->info(sprintf('Newsletter subscription sent to Newsletter2GO: %s', $subscriberData['email']));
            $this->messageManager->addSuccessMessage(__('Thank you for your interest in our newsletter. Please confirm the registration in the seperate mail you will receive.'));
        } else {
            $this->logger->warning('Cannot process newsletter subscription', ['result' => $result, 'subscriberData' => $subscriberData]);
            $this->messageManager->addWarningMessage(__('Sorry, your newsletter subscription could not be processed. Code: %1', $result->status));
        }
    }

    private function sendCurlPostRequest($endPoint, $data)
    {
        $ch = curl_init();

        $data_string = json_encode($data);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_URL, $endPoint );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string)
        ));

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        curl_close($ch);

        $json_decoded = json_decode($response);

        if(isset($json_decoded)){
            return $json_decoded;
        }
    }
}