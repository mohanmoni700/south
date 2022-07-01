<?php
namespace Alfakher\AddtocartPriceHide\Block\Index;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

class CategoryBrand extends Template
{

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;

    /**
     * CategoryList constructor.
     *
     * @param Registry $registry
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param Context $context
     */
    public function __construct(
        Registry $registry,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        Context $context
    ) {
        $this->registry = $registry;
        $this->categoryRepository = $categoryRepository;
        $this->_filterProvider = $filterProvider;

        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function getCurrentCategory()
    {
        return $this->registry->registry('current_category');
    }
    /**
     * @param int $id
     * @param null $storeId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */

    /**
     * @inheritDoc
     */
    protected function getChildCategory($id, $storeId = null)
    {
        $categoryInstance = $this->categoryRepository->get($id, $storeId);

        return $categoryInstance;
    }

    /**
     * @inheritDoc
     */
    public function getContentFromStaticBlock($content)
    {
        return $this->_filterProvider->getBlockFilter()->filter($content);
    }
}
