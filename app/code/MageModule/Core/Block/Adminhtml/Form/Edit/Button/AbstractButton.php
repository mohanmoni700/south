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
use Magento\Framework\Model\AbstractModel;

abstract class AbstractButton implements \Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var AuthorizationInterface
     */
    protected $authorization;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var string
     */
    protected $registryKey;

    /**
     * @var string|null
     */
    protected $label;

    /**
     * @var string|null
     */
    protected $cssClass;

    /**
     * @var int|null
     */
    protected $sortOrder;

    /**
     * @var string|null
     */
    protected $route;

    /**
     * @var null|string
     */
    protected $aclResource;

    /**
     * AbstractButton constructor.
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
        $aclResource = null
    ) {
        $this->registry      = $registry;
        $this->authorization = $authorization;
        $this->urlBuilder    = $urlBuilder;
        $this->registryKey   = $registryKey;
        $this->label         = $label;
        $this->cssClass      = $cssClass;
        $this->sortOrder     = $sortOrder;
        $this->route         = $route;
        $this->aclResource   = $aclResource;
    }

    /**
     * @return AbstractModel
     */
    public function getDataObject()
    {
        return $this->registry->registry($this->registryKey);
    }

    /**
     * @return int|null
     */
    public function getDataObjectId()
    {
        if ($this->getDataObject() instanceof AbstractModel) {
            return $this->getDataObject()->getId();
        }

        return null;
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        return [];
    }

    /**
     * @param string $route
     * @param array  $params
     *
     * @return string
     */
    protected function getUrl($route, array $params = [])
    {
        return $this->urlBuilder->getUrl($route, $params);
    }

    /**
     * @param string $resource
     *
     * @return bool
     */
    protected function isAuthorized($resource)
    {
        return $this->authorization->isAllowed($resource);
    }
}
