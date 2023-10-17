<?php
declare(strict_types=1);

namespace Corra\CmsSitemapGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * This module is to retrieve the CMS page content for sitemap in pylot projects
 */
class SitemapCmsPages implements ResolverInterface
{
    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * SitemapCmsPages constructor.
     * @param PageRepositoryInterface $pageRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        PageRepositoryInterface $pageRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->pageRepository = $pageRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field                 $field,
        $context,
        ResolveInfo           $info,
        array                 $value = null,
        array                 $args = null
    ): array {
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('is_active', 1)
            ->addFilter('add_in_sitemap', 1)
            ->addFilter('store_id', $storeId)
            ->create();
        $list = $this->pageRepository->getList($searchCriteria);
        $data = [];
        foreach ($list->getItems() as $item) {
            $data[] = [
                'url_key'   => $item->getIdentifier(),
                'title'     => $item->getTitle(),
                'updated_at' => $item->getUpdateTime()
            ];
        }
        return $data;
    }
}
