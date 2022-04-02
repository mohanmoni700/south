<?php
namespace Alfakher\SearchOverride\SearchAdapter\Query\Builder;

use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeProvider;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ResolverInterface as TypeResolver;
use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\SearchAdapter\Query\ValueTransformerPool;

class Match extends \Magento\Elasticsearch\SearchAdapter\Query\Builder\Match
{

    const QUERY_CONDITION_MUST_NOT = 'must_not';

    /**
     * @var FieldMapperInterface
     */
    private $fieldMapper;

    /**
     * @var AttributeProvider
     */
    private $attributeProvider;

    /**
     * @var TypeResolver
     */
    private $fieldTypeResolver;

    /**
     * @var ValueTransformerPool
     */
    private $valueTransformerPool;
    /**
     * @var Config
     */
    private $config;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param FieldMapperInterface $fieldMapper
     * @param AttributeProvider $attributeProvider
     * @param TypeResolver $fieldTypeResolver
     * @param ValueTransformerPool $valueTransformerPool
     * @param Config $config
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        FieldMapperInterface $fieldMapper,
        AttributeProvider $attributeProvider,
        TypeResolver $fieldTypeResolver,
        ValueTransformerPool $valueTransformerPool,
        Config $config
    ) {
        $this->fieldMapper = $fieldMapper;
        $this->attributeProvider = $attributeProvider;
        $this->fieldTypeResolver = $fieldTypeResolver;
        $this->valueTransformerPool = $valueTransformerPool;
        $this->config = $config;
        $this->storeManager = $storeManager;
        parent::__construct($fieldMapper, $attributeProvider, $fieldTypeResolver, $valueTransformerPool, $config);
    }

    /**
     * Override search for the b2b website
     *
     * @param array $matches
     * @param array $queryValue
     * @return array
     */
    protected function buildQueries(array $matches, array $queryValue)
    {
        $conditions = [];
        $count = 0;
        $value = preg_replace('#^"(.*)"$#m', '$1', $queryValue['value'], -1, $count);
        $condition = ($count) ? 'match_phrase' : 'match';
        $storeCode = $this->storeManager->getStore()->getCode();

        $transformedTypes = [];
        foreach ($matches as $match) {
            $resolvedField = $this->fieldMapper->getFieldName(
                $match['field'],
                ['type' => FieldMapperInterface::TYPE_QUERY]
            );

            $attributeAdapter = $this->attributeProvider->getByAttributeCode($resolvedField);
            $fieldType = $this->fieldTypeResolver->getFieldType($attributeAdapter);
            $valueTransformer = $this->valueTransformerPool->get($fieldType ?? 'text');
            $valueTransformerHash = \spl_object_hash($valueTransformer);
            if (!isset($transformedTypes[$valueTransformerHash])) {
                $transformedTypes[$valueTransformerHash] = $valueTransformer->transform($value);
            }
            $transformedValue = $transformedTypes[$valueTransformerHash];
            if (null === $transformedValue) {
                continue;
            }

            $matchCondition = $match['matchCondition'] ?? $condition;

            if ($storeCode == 'hookah_wholesalers_store_view') {
                if ($matchCondition != 'match_phrase_prefix') {
                    $field = [
                        'query' => $transformedValue,
                        'boost' => $match['boost'] ?? 1,
                        'operator' => 'and',
                    ];
                } else {
                    $field = [
                        'query' => $transformedValue,
                        'boost' => $match['boost'] ?? 1,
                    ];
                }
                $conditions[] = [
                    'condition' => $queryValue['condition'],
                    'body' => [
                        $matchCondition => [
                            $resolvedField => $field,
                        ],
                    ],
                ];
            } else {
                $fields = [];
                $fields[$resolvedField] = [
                    'query' => $transformedValue,
                    'boost' => $match['boost'] ?? 1,
                ];
                if (isset($match['analyzer'])) {
                    $fields[$resolvedField]['analyzer'] = $match['analyzer'];
                }
                $conditions[] = [
                    'condition' => $queryValue['condition'],
                    'body' => [
                        $matchCondition => $fields,
                    ],
                ];
            }
        }
        return $conditions;
    }
}
