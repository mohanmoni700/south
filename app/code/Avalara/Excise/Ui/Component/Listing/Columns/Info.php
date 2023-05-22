<?php

namespace Avalara\Excise\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Info extends Column
{
   /**
    * @var StoreManagerInterface
    */
    private $storeManager;
 
   /**
    * @var LoggerInterface
    */
    private $logger;
 
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        array $components = [],
        array $data = []
    ) {

        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['store_id'])):
                      $item['store_id'] = $this->getStoreCodeById($item['store_id']);
                endif;
            }
        }

        return $dataSource;
    }
    
    /**
     * Get Store code by id
     *
     * @param int $id
     * @return string|null
     */
    public function getStoreCodeById(int $id): ? string
    {
        try {
            $storeData = $this->storeManager->getStore($id);
            $storeCode = (string)$storeData->getName();
        } catch (\Exception $e) {
            $storeCode = null;
            $this->logger->error($e->getMessage());
            // code to add CEP logs for exception
            try {
                $functionName = __METHOD__;
                $operationName = get_class($this);   
                // @codeCoverageIgnoreStart             
                $this->logger->logDebugMessage(
                    $functionName,
                    $operationName,
                    $e
                );
                // @codeCoverageIgnoreEnd
            } catch (\Exception $e) {
                //do nothing
            }
            // end of code to add CEP logs for exception
        }
        return $storeCode;
    }
}
