<?php
declare(strict_types=1);

namespace Corra\Spreedly\Model;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\QuoteGraphQl\Model\Cart\Payment\AdditionalDataProviderInterface;
use Corra\Spreedly\Model\Ui\ConfigProvider;

/**
 * Spreedly payment method additional data provider
 */
class SpreedlyAdditionalDataProvider implements AdditionalDataProviderInterface
{
    private const PATH_ADDITIONAL_DATA = 'additional_data';
    /**
     * Format Spreedly input into value expected when setting payment method
     *
     * @param array $args
     * @return array
     * @throws GraphQlInputException
     */
    public function getData(array $args): array
    {
        if ($args['code'] === ConfigProvider::CODE && !isset($args[self::PATH_ADDITIONAL_DATA])) {
            throw new GraphQlInputException(
                __('Required parameter "additional_data" for "payment_method" (spreedly) is missing.')
            );
        }
        return $args[self::PATH_ADDITIONAL_DATA];
    }
}
