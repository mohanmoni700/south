<?php
declare(strict_types=1);

namespace HookahShisha\Catalog\Plugin;

use Magento\Catalog\Controller\Adminhtml\Product\Save;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;

class ValidateRelatedProduct
{
    private ManagerInterface $messageManager;
    private RedirectFactory $redirectFactory;

    /**
     * @param ManagerInterface $messageManager
     * @param RedirectFactory $redirectFactory
     */
    public function __construct(
        ManagerInterface $messageManager,
        RedirectFactory $redirectFactory
    ) {
        $this->messageManager = $messageManager;
        $this->redirectFactory = $redirectFactory;
    }

    /**
     * Validate related product before save
     *
     * @param Save $subject
     * @param callable $proceed
     * @return Redirect
     */
    public function aroundExecute(Save $subject, callable $proceed)
    {
        $relatedProducts = $subject->getRequest()->getParam('links')['related'] ?? [];
        if (count($relatedProducts) > 3) {
            $this->messageManager
               ->addErrorMessage(
                   __('Cannot add more than 3 products for related product')
               );
            $resultRedirect = $this->redirectFactory->create();

            $productId = $subject->getRequest()->getParam('id');
            $productAttributeSetId = $subject->getRequest()->getParam('set');

            if ($productId) {
                $resultRedirect->setPath(
                    'catalog/*/edit',
                    ['id' => $productId, '_current' => true, 'set' => $productAttributeSetId]
                );
            } else {
                $productTypeId = $subject->getRequest()->getParam('type');
                $resultRedirect->setPath(
                    'catalog/*/new',
                    ['set' => $productAttributeSetId, 'type' => $productTypeId]
                );
            }
            return $resultRedirect;
        }
        return $proceed();
    }
}
