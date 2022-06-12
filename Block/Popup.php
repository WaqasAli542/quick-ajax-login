<?php

namespace WMZ\QuickAjaxLogin\Block;

use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Popup extends Template
{
    /**
     * @var SessionFactory
     */
    private $sessionFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param Context $context
     * @param SessionFactory $sessionFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        SessionFactory $sessionFactory,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->sessionFactory = $sessionFactory;
        $this->scopeConfig = $scopeConfig;
    }

    public function isLoggedIn()
    {
        $customerSession = $this->sessionFactory->create();
        return $customerSession->isLoggedIn();
    }

    public function getMinimumPasswordLength()
    {
        return $this->scopeConfig->getValue(AccountManagement::XML_PATH_MINIMUM_PASSWORD_LENGTH);
    }

    public function getRequiredCharacterClassesNumber()
    {
        return $this->scopeConfig->getValue(AccountManagement::XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER);
    }
}
