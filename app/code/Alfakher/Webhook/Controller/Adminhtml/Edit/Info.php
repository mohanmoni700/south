<?php

namespace Alfakher\Webhook\Controller\Adminhtml\Edit;

use MageWorx\OrderEditor\Api\ChangeLoggerInterface;

class Info extends \MageWorx\OrderEditor\Controller\Adminhtml\Edit\Info
{
    /**
     * @inheritDoc
     */
    protected function update()
    {
        $order    = $this->loadOrder();
        $params   = $this->getRequest()->getParams();
        $infoData = !empty($params['order']['info']) ? $params['order']['info'] : [];
        $changeLog = [];
        if (isset($infoData['created_at'])) {
            $createdAt = $this->localeDate->date(
                $infoData['created_at'],
                null,
                false
            );
            $newCreatedAt = $this->localeDate->scopeDate($order->getStoreId());
            $newCreatedAt->setDate(
                $createdAt->format('Y'),
                $createdAt->format('m'),
                $createdAt->format('d')
            );
            $newCreatedAt->setTime(
                $createdAt->format('H'),
                $createdAt->format('i'),
                $createdAt->format('s')
            );
            $newCreatedAt = $newCreatedAt->setTimezone(new \DateTimeZone('UTC'));
            $infoData['created_at'] = $newCreatedAt->format('U');
            $changeLog[] = $this->logMessageFactory->create(
                [
                    'message' => __(
                        'Order Date has been changed from %1 to %2',
                        $order->getCreatedAt(),
                        $newCreatedAt->format('Y-m-d H:i:s')
                    ),
                    'level' => 0
                ]
            );
        }

        if ($infoData['status'] != $order->getStatus()) {
            $changeLog[] = $this->logMessageFactory->create(
                [
                    'message' => __(
                        'Order Status has been changed from %1 to %2',
                        ucwords($order->getStatus()),
                        ucwords($infoData['status'])
                    ),
                    'level' => 0
                ]
            );
        }

        if ($infoData['state'] != $order->getState()) {
            $changeLog[] = $this->logMessageFactory->create(
                [
                    'message' => __(
                        'Order State has been changed from %1 to %2',
                        ucwords($order->getState()),
                        ucwords($infoData['state'])
                    ),
                    'level' => 0
                ]
            );
        }

        $order->addData($infoData);
        $order->setCreatedAt($newCreatedAt);
        try {
            $this->orderRepository->save($order);
            $this->_eventManager->dispatch(
                'mageworx_log_changes_on_order_edit',
                [
                    ChangeLoggerInterface::MESSAGES_KEY => $changeLog
                ]
            );
            $this->_eventManager->dispatch(
                'mageworx_save_logged_changes_for_order',
                [
                    'order_id'        => $order->getId(),
                    'notify_customer' => false
                ]
            );
            /* Start - New event added*/
            $this->_eventManager->dispatch(
                'blueedit_save_after',
                [
                    'item' => $this->getOrder()
                ]
            );
            /* end - New event added*/
        } catch (\Exception $e) {
            $this->getMessageManager()->addErrorMessage($e->getMessage());
        }
    }
}
