<?php

namespace HookahShisha\SubscribeGraphQl\Model\Magento\QuoteGraphQl\Resolver;

use Magento\QuoteGraphQl\Model\Resolver\CartPrices as CartPricesSubject;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Quote\TotalsCollector;

/**
 * 
 */
class CartPrices
{
	/**
     * @var TotalsCollector
     */
    private $totalsCollector;

    /**
     * @param TotalsCollector $totalsCollector
     */
    public function __construct(
        TotalsCollector $totalsCollector
    ) {
        $this->totalsCollector = $totalsCollector;
    }

	/**
	 * Adding sunscription initial fee field
	 *
	 * @param CartPricesSubject $subject
	 * @param array $return
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed 
	 */
	public function afterResolve(
		CartPricesSubject $subject,
		$result,
		Field $field,
		$context,
		ResolveInfo $info,
		array $value = null,
		array $args = null
	) {
		if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        $quote = $value['model'];
        $cartTotals = $this->totalsCollector->collectQuoteTotals($quote);
        $currency = $quote->getQuoteCurrencyCode();

        $result['subscribenow_init_amount'] = ['value' => $cartTotals->getSubscribenowInitAmount() ?? 0, 'currency' => $currency];

        return $result;
	}
}