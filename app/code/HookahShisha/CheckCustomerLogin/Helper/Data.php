<?php
namespace HookahShisha\CheckCustomerLogin\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {
	protected $_customerSession;

	public function __construct(
		\Magento\Customer\Model\Session $session
	) {
		$this->_customerSession = $session;
	}

	public function CustomerLogin() {
		if ($this->_customerSession->isLoggedIn()) {
			return true;
		} else {
			return false;
		}
	}
}