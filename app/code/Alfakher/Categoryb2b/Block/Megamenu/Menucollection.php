<?php

declare(strict_types=1);

namespace Alfakher\Categoryb2b\Block\Megamenu;

use Magento\Catalog\Helper\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\Data\Tree\Node\Collection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Menucollection extends Template
{
    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;
    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepository;
    /**
     * @var Category
     */
    private $categoryHelper;
    /**
     * @var FilterProvider
     */
    private $contentProcessor;

    /**
     * @param BlockRepositoryInterface $blockRepository
     * @param Context $context
     * @param Category $categoryHelper
     * @param CategoryFactory $categoryFactory
     * @param FilterProvider $contentProcessor
     * @param array $data
     */
    public function __construct(
        BlockRepositoryInterface $blockRepository,
        Context                  $context,
        Category                 $categoryHelper,
        CategoryFactory          $categoryFactory,
        FilterProvider           $contentProcessor,
        array                    $data = []
    ) {
        parent::__construct($context, $data);
        $this->blockRepository = $blockRepository;
        $this->categoryHelper = $categoryHelper;
        $this->categoryFactory = $categoryFactory;
        $this->contentProcessor = $contentProcessor;
    }

    /**
     * Get categories
     *
     * @param bool $sorted
     * @param bool $asCollection
     * @param bool $toLoad
     *
     * @return array|Collection
     */
    public function getMainStoreCategories($sorted = false, $asCollection = false, $toLoad = true)
    {
        return $this->categoryHelper->getStoreCategories($sorted, $asCollection, $toLoad);
    }

    /**
     * Get CMS content
     *
     * @param int|string $staticBlockId
     * @return string|null
     * @throws LocalizedException
     * @throws \Exception
     */
    public function getContent($staticBlockId): ?string
    {
        $block = $this->blockRepository->getById($staticBlockId);
        return $this->contentProcessor->getBlockFilter()->filter($block->getContent());
    }

    /**
     * Get category object by ID
     *
     * @param int|string $categoryId
     * @return mixed
     */
    public function getCategoryById($categoryId)
    {
        return $this->categoryFactory->create()->load($categoryId);
    }
}
