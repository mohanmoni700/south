<?php

declare(strict_types=1);

namespace HookahShisha\Customization\Controller\Storeblock;

use Magento\Cms\Model\BlockFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;

class Generatemegamenu extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var BlockFactory
     */
    protected $blockFactory;

    /**
     * @var $resultJsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var $resultPageFactory
     */
    protected $resultPageFactory;

    /**
     * Constructor for Generatemegamenu
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param BlockFactory $blockFactory
     * @param JsonFactory $resultJsonFactory
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        BlockFactory $blockFactory,
        JsonFactory $resultJsonFactory,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_blockFactory = $blockFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_pageFactory = $resultPageFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {

        $error = 0;
        $errorMessage = 'Block Created Successfully!';

        try {
            $resultPage = $this->_pageFactory->create();
            $content = $resultPage->getLayout()
                ->createBlock(\Smartwave\Megamenu\Block\Topmenu::class)
                ->setTemplate('Smartwave_Megamenu::topmenu.phtml')->toHtml();

            $updateBlock = $this->_blockFactory->create();
            $updateBlock->setStoreId($this->_storeManager->getStore()->getId())->load('megamenu-desktop', 'identifier');

            if ($updateBlock->getId()) {
                $updateBlock->setContent('
                    <div data-content-type="html" data-appearance="default" data-element="main">' .
                    htmlentities($content) .
                    '</div>');
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
