<?php
namespace HookahShisha\Bundle\Plugin;

use Magento\Framework\App\Request\Http;
use Magento\Rma\Model\ItemFactory;
use Magento\Sales\Api\OrderItemRepositoryInterface;

class RmaAddData
{

    /**
     * @var OrderItemRepositoryInterface
     */
    protected $orderItemRepository;

    /**
     * @var itemLoader
     */
    protected $itemLoader;

    /**
     * @var request
     */
    protected $request;

    /**
     * Item constructor.
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param ItemFactory $itemLoader
     */
    public function __construct(
        OrderItemRepositoryInterface $orderItemRepository,
        ItemFactory $itemLoader,
        Http $request
    ) {
        $this->orderItemRepository = $orderItemRepository;
        $this->itemLoader = $itemLoader;
        $this->request = $request;
    }

    public function afterExecute(
        \Magento\Rma\Controller\Adminhtml\Rma\SaveNew $subject,
        $result
    ) {
        $value = $this->request->getPostValue();
        foreach ($value['items'] as $key => $item) {
            $orderItemId = $item['order_item_id'];
            $orderItem = $this->orderItemRepository->get($orderItemId);
            $itemData = $this->itemLoader->create()->load($key);
            $itemData->setAlfaIsBundle($orderItem->getAlfaIsBundle());
            $itemData->save();
        }

        return $result;
    }
}
