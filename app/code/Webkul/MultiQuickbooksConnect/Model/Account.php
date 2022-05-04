<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_MultiQuickbooksConnect
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\MultiQuickbooksConnect\Model;

use Webkul\MultiQuickbooksConnect\Api\Data\AccountInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Account extends \Magento\Framework\Model\AbstractModel implements IdentityInterface, AccountInterface
{

    const NOROUTE_ENTITY_ID = 'no-route';

    const CACHE_TAG = 'multi_quickbook_accounts';

    protected $_cacheTag = 'multi_quickbook_accounts';

    protected $_eventPrefix = 'multi_quickbook_accounts';

    /**
     * set resource model
     */
    public function _construct()
    {
        $this->_init(\Webkul\MultiQuickbooksConnect\Model\ResourceModel\Account::class);
    }

    /**
     * Get Id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Set Id.
     */
    public function setId($entityId)
    {
        return $this->setData(self::ID, $entityId);
    }

    /**
     * Load No-Route Indexer.
     *
     * @return $this
     */
    public function noRouteReasons()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Get identities.
     *
     * @return []
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }
}
