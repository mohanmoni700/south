<?php
declare(strict_types=1);

namespace Alfakher\Payment\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Resolver\PlaceOrder as ParentPlaceOrder;
use Magento\Store\Model\StoreManagerInterface;

class PlaceOrder
{
    const DEFAULT_STORE_CODE = 'default';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Constructor
     *
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Magento\QuoteGraphQl\Model\Resolver\PlaceOrder $subject
     * @param \Closure $proceed
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return void
     * @throws GraphQlInputException
     */
    public function aroundResolve(
        ParentPlaceOrder $subject,
        \Closure $proceed,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        try {
            $result = $proceed($field, $context, $info, $value, $args);
        } catch (LocalizedException $e) {
            $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();

            $storeData = $this->storeManager->getStore($storeId);
            $storeCode = (string)$storeData->getCode();

            if (
                $storeCode == self::DEFAULT_STORE_CODE &&
                $e->getMessage() == 'Unable to place order: Transaction has been declined. Please try again later.'
            ) {
                throw new GraphQlInputException(__('Your credit card has been redacted. Please re-enter your credit card details, click “Add a new card”.'));
            } else {
                throw new GraphQlInputException(__('%message', ['message' => $e->getMessage()]), $e);
            }
        }

        return $result;
    }
}
