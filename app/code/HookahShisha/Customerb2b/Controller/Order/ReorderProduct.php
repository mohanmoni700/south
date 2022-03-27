<?php

namespace HookahShisha\Customerb2b\Controller\Order;

use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Sales\Model\Order\Item;

/**
 * Class Reorder given Product on the order
 */
class ReorderProduct extends \Magento\Checkout\Controller\Cart implements HttpPostActionInterface
{
    /**
     * @var OrderItemRepositoryInterface
     */
    protected $orderItem;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param CustomerCart $cart
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @param \Magento\Framework\App\Http\Context $httpContext
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        CustomerCart $cart,
        \Magento\Sales\Model\Order\Item $orderItem,
        \Magento\Framework\App\Http\Context $httpContext
    ) {
        parent::__construct($context, $scopeConfig, $checkoutSession, $storeManager, $formKeyValidator, $cart);
        $this->orderItem = $orderItem;
        $this->httpContext = $httpContext;
    }

    /**
     * @inheritdoc
     * @throws \InvalidArgumentException
     */
    public function execute()
    {
        $orderItemId = $this->getRequest()->getParam('order_item');
        if ($orderItemId) {
            $item = $this->orderItem->load($orderItemId);
            try {
                $this->addOrderItem($item);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {

                $this->messageManager->addErrorMessage($e->getMessage());

            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('We can\'t add this item to your shopping cart right now.')
                );
                return $this->_goBack();
            }
            $this->cart->save();
        } else {
            $this->messageManager->addErrorMessage(__('Something went wrong. Please try again.'));
        }

        return $this->_goBack();
    }

    /**
     * Add item to cart.
     *
     * Add item to cart only if it's belongs to customer.
     *
     * @param Item $item
     * @return void
     */
    private function addOrderItem(Item $item)
    {
        $isLoggedIn = (bool) $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
        if ($isLoggedIn) {
            $orderCustomerId = $item->getOrder()->getCustomerId();
            $currentCustomerId = $this->httpContext->getValue('customer_id');
            if ($orderCustomerId == $currentCustomerId) {
                $this->cart->addOrderItem($item);
                if (!$this->cart->getQuote()->getHasError()) {
                    $this->messageManager->addComplexSuccessMessage(
                        'addCartSuccessMessage',
                        [
                            'product_name' => $item->getName(),
                            'cart_url' => $this->getCartUrl(),
                        ]
                    );
                }
            }
        }
    }

    /**
     * Returns cart url
     *
     * @return string
     */
    private function getCartUrl()
    {
        return $this->_url->getUrl('checkout/cart', ['_secure' => true]);
    }
}
