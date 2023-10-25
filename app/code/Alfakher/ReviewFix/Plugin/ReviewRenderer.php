<?php
declare(strict_types=1);

namespace Alfakher\ReviewFix\Plugin;

use Magento\Catalog\Block\Product\ReviewRendererInterface;
use Magento\Catalog\Model\Product;
use Yotpo\Yotpo\Plugin\AbstractYotpoReviewsSummary;

class ReviewRenderer extends AbstractYotpoReviewsSummary
{
    /**
     * Around plugin to return empty string for pagebuilder_stage_preview
     *
     * @param \Magento\Review\Block\Product\ReviewRenderer $reviewRendererBlock
     * @param callable $proceed
     * @param Product $product
     * @param string $templateType
     * @param bool $displayIfNoReviews
     * @return string
     */
    public function aroundGetReviewsSummaryHtml(
        \Magento\Review\Block\Product\ReviewRenderer $reviewRendererBlock,
        callable                                     $proceed,
        Product                                      $product,
        $templateType = \Magento\Review\Block\Product\ReviewRenderer::DEFAULT_VIEW,
        $displayIfNoReviews = false
    ) {
        if ($this->_context->getRequest()->getFullActionName() != 'pagebuilder_stage_preview') {
            if (!$this->_yotpoConfig->isEnabled()) {
                return $proceed($product, $templateType, $displayIfNoReviews);
            }
            $currentProduct = $this->_coreRegistry->registry('current_product');
            if (!$currentProduct || $currentProduct->getId() !== $product->getId()) {
                if ($this->_yotpoConfig->isCategoryBottomlineEnabled()) {
                    return $this->_getCategoryBottomLineHtml($product);
                } elseif (!$this->_yotpoConfig->isMdrEnabled()) {
                    return $proceed($product, $templateType, $displayIfNoReviews);
                }
            }
        }

        return '';
    }
}
