<?php
declare (strict_types = 1);

namespace Ooka\OokaSerialNumber\Controller\Adminhtml\Serialnumber;

use Ooka\OokaSerialNumber\Api\SerialNumberRepositoryInterface;
use Ooka\OokaSerialNumber\Model\ResourceModel\SerialNumber\Collection;
use Ooka\OokaSerialNumber\Ui\DataProvider\SerialNumber\Form\SerialNumberDataProvider;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class Save extends Action
{
    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;
    /**
     * @var SerialNumberRepositoryInterface
     */
    private $serialNumberRepository;
    /**
     * @var Collection
     */
    private $collection;

    /**
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param SerialNumberRepositoryInterface $serialNumberRepository
     * @param SerialNumberDataProvider $dataProvider
     * @param Collection $collection
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        SerialNumberRepositoryInterface $serialNumberRepository,
        SerialNumberDataProvider $dataProvider,
        Collection $collection
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->dataProvider = $dataProvider;
        parent::__construct($context);
        $this->serialNumberRepository = $serialNumberRepository;
        $this->collection = $collection;
    }

    /**
     * Method to save the data in edit form
     *
     * @return ResponseInterface|Redirect|(Redirect&ResultInterface)|ResultInterface
     */
    public function execute()
    {
        /** Getting Data From  Form Field through Url */
        $id = $this->getRequest()->getParam('id');
        $orderId = $this->getRequest()->getParam('order_id');
        $sku = $this->getRequest()->getParam('sku');
        $serialCode = $this->getRequest()->getParam('serial_code');
        $customerEmail = $this->getRequest()->getParam('customer_email');

        /** variable for setting the url */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $serialNumber = $this->serialNumberRepository->load($id);
        $serialNumber->setOrderId((int)$orderId);
        $serialNumber->setSku($sku);
        $serialNumber->setSerialCode($serialCode);
        $serialNumber->setCustomerEmail($customerEmail);
        /** Saving back to database */
        $this->serialNumberRepository->save($serialNumber);
        $this->messageManager->addSuccessMessage('User  successfully save');
        $resultRedirect->setUrl($this->getUrl('serialnumber/serialnumber/index'));
        return $resultRedirect;
    }
}
