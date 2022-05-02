<?php
declare(strict_types=1);

namespace HookahShisha\SuperPack\Plugin\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class StockStatusProvider
{
    /**
     * @inheritdoc
     */
    public function afterResolve(
        $subject, //NOSONAR
        $return,
        Field $field, //NOSONAR
        $context, //NOSONAR
        ResolveInfo $info, //NOSONAR
        array $value = null
    ) {
        if (isset($value['model']['super_pack_status'])) {
            return 'OUT_OF_STOCK';
        }
        return $return;
    }
}