<?php

declare(strict_types=1);

namespace Alfakher\StockAlert\Controller\ProductAlert;

use Alfakher\StockAlert\Api\Data\ProductAlertStockGuestUserInterface;
use Alfakher\StockAlert\Api\ProductAlertStockGuestUserRepositoryInterface;
use Alfakher\StockAlert\Model\ProductAlertStockGuestUserRepository;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NoSuchEntityException;
use Alfakher\StockAlert\Helper\Data;
use Magento\Framework\Message\ManagerInterface;

class Save implements \Magento\Framework\App\Action\HttpPostActionInterface
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
     * @var Data
     */
    private Data $helper;

    /**
     * @var ManagerInterface
     */
    private ManagerInterface $messageManager;

    /**
     * @var ProductAlertStockGuestUserRepository
     */
    private ProductAlertStockGuestUserRepository $productAlertRepository;

    /**
     * Save Controller
     *
     * @param Validator $formKeyValidator
     * @param ManagerInterface $messageManager
     * @param ProductAlertStockGuestUserRepositoryInterface $guestSubscriptionRepository
     * @param ProductAlertStockGuestUserInterface $guestSubscriptionDataFactory
     * @param ProductAlertStockGuestUserRepository $productAlertRepository
     * @param Data $helper
     */
    public function __construct(
        Validator                                     $formKeyValidator,
        ManagerInterface                              $messageManager,
        ProductAlertStockGuestUserRepositoryInterface $guestSubscriptionRepository,
        ProductAlertStockGuestUserInterface           $guestSubscriptionDataFactory,
        ProductAlertStockGuestUserRepository          $productAlertRepository,
        Data                                          $helper
    ) {
        $this->formKeyValidator = $formKeyValidator;
        $this->messageManager = $messageManager;
        $this->guestSubscriptionDataFactory = $guestSubscriptionDataFactory;
        $this->guestSubscriptionRepository = $guestSubscriptionRepository;
        $this->productAlertRepository = $productAlertRepository;
        $this->helper = $helper;
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
            $this->messageManager->addErrorMessage(__('Invalid form key. Please try again.'));
            return $resultRedirect->setPath('*/*/');
        }

        $name = $this->getRequest()->getPost('name');
        $email = $this->getRequest()->getPost('email');
        $storeId = $this->helper->getStoreId();
        $websiteId = $this->helper->getWebsiteId();
        $productId = $this->helper->getProductId();

        $data = [
            'name' => $name,
            'email_id' => $email,
            'product_id' => $productId,
            'website_id' => $websiteId,
            'store_id'  => $storeId,
            'status' => 1
        ];
        try {
            $productAlert = $this->productAlertRepository->get($email, $name, $productId);
            if ($productAlert !== null) {
                $this->messageManager->addSuccessMessage(__('You are already subscribed to alerts for this product.'));
            } else {
                $dataModel = $this->guestSubscriptionDataFactory->setData($data);
                $this->guestSubscriptionRepository->save($dataModel);
                $this->messageManager->addSuccessMessage(__('Alert subscription has been saved.'));
            }
            return $resultRedirect->setPath('catalog/product/view', ['id' => $productId]);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while saving the subscription.'));
        }
        return $resultRedirect->setPath('*/*/');
    }
}
