<?php

namespace Avalara\Excise\Block\Adminhtml\Log;

use Avalara\Excise\Model\ResourceModel\Log\CollectionFactory;
use Avalara\Excise\Model\ResourceModel\Log\Collection;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;

class Summary extends Template
{
    /**
     * @var CollectionFactory
     */
    protected $logCollectionFactory;

    /**
     * @var Collection
     */
    protected $logCollection;

    /**
     * @var array
     */
    protected $summaryData;

    /**
     * Summary constructor.
     * @param Context $context
     * @param CollectionFactory $logCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $logCollectionFactory,
        array $data = []
    ) {
        $this->logCollectionFactory = $logCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return Collection
     */
    protected function getLogCollection()
    {
        // Initialize the log collection
        if ($this->logCollection == null) {
            $this->logCollection = $this->logCollectionFactory->create();
        }
        return $this->logCollection;
    }

    /**
     * @return array
     */
    public function getSummaryData()
    {
        // Initialize the summary data
        if ($this->summaryData == null) {
            $this->summaryData = $this->getLogCollection()->getLevelSummaryCount();
        }
        return $this->summaryData;
    }
}
