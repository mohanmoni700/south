<?php
declare(strict_types=1);

namespace HookahShisha\SubscribeGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magedelight\Subscribenow\Block\Catalog\Product\View\Subscription\BillingPeriod;
use Magedelight\Subscribenow\Model\Service\SubscriptionService;
use Magedelight\Subscribenow\Block\Catalog\Product\View\Subscription;
use Magento\Catalog\Model\ProductRepository;

/**
 * Class to resolve custom attribute_set_name field in product GraphQL query
 */
class BillingPeriodDataResolver implements ResolverInterface
{
    /**
     * @var BillingPeriod
     */
    private $billingPeriod;

    /**
     * @var Subscription
     */
    private $subscription;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @param BillingPeriod $billingPeriod
     * @param Subscription $subscription
     * @param ProductRepository $productRepository
     */
    public function __construct(
        BillingPeriod $billingPeriod,
        Subscription $subscription,
        ProductRepository $productRepository
    ) {
        $this->billingPeriod = $billingPeriod;
        $this->subscription = $subscription;
        $this->productRepository = $productRepository;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $billingPeriodData = [];
        $product = $this->productRepository->getById($value['entity_id']);
        $billingPeriodType = $product->getBillingPeriodType();
        if ($billingPeriodType == 'customer') {
            foreach ($this->billingPeriod->getBillingPeriods() as $period => $periodLabel) {
                $billingPeriodData[$period]['value'] = $period;
                $billingPeriodData[$period]['label'] = $periodLabel;
            }
        } else {
            $billingPeriodData[$product->getBillingPeriod()]['value'] = $product->getBillingPeriod();
            $billingPeriodData[$product->getBillingPeriod()]['label'] = $product->getAttributeText('billing_period');
        }
        return $billingPeriodData;
    }
}
