<?php
declare(strict_types=1);

namespace Tabby\Checkout\Lock\Backend;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Phrase;
use Magento\Framework\DB\ExpressionConverter;

/*
 *  Compatibility with magento 2.2.3 version without LockManagerInterface
 */
if (class_exists("\Magento\Framework\Lock\LockManagerInterface")) {
    class Database extends \Magento\Framework\Lock\Backend\Database {}
} else {

/**
 * Implementation of the lock manager on the basis of MySQL.
 */
class Database implements \Tabby\Checkout\Lock\LockManagerInterface
{
    private $MAX_LOCK_TIME = 604800;
    private $resource;
    private $deploymentConfig;
    private $prefix;
    private $currentLock = false;

    /**
     * @param ResourceConnection $resource
     * @param DeploymentConfig $deploymentConfig
     * @param string|null $prefix
     */
    public function __construct(
        ResourceConnection $resource,
        DeploymentConfig $deploymentConfig,
        string $prefix = null
    ) {
        $this->resource = $resource;
        $this->deploymentConfig = $deploymentConfig;
        $this->prefix = $prefix;
    }

    /**
     * Sets a lock for name
     *
     * @param string $name lock name
     * @param int $timeout How long to wait lock acquisition in seconds, negative value means infinite timeout
     * @return bool
     * @throws AlreadyExistsException
     * @throws \Zend_Db_Statement_Exception
     */
    public function lock(string $name, int $timeout = -1): bool
    {
        if (!$this->deploymentConfig->isDbAvailable()) {
            return true;
        }
        $name = $this->addPrefix($name);

        /**
         * Before MySQL 5.7.5, only a single simultaneous lock per connection can be acquired.
         * This limitation can be removed once MySQL minimum requirement has been raised,
         * currently we support MySQL 5.6 way only.
         */
        if ($this->currentLock) {
            throw new AlreadyExistsException(
                new Phrase(
                    'Current connection is already holding lock for %1, only single lock allowed',
                    [$this->currentLock]
                )
            );
        }

        $result = (bool)$this->resource->getConnection()->query(
            "SELECT GET_LOCK(?, ?);",
            [$name, $timeout < 0 ? $this->MAX_LOCK_TIME : $timeout]
        )->fetchColumn();

        if ($result === true) {
            $this->currentLock = $name;
        }

        return $result;
    }

    /**
     * Releases a lock for name
     *
     * @param string $name lock name
     * @return bool
     * @throws \Zend_Db_Statement_Exception
     */
    public function unlock(string $name): bool
    {
        if (!$this->deploymentConfig->isDbAvailable()) {
            return true;
        }

        $name = $this->addPrefix($name);

        $result = (bool)$this->resource->getConnection()->query(
            "SELECT RELEASE_LOCK(?);",
            [(string)$name]
        )->fetchColumn();

        if ($result === true) {
            $this->currentLock = false;
        }

        return $result;
    }

    /**
     * Tests of lock is set for name
     *
     * @param string $name lock name
     * @return bool
     * @throws \Zend_Db_Statement_Exception
     */
    public function isLocked(string $name): bool
    {
        if (!$this->deploymentConfig->isDbAvailable()) {
            return false;
        }

        $name = $this->addPrefix($name);

        return (bool)$this->resource->getConnection()->query(
            "SELECT IS_USED_LOCK(?);",
            [$name]
        )->fetchColumn();
    }

    /**
     * Adds prefix and checks for max length of lock name
     *
     * Limited to 64 characters in MySQL.
     *
     * @param string $name
     * @return string
     */
    private function addPrefix(string $name): string
    {
        $prefix = $this->getPrefix() ? $this->getPrefix() . '|' : '';
        $name = ExpressionConverter::shortenEntityName($prefix . $name, $prefix);

        return $name;
    }

    /**
     * Get installation specific lock prefix to avoid lock conflicts
     *
     * @return string lock prefix
     */
    private function getPrefix(): string
    {
        if ($this->prefix === null) {
            $this->prefix = (string)$this->deploymentConfig->get(
                ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT
                . '/'
                . ConfigOptionsListConstants::KEY_NAME,
                ''
            );
        }

        return $this->prefix;
    }
}
}
