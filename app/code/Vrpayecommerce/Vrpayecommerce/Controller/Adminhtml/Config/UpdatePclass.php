<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vrpayecommerce\Vrpayecommerce\Controller\Adminhtml\Config;

class Updatepclass extends \Magento\Backend\App\Action
{
    protected $klarnaHelper;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Vrpayecommerce\Vrpayecommerce\Helper\Klarna $klarnaHelper
    ) {
        parent::__construct($context);
        $this->response = $context->getResponse();
        $this->klarnaHelper = $klarnaHelper;
    }

    /**
     * update pclass
     * @return array
     */
    public function execute()
    {
		$pClasses = array();
		$pClasses['success'] = 0;

		$pClassParameters['merchantId'] = $this->getRequest()->getParam('merchantId');
		$pClassParameters['sharedSecret'] = $this->getRequest()->getParam('sharedSecret');

		if (!empty($pClassParameters['merchantId']) && !empty($pClassParameters['sharedSecret'])) {
			$pClassParameters['currency'] = $this->getRequest()->getParam('currency');
			$pClassParameters['country'] = $this->getRequest()->getParam('country');
			$pClassParameters['language'] = $this->getRequest()->getParam('language');
		    $pClassParameters['digest'] = $this->klarnaHelper->getPClassDigest($pClassParameters);
            $serverMode = $this->getRequest()->getParam('serverMode');
            
			$proxyParameters['proxy']['behind'] = $this->getRequest()->getParam('behindProxy');
			$proxyParameters['proxy']['url'] = $this->getRequest()->getParam('proxyURL');
			$proxyParameters['proxy']['port'] = $this->getRequest()->getParam('proxyPort');

			$pClasses = $this->klarnaHelper->getPClasses($pClassParameters, $proxyParameters, $serverMode);

			if(isset($pClasses['success']) && $pClasses['success'] == 'ERROR_MERCHANT_SSL_CERTIFICATE'){
                $pClasses = $pClasses;
            } elseif (!empty($pClasses['id'])) {
	        	$pClasses['success'] = 1;
			} else {
                $pClasses['success'] = 0;
            }
		}
        $jsonPClass = json_encode($pClasses);
        $this->response->setBody($jsonPClass);
    }
}
