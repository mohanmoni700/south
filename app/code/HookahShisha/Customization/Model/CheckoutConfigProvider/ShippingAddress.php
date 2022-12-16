<?php
declare(strict_types=1);

namespace HookahShisha\Customization\Model\CheckoutConfigProvider;

use Amasty\RequestQuote\Api\QuoteRepositoryInterface;
use Amasty\RequestQuote\Model\Customer\Address\CustomerAddressDataFormatterFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Amasty\RequestQuote\Model\CheckoutConfigProvider\ShippingAddress as AmastyShippingAddress;

class ShippingAddress
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var CustomerAddressDataFormatterFactory
     */
    private $customerAddressDataFormatterFactory;

    /**
     * @var QuoteRepositoryInterface
     */
    private $quoteRepository;

    /**
     * Construct
     *
     * @param QuoteRepositoryInterface $quoteRepository
     * @param CheckoutSession $checkoutSession
     * @param CustomerAddressDataFormatterFactory $customerAddressDataFormatterFactory
     */
    public function __construct(
        QuoteRepositoryInterface $quoteRepository,
        CheckoutSession $checkoutSession,
        CustomerAddressDataFormatterFactory $customerAddressDataFormatterFactory
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->customerAddressDataFormatterFactory = $customerAddressDataFormatterFactory;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * AfterGetConfig
     *
     * @param AmastyShippingAddress $subject
     * @param array $result
     * @return array
     */
    public function afterGetConfig(
        AmastyShippingAddress $subject,
        $result
    ) {
        if (array_key_exists('amasty_quote', $result)) {
            if (array_key_exists('shipping_address', $result['amasty_quote'])) {
                $result['amasty_quote']['shipping_address'] = $this->getShippingAddress();
            }
        }
        return $result;
    }

    /**
     * Get ShippingAddress
     *
     * @return array
     */
    private function getShippingAddress(): array
    {
        $shippingAddress = $this->customerAddressDataFormatterFactory->create()->prepareAddress(
            $this->checkoutSession->getQuote()->getShippingAddress()->exportCustomerAddress()
        );
        foreach ($shippingAddress['custom_attributes'] as $key => $customAttribute) {
            if ($customAttribute['value'] === null) {
                unset($shippingAddress['custom_attributes'][$key]);
            }
        }
        unset($shippingAddress['region']);
        return $shippingAddress;
    }
}
