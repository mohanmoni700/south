<?php
namespace Alfakher\ExciseReport\Block\Adminhtml\Report;

use Magento\Framework\Data\Form\FormKey;

class ExciseTax extends \Magento\Framework\View\Element\Template {

	/**
	 * @var formKey
	 */
	protected $formKey;

	/**
	 * Construct
	 *
	 * @param \Magento\Framework\View\Element\Template\Context $context
	 * @param Magento\Framework\Data\Form\FormKey $formKey
	 * @param \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteCollectionFactory
	 * @param array $data
	 */
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		FormKey $formKey,
		\Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteCollectionFactory,
		array $data = []
	) {
		$this->formKey = $formKey;
		$this->_websiteCollectionFactory = $websiteCollectionFactory;
		parent::__construct($context, $data);
	}

	/**
	 * @inheritDoc
	 */
	public function getFormKey() {
		return $this->formKey->getFormKey();
	}

	/**
	 * Retrieve websites collection of system
	 *
	 * @return Website Collection
	 */
	public function getWebsiteCollection() {
		$collection = $this->_websiteCollectionFactory->create();
		return $collection;
	}
}
