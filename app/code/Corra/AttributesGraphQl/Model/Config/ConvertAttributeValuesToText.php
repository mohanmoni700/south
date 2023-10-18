<?php
/**
 * @author CORRA
 */
namespace Corra\AttributesGraphQl\Model\Config;

use Corra\AttributesGraphQl\Model\Resolver\PageBuilderAttributes;
use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\GraphQl\Schema\Type\Entity\MapperInterface;
use Magento\EavGraphQl\Model\Resolver\Query\Type;
use Magento\CatalogGraphQl\Model\Resolver\Products\Attributes\Collection;
use Corra\AttributesGraphQl\Model\Resolver\Attributes;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\Product;

/**
 * Adds dropdown type attributes text values as a new graphql field into existing schema
 */
class ConvertAttributeValuesToText implements ReaderInterface
{
    const TEXTRESOLVER = Attributes::class;
    /**
     * @var MapperInterface
     */
    private $mapper;

    /**
     * @var Type
     */
    private $typeLocator;

    /**
     * @var Collection
     */
    private $collection;

    /**
     * @param MapperInterface $mapper
     * @param Type $typeLocator
     * @param Collection $collection
     */
    public function __construct(
        MapperInterface $mapper,
        Type $typeLocator,
        Collection $collection
    ) {
        $this->mapper = $mapper;
        $this->typeLocator = $typeLocator;
        $this->collection = $collection;
    }

    /**
     * Read configuration scope
     * @param null $scope
     * @return array
     */
    public function read($scope = null) : array
    {
        $typeNames = $this->mapper->getMappedTypes(Product::ENTITY);
        $config =[];
        /** @var Attribute $attribute */
        foreach ($this->collection->getAttributes() as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            foreach ($typeNames as $typeName) {
                if ($attribute->getBackendType()=='int') { // attributes is either Select or Boolean
                    $config[$typeName]['fields'][$attributeCode.'_text'] = [
                        'name' => $attributeCode .'_text',
                        'type' => 'String',
                        'arguments' => [],
                        'resolver' => self::TEXTRESOLVER
                    ];
                }
                if ($attribute->getBackendType()=='text') { // page builder attribute from filter template to html
                    $config[$typeName]['fields'][$attributeCode] = [
                        'name' => $attributeCode,
                        'type' => 'String',
                        'arguments' => [],
                        'resolver' => PageBuilderAttributes::class
                    ];
                }
            }
        }

        return $config;
    }
}
