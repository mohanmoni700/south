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
    protected $errors = [];

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
     * @param $result
     * @return $this
     * @throws Exception
     */
    public function afterProcess(Observer $subject, $result)
    {
        try {
            $this->processBackInStock->execute();
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            throw $e;
        }

        return $result;
    }
}
