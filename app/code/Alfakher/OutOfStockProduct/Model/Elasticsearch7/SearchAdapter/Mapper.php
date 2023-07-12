<?php
/**
 * @category  Alfakher
 * @package   Alfakherd_OutOfStockProduct
 */
declare(strict_types=1);
namespace Alfakher\OutOfStockProduct\Model\Elasticsearch7\SearchAdapter;

use Magento\Elasticsearch7\SearchAdapter\Mapper as ElasticsearchMapper;

/** Plugin to move out of stock order to bottom of the page */
class Mapper
{
    /**
     * @param ElasticsearchMapper $subject
     * @param $query
     * @return array
     */
    public function afterBuildQuery(ElasticsearchMapper $subject, $query) //NOSONAR $subject is required
    {
        $sorts = $query['body']['sort'] ?? [];
        array_unshift($sorts, [
            'stock_status' => [
                'order' => 'desc'
            ]
        ]);
        $query['body']['sort'] = $sorts;
        return $query;
    }
}
