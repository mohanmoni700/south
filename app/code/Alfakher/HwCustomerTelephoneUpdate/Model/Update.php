<?php
declare (strict_types = 1);

namespace Alfakher\HwCustomerTelephoneUpdate\Model;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\File\Csv;
use Magento\Framework\App\ResourceConnection;

/**
 * Update Customer Data
 */
class Update extends Command
{
    /**
     * @var Csv
     */
    protected Csv $csv;
    /**
     * @var AdapterInterface
     */
    protected AdapterInterface $connection;

    public function __construct(
        Csv $csv,
        ResourceConnection $resourceConnection
    ) {
        $this->csv = $csv;
        $this->connection = $resourceConnection->getConnection();
        parent::__construct();
    }

    protected function configure()
    {
        /* Command - php bin/magento customer_telephone:update */
        $this->setName('customer_telephone:update')
            ->setDescription('Import data from a CSV file');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Path to the CSV file
        $csvFilePath = BP .'/var/import/customer_updated_data.csv';

        try {
            $data = $this->csv->getData($csvFilePath);

            $rowCount = count($data) - 1;
            $updatedCount = 0;

            foreach ($data as $row) {
                $email = $row[2];
                $telephone = $row[3];
                /* store_id = '8' for HW store */
                if (ctype_digit($telephone) && $this->isValidEmail($email)) {
                    $updateQuery = "
                        UPDATE customer_address_entity
                        SET telephone = '$telephone'
                        WHERE parent_id IN (
                            SELECT entity_id
                            FROM customer_entity
                            WHERE email = '$email' AND store_id = '8'
                        )
                    ";
                    $this->connection->query($updateQuery);
                    $updatedCount++;
                    $output->writeln('Telephone is updated for - '.$email);
                }
            }
            $output->writeln('Number of rows successfully updated: ' . $updatedCount . ' out of ' . $rowCount);
        } catch (\Exception $e) {
            $output->writeln('Error: ' . $e->getMessage());
        }
    }
    private function isValidEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}
