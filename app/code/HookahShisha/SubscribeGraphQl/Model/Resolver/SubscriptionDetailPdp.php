<?php
declare(strict_types=1);

namespace HookahShisha\SubscribeGraphQl\Model\Resolver;

use Magedelight\Subscribenow\Model\Source\DiscountType;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magedelight\Subscribenow\Block\Catalog\Product\View\Subscription\BillingPeriod;
use Magento\Catalog\Model\Product;
use Magento\Bundle\Model\Product\Type;
class SubscriptionDetailPdp implements ResolverInterface
{
    /**
     * @var FilterBuilder
     */
    protected FilterBuilder $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    protected SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var BillingPeriod
     */
    private BillingPeriod $billingPeriod;

    private Type $type;

    /**
     *
     * @param ProductRepositoryInterface $productRepository
     * @param BillingPeriod $billingPeriod
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        BillingPeriod $billingPeriod,
        FilterBuilder $filterBuilder,
        Type $type,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->productRepository = $productRepository;
        $this->billingPeriod = $billingPeriod;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->type = $type;
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
        if (!isset($args['url_key']) || !$args['url_key']) {
            throw new GraphQlInputException(
                __(
                    'url_key is not specified'
                )
            );
        }

        $filter = [
            $this->filterBuilder->setField('url_key')
                ->setValue($args['url_key'])
                ->setConditionType('eq')
                ->create(),
        ];

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilters($filter)
            ->setCurrentPage(1)
            ->setPageSize(1)
            ->create();

        $products = $this->productRepository
            ->getList($searchCriteria)
            ->getItems();

        $finalProduct = null;
        foreach ($products as $product) {
            $finalProduct = $product;
        }

        if (isset($finalProduct)) {

            //Number of child product with discount amount
            $discountAmount = $this->getDiscountAmountByType($finalProduct);

            $billingPeriod = $this->getBillingPeriod($finalProduct);
            return [
                "is_subscription" => $finalProduct->getIsSubscription(),
                "subscription_type" => $finalProduct->getSubscriptionType(),
                "discount_type" => $finalProduct->getDiscountType(),
                "discount_amount" => $discountAmount,
                "initial_amount" => $finalProduct->getInitialAmount(),
                "billing_period_type" => $finalProduct->getBillingPeriodType(),
                "billing_max_cycles" => $finalProduct->getBillingMaxCycles(),
                "define_start_from" => $finalProduct->getDefineStartFrom(),
                "day_of_month" => $finalProduct->getDayOfMonth(),
                "allow_update_date" => $finalProduct->getAllowUpdateDate(),
                "allow_trial" => $finalProduct->getAllowTrial(),
                "trial_period" => $finalProduct->getTrialPeriod(),
                "trial_amount" => $finalProduct->getTrialAmount(),
                "trial_maxcycle" => $finalProduct->getTrialMaxcycle(),
                "allow_subscription_end_date" => $finalProduct->getAllowSubscriptionEndDate(),
                "billing_period" => $billingPeriod
            ];
        }
    }

    /**
     * Fetch billing periods
     *
     * @param Product $product
     * @return array
     */
    protected function getBillingPeriod($product)
    {
        $billingPeriodData = [];
        if ($product->getBillingPeriodType() == 'customer') {
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

    /**
     * @param $product
     * @return float|int
     */
    public function getDiscountAmountByType($product)
    {
        if ($product->getDiscountType() == DiscountType::FIXED) {
            $options = $this->type->getOptionsCollection($product);
            return $options->count() * $product->getDiscountAmount();
        } else {
            return $product->getDiscountAmount();
        }
    }
}
