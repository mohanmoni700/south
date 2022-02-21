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
     * CategoryList constructor.
     *
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param Context $context
     * @param Registry $registry

     */
    public function __construct(
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        Context $context,
        Registry $registry
    ) {
        $this->registry = $registry;
        $this->categoryRepository = $categoryRepository;

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
}
