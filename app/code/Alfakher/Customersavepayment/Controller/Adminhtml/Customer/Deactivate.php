<?php

namespace Alfakher\Customersavepayment\Controller\Adminhtml\Customer;

use Corra\Spreedly\Model\Ui\ConfigProvider as CorraConfig;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Vault\Model\CreditCardTokenFactory;
use ParadoxLabs\FirstData\Model\ConfigProvider as ParadoxsConfig;
use ParadoxLabs\TokenBase\Model\CardFactory;

class Deactivate extends Action
{
    /**
     * [__construct]
     *
     * @param Context $context
     * @param CreditCardTokenFactory $customerCreditCardFactory
     * @param Session $customerSession
     * @param CardFactory $cardCollectionFactory
     * @param ResultFactory $resultFactory
     * @param RedirectInterface $redirect
     */
    public function __construct(
        Context $context,
        CreditCardTokenFactory $customerCreditCardFactory,
        Session $customerSession,
        CardFactory $cardCollectionFactory,
        ResultFactory $resultFactory,
        RedirectInterface $redirect
    ) {
        $this->customerCreditCardFactory = $customerCreditCardFactory;
        $this->customerSession = $customerSession;
        $this->cardCollectionFactory = $cardCollectionFactory;
        $this->resultFactory = $resultFactory;
        $this->redirect = $redirect;
        parent::__construct($context);
    }

    /**
     * [execute]
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        try {
            $params = $this->getRequest()->getParams();
            if ($params['payment_method'] === CorraConfig::CODE) {
                $id = $params['id'];
                if ($id) {
                    try {
                        $tokenModel = $this->customerCreditCardFactory->create()->load($id);
                        $tokenModel->setIsActive(false);
                        $tokenModel->setIsVisible(false);
                        $tokenModel->save();
                        $this->messageManager->addSuccessMessage(__('Payment Record has been deleted.'));
                    } catch (\Exception $e) {
                        $this->messageManager->addErrorMessage(__("Card Details doesn't exist"));
                    }
                }
            } elseif ($params['payment_method'] === ParadoxsConfig::CODE) {
                $id = $params['id'];
                if ($id) {
                    try {
                        $paradoxLabsTokenModel = $this->cardCollectionFactory->create()->load($id);
                        $paradoxLabsTokenModel->setActive(false);
                        $paradoxLabsTokenModel->save();
                        $this->messageManager->addSuccessMessage(__('Payment Record has been deleted.'));
                    } catch (\Exception $e) {
                        $this->messageManager->addErrorMessage(__("Card Details doesn't exist"));
                    }
                }
            } else {
                $this->messageManager->addErrorMessage(__('Something wrong. Please check logs'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e->getMessage());
        }
        $resultRedirect->setUrl($this->redirect->getRefererUrl());
        return $resultRedirect;
    }
}
