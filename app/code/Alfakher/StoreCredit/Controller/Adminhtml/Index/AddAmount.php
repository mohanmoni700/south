<?php
namespace Alfakher\StoreCredit\Controller\Adminhtml\Index;

class AddAmount extends \Magento\Backend\App\Action
{
    /**
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_pageFactory;
    /**
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    /**
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;
    /**
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;
    /**
     *
     * @var \Magento\CustomerBalance\Model\BalanceFactory
     */
    protected $balanceFactory;
    /**
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     *
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $backendQuoteSession;

    /**
     * AddAmount constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\CustomerBalance\Model\BalanceFactory $balanceFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Backend\Model\Session\Quote $backendQuoteSession
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\CustomerBalance\Model\BalanceFactory $balanceFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Backend\Model\Session\Quote $backendQuoteSession
    ) {
        $this->_pageFactory = $pageFactory;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->balanceFactory = $balanceFactory;
        $this->storeManager = $storeManager;
        $this->backendQuoteSession = $backendQuoteSession;
        return parent::__construct($context);
    }
    /**
     * Default Method
     *
     * @return json
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $quoteId = $this->backendQuoteSession->getQuote()->getId();
        $storeCreditAmount = $this->getRequest()->getParam('storeCreditAmount');
        $storeCreditType = $this->getRequest()->getParam('storeCreditType');
        $quote = $this->quoteRepository->get($quoteId);
        $quote->setStorecreditType($storeCreditType);
        $quote->setStorecreditPartialAmount($storeCreditAmount);
        $quote->save();
        if ($quote->getCustomer()->getId()) {
            $store = $this->storeManager->getStore($quote->getStoreId());
            $baseBalance = $this->balanceFactory->create()->setCustomer(
                $quote->getCustomer()
            )->setCustomerId(
                $quote->getCustomer()->getId()
            )->setWebsiteId(
                $store->getWebsiteId()
            )->loadByCustomer()->getAmount();
        }
        $shippingChanges = $quote->getShippingAddress()->getShippingAmount();
        $quoteTotal = $quote->getBaseSubTotal() + $shippingChanges;

        if ($storeCreditAmount > $baseBalance) {
            return $resultJson->setData([
                'success' => false
            ]);
        } else {
            return $resultJson->setData([
             'success' => true
            ]);
        }
    }
}
