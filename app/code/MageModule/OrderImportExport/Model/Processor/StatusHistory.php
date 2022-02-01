<?php
/**
 * Copyright (c) 2019 MageModule, LLC: All rights reserved
 *
 * LICENSE: This source file is subject to our standard End User License
 * Agreeement (EULA) that is available through the world-wide-web at the
 * following URI: https://www.magemodule.com/magento2-ext-license.html.
 *
 *  If you did not receive a copy of the EULA and are unable to obtain it through
 *  the web, please send a note to admin@magemodule.com so that we can mail
 *  you a copy immediately.
 *
 * @author         MageModule admin@magemodule.com
 * @copyright      2019 MageModule, LLC
 * @license        https://www.magemodule.com/magento2-ext-license.html
 */

namespace MageModule\OrderImportExport\Model\Processor;

use Magento\Framework\DataObject;
use Magento\Sales\Api\Data\OrderStatusHistoryInterface;

class StatusHistory extends \MageModule\OrderImportExport\Model\Processor\AbstractProcessor implements
    \MageModule\OrderImportExport\Model\Processor\ProcessorInterface
{
    const KEY = 'status_histories';

    /**
     * @var \MageModule\OrderImportExport\Model\Parser\ParserInterface
     */
    private $parser;

    /**
     * @var \Magento\Sales\Api\Data\OrderStatusHistoryInterfaceFactory
     */
    private $objectFactory;

    /**
     * StatusHistory constructor.
     *
     * @param \MageModule\OrderImportExport\Model\Parser\ParserInterface $parser
     * @param \Magento\Sales\Api\Data\OrderStatusHistoryInterfaceFactory $objectFactory
     * @param array                                                      $excludedFields
     */
    public function __construct(
        \MageModule\OrderImportExport\Model\Parser\ParserInterface $parser,
        \Magento\Sales\Api\Data\OrderStatusHistoryInterfaceFactory $objectFactory,
        $excludedFields = []
    ) {
        parent::__construct($excludedFields);
        $this->parser        = $parser;
        $this->objectFactory = $objectFactory;
    }

    /**
     * @param array                                  $data
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     *
     * @return $this
     */
    public function process(array $data, \Magento\Sales\Api\Data\OrderInterface $order)
    {
        $statusHistoryString = $order instanceof DataObject ? $order->getData(self::KEY) : '';
        if ($statusHistoryString) {
            $statusHistories = $this->parser->parse($statusHistoryString);
            foreach ($statusHistories as &$statusHistory) {
                if (isset($statusHistory[OrderStatusHistoryInterface::ENTITY_ID])) {
                    unset($statusHistory[OrderStatusHistoryInterface::ENTITY_ID]);
                }

                $statusHistory = $this->objectFactory->create(['data' => $statusHistory]);
            }

            if ($statusHistories) {
                $order->setStatusHistories($statusHistories);
            }
        }

        return $this;
    }
}
