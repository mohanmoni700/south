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

namespace MageModule\Core\Ui\Component\Listing;

/**
 * Class Columns
 *
 * @package MageModule\Core\Ui\Component\Listing
 */
class Columns extends \Magento\Ui\Component\Listing\Columns
{
    const DEFAULT_COLUMNS_MAX_ORDER = 100;

    /**
     * @var \MageModule\Core\Ui\Component\Listing\Attribute\RepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var \MageModule\Core\Ui\Component\Listing\ColumnFactory
     */
    private $columnFactory;

    /**
     * @var array
     */
    protected $filterMap = [
        'default'     => 'text',
        'select'      => 'select',
        'boolean'     => 'select',
        'multiselect' => 'select',
        'date'        => 'dateRange',
        'checkbox'    => 'select'
    ];

    /**
     * Columns constructor.
     *
     * @param \MageModule\Core\Ui\Component\Listing\Attribute\RepositoryInterface $attributeRepository
     * @param \MageModule\Core\Ui\Component\Listing\ColumnFactory                 $columnFactory
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface        $context
     * @param array                                                               $components
     * @param array                                                               $data
     */
    public function __construct(
        \MageModule\Core\Ui\Component\Listing\Attribute\RepositoryInterface $attributeRepository,
        \MageModule\Core\Ui\Component\Listing\ColumnFactory $columnFactory,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        $components = [],
        array $data = []
    ) {
        parent::__construct(
            $context,
            $components,
            $data
        );

        $this->attributeRepository = $attributeRepository;
        $this->columnFactory       = $columnFactory;
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepare()
    {
        $columnSortOrder = self::DEFAULT_COLUMNS_MAX_ORDER;
        $attributes      = $this->attributeRepository->getList();

        /** @var \MageModule\Core\Api\Data\AttributeInterface $attribute */
        foreach ($attributes->getItems() as $attribute) {
            $config = [];
            if (!isset($this->components[$attribute->getAttributeCode()])) {
                $config['sortOrder'] = ++$columnSortOrder;
                if ($attribute->getIsFilterableInGrid()) {
                    $config['filter'] = $this->getFilterType($attribute->getFrontendInput());
                }
                $column = $this->columnFactory->create($attribute, $this->getContext(), $config);
                $column->prepare();
                $this->addComponent($attribute->getAttributeCode(), $column);
            }
        }
        parent::prepare();
    }

    /**
     * Retrieve filter type by $frontendInput
     *
     * @param string $frontendInput
     *
     * @return string
     */
    protected function getFilterType($frontendInput)
    {
        return isset($this->filterMap[$frontendInput]) ?
            $this->filterMap[$frontendInput] :
            $this->filterMap['default'];
    }
}
