<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace HookahShisha\Checkoutchanges\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\Data\CartInterface;
use Magento\QuoteGraphQl\Model\Cart\ExtractQuoteAddressData;
use Magento\QuoteGraphQl\Model\Cart\ValidateAddressFromSchema;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class BillingAddress extends \Magento\QuoteGraphQl\Model\Resolver\BillingAddress
{
    /**
     * @var ExtractQuoteAddressData
     */
    private $extractQuoteAddressData;

    /**
     * @var ValidateAddressFromSchema
     */
    private $validateAddressFromSchema;

    /**
     * @param ExtractQuoteAddressData $extractQuoteAddressData
     * @param ValidateAddressFromSchema $validateAddressFromSchema
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\AddressFactory $addressFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        ExtractQuoteAddressData $extractQuoteAddressData,
        ValidateAddressFromSchema $validateAddressFromSchema,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        LoggerInterface $logger
    ) {
        $this->extractQuoteAddressData = $extractQuoteAddressData;
        $this->validateAddressFromSchema = $validateAddressFromSchema;
        $this->_customerFactory = $customerFactory;
        $this->_addressFactory = $addressFactory;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
     
        /** @var CartInterface $cart */
        $cart = $value['model'];
        $billingAddress = $cart->getBillingAddress();

        if ($cart->getBillingAddress()->getCustomerId()) {

            $customerid = $cart->getBillingAddress()->getCustomerId();
            if (!$cart->getShippingAddress()->getCity()) {
                $customer = $this->_customerFactory->create()->load($customerid);
                $shippingAddressId = $customer->getDefaultShipping();
                if ($shippingAddressId) {
                    $shippingAddress = $this->_addressFactory->create()->load($shippingAddressId);
                    $addressnew = $shippingAddress->getData();
                    $cart->getShippingAddress()->setFirstname($addressnew['firstname']);
                    $cart->getShippingAddress()->setLastname($addressnew['lastname']);
                    $cart->getShippingAddress()->setStreet($addressnew['street']);
                    $cart->getShippingAddress()->setCity($addressnew['city']);
                    $cart->getShippingAddress()->setTelephone($addressnew['telephone']);
                    $cart->getShippingAddress()->setPostcode($addressnew['postcode']);
                    $cart->getShippingAddress()->setRegion($addressnew['region']);
                    $cart->getShippingAddress()->setRegionId($addressnew['region_id']);
                    $cart->getShippingAddress()->setCountryId($addressnew['country_id']);
                    try {
                        $cart->save();
                    } catch (Exception $e) {
                        $this->logger->err($e->getMessage());
                    }
                }
            }
        }
        $addressData = $this->extractQuoteAddressData->execute($billingAddress);
        if (!$this->validateAddressFromSchema->execute($addressData)) {
            return null;
        }
        return $addressData;
    }
}
