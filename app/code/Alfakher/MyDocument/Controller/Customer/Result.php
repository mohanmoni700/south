<?php

namespace Alfakher\MyDocument\Controller\Customer;

use Alfakher\MyDocument\Model\MyDocumentFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;

class Result extends Action {
	protected $_myDocument;
	protected $resultRedirect;

	/**
	 * @var UploaderFactory
	 */
	protected $uploaderFactory;

	/**
	 * @var AdapterFactory
	 */
	protected $adapterFactory;

	protected $customerSession;
	/**
	 * @var Filesystem
	 */
	protected $filesystem;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Alfakher\MyDocument\Model\MyDocumentFactory $myDocument,
		\Magento\Framework\Controller\ResultFactory $result,
		\Magento\Customer\Model\Session $customerSession,
		UploaderFactory $uploaderFactory,
		AdapterFactory $adapterFactory,
		Filesystem $filesystem,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
	) {
		parent::__construct($context);
		$this->_myDocument = $myDocument;
		$this->resultRedirect = $result;
		$this->uploaderFactory = $uploaderFactory;
		$this->adapterFactory = $adapterFactory;
		$this->filesystem = $filesystem;
		$this->customerSession = $customerSession;
		$this->resultJsonFactory = $resultJsonFactory;
	}
	public function execute() {
		$post = $this->getRequest()->getPostValue();
		/*echo "<pre>";
		print_r($post);*/
		$name = $post['name'];
		$newArray = array();

		foreach ($post['name'] as $key => $value) {
			$newArray[$key]['name'] = $value;

		}
		foreach ($post['expiry_date'] as $key => $value) {

			$expiryDate = date('Y-m-d', strtotime($value));
			$newArray[$key]['expiry_date'] = $expiryDate;

		}

		$filesData = $this->getRequest()->getFiles('filename');
		/*print_r($filesData);*/

		if (count($filesData)) {
			$i = 0;
			foreach ($filesData as $files) {

				if (isset($files['tmp_name']) && strlen($files['tmp_name']) > 0) {

					try {

						$uploaderFactories = $this->uploaderFactory->create(['fileId' => $filesData[$i]]);

						$uploaderFactories->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png', 'pdf']);

						$imageAdapter = $this->adapterFactory->create();

						$uploaderFactories->addValidateCallback('custom_image_upload', $uploaderFactories, 'validateUploadFile');

						// allow folder creation
						$uploaderFactories->setAllowCreateFolders(true);
						$maxsize = 20;
						/*number_format($_FILES['filename']['size'] / 1048576, 2) . ' MB';*/
						if ((number_format($files['size'] / 1048576, 2) >= $maxsize)) {
							exit('File too large. File must be less than 20 megabytes.');
						}

						// rename file name if already exists
						$uploaderFactories->setAllowRenameFiles(true);
						$uploaderFactories->setFilesDispersion(false);

						$mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
						$destinationPath = $mediaDirectory->getAbsolutePath('myDocument');

						$result = $uploaderFactories->save($destinationPath);
						if (!$result) {
							throw new LocalizedException(
								__('File cannot be saved to path: $1', $destinationPath)
							);
						}

						$imagePath = $result['file'];
						$data['filename'] = $imagePath;
					} catch (\Exception $e) {
						$this->messageManager->addError(__($e->getMessage()));
					}

				}

				/*echo $newArray[$i]['name'];*/
				if (isset($newArray[$i]['expiry_date'])) {
					$expirydate = $newArray[$i]['expiry_date'];
				} else {
					$expirydate = '';
				}

				$resultRedirect = $this->resultRedirect->create(ResultFactory::TYPE_REDIRECT);
				$resultRedirect->setUrl($this->_redirect->getRefererUrl());
				$model = $this->_myDocument->create();
				$model->setData($data);

				$model->addData([
					"document_name" => $newArray[$i]['name'],
					"customer_id" => $this->customerSession->getCustomer()->getId(),
					"expiry_date" => $expirydate,
				]);

				$saveData = $model->save();
				$i++;
			}

		}

		/*if ($saveData) {
				$this->messageManager->addSuccess(__('Insert Record Successfully !'));
				$res = "1";
			} else {
				$res = false;
			}
			echo json_encode($res);
		*/
		$resultJson = $this->resultJsonFactory->create();
		if ($saveData) {
			$htmlContent = "Insert Record Successfully !";
			$success = true;
		} else {
			$success = false;
		}
		return $resultJson->setData([
			'html' => $htmlContent,
			'success' => $success]);
	}
}
