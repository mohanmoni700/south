<?php
    namespace Alfakher\Categoryb2b\Block\Megamenu;

    class Menucollection extends \Magento\Framework\View\Element\Template{
        protected $categoryCollection;
        protected $storeManager;
        protected $categoryFactory;

        public function __construct(
            \Magento\Cms\Api\BlockRepositoryInterface $blockRepository,
            \Magento\Framework\View\Element\Template\Context $context,
            \Magento\Catalog\Helper\Category $categoryHelper,
            \Magento\Catalog\Model\CategoryFactory $categoryFactory,
            array $data = []
        ) {
            $this->blockRepository = $blockRepository;
            $this->_categoryHelper = $categoryHelper;
            $this->categoryFactory = $categoryFactory;
            parent::__construct($context, $data);
        }
        public function getMainStoreCategories($sorted = false, $asCollection = false, $toLoad = true)
        {
            return $this->_categoryHelper->getStoreCategories($sorted , $asCollection, $toLoad);
        }
        public function getContent($staticBlockId)
        {
            $block = $this->blockRepository->getById($staticBlockId);
            return $block->getContent();
        }

        public function getCategoryById($categoryId)
        {
            return $this->categoryFactory->create()->load($categoryId);
        }

    }
