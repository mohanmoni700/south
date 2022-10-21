<?php

namespace Alfakher\CompanyImport\Model;

use Magento\Company\Api\Data\CompanyInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\Filesystem\DirectoryList;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CompanyAccount extends Command
{
    /**
     * Url path
     */
    public const NAME = 'name';

    public const WEBSITE = 'website';

    /**
     * @var csv
     */
    protected $csv;
    /**
     * @var file
     */
    protected $file;
    /**
     * @var companyRepositoryInterface
     */
    protected $companyRepositoryInterface;
    /**
     * @var state
     */
    protected $state;
    /**
     * @var companyFactory
     */
    protected $companyFactory;
    /**
     * @var groupManagement
     */
    protected $groupManagement;
    /**
     * @var customerRepository
     */
    protected $customerRepository;
    /**
     * @var logger
     */
    protected $logger;
    /**
     * @var customerDataFactory
     */
    protected $customerDataFactory;
    /**
     * @var customerAccountManagement
     */
    protected $customerAccountManagement;
    /**
     * @var storemanager
     */
    protected $storemanager;
    /**
     * @var dataObject
     */
    protected $dataObject;
    /**
     * @var companyInterface
     */
    protected $companyInterface;
    /**
     * @var filesystem
     */
    protected $filesystem;
    /**
     * @var searchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
    /**
     * @var customLogger
     */
    protected $customLogger;
    /**
     * @var companyManagement
     */
    protected $companyManagement;
    /**
     * @var companyManagementModel
     */
    protected $companyManagementModel;
    /**
     * @var companyCollectionFactory
     */
    protected $companyCollectionFactory;
    /**
     * @var customerCollectionFactory
     */
    protected $customerCollectionFactory;
    /**
     * @var registry
     */
    protected $registry;
    /**
     * @var newfileSystem
     */
    protected $newfileSystem;

    /**
     * @var int
     */
    protected $successCounter = 0;

    /**
     *
     * @param \Magento\Framework\File\Csv $csv
     * @param \Magento\Framework\Filesystem\Io\File $file
     * @param \Magento\Company\Api\CompanyRepositoryInterface $companyRepositoryInterface
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory
     * @param \Magento\Customer\Api\AccountManagementInterface $customerAccountManagement
     * @param \Magento\Company\Api\Data\CompanyInterfaceFactory $companyFactory
     * @param \Magento\Customer\Api\GroupManagementInterface $groupManagement
     * @param \Magento\Store\Model\StoreManagerInterface $storemanager
     * @param \Magento\Framework\Api\DataObjectHelper $dataObject
     * @param CompanyInterface $companyInterface
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Alfakher\CompanyImport\Logger\Logger $customLogger
     * @param \Magento\Company\Api\CompanyManagementInterface $companyManagement
     * @param \Magento\Company\Model\CompanyManagement $companyManagementModel
     * @param \Magento\Company\Model\ResourceModel\Company\CollectionFactory $companyCollectionFactory
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Filesystem\Driver\File $newfileSystem
     */

    public function __construct(
        \Magento\Framework\File\Csv $csv,
        \Magento\Framework\Filesystem\Io\File $file,
        \Magento\Company\Api\CompanyRepositoryInterface $companyRepositoryInterface,
        \Magento\Framework\App\State $state,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory,
        \Magento\Customer\Api\AccountManagementInterface $customerAccountManagement,
        \Magento\Company\Api\Data\CompanyInterfaceFactory $companyFactory,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        \Magento\Store\Model\StoreManagerInterface $storemanager,
        \Magento\Framework\Api\DataObjectHelper $dataObject,
        CompanyInterface $companyInterface,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Alfakher\CompanyImport\Logger\Logger $customLogger,
        \Magento\Company\Api\CompanyManagementInterface $companyManagement,
        \Magento\Company\Model\CompanyManagement $companyManagementModel,
        \Magento\Company\Model\ResourceModel\Company\CollectionFactory $companyCollectionFactory,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Filesystem\Driver\File $newfileSystem
    ) {
        $this->csv = $csv;
        $this->file = $file;
        $this->companyRepositoryInterface = $companyRepositoryInterface;
        $this->state = $state;
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
        $this->customerDataFactory = $customerDataFactory;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->companyFactory = $companyFactory;
        $this->groupManagement = $groupManagement;
        $this->_storemanager = $storemanager;
        $this->dataObject = $dataObject;
        $this->companyInterface = $companyInterface;
        $this->_filesystem = $filesystem;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->customLogger = $customLogger;
        $this->companyManagement = $companyManagement;
        $this->companyManagementModel = $companyManagementModel;
        $this->companyCollectionFactory = $companyCollectionFactory;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->registry = $registry;
        $this->newfileSystem = $newfileSystem;
        $this->registry->register('isSecureArea', true);
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('alfakher:create:company:account')
            ->setDescription('Alfakher Company Account Create Command');
        $this->addOption(
            self::NAME,
            null,
            InputOption::VALUE_REQUIRED,
            'File Name'
        );
        $this->addOption(
            self::WEBSITE,
            null,
            InputOption::VALUE_REQUIRED,
            'Web Site'
        );
        parent::configure();
    }

    /**
     * Invoice Capture change process
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(Area::AREA_ADMINHTML);
        $filename = $input->getOption(self::NAME);
        $optionWebsiteId =  $input->getOption(self::WEBSITE);
        if (!$filename && !$optionWebsiteId) {
            return $output->writeln('<error>The name param is missing.
                Use command alfakher:create:company:account --name "xyz.csv" -- --website "8" </error>');
        }

        $ext = $this->file->getPathInfo($filename, PATHINFO_EXTENSION);
        $allowedfile = ['csv'];

        if ($filename && !in_array($ext['extension'], $allowedfile)) {
            $output->writeln('<error>CSV file allow only.</error>');
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
        $data = [];
        $mediapath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        $csvFile = $mediapath . $filename;

        try {
            if ($this->file->fileExists($csvFile)) {
                $allRows = [];
                $handle = $this->newfileSystem->fileOpen($csvFile, "r");
                $header = $this->newfileSystem->fileGetCsv($handle);

                while ($row = $this->newfileSystem->fileGetCsv($handle)) {
                    $allRows[] = array_combine($header, $row);
                }
                if (count($allRows) > 0) {
                    $this->successCounter = 1;
                    $this->customLogger->info('START IMPORT :');
                    foreach ($allRows as $key => $value) {
                        $companyData = [];
                        //$customerData = [];
                        $customerData = [
                            'email' => $value['email'],
                            'firstname' => $value['firstname'],
                            'lastname' => $value['lastname'],
                        ];

                        $websiteID = $this->_storemanager->getStore()->getWebsiteId();
                        $isEmailNotExists = $this->customerAccountManagement
                            ->isEmailAvailable($value['email'], $optionWebsiteId);

                        if ($isEmailNotExists) {
                            $customer = $this->customerDataFactory->create();
                            $this->dataObject->populateWithArray(
                                $customer,
                                $customerData,
                                \Magento\Customer\Api\Data\CustomerInterface::class
                            );
                            $customer = $this->customerAccountManagement->createAccount($customer);
                        } else {
                            $customer = $this->customerRepository->get($value['email'], $optionWebsiteId);
                        }

                        //skip if customer has already company assigned
                        $customer_company = $this->companyManagement->getByCustomerId($customer->getId());
                        $this->companyAssigned($customer_company, $value, $customer, $output);
                    }
                }
                return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
            } else {
                $output->writeln('<error>CSV file is not exist.</error>');
                return \Magento\Framework\Console\Cli::RETURN_FAILURE;
            }
        } catch (FileSystemException $e) {
            $output->writeln('<error>' . $e->getMessage() . '.</error>');
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
    }

    /**
     * Company assigned function
     *
     * @param \Magento\Company\Api\CompanyManagementInterface $customer_company
     * @param array|null $value
     * @param \Magento\Customer\Api\Data\CustomerInterfaceFactory $customer
     * @param OutputInterface $output
     */
    private function companyAssigned($customer_company, $value, $customer, $output)
    {
        if (!$customer_company && empty($customer_company)) {
            $checkCompanyExists = $this->isCompanyEmailAvailable($value['email']);
            if (!$checkCompanyExists) {
                $companyData = [
                    'status' => 1,
                    "company_name" => $value['company_name'],
                    "company_email" => $customer->getEmail(),
                    "super_user_id" => $customer->getId(),
                    "customer_group_id" => $customer->getGroupId(),
                    'vat_tax_id' => $value['vat_tax_id'],
                    'tobacco_permit_number' => $value['tobbaco_permit_number'],
                ];
                $companyFactoryCreate = $this->companyFactory->create();
                $this->dataObject->populateWithArray(
                    $companyFactoryCreate,
                    $companyData,
                    CompanyInterface::class
                );
                $this->companyRepositoryInterface->save($companyFactoryCreate);
                $output->writeln('<info>' . $this->successCounter . ' - ' .
                    $customer->getEmail() . ' account created successfully.</info>');
                $this->customLogger->info('IMPORTED : ' . $customer->getEmail());
                $this->successCounter++;
            } else {
                $output->writeln('<error>Company is already
                    exists with email so not assigned
                    to customer : ' . $value['email'] . '</error>');
                $this->customLogger->info('Company is
                        already exists with email so not assigned to customer : ' . $value['email']);
            }
        } else {
            $output->writeln('<error>Company is already
                assigned to customer : ' . $value['email'] . '</error>');
            $this->customLogger->info('Company is already assigned to customer : ' . $value['email']);
        }
    }

    /**
     * Check if there are no companies with this email.
     *
     * @param  string $email
     */
    private function isCompanyEmailAvailable($email)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(CompanyInterface::COMPANY_EMAIL, $email)
            ->create();
        return $this->companyRepositoryInterface->getList($searchCriteria)->getTotalCount();
    }

    /**
     * Load company by with this email.
     *
     * @param  string $email
     */
    private function loadCompanyByEmail($email)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(CompanyInterface::COMPANY_EMAIL, $email)
            ->create();
        return $this->companyRepositoryInterface->getList($searchCriteria)->getItems();
    }

    /**
     * Assign company to customer.
     *
     * @param  int $companyId
     * @param  int $customerId
     */
    public function assignCustomerToCompany($companyId, $customerId)
    {
        $this->companyManagementModel->assignCustomer($companyId, $customerId);
    }

    /**
     * Remove all companies.
     *
     * @param string $output
     */
    public function removeCompanies($output)
    {
        $companyCollection = $this->companyCollectionFactory->create();
        if ($companyCollection && !empty($companyCollection)) {
            foreach ($companyCollection->getItems() as $item) {
                $item->delete();
                $output->writeln('<info> Company removed : ' . $item->getCompanyEmail() . '</info>');
            }
        }
    }

    /**
     * Remove all customers.
     *
     * @param string $output
     */
    public function removeCustomers($output)
    {
        $customerCollection = $this->customerCollectionFactory->create();
        if ($customerCollection && !empty($customerCollection)) {
            foreach ($customerCollection->getItems() as $item) {
                $item->delete();
                $output->writeln('<info> Customer removed : ' . $item->getEmail() . '</info>');
            }
        }
    }
}
