<?php

namespace HookahShisha\Magefan\Model\Resolver\DataProvider;

use Magefan\BlogGraphQl\Model\Resolver\DataProvider\Author;
use Magefan\BlogGraphQl\Model\Resolver\DataProvider\Category;
use Magefan\BlogGraphQl\Model\Resolver\DataProvider\Tag;
use Magefan\Blog\Api\PostRepositoryInterface;
use Magefan\Blog\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Widget\Model\Template\FilterEmulate;

/**
 * Blog Post model
 */
class Post extends \Magefan\BlogGraphQl\Model\Resolver\DataProvider\Post
{

    /**
     * Post constructor.
     * @param PostRepositoryInterface $postRepository
     * @param FilterEmulate $widgetFilter
     * @param Tag $tagDataProvider
     * @param Category $categoryDataProvider
     * @param Author $authorDataProvider
     * @param State $state
     * @param DesignInterface $design
     * @param ThemeProviderInterface $themeProvider
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        PostRepositoryInterface $postRepository,
        FilterEmulate $widgetFilter,
        Tag $tagDataProvider,
        Category $categoryDataProvider,
        Author $authorDataProvider,
        State $state,
        DesignInterface $design,
        ThemeProviderInterface $themeProvider,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->postRepository = $postRepository;
        $this->widgetFilter = $widgetFilter;
        $this->tagDataProvider = $tagDataProvider;
        $this->categoryDataProvider = $categoryDataProvider;
        $this->authorDataProvider = $authorDataProvider;
        $this->state = $state;
        $this->design = $design;
        $this->themeProvider = $themeProvider;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($postRepository, $widgetFilter, $tagDataProvider, $categoryDataProvider, $authorDataProvider, $state, $design, $themeProvider, $scopeConfig); //phpcs:ignore
    }

    /**
     * Prepare all additional data
     *
     * @param object $post
     * @param null|array $fields
     * @return array
     */
    public function getDynamicData($post, $fields = null)
    {
        $data = $post->getData();

        $keys = [
            'og_image',
            'og_type',
            'og_description',
            'og_title',
            'meta_description',
            'meta_title',
            'short_filtered_content',
            'filtered_content',
            'first_image',
            'featured_image',
            'featured_list_image',
            'post_url',
        ];

        foreach ($keys as $key) {
            if (null === $fields || array_key_exists($key, $fields)) {
                $method = 'get' . str_replace(
                    '_',
                    '',
                    ucwords($key, '_')
                );
                $data[$key] = $post->$method();
            }
        }

        if (null === $fields || array_key_exists('tags', $fields)) {
            $tags = [];
            foreach ($post->getRelatedTags() as $tag) {
                $tags[] = $this->tagDataProvider->getDynamicData(
                    $tag
                    // isset($fields['tags']) ? $fields['tags'] : null
                );
            }
            $data['tags'] = $tags;
        }

        /* Do not use check for null === $fields here
         * this checks is used for REST, and related data was not provided via reset */
        if (is_array($fields) && array_key_exists('related_posts', $fields)) {
            $relatedPosts = [];

            $isEnabled = $this->scopeConfig->getValue(
                Config::XML_RELATED_POSTS_ENABLED,
                ScopeInterface::SCOPE_STORE
            );

            if ($isEnabled) {
                $pageSize = (int) $this->scopeConfig->getValue(
                    Config::XML_RELATED_POSTS_NUMBER,
                    ScopeInterface::SCOPE_STORE
                );

                $postCollection = $post->getRelatedPosts()
                    ->addActiveFilter()
                    ->setPageSize($pageSize ?: 5);
                foreach ($postCollection as $relatedPost) {
                    $relatedPosts[] = $this->getDynamicData(
                        $relatedPost,
                        isset($fields['related_posts']) ? $fields['related_posts'] : null
                    );
                }
            }

            $data['related_posts'] = $relatedPosts;
        }

        /* Do not use check for null === $fields here */
        if (is_array($fields) && array_key_exists('related_products', $fields)) {
            $relatedProducts = [];

            $isEnabled = $this->scopeConfig->getValue(
                Config::XML_RELATED_PRODUCTS_ENABLED,
                ScopeInterface::SCOPE_STORE
            );

            if ($isEnabled) {
                $pageSize = (int) $this->scopeConfig->getValue(
                    Config::XML_RELATED_PRODUCTS_NUMBER,
                    ScopeInterface::SCOPE_STORE
                );

                $productCollection = $post->getRelatedProducts()
                    ->setPageSize($pageSize ?: 5);
                foreach ($productCollection as $relatedProduct) {
                    $relatedProducts[] = $relatedProduct->getSku();
                }
            }
            $data['related_products'] = $relatedProducts;
        }

        if (null === $fields || array_key_exists('categories', $fields)) {
            $categories = [];
            foreach ($post->getParentCategories() as $category) {
                $categories[] = $this->categoryDataProvider->getDynamicData(
                    $category,
                    isset($fields['categories']) ? $fields['categories'] : null
                );
            }
            $data['categories'] = $categories;
        }

        if (null === $fields || array_key_exists('author', $fields)) {
            if ($author = $post->getAuthor()) {
                $data['author'] = $this->authorDataProvider->getDynamicData(
                    $author
                    //isset($fields['author']) ? $fields['author'] : null
                );
            }
        }

        if (is_array($fields) && array_key_exists('canonical_url', $fields)) {
            $data['canonical_url'] = $post->getCanonicalUrl();
        }

        return $data;
    }
}
