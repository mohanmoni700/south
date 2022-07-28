<?php
namespace Alfakher\Webhook\Controller\Adminhtml\Rma;

/**
 * Controller class SaveNew. Responsible for save RMA request
 */
class SaveNew extends \Magento\Rma\Controller\Adminhtml\Rma\SaveNew
{
    /**
     * @inheritDoc
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost() || $this->getRequest()->getParam('back', false)) {
            $this->_redirect('adminhtml/*/');
            return;
        }
        try {
            /** @var $model \Magento\Rma\Model\Rma */
            $model = $this->_initModel();
            $saveRequest = $this->rmaDataMapper->filterRmaSaveRequest($this->getRequest()->getPostValue());
            $model->setData(
                $this->rmaDataMapper->prepareNewRmaInstanceData(
                    $saveRequest,
                    $this->_coreRegistry->registry('current_order')
                )
            );
            if (!$model->saveRma($saveRequest)) {
                throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t save this RMA.'));
            }
            $this->_processNewRmaAdditionalInfo($saveRequest, $model);
            $this->messageManager->addSuccess(__('You submitted the RMA request.'));
            /* Start - New event added*/
            $this->_eventManager->dispatch(
                'rma_create_after',
                [
                    'item' => $model,
                ]
            );
            /* end - New event added*/
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
            $errorKeys = $this->_objectManager->get(\Magento\Framework\Session\Generic::class)->getRmaErrorKeys();
            $controllerParams = ['order_id' => $this->_coreRegistry->registry('current_order')->getId()];
            if (!empty($errorKeys) && isset($errorKeys['tabs']) && $errorKeys['tabs'] == 'items_section') {
                $controllerParams['active_tab'] = 'items_section';
            }
            $this->_redirect('adminhtml/*/new', $controllerParams);
            return;
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We can\'t save this RMA.'));
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
        }
        $this->_redirect('adminhtml/*/');
    }
}
