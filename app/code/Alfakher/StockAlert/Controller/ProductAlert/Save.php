<?php

declare(strict_types=1);

namespace Alfakher\StockAlert\Controller\ProductAlert;

use Alfakher\StockAlert\Api\Data\ProductAlertStockGuestUserInterface;
use Alfakher\StockAlert\Api\ProductAlertStockGuestUserRepositoryInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\App\Request\Http;
use Magento\Store\Model\StoreManagerInterface;
use Alfakher\StockAlert\Helper\Data;

class Save extends Action
{
    /**
     * @var Validator
     */
    protected Validator $formKeyValidator;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var ProductAlertStockGuestUserRepositoryInterface
     */
    private ProductAlertStockGuestUserRepositoryInterface $guestSubscriptionRepository;

    /**
     * @var ProductAlertStockGuestUserInterface
     */
    private ProductAlertStockGuestUserInterface $guestSubscriptionDataFactory;

    /**
     * @var Registry
     */
    private Registry $coreRegistry;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    private \Magento\Catalog\Helper\Data $catalogHelper;

    public function __construct(
        Context $context,
        Http $httpRequest,
        Validator $formKeyValidator,
        StoreManagerInterface $storeManager,
        Registry              $coreRegistry,
        ProductAlertStockGuestUserRepositoryInterface $guestSubscriptionRepository,
        ProductAlertStockGuestUserInterface $guestSubscriptionDataFactory,
        \Magento\Catalog\Helper\Data $catalogHelper,
        Data $data
    ) {
        parent::__construct($context);
        $this->httpRequest = $httpRequest;
        $this->formKeyValidator = $formKeyValidator;
        $this->storeManager = $storeManager;
        $this->coreRegistry = $coreRegistry;
        $this->guestSubscriptionDataFactory = $guestSubscriptionDataFactory;
        $this->guestSubscriptionRepository = $guestSubscriptionRepository;
        $this->catalogHelper = $catalogHelper;
        $this->data = $data;
    }

    /**
     * Save Function
     *
     * @throws NoSuchEntityException
     * @throws \Zend_Log_Exception
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addError(__('Invalid form key. Please try again.'));
            return $resultRedirect->setPath('*/*/');
        }

        $name = $this->getRequest()->getPost('name');
        $email = $this->getRequest()->getPost('email');
        $storeId = $this->data->getStoreId();
        $websiteId = $this->data->getWebsiteId();
        $productId = $this->data->getProductId();

        $data = [
            'name' => $name,
            'email_id' => $email,
            'product_id' => $productId,
            'website_id' => $websiteId,
            'store_id'  => $storeId
        ];

        try {
            $dataModel = $this->guestSubscriptionDataFactory->setData($data);
            $this->guestSubscriptionRepository->save($dataModel);
            $this->messageManager->addSuccess(__('Form data saved successfully.'));
            return $resultRedirect->setPath('catalog/product/view', ['id' => $productId]);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while saving the subscription.'));
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Get Store Id
     *
     * @throws NoSuchEntityException
     */
    public function getStoreId(): int
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Get Website Id
     *
     * @throws NoSuchEntityException
     */
    public function getWebsiteId(): int
    {
        return $this->storeManager->getStore()->getWebsiteId();
    }

    /**
     * Get Product Id
     *
     * @return mixed|null
     */
    public function getProduct()
    {
        return $this->coreRegistry->registry('current_product');
    }

//    public function getProductId(): int
//    {
//        return $this->catalogHelper->getProduct()->getId();
//    }
}
