<?php
namespace Alfakher\Webhook\Plugin;

use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Registry as registry;

class AddNewRMA
{
    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @param EventManager $eventManager
     * @param Registry $registry
     */
    public function __construct(
        EventManager $eventManager,
        Registry $registry
    ) {
        $this->_eventManager = $eventManager;
        $this->registry = $registry;
    }

    /**
     * Using around method for dispatch event after RMA save
     *
     * @param string $subject
     * @param array $proceed
     * @param array $data
     * @return array
     */
    public function aroundSaveRma(\Magento\Rma\Model\Rma $subject, callable $proceed, $data)
    {
        $returnValue = $proceed($data);
        if (!array_key_exists('entity_id', $data) || $data['entity_id'] === '') {
            $this->_eventManager->dispatch(
                'rma_create_after',
                [
                    'item' => $returnValue,
                ]
            );
        } else {
            $this->_eventManager->dispatch(
                'rma_update_after',
                [
                    'item' => $returnValue,
                ]
            );
        }
        return $returnValue;
    }
}
