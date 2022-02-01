<?php

namespace MageModule\Core\Helper\Directory;

use Magento\Directory\Model\ResourceModel\Country\Collection;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;
use Magento\Framework\App\Helper\Context;

/**
 * Class Country
 */
class Country extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var array
     */
    private $options;

    /**
     * Country constructor.
     *
     * @param Context           $context
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return array
     */
    private function getOptions()
    {
        if ($this->options === null) {
            /** @var Collection $collection */
            $collection = $this->collectionFactory->create();

            /** @var \Magento\Directory\Model\Country $country */
            foreach ($collection as $country) {
                $this->options[strtolower($country->getCountryId())] = $country->getName();
            }
        }

        return $this->options;
    }

    /**
     * @param string $id
     *
     * @return string|null
     */
    public function getNameById($id)
    {
        $id      = strtolower($id);
        $options = $this->getOptions();

        return isset($options[$id]) ? $options[$id] : null;
    }
}
