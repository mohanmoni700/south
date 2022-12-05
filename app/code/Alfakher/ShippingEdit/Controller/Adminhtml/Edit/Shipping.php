<?php
declare(strict_types=1);

namespace Alfakher\ShippingEdit\Controller\Adminhtml\Edit;

use Magento\Framework\Exception\LocalizedException;
use MageWorx\OrderEditor\Controller\Adminhtml\AbstractAction;
use Magento\Quote\Api\CartRepositoryInterface;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\RawFactory;
use MageWorx\OrderEditor\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use MageWorx\OrderEditor\Api\QuoteRepositoryInterface;
use MageWorx\OrderEditor\Model\Shipping as ShippingModel;
use MageWorx\OrderEditor\Model\Payment as PaymentModel;
use MageWorx\OrderEditor\Api\OrderRepositoryInterface;
use MageWorx\OrderEditor\Model\MsiStatusManager;
use MageWorx\OrderEditor\Model\InventoryDetectionStatusManager;
use Magento\Framework\Serialize\Serializer\Json as SerializerJson;
use Magento\Framework\Exception\InputException;

/**
 * Class shipping method
 */
class Shipping extends \MageWorx\OrderEditor\Controller\Adminhtml\Edit\Shipping
{
    public const ADMIN_RESOURCE = 'MageWorx_OrderEditor::edit_shipping';

    /**
     * Body constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param RawFactory $resultRawFactory
     * @param Data $helper
     * @param ScopeConfigInterface $scopeConfig
     * @param QuoteRepositoryInterface $quoteRepository
     * @param ShippingModel $shipping
     * @param PaymentModel $payment
     * @param OrderRepositoryInterface $orderRepository
     * @param MsiStatusManager $msiStatusManager
     * @param InventoryDetectionStatusManager $inventoryDetectionStatusManager
     * @param SerializerJson $serializer
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        RawFactory $resultRawFactory,
        Data $helper,
        ScopeConfigInterface $scopeConfig,
        QuoteRepositoryInterface $quoteRepository,
        ShippingModel $shipping,
        PaymentModel $payment,
        OrderRepositoryInterface $orderRepository,
        MsiStatusManager $msiStatusManager,
        InventoryDetectionStatusManager $inventoryDetectionStatusManager,
        SerializerJson $serializer,
        CartRepositoryInterface $cartRepository
    ) {
        parent::__construct(
            $context,
            $resultPageFactory,
            $resultRawFactory,
            $helper,
            $scopeConfig,
            $quoteRepository,
            $shipping,
            $payment,
            $orderRepository,
            $msiStatusManager,
            $inventoryDetectionStatusManager,
            $serializer
        );
        $this->cartRepository = $cartRepository;
    }

    /**
     * Update method
     *
     * @return void
     */
    protected function update()
    {
        $this->updateShippingMethod();
    }

    /**
     * Shipping method update
     *
     * @return void
     * @throws \Exception
     */
    protected function updateShippingMethod()
    {
        /* bv_mp; date : 06-09-22; save quote after edit shipping method*/
        $order = $this->getOrder();
        $quote = $this->cartRepository->get($order->getQuoteId());
        $quote->collectTotals()->save();
        /* bv_mp; date : 06-09-22; save quote after edit shipping method */

        $params = $this->prepareParams();
        $this->shipping->initParams($params);

        $this->shipping->updateShippingMethod();
    }

    /**
     * Response
     *
     * @return string
     */
    protected function prepareResponse(): string
    {
        return static::ACTION_RELOAD_PAGE;
    }

    /**
     * Collect params from the request to the array.
     *
     * Contains validation.
     *
     * @return array
     * @throws LocalizedException
     */
    protected function prepareParams(): array
    {
        $params         = [];
        $paramsToUpdate = [
            'shipping_method',
            'order_id',
            'price_excl_tax',
            'price_incl_tax',
            'tax_percent',
            'description',
            'discount_amount',
            'tax_rates'
        ];

        foreach ($paramsToUpdate as $paramToUpdate) {
            $val = $this->getRequest()->getParam($paramToUpdate);
            if ($val == null) {
                if ($paramToUpdate === 'tax_rates') {
                    $val = [];
                } else {
                    throw new LocalizedException(__('Empty param %1', $paramToUpdate));
                }
            }
            $params[$paramToUpdate] = $val;
        }

        return $params;
    }
}
