<?php

namespace WMZ\QuickAjaxLogin\Model;

use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;

class Customer
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var CustomerInterfaceFactory
     */
    private $customerInterfaceFactory;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * Customer constructor.
     * @param StoreManagerInterface $storeManager
     * @param CustomerFactory $customerFactory
     * @param CustomerInterfaceFactory $customerInterfaceFactory
     * @param EncryptorInterface $encryptor
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        CustomerFactory $customerFactory,
        CustomerInterfaceFactory $customerInterfaceFactory,
        EncryptorInterface $encryptor,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->storeManager     = $storeManager;
        $this->customerFactory  = $customerFactory;
        $this->customerInterfaceFactory = $customerInterfaceFactory;
        $this->encryptor = $encryptor;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @return int
     * @throws LocalizedException
     */
    public function getWebsiteId()
    {
        return $this->storeManager->getWebsite()->getWebsiteId();
    }

    /**
     * @param $email
     * @return bool
     * @throws LocalizedException
     */
    public function userExists($email)
    {
        $customer =  $this->customerFactory->create();
        $customer->setWebsiteId($this->getWebsiteId());
        if ($customer->loadByEmail($email)->getId()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $userData
     * @return bool
     */
    public function createUser($userData)
    {
        try {
            $customer = $this->customerInterfaceFactory->create();
            $customer->setWebsiteId($this->getWebsiteId());
            $customer->setEmail($userData['email']);
            $customer->setFirstname($userData['firstname']);
            $customer->setLastname($userData['lastname']);
            $hashedPassword = $this->encryptor->getHash($userData['password'], true);
            $this->customerRepository->save($customer, $hashedPassword);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
