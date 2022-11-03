<?php
declare (strict_types = 1);

namespace HookahShisha\BlogGraphQl\Model\Resolver\DataProvider;

use Magefan\Blog\Api\CategoryRepositoryInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Widget\Model\Template\FilterEmulate;

class Category extends \Magefan\BlogGraphQl\Model\Resolver\DataProvider\Category
{
    /**
     * @var FilterEmulate
     */
    private $widgetFilter;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var State
     */
    protected $state;

    /**
     * @var DesignInterface
     */
    private $design;

    /**
     * @var ThemeProviderInterface
     */
    private $themeProvider;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Category constructor
     *
     * @param CategoryRepositoryInterface $categoryRepository
     * @param FilterEmulate $widgetFilter
     * @param State $state
     * @param DesignInterface $design
     * @param ThemeProviderInterface $themeProvider
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        FilterEmulate $widgetFilter,
        State $state,
        DesignInterface $design,
        ThemeProviderInterface $themeProvider,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->widgetFilter = $widgetFilter;
        $this->state = $state;
        $this->design = $design;
        $this->themeProvider = $themeProvider;
        $this->scopeConfig = $scopeConfig;
        parent::__construct(
            $categoryRepository,
            $widgetFilter,
            $state,
            $design,
            $themeProvider,
            $scopeConfig
        );
    }

    /**
     * GetData
     *
     * @param  string $categoryId
     * @param  mixed $fields
     * @param  mixed $storeId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getData(string $categoryId, $fields = null, $storeId = null): array
    {
        $category = $this->categoryRepository->getFactory()->create();
        $category->getResource()->load($category, $categoryId);

        if (!$category->isActive()) {
            throw new NoSuchEntityException();
        }

        $data = [];
        $this->state->emulateAreaCode(
            Area::AREA_FRONTEND,
            function () use ($category, $fields, &$data, $storeId) {
                $themeId = $this->scopeConfig->getValue(
                    'design/theme/theme_id',
                    ScopeInterface::SCOPE_STORE
                );
                $theme = $this->themeProvider->getThemeById($themeId);
                $this->design->setDesignTheme($theme, Area::AREA_FRONTEND);
                $category->setStoreId($storeId);
                $data = $this->getDynamicData($category, $fields);
                return $data;
            }
        );

        return $data;
    }
}
