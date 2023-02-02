<?php
/**
 * @category   Webkul
 * @package    Webkul_QuickbookCustomInv
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\QuickbookCustomInv\Model;

use Webkul\MultiQuickbooksConnect\Helper\Data as QBHelperData;
use Webkul\MultiQuickbooksConnect\Model\AccountFactory;
use Webkul\QuickbookCustomInv\Logger\Logger;

/**
 * custom cron actions
 */
class Cron
{
    /**
     * @var \Webkul\QuickbookCustomInv\Helper\Data
     */
    private $qbHelperData;

    /**
     * @var \Webkul\QuickbookCustomInv\Logger\Logger
     */
    private $logger;

    /**
     * Class constructor
     *
     * @param QBHelperData $qbHelperData
     * @param AccountFactory $accountFactory
     * @param Logger $logger
     */
    public function __construct(
        QBHelperData $qbHelperData,
        AccountFactory $accountFactory,
        Logger $logger
    ) {
        $this->qbHelperData = $qbHelperData;
        $this->accountFactory = $accountFactory;
        $this->logger = $logger;
    }

    /**
     * For AccessTokenValidate
     *
     * @return void
     */
    public function accessTokenValidate()
    {
        $this->logger->info("===============QB access Token Cron execution start ================ ");
        try {
            $qbAccounts = $this->accountFactory->create()->getCollection()
                                ->addFieldToFilter('oauth2_refresh_token', ['neq' => 'NULL']);
            foreach ($qbAccounts as $qba) {
                try {
                    $this->qbHelperData->getAccessToken($qba->getId());
                } catch (\Exception $e) {
                    $this->logger->addError(__('cron error %1 for account ID %2'), $e->getMessage(), $qba->getId());
                }
            }
        } catch (\Exception $e) {
            $this->logger->addError(__('eron error %1'), $e->getMessage());
        }
    }
}
