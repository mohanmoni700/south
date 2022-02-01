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

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;

class Delete extends AbstractButton
{
    /**
     * @var string
     */
    private $confirmationMessage;

    /**
     * Delete constructor.
     *
     * @param Registry               $registry
     * @param AuthorizationInterface $authorization
     * @param UrlInterface           $urlBuilder
     * @param string|null            $registryKey
     * @param string|null            $label
     * @param string|null            $cssClass
     * @param string|int|null        $sortOrder
     * @param string|null            $route
     * @param string|null            $aclResource
     * @param string                 $confirmationMessage
     */
    public function __construct(
        Registry $registry,
        AuthorizationInterface $authorization,
        UrlInterface $urlBuilder,
        $registryKey = null,
        $label = null,
        $cssClass = null,
        $sortOrder = null,
        $route = null,
        $aclResource = null,
        $confirmationMessage = 'Are you sure you want to delete this item?'
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
            $aclResource
        );

        $this->confirmationMessage = $confirmationMessage;
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getDataObjectId()) {
            $data = [
                'label'      => __($this->label ? $this->label : 'Delete'),
                'class'      => $this->cssClass,
                'on_click'   => 'deleteConfirm(\'' . __($this->confirmationMessage) . '\', \'' . $this->getDeleteUrl() . '\')',
                'sort_order' => $this->sortOrder,
                'disabled'   => !$this->isAuthorized($this->aclResource)
            ];
        }
        return $data;
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl($this->route ? $this->route : '*/*/delete', ['id' => $this->getDataObjectId()]);
    }
}
