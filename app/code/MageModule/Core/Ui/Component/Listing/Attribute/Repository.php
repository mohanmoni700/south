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

namespace MageModule\Core\Ui\Component\Listing\Attribute;

use MageModule\Core\Api\Data\AttributeInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchResultsInterface;

class Repository implements \MageModule\Core\Ui\Component\Listing\Attribute\RepositoryInterface
{
    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var string
     */
    private $entityTypeCode;

    /**
     * Repository constructor.
     *
     * @param AttributeRepositoryInterface $attributeRepository
     * @param SearchCriteriaBuilder        $searchCriteriaBuilder
     * @param string                       $entityTypeCode
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        $entityTypeCode
    ) {
        $this->attributeRepository   = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->entityTypeCode        = $entityTypeCode;
    }

    /**
     * @return SearchResultsInterface
     */
    public function getList()
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(AttributeInterface::IS_USED_IN_GRID, 1)
            ->create();

        return $this->attributeRepository->getList($this->entityTypeCode, $searchCriteria);
    }
}
