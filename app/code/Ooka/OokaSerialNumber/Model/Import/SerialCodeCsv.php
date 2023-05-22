<?php
declare(strict_types=1);

namespace Ooka\OokaSerialNumber\Model\Import;

use Exception;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\ImportExport\Helper\Data as ImportHelper;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Magento\ImportExport\Model\ResourceModel\Import\Data;
use Magento\Sales\Model\OrderFactory;

class SerialCodeCsv extends AbstractEntity
{
    public const ENTITY_CODE = 'serialnumber';

    private const TABLE = 'product_serial_code';
    private const ORDER_ID_NOT_PRESENT = 'OrderIdIsNotPresent';
    private const SKU_IS_REQUIRED = 'SkuIsRequired';
    private const SERIAL_CODE_IS_REQUIRED = 'SerialCodeIsRequired';
    private const SERIAL_CODE_IS_COLUMN = 'serial_code';
    private const CUSTOMER_EMAIL_IS_REQUIRED = 'CustomerEmailIsRequired';

    /**
     * If we should check column names
     *
     * @var bool
     */
    protected $needColumnCheck = true;
    /**
     * Need to log in import history
     *
     * @var bool
     */
    protected $logInHistory = true;
    /**
     * Permanent entity columns.
     *
     * @var string[]
     */
    protected $_permanentAttributes = [
        'order_id'
    ];

    /**
     * Valided column names
     *
     * @var string[]
     */
    protected $validColumnNames = [
        'order_id',
        'sku',
        'serial_code',
        'customer_email'
    ];

    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var ResourceConnection
     */
    private $resource;
    /**
     * @var orderFactory
     */
    protected $orderFactory;

    /**
     * @param JsonHelper $jsonHelper
     * @param ImportHelper $importExportData
     * @param Data $importData
     * @param ResourceConnection $resource
     * @param Helper $resourceHelper
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param OrderFactory $orderFactory
     */
    public function __construct(
        JsonHelper $jsonHelper,
        ImportHelper $importExportData,
        Data $importData,
        ResourceConnection $resource,
        Helper $resourceHelper,
        ProcessingErrorAggregatorInterface $errorAggregator,
        OrderFactory $orderFactory
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->_importExportData = $importExportData;
        $this->_resourceHelper = $resourceHelper;
        $this->_dataSourceModel = $importData;
        $this->connection = $resource->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $this->errorAggregator = $errorAggregator;
        $this->orderFactory = $orderFactory;
        $this->initMessageTemplates();
    }

    /**
     * Init Error Messages
     */
    private function initMessageTemplates()
    {
        $this->addMessageTemplate(
            self::ORDER_ID_NOT_PRESENT,
            __('Order id  cannot be empty.')
        );

        $this->addMessageTemplate(
            self::SKU_IS_REQUIRED,
            __('The sku cannot be empty.')
        );

        $this->addMessageTemplate(
            self::SERIAL_CODE_IS_REQUIRED,
            __('The Serial cannot be empty.')
        );

        $this->addMessageTemplate(
            self::CUSTOMER_EMAIL_IS_REQUIRED,
            __('The email cannot be empty.')
        );
    }

    /**
     * Entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return static::ENTITY_CODE;
    }

    /**
     * Import data
     *
     * @return bool
     *
     * @throws Exception
     */
    protected function _importData(): bool
    {
        if ($this->getBehavior() === Import::BEHAVIOR_APPEND) {
            $this->saveAndReplaceEntity();
        }
        return true;
    }

    /**
     * Save and replace entities
     *
     * @return void
     */
    private function saveAndReplaceEntity()
    {
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $entityList = [];
            $this->validateCsvForDuplication($bunch);
            foreach ($bunch as $rowNum => $row) {
                if ($this->getErrorAggregator()->hasToBeTerminated() || !$this->validateRow($row, $rowNum)) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }
                $serialCode = $row[static::SERIAL_CODE_IS_COLUMN];
                $columnValues = [];
                foreach ($this->getAvailableColumns() as $columnKey) {
                    $columnValues[$columnKey] = $row[$columnKey];
                    $columnValues['item_id'] = $row['item_id'] ?? null;
                }

                $entityList[$serialCode][] = $columnValues;
                ++$this->countItemsCreated;
            }
            $this->saveEntityFinish($entityList);
        }
    }

    /**
     * Validate csv data for unique serial number
     *
     * @param array $bunch
     * @return void
     */
    public function validateCsvForDuplication(&$bunch)
    {
        $qtyKeeping = [];
        foreach ($bunch as &$rowValue) {
            $order = $this->orderFactory->create()->loadByIncrementId($rowValue['order_id']);
            $orderItems = $order->getAllItems();
            foreach ($orderItems as $item) {
                if ($rowValue['sku'] === $item->getSku()) {
                    if (array_key_exists($item->getItemId(), $qtyKeeping)) {
                        if ((int)$qtyKeeping[$item->getItemId()] < (int)$item->getQtyOrdered()) {
                            $rowValue['item_id'] = $item->getItemId();
                            $qtyKeeping[$item->getItemId()] = (int)$qtyKeeping[$item->getItemId()] + 1;
                            break;
                        }
                    } else {
                        $rowValue['item_id'] = $item->getItemId();
                        $qtyKeeping[$item->getItemId()] = 1;
                        break;
                    }
                }
            }
        }
    }

    /**
     * Row validation
     *
     * @param array $rowData
     * @param int $rowNum
     *
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum): bool
    {
        $orderId = $rowData['order_id'] ?? 0;
        $sku = $rowData['sku'] ?? '';
        $serialCode = $rowData['serial_code'] ?? '';
        $customerEmail = $rowData['customer_email'] ?? '';

        if (!$orderId) {
            $this->addRowError(self::ORDER_ID_NOT_PRESENT, $rowNum);
        }

        if (!$sku) {
            $this->addRowError(self::SKU_IS_REQUIRED, $rowNum);
        }

        if (!$serialCode) {
            $this->addRowError(self::SERIAL_CODE_IS_REQUIRED, $rowNum);
        }

        if (!$customerEmail) {
            $this->addRowError(self::CUSTOMER_EMAIL_IS_REQUIRED, $rowNum);
        }

        if (isset($this->_validatedRows[$rowNum])) {
            return !$this->getErrorAggregator()->isRowInvalid($rowNum);
        }

        $this->_validatedRows[$rowNum] = true;
        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }

    /**
     * Get available columns
     *
     * @return array
     */
    private function getAvailableColumns(): array
    {
        return $this->validColumnNames;
    }

    /**
     * Save entities
     *
     * @param array $entityData
     *
     * @return bool
     */
    private function saveEntityFinish(array $entityData): bool
    {
        if ($entityData) {
            $tableName = $this->connection->getTableName(static::TABLE);
            $rows = [];

            foreach ($entityData as $entityRows) {
                foreach ($entityRows as $row) {
                    $rows[] = $row;
                }
            }
            if ($rows) {
                $this->connection->insertOnDuplicate($tableName, $rows, $this->getAvailableColumns());

                return true;
            }
            return false;
        }
    }
}
