<?php
namespace Alfakher\Productpageb2b\Block\Product;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Categoryview extends Template
{

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    public $categoryRepository;

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
    public function getChildCategory($id, $storeId = null)
    {
        $categoryInstance = $this->categoryRepository->get($id, $storeId);

        return $categoryInstance;
    }
}
