<?php

declare(strict_types=1);

namespace Alfakher\StockAlert\Controller\ProductAlert;

use Alfakher\StockAlert\Api\Data\ProductAlertStockGuestUserInterface;
use Alfakher\StockAlert\Api\ProductAlertStockGuestUserRepositoryInterface;
use Alfakher\StockAlert\Model\ProductAlertStockGuestUserFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NoSuchEntityException;
use Alfakher\StockAlert\Helper\Data;

class Save extends Action
{
    /**
     * @var Validator
     */
    protected Validator $formKeyValidator;

    /**
     * @var ProductAlertStockGuestUserRepositoryInterface
     */
    private ProductAlertStockGuestUserRepositoryInterface $guestSubscriptionRepository;

    /**
     * @var ProductAlertStockGuestUserInterface
     */
    private ProductAlertStockGuestUserInterface $guestSubscriptionDataFactory;

    /**
     * @var ProductAlertStockGuestUserFactory
     */
    private ProductAlertStockGuestUserFactory $productAlertStockGuestUserFactory;

    /**
     * @var Data
     */
    private Data $data;

    /**
     * Save Controller
     *
     * @param Context $context
     * @param Validator $formKeyValidator
     * @param ProductAlertStockGuestUserRepositoryInterface $guestSubscriptionRepository
     * @param ProductAlertStockGuestUserInterface $guestSubscriptionDataFactory
     * @param ProductAlertStockGuestUserFactory $productAlertStockGuestUserFactory
     * @param Data $data
     */
    public function __construct(
        Context $context,
        Validator $formKeyValidator,
        ProductAlertStockGuestUserRepositoryInterface $guestSubscriptionRepository,
        ProductAlertStockGuestUserInterface $guestSubscriptionDataFactory,
        ProductAlertStockGuestUserFactory $productAlertStockGuestUserFactory,
        Data $data
    ) {
        parent::__construct($context);
        $this->formKeyValidator = $formKeyValidator;
        $this->guestSubscriptionDataFactory = $guestSubscriptionDataFactory;
        $this->guestSubscriptionRepository = $guestSubscriptionRepository;
        $this->productAlertStockGuestUserFactory = $productAlertStockGuestUserFactory;
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
            'store_id'  => $storeId,
            'status' => 1
        ];
        try {
            $guestSubscriptionModel = $this->productAlertStockGuestUserFactory->create();
            $collection = $guestSubscriptionModel->getCollection();
            $collection->addFieldToFilter('name', $name)
                ->addFieldToFilter('email_id', $email)
                ->addFieldToFilter('product_id', $productId);
            if ($collection->getSize() > 0) {
                $this->messageManager->addSuccess(__('You are already subscribed to alerts for this product.'));
            } else {
                $dataModel = $this->guestSubscriptionDataFactory->setData($data);
                $this->guestSubscriptionRepository->save($dataModel);
                $this->messageManager->addSuccess(__('Alert subscription has been saved.'));
            }
            return $resultRedirect->setPath('catalog/product/view', ['id' => $productId]);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while saving the subscription.'));
        }
        return $resultRedirect->setPath('*/*/');
    }
}
