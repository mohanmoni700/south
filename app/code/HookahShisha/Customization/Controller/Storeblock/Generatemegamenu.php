<?php

namespace HookahShisha\Customization\Controller\Storeblock;

/**
 *
 */
class Generatemegamenu extends \Magento\Framework\App\Action\Action
{

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);

        $this->_storeManager = $storeManager;
        $this->_blockFactory = $blockFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_pageFactory = $resultPageFactory;
    }

    public function execute()
    {

        $error = 0;
        $errorMessage = 'Block Created Successfully!';

        try {
            $resultPage = $this->_pageFactory->create();
            $content = $resultPage->getLayout()->createBlock('Smartwave\Megamenu\Block\Topmenu')->setTemplate('Smartwave_Megamenu::topmenu.phtml')->toHtml();

            $updateBlock = $this->_blockFactory->create();
            $updateBlock->setStoreId($this->_storeManager->getStore()->getId())->load('megamenu-desktop', 'identifier');

            if ($updateBlock->getId()) {
                $updateBlock->setContent($content);
                $updateBlock->save();
            } else {
                $newCmsStaticBlock = [
                    'title' => 'Megamenu Desktop',
                    'identifier' => 'megamenu-desktop',
                    'content' => $content,
                    'is_active' => 1,
                    'stores' => [$this->_storeManager->getStore()->getId()],
                ];
                $newBlock = $this->_blockFactory->create();
                $newBlock->setData($newCmsStaticBlock)->save();
            }
        } catch (\Exception $e) {
            $error = 1;
            $errorMessage = $e->getMessage();
        }

        $result = $this->_resultJsonFactory->create();
        $result->setData(['error' => $error, 'message' => $errorMessage]);
        return $result;
    }
}
