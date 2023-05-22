<?php
/**
 * Avalara_Excise
 *
 * @copyright  Copyright (c) 2021 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace Avalara\Excise\Api\Rest;

use Magento\Framework\DataObject;
use Avalara\Excise\Api\RestInterface;

interface ListEntityUseCodesInterface extends RestInterface
{
    /**
     * Perform REST request to get companies associated with the account
     *
     * @param DataObject|null $request
     * @param string|null $type
     * @param string|int|null $scopeId
     * @param string $scopeType
     *
     * @return DataObject[]
     * @throws \Avalara\Excise\Exception\AvalaraConnectionException
     */
    public function getEntityUseCodes(
        $request = null,
        $type = null,
        $scopeId = null,
        $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    );
}
