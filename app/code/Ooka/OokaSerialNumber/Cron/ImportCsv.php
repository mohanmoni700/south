<?php
declare(strict_types=1);

namespace Ooka\OokaSerialNumber\Cron;

use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Ooka\OokaSerialNumber\Api\Data\SerialNumberInterface;
use Ooka\OokaSerialNumber\Api\Data\SerialNumberInterfaceFactory;
use Ooka\OokaSerialNumber\Api\SerialNumberRepositoryInterface;

class ImportCsv
{
    private const SWFTBOX_IMPORTS_SERIALCODE_PATH = '/swftbox_imports/serial_numer';
    private const ARCHIEVE_PATH = '/archieve';
    /**
     * @var Filesystem
     */
    protected Filesystem $filesystem;
    /**
     * @var File
     */
    protected File $file;
    /**
     * @var TimezoneInterface
     */
    protected TimezoneInterface $timezone;
    /**
     * @var string[]
     */
    private array $validColumns = ['order_id', 'sku', 'serial_code', 'customer_email'];
    /**
     * @var Filesystem\Driver\File
     */
    private Filesystem\Driver\File $driverFile;
    /**
     * @var Csv
     */
    private Csv $csvParser;
    /**
     * @var SerialNumberInterface
     */
    private SerialNumberInterface $serialNumber;
    /**
     * @var SerialNumberInterfaceFactory
     */
    private SerialNumberInterfaceFactory $serialNumberFactory;
    /**
     * @var SerialNumberRepositoryInterface
     */
    private SerialNumberRepositoryInterface $serialNumberRepository;

    /**
     * @param Filesystem $filesystem
     * @param File $file
     * @param Filesystem\Driver\File $driverFile
     * @param Csv $csvParser
     * @param SerialNumberInterface $serialNumber
     * @param SerialNumberInterfaceFactory $serialNumberFactory
     * @param SerialNumberRepositoryInterface $serialNumberRepository
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        Filesystem $filesystem,
        File $file,
        Filesystem\Driver\File $driverFile,
        Csv $csvParser,
        SerialNumberInterface $serialNumber,
        SerialNumberInterfaceFactory $serialNumberFactory,
        SerialNumberRepositoryInterface $serialNumberRepository,
        TimezoneInterface $timezone
    ) {
        $this->filesystem = $filesystem;
        $this->file = $file;
        $this->driverFile = $driverFile;
        $this->csvParser = $csvParser;
        $this->serialNumber = $serialNumber;
        $this->serialNumberFactory = $serialNumberFactory;
        $this->serialNumberRepository = $serialNumberRepository;
        $this->timezone = $timezone;
    }

    /**
     * Method for import the csv file through cron
     *
     * @return void
     */
    public function execute()
    {
        try {
            $absolutePath = $this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR)
                ->getAbsolutePath(self::SWFTBOX_IMPORTS_SERIALCODE_PATH);
            $csvFiles = $this->driverFile->readDirectoryRecursively($absolutePath);
            $csvFiles = $this->removeJunkFile($csvFiles);
            foreach ($csvFiles as $file) {
                $this->readCSVFile($file);
                $this->moveFileToArchive($file, $absolutePath);
            }
        } catch (Exception $exception) {
            return;
        }
    }

    /**
     * Remove junk files from swftbox_imports folder
     *
     * @param array $csvFiles
     * @return array
     */
    public function removeJunkFile(array $csvFiles): array
    {
        $finalUpdatedFiles = [];
        foreach ($csvFiles as $file) {
            $explodeFile = explode("/", $file);
            if (is_array($explodeFile) &&
                preg_match('/^[a-zA-Z0-9_.-]+\.csv$/', end($explodeFile))) {
                $finalUpdatedFiles [] = $file;
            }
        }
        return $finalUpdatedFiles;
    }

    /**
     * Read csv file data
     *
     * @param string $absoluteFilePath
     * @return void
     * @throws FileSystemException
     */
    public function readCSVFile(string $absoluteFilePath)
    {
        if ($this->driverFile->isExists($absoluteFilePath)) {
            $this->csvParser->setDelimiter(',');
            $fileContent = $this->csvParser->getData($absoluteFilePath);
            if (!empty($fileContent) &&
                $this->validateCSVHeader(reset($fileContent))) {
                array_shift($fileContent);
                foreach ($fileContent as $file) {
                    $this->saveSerialCodeEntity(array_combine($this->validColumns, $file));
                }
            }
        }
    }

    /**
     * Validate csv header data
     *
     * @param array $header
     * @return bool
     */
    public function validateCSVHeader(array $header): bool
    {
        $header = implode(",", $header);
        $validColumn = implode(",", $this->validColumns);
        if (trim($header) === trim($validColumn)) {
            return true;
        }
        return false;
    }

    /**
     * Saving csv file data to table
     *
     * @param array $entity
     * @return bool|void
     */
    public function saveSerialCodeEntity(array $entity)
    {
        try {
            if (!empty(
                $entity['order_id'] && $entity['sku'] &&
                !empty($entity['serial_code']) &&
                !empty($entity['customer_email'])
            )) {
                /**
                 * @var SerialNumberInterface $serialCode
                 */
                $serialCode = $this->serialNumberFactory->create();
                $serialCode->setOrderId((int)$entity['order_id']);
                $serialCode->setSku($entity['sku']);
                $serialCode->setCustomerEmail($entity['customer_email']);
                $serialCode->setSerialCode($entity['serial_code']);
                $this->serialNumberRepository->save($serialCode);
                return true;
            }
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * Move file to archieve folder
     *
     * @param string $filePath
     * @param string $absolutePath
     * @return false|void
     */
    public function moveFileToArchive(string $filePath, string $absolutePath)
    {
        try {
            if (!$this->driverFile->isExists($absolutePath . self::ARCHIEVE_PATH)) {
                $this->driverFile->createDirectory($absolutePath . self::ARCHIEVE_PATH);
            }
            $this->file->mv(
                $filePath,
                $absolutePath . self::ARCHIEVE_PATH . '/' . $this->timezone->scopeTimeStamp() . rand(10, 100) . '.csv'
            );
        } catch (Exception $exception) {
            return false;
        }
    }
}
