<?php
declare(strict_types=1);

namespace Alfakher\SlopePayment\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field as BaseField;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Store\Model\Store;

class KeyValidation extends BaseField
{
    /**
     * @inheritDoc
     */
    protected function _renderScopeLabel(AbstractElement $element): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $title = __('Validate Credentials');
        $envId = 'select-groups-slope-payment-groups-slopeapi-fields-environment-value';
        $storeId = 0;

        if ($this->getRequest()->getParam('website')) {
            $website = $this->_storeManager->getWebsite($this->getRequest()->getParam('website'));
            if ($website->getId()) {
                /** @var Store $store */
                $store = $website->getDefaultStore();
                $storeId = $store->getStoreId();
            }
        }

        $endpoint = $this->getUrl('slopepayment/configuration/keyvalidate', ['storeId' => $storeId]);

        // @codingStandardsIgnoreStart
        $html = <<<TEXT
            <button
                type="button"
                title="{$title}"
                class="button"
                onclick="slopeKeyValidator.call(this, '{$endpoint}', '{$envId}')">
                <span>{$title}</span>
            </button>
TEXT;
        // @codingStandardsIgnoreEnd

        return $html;
    }
}
