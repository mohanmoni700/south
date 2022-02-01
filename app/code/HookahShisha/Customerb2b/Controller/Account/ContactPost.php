<?php

namespace HookahShisha\Customerb2b\Controller\Account;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

/**
 * Controller for saving Contact details.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ContactPost extends \Magento\Framework\App\Action\Action implements HttpPostActionInterface
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    private $formKeyValidator;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepositoryInterface;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface

     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
    ) {
        parent::__construct($context);
        $this->formKeyValidator = $formKeyValidator;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
    }

    /**
     * Edit Other Contact details form.
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $request = $this->getRequest();
        $resultRedirect = $this->resultRedirectFactory->create()->setPath('customer/account/');

        if ($request->isPost()) {
            if (!$this->formKeyValidator->validate($request)) {
                return $resultRedirect;
            }

            try {
                $customerId = $this->getRequest()->getParam('id');
                $data = $this->getRequest()->getParams();

                if ($customerId) {
                    $customer = $this->_customerRepositoryInterface->getById($customerId);
                    $customer->setCustomAttribute('contact_name', $data['contact_name'])
                        ->setCustomAttribute('contact_phone', $data['contact_phone'])
                        ->setCustomAttribute('contact_email', $data['contact_email']);
                    $this->_customerRepositoryInterface->save($customer);

                    $this->messageManager->addSuccess(
                        __('You saved the other contact details.')
                    );
                    return $resultRedirect;
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError(__('You must fill in all required fields before you can continue.'));
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('An error occurred on the server. Your changes have not been saved.')
                );
            }
        }

        return $resultRedirect;
    }
}
