<?php

declare(strict_types=1);

namespace HookahShisha\Checkoutchanges\Plugin\QuoteGraphQl\Model\Resolver;

use Magento\QuoteGraphQl\Model\Resolver\BillingAddress as Subject;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Psr\Log\LoggerInterface;

class BillingAddressPlugin
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;
    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    private $addressFactory;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\AddressFactory $addressFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        LoggerInterface $logger
    ) {
        $this->_customerFactory = $customerFactory;
        $this->_addressFactory = $addressFactory;
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

        return $result;
    }
}
