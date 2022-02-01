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

/** @noinspection PhpCSValidationInspection */

namespace MageModule\Core\Helper\Eav\Attribute;

use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\Store;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Option
 *
 * @package MageModule\Core\Helper\Eav\Attribute
 */
class Option extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var AttributeInterface
     */
    private $attributes = [];

    /**
     * @var array
     */
    private $attributeOptions = [];

    /**
     * Option constructor.
     *
     * @param Context                      $context
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        Context $context,
        AttributeRepositoryInterface $attributeRepository
    ) {
        parent::__construct($context);
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @param string $entityTypeCode
     * @param string $attrCode
     *
     * @return AttributeInterface
     * @throws NoSuchEntityException
     */
    private function getAttribute($entityTypeCode, $attrCode)
    {
        $key = $entityTypeCode . '-' . $attrCode;
        if (!isset($this->attributes[$key])) {
            $this->attributes[$key] = $this->attributeRepository->get($entityTypeCode, $attrCode);
        }

        return $this->attributes[$key];
    }

    /**
     * @param string $entityTypeCode
     * @param string $attrCode
     * @param int    $storeId
     *
     * @return array
     */
    private function getAttributeOptions($entityTypeCode, $attrCode, $storeId = Store::DEFAULT_STORE_ID)
    {
        $key = $entityTypeCode . '-' . $attrCode . '-' . $storeId;
        if (!isset($this->attributeOptions[$key])) {
            $this->attributeOptions[$key] = [];

            try {
                $attribute = $this->getAttribute($entityTypeCode, $attrCode);
                if ($attribute instanceof \Magento\Eav\Model\Entity\Attribute\AbstractAttribute &&
                    $attribute->usesSource()
                ) {
                    $attribute->setStoreId($storeId);
                    $options = $attribute->getSource()->getAllOptions();
                    foreach ($options as $option) {
                        if (!isset($option['value']) || $option['value'] === '') {
                            continue;
                        }

                        $this->attributeOptions[$key][$option['value']] = trim(strtolower($option['label']));
                    }
                }
            } catch (\Exception $e) {

            }
        }

        return $this->attributeOptions[$key];
    }

    /**
     * @param string $entityTypeCode
     * @param string $attrCode
     * @param string $label
     * @param int    $storeId
     *
     * @return false|int|null|string
     */
    public function getAttributeOptionIdByLabel($entityTypeCode, $attrCode, $label, $storeId = Store::DEFAULT_STORE_ID)
    {
        $label    = trim(strtolower($label));
        $options  = $this->getAttributeOptions($entityTypeCode, $attrCode, $storeId);
        $optionId = array_search($label, $options);

        return $optionId !== false ? $optionId : null;
    }
}
