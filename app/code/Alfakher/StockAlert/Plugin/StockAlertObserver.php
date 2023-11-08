<?php

declare(strict_types=1);

namespace Alfakher\StockAlert\Plugin;

use Alfakher\StockAlert\Observer\ProcessBackInStock;
use Exception;
use Magento\ProductAlert\Model\Observer;

class StockAlertObserver
{
    /**
     * Warning (exception) errors array
     *
     * @var array
     */
    protected $_errors = [];

    /**
     * @var ProcessBackInStock
     */
    private ProcessBackInStock $processBackInStock;

    /**
     * @param ProcessBackInStock $processBackInStock
     */
    public function __construct(
        ProcessBackInStock $processBackInStock
    ) {
        $this->processBackInStock = $processBackInStock;
    }

    /**
     * Run process for guest user stock alert
     *
     * @param Observer $subject
     * @param $proceed
     * @return $this
     * @throws Exception
     */
    public function afterProcess(Observer $subject, $proceed)
    {
        try {
            $this->processBackInStock->execute();
        } catch (Exception $e) {
            $this->_errors[] = $e->getMessage();
            throw $e;
        }

        return $this;
    }
}
