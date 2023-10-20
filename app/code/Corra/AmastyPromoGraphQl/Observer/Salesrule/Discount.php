<?php

namespace Corra\AmastyPromoGraphQl\Observer\Salesrule;

use Amasty\Promo\Model\Config;
use Amasty\Promo\Model\DiscountCalculator;
use Amasty\Promo\Model\RuleResolver;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Item;
use Magento\SalesRule\Model\Rule\Action\Discount\Data;
use Psr\Log\LoggerInterface;
use Amasty\Promo\Helper\Item as AmastyItem;

class Discount extends \Amasty\Promo\Observer\Salesrule\Discount
{
    /**
     * @var DiscountCalculator
     */
    private $discountCalculator;

    /**
     * @var RuleResolver
     */
    private $ruleResolver;

    /**
     * @var State
     */
    private $state;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param \Amasty\Promo\Helper\Item $promoItemHelper
     * @param ProductRepositoryInterface $productRepository
     * @param DiscountCalculator $discountCalculator
     * @param RuleResolver $ruleResolver
     * @param State $state
     * @param LoggerInterface $logger
     * @param Config $config
     */
    public function __construct(
        AmastyItem $promoItemHelper,
        ProductRepositoryInterface $productRepository,
        DiscountCalculator $discountCalculator,
        RuleResolver $ruleResolver,
        State $state,
        LoggerInterface $logger,
        Config $config
    ) {
        parent::__construct(
            $promoItemHelper,
            $productRepository,
            $discountCalculator,
            $ruleResolver,
            $state,
            $logger,
            $config
        );
        $this->discountCalculator = $discountCalculator;
        $this->ruleResolver = $ruleResolver;
        $this->state = $state;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     *
     * @return Data|void
     */
    public function execute(Observer $observer)
    {
        try {
            /** @var Item $item */
            $item = $observer->getItem();
            /** @var \Magento\SalesRule\Model\Rule $rule */
            $rule = $observer->getRule();

            if ($this->checkItemForPromo($rule, $item)) {
                /** @var Data $result */
                $result = $observer->getResult();
                if (!$item->getAmDiscountAmount()) {
                    $baseDiscount = $this->discountCalculator->getBaseDiscountAmount($observer->getRule(), $item);
                    $discount = $this->discountCalculator->getDiscountAmount($observer->getRule(), $item);

                    $result->setBaseAmount($baseDiscount);
                    $result->setAmount($discount);
                    $item->setAmBaseDiscountAmount($baseDiscount);
                    $item->setAmDiscountAmount($discount);
                    // added support for graphql
                } elseif ($this->state->getAreaCode() === Area::AREA_GRAPHQL) {
                    $result->setAmount($item->getAmDiscountAmount());
                    $result->setBaseAmount($item->getAmBaseDiscountAmount());
                }
            }
        } catch (LocalizedException $e) {
            $this->logger->critical($e->getMessage());
        }
    }

}
