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

namespace MageModule\Core\Block\Adminhtml\Media;

use MageModule\Core\Api\Data\MediaGalleryInterface;
use MageModule\Core\Api\Data\ScopedAttributeInterface;
use MageModule\Core\Model\AbstractExtensibleModel;
use MageModule\Core\Model\MediaGalleryConfigInterface;
use MageModule\Core\Model\MediaGalleryConfigPoolInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\Registry;
use Magento\Framework\Data\Form;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Store\Model\StoreManagerInterface;

class Gallery extends \Magento\Framework\View\Element\AbstractBlock
{
    /**
     * @var MediaGalleryConfigInterface
     */
    private $configPool;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var string
     */
    private $registryKey;

    /**
     * @var Form
     */
    private $form;

    /**
     * @var string
     */
    private $formName;

    /**
     * @var string
     */
    private $fieldName;

    /**
     * @var string
     */
    private $attributeCode;

    /**
     * @var string|null
     */
    private $fieldNameSuffix;

    /**
     * @var string
     */
    private $htmlId;

    /**
     * Gallery constructor.
     *
     * @param Context                         $context
     * @param MediaGalleryConfigPoolInterface $configPool
     * @param StoreManagerInterface           $storeManager
     * @param Registry                        $registry
     * @param Form                            $form
     * @param string|null                     $formName
     * @param string                          $registryKey
     * @param string                          $attributeCode
     * @param string|null                     $fieldName
     * @param string|null                     $fieldNameSuffix
     * @param array                           $data
     */
    public function __construct(
        Context $context,
        MediaGalleryConfigPoolInterface $configPool,
        StoreManagerInterface $storeManager,
        Registry $registry,
        Form $form,
        $formName,
        $registryKey,
        $attributeCode,
        $fieldName = null,
        $fieldNameSuffix = null,
        $data = []
    ) {
        parent::__construct($context, $data);

        $this->configPool      = $configPool;
        $this->storeManager    = $storeManager;
        $this->registry        = $registry;
        $this->form            = $form;
        $this->formName        = $formName;
        $this->fieldName       = $fieldName;
        $this->registryKey     = $registryKey;
        $this->htmlId          = $attributeCode;
        $this->attributeCode   = $attributeCode;
        $this->fieldNameSuffix = $fieldNameSuffix;
    }

    /**
     * @return AbstractBlock
     */
    protected function _prepareLayout()
    {
        $block = $this->getContentBlock();
        $block->setElement($this);
        $block->setDataObject($this->getDataObject());
        $block->setConfig($this->getConfig());
        $block->setUploadUrl($this->getUploadUrl());
        $block->setFieldName($this->getFieldName());

        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        return $this->getElementHtml();
    }

    /**
     * @return string
     */
    public function getElementHtml()
    {
        $html = $this->getContentHtml();

        return $html;
    }

    /**
     * @return string
     */
    public function getHtmlId()
    {
        return $this->htmlId;
    }

    /**
     * @return string
     */
    public function getAttributeCode()
    {
        return $this->attributeCode;
    }

    /**
     * @return string
     */
    public function getFieldName()
    {
        $fieldname = $this->fieldName;
        if (!$fieldname) {
            $fieldname = $this->getAttributeCode();
        }

        if ($this->fieldNameSuffix) {
            $fieldname = $this->form->addSuffixToName($fieldname, $this->fieldNameSuffix);
        }

        return $fieldname;
    }

    /**
     * @return string
     */
    public function getFieldNameSuffix()
    {
        return $this->fieldNameSuffix;
    }

    /**
     * @return AbstractExtensibleModel|AbstractModel
     */
    public function getDataObject()
    {
        return $this->registry->registry($this->registryKey);
    }

    /**
     * @return bool|MediaGalleryConfigInterface
     */
    private function getConfig()
    {
        return $this->configPool->getConfig($this->getAttributeCode());
    }

    /**
     * @return string
     */
    public function getUploadUrl()
    {
        return $this->_urlBuilder
            ->addSessionParam()
            ->getUrl(
                $this->getConfig()->getUploadControllerRoute(),
                [
                    'attribute_code' => $this->getAttributeCode(),
                    'field_name'     => $this->getFieldName()
                ]
            );
    }

    /**
     * @return MediaGalleryInterface[]
     */
    public function getImages()
    {
        return $this->getDataObject()->getData($this->getAttributeCode()) ?: [];
    }

    /**
     * @return bool|AbstractBlock|Gallery\Content
     */
    public function getContentBlock()
    {
        return $this->getChildBlock('content');
    }

    /**
     * @return string
     */
    public function getContentHtml()
    {
        $block = $this->getContentBlock();
        $block->setId($this->getHtmlId() . '_content');
        $block->setFormName($this->formName);

        $galleryJs = $block->getJsObjectName();
        $block->getUploader()->getConfig()->setMediaGallery($galleryJs);

        return $block->toHtml();
    }

    /**
     * @param AttributeInterface|ScopedAttributeInterface $attribute
     *
     * @return string
     */
    public function getScopeLabel($attribute)
    {
        $html = '';
        if ($this->storeManager->isSingleStoreMode() || !$attribute instanceof ScopedAttributeInterface) {
            return $html;
        }

        if ($attribute->isScopeGlobal()) {
            $html .= __('[GLOBAL]');
        } elseif ($attribute->isScopeWebsite()) {
            $html .= __('[WEBSITE]');
        } elseif ($attribute->isScopeStore()) {
            $html .= __('[STORE VIEW]');
        }
        return $html;
    }

    /**
     * @param AttributeInterface|ScopedAttributeInterface $attribute
     *
     * @return string
     */
    public function getAttributeFieldName($attribute)
    {
        $name   = $attribute->getAttributeCode();
        $suffix = $this->getFieldNameSuffix();
        if ($suffix) {
            $name = $this->form->addSuffixToName($name, $suffix);
        }

        return $name;
    }
}
