<?php

declare(strict_types=1);

namespace Alfakher\CustomerExtended\Controller\Address;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\ResourceModel\Address as AddressResourceModel;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class SetDefault updates shipping and billing address
 */
class SetDefault implements ActionInterface
{
    /**
     * @var RequestInterface
     */
    private RequestInterface $request;
    /**
     * @var UserContextInterface
     */
    private UserContextInterface $userContext;
    /**
     * @var RedirectFactory
     */
    private RedirectFactory $redirectFactory;
    /**
     * @var AddressFactory
     */
    private AddressFactory $addressFactory;
    /**
     * @var AddressResourceModel
     */
    private AddressResourceModel $addressResourceModel;
    /**
     * @var ManagerInterface
     */
    private ManagerInterface $manager;

    /**
     * @param RequestInterface $request
     * @param UserContextInterface $userContext
     * @param RedirectFactory $redirectFactory
     * @param AddressFactory $addressFactory
     * @param AddressResourceModel $addressResourceModel
     * @param ManagerInterface $manager
     */
    public function __construct(
        RequestInterface $request,
        UserContextInterface $userContext,
        RedirectFactory $redirectFactory,
        AddressFactory $addressFactory,
        AddressResourceModel $addressResourceModel,
        ManagerInterface $manager
    ) {
        $this->request = $request;
        $this->userContext = $userContext;
        $this->redirectFactory = $redirectFactory;
        $this->addressFactory = $addressFactory;
        $this->addressResourceModel = $addressResourceModel;
        $this->manager = $manager;
    }

    /**
     * Updates shipping and billing address
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $params = $this->request->getParams();
        if ($this->userContext->getUserType() == UserContextInterface::USER_TYPE_CUSTOMER) {
            $customerId = $this->userContext->getUserId();
            $address = $this->addressFactory->create();
            if (array_key_exists('shipId', $params)) {
                $addressId = $this->request->getParam('shipId');
                $this->addressResourceModel->load($address, $addressId);
                $address->setData('is_default_shipping', 1);
                $address->setCustomerId($customerId);
            }
            if (array_key_exists('billId', $params)) {
                $addressId = $this->request->getParam('billId');
                $this->addressResourceModel->load($address, $addressId);
                $address->setData('is_default_billing', 1);
                $address->setCustomerId($customerId);
            }
            try {
                $this->addressResourceModel->save($address);
                $this->manager->addSuccessMessage(__('Address saved successfully.'));
            } catch (\Exception $exception) {
                $this->manager->addErrorMessage($exception->getMessage());
            }
        }
        $resultRedirect = $this->redirectFactory->create();
        $resultRedirect->setPath('customer/address/index');
        return $resultRedirect;
    }
}
