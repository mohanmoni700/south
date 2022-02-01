<?php
/**
 * Copyright (c) 2018 MageModule, LLC: All rights reserved
 *
 * LICENSE: This source file is subject to our standard End User License
 * Agreeement (EULA) that is available through the world-wide-web at the
 * following URI: https://www.magemodule.com/magento2-ext-license.html.
 *
 *  If you did not receive a copy of the EULA and are unable to obtain it through
 *  the web, please send a note to admin@magemodule.com so that we can mail
 *  you a copy immediately.
 *
 * @author        MageModule admin@magemodule.com
 * @copyright    2018 MageModule, LLC
 * @license        https://www.magemodule.com/magento2-ext-license.html
 */

namespace MageModule\Core\Model\ResourceModel\Entity\Attribute;

use MageModule\Core\Api\Data\ScopedAttributeInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Model\Entity\ScopeInterface;

/**
 * Class Persistor
 *
 * @package MageModule\Core\Model\ResourceModel\Entity\Attribute
 */
class Persistor extends \Magento\Eav\Model\ResourceModel\AttributePersistor
{
    /**
     * @var ConditionBuilder
     */
    private $conditionBuilder;

    /**
     * AttributePersistor constructor.
     *
     * @param FormatInterface              $localeFormat
     * @param AttributeRepositoryInterface $attributeRepository
     * @param MetadataPool                 $metadataPool
     * @param ConditionBuilder             $conditionBuilder
     */
    public function __construct(
        FormatInterface $localeFormat,
        AttributeRepositoryInterface $attributeRepository,
        MetadataPool $metadataPool,
        ConditionBuilder $conditionBuilder
    ) {
        parent::__construct(
            $localeFormat,
            $attributeRepository,
            $metadataPool
        );

        $this->conditionBuilder = $conditionBuilder;
    }

    /**
     * @param ScopeInterface    $scope
     * @param AbstractAttribute $attribute
     * @param bool              $useDefault
     *
     * @return string
     */
    protected function getScopeValue(ScopeInterface $scope, AbstractAttribute $attribute, $useDefault = false)
    {
        if ($attribute instanceof ScopedAttributeInterface) {
            $useDefault = $useDefault || $attribute->isScopeGlobal();
        }

        return parent::getScopeValue($scope, $attribute, $useDefault);
    }

    /**
     * @param AbstractAttribute       $attribute
     * @param EntityMetadataInterface $metadata
     * @param array                   $scopes
     * @param string                  $linkFieldValue
     *
     * @return array
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    protected function buildInsertConditions(
        AbstractAttribute $attribute,
        EntityMetadataInterface $metadata,
        array $scopes,
        $linkFieldValue
    ) {
        if ($attribute instanceof ScopedAttributeInterface && $attribute->isScopeWebsite()) {
            return $this->conditionBuilder->buildNewAttributesWebsiteScope(
                $attribute,
                $metadata,
                $scopes,
                $linkFieldValue
            );
        }

        return parent::buildInsertConditions($attribute, $metadata, $scopes, $linkFieldValue);
    }

    /**
     * @param AbstractAttribute       $attribute
     * @param EntityMetadataInterface $metadata
     * @param array                   $scopes
     * @param string                  $linkFieldValue
     *
     * @return array
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    protected function buildUpdateConditions(
        AbstractAttribute $attribute,
        EntityMetadataInterface $metadata,
        array $scopes,
        $linkFieldValue
    ) {
        if ($attribute instanceof ScopedAttributeInterface && $attribute->isScopeWebsite()) {
            return $this->conditionBuilder->buildExistingAttributeWebsiteScope(
                $attribute,
                $metadata,
                $scopes,
                $linkFieldValue
            );
        }

        return parent::buildUpdateConditions($attribute, $metadata, $scopes, $linkFieldValue);
    }

    /**
     * @param AbstractAttribute       $attribute
     * @param EntityMetadataInterface $metadata
     * @param array                   $scopes
     * @param string                  $linkFieldValue
     *
     * @return array
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    protected function buildDeleteConditions(
        AbstractAttribute $attribute,
        EntityMetadataInterface $metadata,
        array $scopes,
        $linkFieldValue
    ) {
        if ($attribute instanceof ScopedAttributeInterface && $attribute->isScopeWebsite()) {
            return $this->conditionBuilder->buildExistingAttributeWebsiteScope(
                $attribute,
                $metadata,
                $scopes,
                $linkFieldValue
            );
        }

        return parent::buildDeleteConditions($attribute, $metadata, $scopes, $linkFieldValue);
    }
}
