<?php

declare(strict_types=1);

namespace Ooka\OokaSerialNumber\Controller\Adminhtml\Serialnumber;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;
use Ooka\OokaSerialNumber\Model\Api\SerialNumberRepository;

class Delete extends Action
{
    /**
     * @var SerialNumberRepository
     */
    protected SerialNumberRepository $serialNumberRepository;
    /**
     * @var ResultFactory
     */
    protected $resultFactory;
    /**
     * @var ManagerInterface
     */
    protected ManagerInterface $manager;
    /**
     * @var RequestInterface
     */
    private RequestInterface $request;
    /**
     * @var UrlInterface
     */
    private UrlInterface $url;

    /**
     * Delete constructor
     *
     * @param Context $context
     * @param ResultFactory $resultFactory
     * @param RequestInterface $request
     * @param SerialNumberRepository $serialNumberRepository
     * @param UrlInterface $url
     * @param ManagerInterface $manager
     */
    public function __construct(
        Context $context,
        ResultFactory $resultFactory,
        RequestInterface $request,
        SerialNumberRepository $serialNumberRepository,
        UrlInterface $url,
        ManagerInterface $manager
    ) {
        $this->resultFactory = $resultFactory;
        $this->request = $request;
        $this->url = $url;
        $this->serialNumberRepository = $serialNumberRepository;
        $this->manager = $manager;
        parent::__construct($context);
    }

    /**
     * Delete execute method
     *
     * @return Redirect|(Redirect&ResultInterface)|ResultInterface
     */
    public function execute()
    {
        $redirectResponse = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $redirectResponse->setUrl($this->url->getUrl('*/*/index'));
        $result = $this->serialNumberRepository->deleteById($this->request->getParam('id'));
        if ($result) {
            $this->manager->addSuccessMessage(
                __(
                    sprintf(
                        'The SerialCode with id %s has been deleted Successfully',
                        $this->request->getParam('id')
                    )
                )
            );
        } else {
            $this->manager->addErrorMessage(
                __(
                    sprintf(
                        'The SerialCode with id %s has not been deleted, Due to some technical reasons',
                        $this->request->getParam('id')
                    )
                )
            );
        }
        return $redirectResponse;
    }
}
