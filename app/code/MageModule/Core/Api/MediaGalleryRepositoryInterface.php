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

namespace MageModule\Core\Api;

interface MediaGalleryRepositoryInterface
{
    /**
     * @param int $id
     *
     * @return \MageModule\Core\Api\Data\MediaGalleryInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($id);

    /**
     * @param \MageModule\Core\Api\Data\MediaGalleryInterface $object
     *
     * @return \MageModule\Core\Api\Data\MediaGalleryInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\MageModule\Core\Api\Data\MediaGalleryInterface $object);

    /**
     * @param \MageModule\Core\Api\Data\MediaGalleryInterface $object
     *
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\MageModule\Core\Api\Data\MediaGalleryInterface $object);

    /**
     * @param int $id
     *
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteById($id);

    /**
     * @param int $entityId
     *
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteByEntityId($entityId);

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     *
     * @return \Magento\Framework\Api\SearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria);
}
