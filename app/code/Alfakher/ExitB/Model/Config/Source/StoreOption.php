<?php
declare(strict_types=1);
namespace Alfakher\ExitB\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory;

class StoreOption implements ArrayInterface
{
    /**
     * @var CollectionFactory
     */
    protected $_websiteCollectionFactory;

   /**
    * Contructor
    *
    * @param CollectionFactory $websiteCollectionFactory
    */
    public function __construct(
        CollectionFactory $websiteCollectionFactory
    ) {
        $this->_websiteCollectionFactory = $websiteCollectionFactory;
    }

    /**
     * Fetch websites
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $websites = $this->getWebsiteCollection()->getData();
        foreach ($websites as $website) {
            $id = $website['website_id'];
            $websiteName= $website['name'];
            $options[] = [
                'value' => $id,
                'label' => $websiteName,
            ];
        }
        return $options;
    }
    
    /**
     * Retrieve websites collection of system
     *
     * @return Website Collection
     */
    public function getWebsiteCollection()
    {
        return $this->_websiteCollectionFactory->create();
    }
}
