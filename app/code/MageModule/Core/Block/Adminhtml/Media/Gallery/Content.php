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
 * @author        MageModule admin@magemodule.com
 * @copyright    2018 MageModule, LLC
 * @license        https://www.magemodule.com/end-user-license-agreement/
 */

namespace MageModule\Core\Block\Adminhtml\Media\Gallery;

use MageModule\Core\Api\Data\MediaGalleryInterface;
use MageModule\Core\Api\Data\ScopedAttributeInterface;
use MageModule\Core\Model\AbstractExtensibleModel;
use MageModule\Core\Model\MediaGalleryConfigInterface;
use MageModule\Core\Block\Adminhtml\Media\Gallery as MediaGalleryBlock;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Media\Uploader;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\Serialize\Serializer\Json as JsonEncoder;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Element\Template;

/**
 * Class Content
 *
 * @package MageModule\Core\Block\Adminhtml\Media\Gallery
 */
class Content extends \Magento\Backend\Block\Widget
{
    /**
     * @var string
     */
    protected $_template = 'MageModule_Core::media/gallery.phtml';

    /**
     * @var JsonEncoder
     */
    private $jsonEncoder;

    /**
     * @var null|string
     */
    private $parentComponent;

    /**
     * Content constructor.
     *
     * @param Context     $context
     * @param JsonEncoder $jsonEncoder
     * @param string|null $parentComponent
     * @param array       $data
     */
    public function __construct(
        Context $context,
        JsonEncoder $jsonEncoder,
        $parentComponent = null,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->jsonEncoder     = $jsonEncoder;
        $this->parentComponent = $parentComponent;
    }

    /**
     * @return AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->addChild('uploader', Uploader::class);

        $callback = function ($value) {
            return '*.' . $value;
        };

        $extensions = $this->getConfig()->getAllowedExtensions();
        $files      = array_map($callback, $extensions);

        $filters = [
            'images' => [
                'label' => __('Images (%1)', '.' . implode(', .', $extensions)),
                'files' => $files,
            ],
        ];

        /** @var DataObject $config */
        $config = $this->getUploader()->getConfig();
        $config->setData('url', $this->getUploadUrl());
        $config->setData('file_field', $this->getFieldName());
        $config->setData('filters', $filters);

        return parent::_prepareLayout();
    }

    /**
     * @param string $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->setData('id', $id);

        return $this;
    }

    /**
     * @return string
     */
    public function getid()
    {
        return $this->getData('id');
    }

    /**
     * @param MediaGalleryBlock $element
     *
     * @return $this
     */
    public function setElement(MediaGalleryBlock $element)
    {
        $this->setData('element', $element);

        return $this;
    }

    /**
     * @return MediaGalleryBlock
     */
    public function getElement()
    {
        return $this->getData('element');
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setFormName($name)
    {
        $this->setData('form_name', $name);

        return $this;
    }

    /**
     * @return string
     */
    public function getFormName()
    {
        return $this->getData('form_name');
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setFieldName($name)
    {
        $this->setData('field_name', $name);

        return $this;
    }

    /**
     * @return string
     */
    public function getFieldName()
    {
        return $this->getData('field_name');
    }

    /**
     * @param AbstractModel $object
     *
     * @return $this
     */
    public function setDataObject(AbstractModel $object)
    {
        $this->setData('data_object', $object);

        return $this;
    }

    /**
     * @return AbstractModel|AbstractExtensibleModel
     */
    public function getDataObject()
    {
        return $this->getData('data_object');
    }

    /**
     * @param MediaGalleryConfigInterface $config
     *
     * @return $this
     */
    public function setConfig(MediaGalleryConfigInterface $config)
    {
        $this->setData('config', $config);

        return $this;
    }

    /**
     * @return MediaGalleryConfigInterface
     */
    public function getConfig()
    {
        return $this->getData('config');
    }

    /**
     * @param string $url
     *
     * @return $this
     */
    public function setUploadUrl($url)
    {
        $this->setData('upload_url', $url);

        return $this;
    }

    /**
     * @return string
     */
    public function getUploadUrl()
    {
        return $this->getData('upload_url');
    }

    /**
     * Retrieve uploader block
     *
     * @return bool|Uploader|AbstractBlock
     */
    public function getUploader()
    {
        $block = $this->getChildBlock('uploader');
        if ($block instanceof Template) {
            $block->setTemplate('MageModule_Core::media/uploader.phtml');
        }

        return $block;
    }

    /**
     * Retrieve uploader block html
     *
     * @return string
     */
    public function getUploaderHtml()
    {
        return $this->getChildHtml('uploader');
    }

    /**
     * @return string
     */
    public function getJsObjectName()
    {
        return $this->getHtmlId() . 'JsObject';
    }

    /**
     * @return string|null
     */
    public function getParentComponent()
    {
        return $this->parentComponent;
    }

    /**
     * @return string
     */
    public function getAddImagesButton()
    {
        return $this->getButtonHtml(
            __('Add New Images'),
            $this->getJsObjectName() . '.showUploader()',
            'add',
            $this->getHtmlId() . '_add_images_button'
        );
    }

    /**
     * @return string
     */
    public function getImagesJson()
    {
        $images = $this->getElement()->getImages();
        if ($images) {
            $resultArray = [];

            $mediaDir = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);
            $config   = $this->getConfig();

            /** @var MediaGalleryInterface|DataObject $image */
            foreach ($images as &$image) {
                $file = $image->getFile();
                $image->setData('file', $file);

                try {
                    $image->setData('url', $config->getMediaUrl($file));
                    $fileHandler = $mediaDir->stat($config->getMediaPath($file));
                    $image->setData('size', $fileHandler['size']);
                } catch (\Exception $e) {
                    $image->setData('size', 0);
                    $this->_logger->warning($e);
                }

                $resultArray[$image->getValueId()] = $image->getData();
            }

            $resultArray = $this->sortImagesByPosition($resultArray);

            return $this->jsonEncoder->serialize($resultArray);
        }

        return '[]';
    }

    /**
     * Sort images array by position key
     *
     * @param array $images
     *
     * @return array
     */
    private function sortImagesByPosition($images)
    {
        if (is_array($images)) {
            usort(
                $images,
                function ($imageA, $imageB) {
                    return ($imageA['position'] < $imageB['position']) ? -1 : 1;
                }
            );
        }
        return $images;
    }

    /**
     * Retrieve media attributes
     *
     * @return array
     */
    public function getMediaAttributes()
    {
        $result = $this->getElement()->getDataObject()->getMediaAttributes();
        if (!$result) {
            $result = [];
        }

        return $result;
    }

    /**
     * Get image types data
     *
     * @return array
     */
    public function getImageTypes()
    {
        $imageTypes = [];

        /** @var AttributeInterface|ScopedAttributeInterface $attribute */
        foreach ($this->getMediaAttributes() as $attribute) {
            $imageTypes[$attribute->getAttributeCode()] = [
                'code'  => $attribute->getAttributeCode(),
                'value' => $this->getElement()->getDataObject()->getData($attribute->getAttributeCode()),
                'label' => $attribute->getFrontend()->getLabel(),
                'scope' => __($this->getElement()->getScopeLabel($attribute)),
                'name'  => $this->getElement()->getAttributeFieldName($attribute),
            ];
        }
        return $imageTypes;
    }

    /**
     * @return string
     */
    public function getImageTypesJson()
    {
        return json_encode($this->getImageTypes());
    }
}
