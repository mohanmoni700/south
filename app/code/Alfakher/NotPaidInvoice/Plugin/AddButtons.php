<?php
namespace Alfakher\NotPaidInvoice\Plugin;

use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Backend\Block\Widget\Button\Toolbar\Interceptor;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Ui\Component\Control\Container;

class AddButtons extends \RedChamps\UnpaidInvoices\Plugin\AddButtons {
	protected $coreRegistry;

	public function beforePushButtons(
		Interceptor $subject,
		AbstractBlock $context,
		ButtonList $buttonList
	) {
		$request = $context->getRequest();
		if ($request->getFullActionName() == 'sales_order_view') {
			$order = $this->coreRegistry->registry('current_order');
			if ($order->canInvoice()) {
				$message = __('Are you sure you want to send invoice email to customer?');
				$buttonList->add(
					'unpaid_invoice',
					[
						'label' => __('Proforma Invoice'),
						'class' => 'unpaid_invoice_btn',
						'class_name' => Container::SPLIT_BUTTON,
						'options' => [
							[
								'label' => __('Send Email'),
								'onclick' => "confirmSetLocation('{$message}', '{$context->getUrl('unpaid_invoices/action/email', ['order_id' => $order->getId()])}')",
							],
							[
								'label' => __('Send Reminder Email'),
								'onclick' => "confirmSetLocation('{$message}', '{$context->getUrl('unpaid_invoices/action/email', ['order_id' => $order->getId(), 'type' => 'reminder'])}')",
							],
							[
								'label' => __('Print Invoice'),
								'onclick' => "setLocation('{$context->getUrl('unpaid_invoices/action/pdf', ['order_id' => $order->getId()])}')",
							],
						],
					],
					1
				);
			}
		}
	}
}
