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

use Webkul\MultiQuickbooksConnect\Api\Data\CreditmemoMapInterface;
use Magento\Framework\DataObject\IdentityInterface;

class CreditmemoMap extends \Magento\Framework\Model\AbstractModel implements IdentityInterface, CreditmemoMapInterface
{

    const NOROUTE_ENTITY_ID = 'no-route';

    const CACHE_TAG = 'multi_quickbook_map_creditmemo';

    protected $_cacheTag = 'multi_quickbook_map_creditmemo';

    protected $_eventPrefix = 'multi_quickbook_map_creditmemo';

    /**
     * set resource model
     */
    public function _construct()
    {
        $this->_init(\Webkul\MultiQuickbooksConnect\Model\ResourceModel\CreditmemoMap::class);
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
     * get MageCreditmemoId
     * @return string
     */
    public function getMageCreditmemoId()
    {
        return $this->getData(self::MAGE_CREDITMEMO_ID);
    }

    /**
     * set MageCreditmemoId
     * @return $this
     */
    public function setMageCreditmemoId($mageCreditmemoId)
    {
        return $this->setData(self::MAGE_CREDITMEMO_ID, $mageCreditmemoId);
    }

    /**
     * get QuickbookCreditmemoId
     * @return string
     */
    public function getQuickbookCreditmemoId()
    {
        return $this->getData(self::QUICKBOOK_CREDITMEMO_ID);
    }

    /**
     * set QuickbookCreditmemoId
     * @return $this
     */
    public function setQuickbookCreditmemoId($qbCreditmemoId)
    {
        return $this->setData(self::QUICKBOOK_CREDITMEMO_ID, $qbCreditmemoId);
    }

    /**
     * get QuickbookCreditmemoDocNumber
     * @return string
     */
    public function getQuickbookCreditmemoDocNumber()
    {
        return $this->getData(self::QUICKBOOK_CREDITMEMO_DOC_NUMBER);
    }

    /**
     * set QuickbookCreditmemoDocNumber
     * @return $this
     */
    public function setQuickbookCreditmemoDocNumber($qbCreditmemoDocNumber)
    {
        return $this->setData(self::QUICKBOOK_CREDITMEMO_DOC_NUMBER, $qbCreditmemoDocNumber);
    }

    /**
     * Get CreatedAt.
     *
     * @return varchar
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set CreatedAt.
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get AccountId.
     *
     * @return varchar
     */
    public function getAccountId()
    {
        return $this->getData(self::ACCOUNT_ID);
    }

    /**
     * Set AccountId.
     */
    public function setAccountId($accountId)
    {
        return $this->setData(self::ACCOUNT_ID, $accountId);
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
