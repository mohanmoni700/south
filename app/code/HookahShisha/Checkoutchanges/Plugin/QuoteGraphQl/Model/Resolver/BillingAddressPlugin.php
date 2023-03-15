<?php

declare(strict_types=1);

namespace HookahShisha\Checkoutchanges\Plugin\QuoteGraphQl\Model\Resolver;

use Magento\QuoteGraphQl\Model\Resolver\BillingAddress as Subject;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\AddressFactory;
use Psr\Log\LoggerInterface;

class BillingAddressPlugin
{
    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var AddressFactory
     */
    private $addressFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param CustomerFactory $customerFactory
     * @param AddressFactory $addressFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        CustomerFactory $customerFactory,
        AddressFactory $addressFactory,
        LoggerInterface $logger
    ) {
        $this->customerFactory = $customerFactory;
        $this->addressFactory = $addressFactory;
        $this->logger = $logger;
    }

    /**
     * AfterResolve
     *
     * @param Subject $subject
     * @param array $result
     * @param Field $field
     * @param array $context
     * @param ResolveInfo $info
     * @param array $value
     * @param array $args
     * @return array
     * @throws Exception
     */
    public function afterResolve(
        Subject $subject,
        $result,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $cart = $value['model'];

        if ($cart->getBillingAddress()->getCustomerId()) {
            $customerid = $cart->getBillingAddress()->getCustomerId();
            if (!$cart->getShippingAddress()->getCity()) {
                $customer = $this->customerFactory->create()->load($customerid);
                $shippingAddressId = $customer->getDefaultShipping();
                if ($shippingAddressId) {
                    $shippingAddress = $this->addressFactory->create()->load($shippingAddressId);
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
                    } catch (\Exception $e) {
                        $this->logger->error($e->getMessage());
                    }
                }
            }
        }

        return $result;
    }
}
