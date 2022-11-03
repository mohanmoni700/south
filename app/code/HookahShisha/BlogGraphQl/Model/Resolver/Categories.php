<?php
declare (strict_types = 1);

namespace HookahShisha\BlogGraphQl\Model\Resolver;

use Magefan\BlogGraphQl\Model\Resolver\DataProvider\Category;
use Magefan\Blog\Api\CategoryRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder as SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Categories extends \Magefan\BlogGraphQl\Model\Resolver\Categories
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepositoryInterface;

    /**
     * @var DataProvider\Category
     */
    protected $categoryDataProvider;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var FilterGroupBuilder
     */
    protected $filterGroupBuilder;

    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * Categories constructor.
     *
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CategoryRepositoryInterface $categoryRepositoryInterface
     * @param DataProvider\Category $categoryDataProvider
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param ScopeResolverInterface $scopeResolver
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CategoryRepositoryInterface $categoryRepositoryInterface,
        Category $categoryDataProvider,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        ScopeResolverInterface $scopeResolver
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->categoryRepositoryInterface = $categoryRepositoryInterface;
        $this->categoryDataProvider = $categoryDataProvider;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->scopeResolver = $scopeResolver;
        parent::__construct(
            $searchCriteriaBuilder,
            $categoryRepositoryInterface,
            $categoryDataProvider,
            $filterBuilder,
            $filterGroupBuilder,
            $scopeResolver
        );
    }

    /**
     * Resolve
     *
     * @param Field $field
     * @param mixed $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $searchCriteria = $this->searchCriteriaBuilder->build('magefan_blog_categories', $args);
        $storeId = (int) $context->getExtensionAttributes()->getStore()->getId();
        $statusFilter = $this->filterBuilder
            ->setField('is_active')
            ->setValue(1)
            ->setConditionType('eq')
            ->create();

        $filterGroups = $searchCriteria->getFilterGroups();
        $filterGroups[] = $this->filterGroupBuilder->addFilter($statusFilter)->create();

        $scope = $this->scopeResolver->getScope()->getId();

        $scopeFilter = $this->filterBuilder
            ->setField('store_id')
            ->setValue($scope)
            ->setConditionType('eq')
            ->create();
        $filterGroups[] = $this->filterGroupBuilder->addFilter($scopeFilter)->create();

        $searchCriteria->setFilterGroups($filterGroups);

        $searchResult = $this->categoryRepositoryInterface->getList($searchCriteria);
        $items = $searchResult->getItems();
        $fields = $info ? $info->getFieldSelection(10) : null;

        foreach ($items as $k => $data) {
            $items[$k] = $this->categoryDataProvider->getData(
                $data['category_id'],
                isset($fields['items']) ? $fields['items'] : null,
                $storeId
            );
        }

        return [
            'total_count' => $searchResult->getTotalCount(),
            'items' => $items,
        ];
    }
}
