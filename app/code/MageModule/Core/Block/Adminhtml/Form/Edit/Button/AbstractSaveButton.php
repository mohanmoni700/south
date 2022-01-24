<?php
/**
 * Copyright (c) 2018 MageModule, LLC: All rights reserved
 *
 * LICENSE: This source file is subject to our standard End User License
 * Agreeement (EULA) that is available through the world-wide-web at the
 * following URI: https://www.magemodule.com/end-user-license-agreement/.
 *
 *  If you did not receive a copy of the EULA and are unable to obtain it through
 *  the web, please send a note to admin@magemodule.com so that we can mail
 *  you a copy immediately.
 *
 * @author         MageModule admin@magemodule.com
 * @copyright      2018 MageModule, LLC
 * @license        https://www.magemodule.com/end-user-license-agreement/
 */

namespace MageModule\Core\Block\Adminhtml\Form\Edit\Button;

use Magento\Framework\Registry;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\UrlInterface;

abstract class AbstractSaveButton extends AbstractButton
{
    /**
     * @var string|null
     */
    protected $editAclResource;

    /**
     * @var string|null
     */
    protected $createAclResource;

    /**
     * AbstractSaveButton constructor.
     *
     * @param Registry               $registry
     * @param AuthorizationInterface $authorization
     * @param UrlInterface           $urlBuilder
     * @param string                 $registryKey
     * @param string                 $editAclResource
     * @param string                 $createAclResource
     * @param string|null            $label
     * @param string|null            $cssClass
     * @param string|int|null        $sortOrder
     * @param string|null            $route
     */
    public function __construct(
        Registry $registry,
        AuthorizationInterface $authorization,
        UrlInterface $urlBuilder,
        $registryKey,
        $editAclResource,
        $createAclResource,
        $label = null,
        $cssClass = null,
        $sortOrder = null,
        $route = null
    ) {
        parent::__construct(
            $registry,
            $authorization,
            $urlBuilder,
            $registryKey,
            $label,
            $cssClass,
            $sortOrder,
            $route,
            $editAclResource
        );

        $this->editAclResource   = $editAclResource;
        $this->createAclResource = $createAclResource;
    }

    /**
     * @return bool
     */
    protected function canShowSaveButton()
    {
        return $this->getDataObjectId() ?
            $this->isAuthorized($this->editAclResource) :
            $this->isAuthorized($this->createAclResource);
    }
}
