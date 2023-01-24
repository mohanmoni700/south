<?php
namespace Alfakher\SlopePayment\Controller\Index;

use Alfakher\SlopePayment\Helper\Config as SlopeConfig;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Action;

class Test extends Action
{
	protected $_pageFactory;

	public function __construct(
		Context $context,
		PageFactory  $pageFactory,
		SlopeConfig $slopeConfig
		)
	{
		$this->_pageFactory = $pageFactory;
		$this->config = $slopeConfig;
		return parent::__construct($context);
	}

	public function execute()
	{
		/* TODO: Remove this controller after extension development is done */

		echo "<h1>Slope Payment Configurations Test</h1><br/><br/>";
		echo "<strong>General Configurations</strong><br/><br/>";
		echo "Is Slope Enabled ? : ".$this->config->isSlopePaymentActive()."<br/>";
		echo "Slope Title : ".$this->config->getSlopeTitle()."<br/>";
		echo "New Order Status : ".$this->config->getNewOrderStatus()."<br/>";
		echo "Slope Instruction : ".$this->config->getSlopeInstructions()."<br/><br/>";
		echo "<strong>API Credential Related Settings</strong><br/></br>";
		echo "Environment : ".$this->config->getEnvironmentType()."</br>";
		echo "Production Public Key : ".$this->config->getProductionPublicKey()."</br>";
		echo "Production Private Key : ".$this->config->getProductionPrivateKey()."</br>";
		echo "Production API Endpoint : ".$this->config->getProductionApiEndpointUrl()."</br>";
		echo "Production JS URL : ".$this->config->getProductionJsUrl()."</br></br>";
		echo "Sandbox Public Key : ".$this->config->getSandboxPublicKey()."</br>";
		echo "Sandbox Private Key : ".$this->config->getSandboxPrivateKey()."</br>";
		echo "Sandbox API Endpoint : ".$this->config->getSandboxApiEndpointUrl()."</br>";
		echo "Sandbox JS URL : ".$this->config->getSandboxJsUrl()."</br></br>";
		echo "<strong>Advanced Settings</strong><br/><br/>";
		echo "Debug Enabled ? : ".$this->config->isSlopeDebugActive()."</br>";
		
		exit;
	}
}
