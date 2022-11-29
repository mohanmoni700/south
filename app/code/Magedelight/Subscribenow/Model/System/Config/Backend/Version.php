<?php
/**
 * Magedelight
 * Copyright (c) 2019 Mage Delight
 *
 * @category    Magedelight
 * @package     Magedelight_Subscribenow
 * @author      Magedelight <info@magedelight.com>
 * @copyright   Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license     http://opensource.org/licenses/gpl-3.0.html
 */
namespace Magedelight\Subscribenow\Model\System\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Module\ResourceInterface;
use Magento\Framework\Registry;

/**
 * Backend system config
 * Render current extension version
 */
class Version extends Value
{
    const MODULE = 'Magedelight_Subscribenow';

    /**
     * @var ResourceInterface
     */
    private $moduleResource;

    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        ResourceInterface $moduleResource,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->moduleResource = $moduleResource;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Inject current installed module version as the config value.
     *
     * @return void
     */
    public function afterLoad()
    {
        $version = $this->moduleResource->getDbVersion(self::MODULE);
        $this->setValue($version);
    }
}
