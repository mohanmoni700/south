<?php

namespace Magedelight\Subscribenow\Console;

use Magento\Framework\App\Area;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Upgrade extends Command
{

    /** Command name */
    const NAME = 'md:subscribenow:upgrade';
    const TBL_PRODUCT_SUBSCRIBER_BACKUP = 'md_subscribenow_product_subscribers_100x1x3';

    private $output;
    private $state;
    private $productSubscribersFactory;
    private $serializeJson;
    private $addressRepository;
    private $addressModel = null;
    private $helperFactory = null;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\App\State $state,
        \Magedelight\Subscribenow\Model\ProductSubscribersFactory $productSubscribersFactory,
        \Magento\Framework\Serialize\Serializer\Json $serializeJson,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magedelight\Subscribenow\Helper\DataFactory $helperFactory
    ) {
        parent::__construct();

        $this->resource = $resource;
        $this->state = $state;
        $this->productSubscribersFactory = $productSubscribersFactory;
        $this->serializeJson = $serializeJson;
        $this->addressRepository = $addressRepository;
        $this->addressFactory = $addressFactory;
        $this->helperFactory = $helperFactory;
    }

    public function configure()
    {
        $this->setName(self::NAME)
            ->setDescription('This will upgrade existing subscriptions');

        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        try {
            $this->appState->setAreaCode(Area::AREA_GLOBAL);
        } catch (\Exception $e) {
        }

        if (!$this->checkIfBackupTableExists()) {
            $output->writeln('Backup table does not exist');
        } else {
            $output->writeln('Started Migrating Data');
            $this->upgradeExistingSubscriptions();
            $output->writeln('Migrated Data Successfully!');
        }
    }

    public function upgradeExistingSubscriptions()
    {
        $table = $this->resource->getTableName(self::TBL_PRODUCT_SUBSCRIBER_BACKUP);

        $sql = "SELECT * FROM `$table`";
        $result = $this->resource->getConnection()->fetchAll($sql);

        $helper = $this->helperFactory->create();

        if ($result) {
            foreach ($result as $row) {
                $subscription_id = $row['subscription_id'];
                $subscription = $this->productSubscribersFactory->create()->load($subscription_id);
                if ($subscription->getId()) {
                    $trial_billing_amount = $row['trial_billing_amount'];
                    $order_item_info = $this->serializeJson->unserialize($row['order_item_info']);
                    $additional_info = $this->serializeJson->unserialize($row['additional_info']);
                    $order_info = $this->serializeJson->unserialize($row['order_info']);
                    $billing_address_info = $this->serializeJson->unserialize($row['billing_address_info']);
                    $shipping_address_info = $this->serializeJson->unserialize($row['shipping_address_info']);

                    $billing_customer_address_id = $this->getAddressId($row['customer_id'], $billing_address_info);
                    $shipping_customer_address_id = $this->getAddressId($row['customer_id'], $shipping_address_info);
                    if (empty($billing_customer_address_id)) {
                        $billing_customer_address_id = $shipping_customer_address_id;
                    }

                    $total_bill_count = (int) !empty($additional_info['total_bill_count']) ? $additional_info['total_bill_count'] : 0;
                    $total_bill_count -= (int) !empty($additional_info['trial_count']) ? $additional_info['trial_count'] : 0;
                    if ($total_bill_count < 0) {
                        $total_bill_count = 0;
                    }

                    $last_bill_date = null;
                    if (!empty($additional_info['last_bill'])) {
                        $last_bill_date = date('Y-m-d H:i:s', $additional_info['last_bill']);
                    }
                    
                    $next_cycle = null;
                    if (!empty($additional_info['next_cycle'])) {
                        $next_cycle = date('Y-m-d H:i:s', $additional_info['next_cycle']);
                    }
                    
                    $isVirtual = $order_info['is_virtual'];

                    $productOptions = [];
                    if (isset($order_item_info[0])
                        && isset($order_item_info[0]['product_options'])
                        && isset($order_item_info[0]['product_options']['options'])) {
                        $productOptions = $order_item_info[0]['product_options']['options'];
                    }

                    $data = [
                        'order_info' => null,
                        'billing_address_info' => null,
                        'shipping_address_info' => null,
                        'additional_info' => [
                            'shipping_title' => (!$isVirtual) ? $order_info['shipping_description'] : null,
                            'product_options' => $productOptions
                        ],
                        'order_item_info' => $order_item_info[0]['product_options']['info_buyRequest'],
                        'last_bill_date' => $last_bill_date,
                        'next_occurrence_date' => $next_cycle,
                        'trial_count' => !empty($additional_info['trial_count']) ? $additional_info['trial_count'] : 0,
                        'is_trial' => (($trial_billing_amount > 0) ? 1 : 0),
                        'shipping_method_code' => (!$isVirtual) ? $order_info['shipping_method'] : null,
                        'payment_token' => $additional_info['subscription_id'] ?? null,
                        'total_bill_count' => $total_bill_count,
                        'base_currency_code' => $order_info['base_currency_code'],
                        'base_billing_amount' => $row['billing_amount'],
                        'base_trial_billing_amount' => $row['trial_billing_amount'],
                        'base_shipping_amount' => (float) $order_info['base_shipping_amount'],
                        'base_tax_amount' => (float) $order_info['base_tax_amount'],
                        'base_initial_amount' => $row['initial_amount'],
                        'base_discount_amount' => (float) $order_info['base_discount_amount'],
                        'initial_order_id' => $order_info['increment_id'],
                        'billing_address_id' => $billing_customer_address_id,
                        'shipping_address_id' => (!$isVirtual) ? $shipping_customer_address_id : null,
                        'product_sku' => $order_item_info[0]['sku'],
                        'product_name' => isset($order_item_info[0]['name']) ? $order_item_info[0]['name'] : null,
                        'payment_title' => $helper->getPaymentTitle($subscription->getPaymentMethodCode())
                    ];
                    $subscription->addData($data);

                    $subscription->save();
                    $this->output->writeln('Processed Record for ID: ' . $subscription_id . ', Profile ID: ' . $subscription->getProfileId());
                } else {
                    $this->output->writeln('Skipping record with ID: ' . $subscription_id . ', As it does not exist in new Table');
                }
            }
        }
    }
    
    private function getAddressId($customerId = 0, $address = [])
    {
        
        $addressId = null;
        if ($address && isset($address['customer_address_id']) && !empty($address['customer_address_id'])) {
            $addressId = $address['customer_address_id'];
        }

        if (!$addressId && $address && $customerId) {
            $firstname = isset($address['firstname']) && !empty($address['firstname']) ? $address['firstname'] : null;
            $middlename = isset($address['middlename']) && !empty($address['middlename']) ? $address['middlename'] : null;
            $lastname = isset($address['lastname']) && !empty($address['lastname']) ? $address['lastname'] : null;
            $company = isset($address['company']) && !empty($address['company']) ? $address['company'] : null;
            $street = isset($address['street']) && !empty($address['street']) ? $address['street'] : null;
            $city = isset($address['city']) && !empty($address['city']) ? $address['city'] : null;
            $region = isset($address['region']) && !empty($address['region']) ? $address['region'] : null;
            $regionId = isset($address['region_id']) && !empty($address['region_id']) ? $address['region_id'] : null;
            $postcode = isset($address['postcode']) && !empty($address['postcode']) ? $address['postcode'] : null;
            $countryId = isset($address['country_id']) && !empty($address['country_id']) ? $address['country_id'] : null;
            $telephone = isset($address['telephone']) && !empty($address['telephone']) ? $address['telephone'] : null;

            $addressCollection = $this->addressFactory->create()->getCollection()
                ->addFieldToFilter('parent_id', $customerId)
                ->addFieldToFilter('firstname', $firstname)
                ->addFieldToFilter('lastname', $lastname)
                ->addFieldToFilter('street', $street)
                ->addFieldToFilter('city', $city)
                ->addFieldToFilter('postcode', $postcode)
                ->addFieldToFilter('country_id', $countryId)
                ->addFieldToFilter('telephone', $telephone);
            
            if ($middlename) {
                $addressCollection->addFieldToFilter('middlename', $middlename);
            }
            if ($company) {
                $addressCollection->addFieldToFilter('company', $company);
            }
            if ($region) {
                $addressCollection->addFieldToFilter('region', $region);
            }
            if ($regionId) {
                $addressCollection->addFieldToFilter('region_id', $regionId);
            }

            if ($addressCollection->getSize()) {
                $addressId = $addressCollection->getFirstItem()->getId();
            } else {
                $addressId = $this->saveAddress($customerId, $address);
            }
        }

        return $addressId;
    }
    
    private function saveAddress($customerId = 0, $address = [])
    {
        $remove = ['customer_address_id', 'email', 'address_type', 'parent_id'];
        $addressdata = array_diff_key($address, array_flip($remove));
        
        $adressFactory = $this->addressFactory->create();
        $adressFactory->setData($addressdata)->setCustomerId($customerId)->save();
        
        return $adressFactory->getId();
    }

    private function checkIfBackupTableExists()
    {
        $table = $this->resource->getTableName(self::TBL_PRODUCT_SUBSCRIBER_BACKUP);
        return $this->resource->getConnection()->isTableExists($table);
    }
}
