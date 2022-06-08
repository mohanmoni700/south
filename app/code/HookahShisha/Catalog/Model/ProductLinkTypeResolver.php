<?php

namespace HookahShisha\Catalog\Model;

use Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;

class ProductLinkTypeResolver implements TypeResolverInterface
{
    /**
     * @var string[]
     */
    private $linkTypes = ['charcoal', 'shisha'];

    /**
     * @inheritdoc
     */
    public function resolveType(array $data): string
    {
        if (isset($data['link_type'])) {
            $linkType = $data['link_type'];
            if (in_array($linkType, $this->linkTypes)) {
                return 'ProductLinks';
            }
        }
        return '';
    }
}
