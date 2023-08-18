<?php
declare(strict_types=1);

namespace Alfakher\Blog\Model\Resolver;

use Magefan\Blog\Model\ResourceModel\Post\Collection;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magefan\BlogGraphQl\Model\Resolver\DataProvider\Post as PostDataProvider;

/**
 * Get individual posts
 */
class Post implements ResolverInterface
{
    /**
     * @var Collection
     */
    private Collection $postCollection;
    /**
     * @var PostDataProvider
     */
    private PostDataProvider $post;

    /**
     * Post constructor.
     * @param Collection $postCollection
     * @param PostDataProvider $post
     */
    public function __construct(
        Collection $postCollection,
        PostDataProvider $post
    ) {
        $this->postCollection = $postCollection;
        $this->post = $post;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $postId = $this->getPostId($args);
        $fields = $info ? $info->getFieldSelection(10) : null;
        $postCollection= $this->postCollection->join(
            'magefan_blog_post_store',
            'main_table.post_id = magefan_blog_post_store.post_id'
        )->addFilter('identifier', $postId)->addFilter('store_id', $storeId)->getFirstItem();

        if (!$postCollection->isActive()) {
            throw new GraphQlNoSuchEntityException(__('The blog is either deactivated or unable to be retrieved.'));
        }
        try {
            $postData =  $this->post->getDynamicData($postCollection, $fields);
        } catch (\Exception $e) {
            throw new GraphQlNoSuchEntityException(__('Something went wrong'));
        }

        return  $postData;
    }

    /**
     * Check is postID exist
     *
     * @param array $args
     * @return string
     * @throws GraphQlInputException
     */
    private function getPostId(array $args): string
    {
        if (!isset($args['id'])) {
            throw new GraphQlInputException(__('Post id should be specified'));
        }

        return (string)$args['id'];
    }
}
