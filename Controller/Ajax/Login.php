<?php

namespace WMZ\QuickAjaxLogin\Controller\Ajax;

use Exception;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;

class Login implements ActionInterface
{
    /**
     * Config name for Redirect Customer to Account Dashboard after Logging in setting
     */
    private const XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD = 'customer/startup/redirect_dashboard';

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var RedirectInterface
     */
    private $redirect;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var RawFactory
     */
    private $resultRawFactory;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var AccountRedirect
     */
    private $accountRedirect;

    /**
     * @var AccountManagementInterface
     */
    private $customerAccountManagement;

    /**
     * Login constructor.
     * @param RequestInterface $request
     * @param RedirectInterface $redirect
     * @param Session $customerSession
     * @param RawFactory $resultRawFactory
     * @param JsonFactory $resultJsonFactory
     * @param AccountRedirect $accountRedirect
     * @param ScopeConfigInterface $scopeConfig
     * @param AccountManagementInterface $customerAccountManagement
     */
    public function __construct(
        RequestInterface $request,
        RedirectInterface $redirect,
        Session $customerSession,
        RawFactory $resultRawFactory,
        JsonFactory $resultJsonFactory,
        AccountRedirect $accountRedirect,
        ScopeConfigInterface $scopeConfig,
        AccountManagementInterface $customerAccountManagement
    ) {
        $this->request = $request;
        $this->redirect = $redirect;
        $this->customerSession = $customerSession;
        $this->resultRawFactory = $resultRawFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->accountRedirect = $accountRedirect;
        $this->scopeConfig = $scopeConfig;
        $this->customerAccountManagement = $customerAccountManagement;
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $credentials = null;
        $httpBadRequestCode = 400;

        $resultRaw = $this->resultRawFactory->create();
        try {
            $credentials = [
                'username' => $this->request->getPost('username'),
                'password' => $this->request->getPost('password')
            ];
        } catch (Exception $e) {
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }
        if (!$credentials || $this->request->getMethod() !== 'POST' || !$this->request->isXmlHttpRequest()) {
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }

        $response = [
            'errors' => false,
            'message' => __('Login successful.')
        ];
        try {
            $customer = $this->customerAccountManagement->authenticate(
                $credentials['username'],
                $credentials['password']
            );
            $this->customerSession->setCustomerDataAsLoggedIn($customer);
            $this->customerSession->regenerateId();
            $redirectRoute = $this->accountRedirect->getRedirectCookie();
            if (!$this->scopeConfig->getValue(self::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD)
                && $redirectRoute
            ) {
                $response['redirectUrl'] = $this->redirect->success($redirectRoute);
                $this->accountRedirect->clearRedirectCookie();
            }
        } catch (InvalidEmailOrPasswordException $e) {
            $response = [
                'errors' => true,
                'message' => $e->getMessage()
            ];
        } catch (Exception $e) {
            $response = [
                'errors' => true,
                'message' => __('Invalid login or password.') . $e->getMessage()
            ];
        }

        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
