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

namespace MageModule\Core\Model\Entity\Attribute\Backend;

/**
 * This class only formats a string like a url key. It does not
 * generate unique keys or create url rewrites
 *
 * Class UrlKeyFormat
 *
 * @package MageModule\Core\Model\Entity\Attribute\Backend
 */
class UrlKeyFormat extends \MageModule\Core\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    private $filterManager;

    /**
     * UrlKeyFormat constructor.
     *
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\Filter\FilterManager   $filterManager
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Filter\FilterManager $filterManager
    ) {
        parent::__construct($resource);
        $this->filterManager = $filterManager;
    }

    /**
     * @param \Magento\Framework\DataObject $object
     *
     * @return \MageModule\Core\Model\Entity\Attribute\Backend\AbstractBackend
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave($object)
    {
        $attrCode = $this->getAttribute()->getName();
        if ($object->getData($attrCode)) {
            $object->setData(
                $attrCode,
                $this->filterManager->translitUrl(
                    $object->getData($attrCode)
                )
            );
        }

        $this->validate($object);

        return parent::beforeSave($object);
    }
}
