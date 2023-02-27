<?php
/**
 * @author  CORRA
 */
namespace Corra\Spreedly\Gateway\Command;

use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\CommandInterface;
use Corra\Spreedly\Gateway\Helper\SubjectReader;

/**
 *  Chooses the best method of Authorize the payment based on the status of the transaction
 */
class AuthorizeStrategyCommand implements CommandInterface
{
    /**
     * authorize
     */
    private const AUTH_ONLY = 'auth_only';

    /**
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * Constructor
     *
     * @param CommandPoolInterface $commandPool
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        CommandPoolInterface $commandPool,
        SubjectReader $subjectReader
    ) {
        $this->commandPool = $commandPool;
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $commandSubject)
    {
        $this->commandPool->get(self::AUTH_ONLY)->execute($commandSubject);
    }
}
