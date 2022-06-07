<?php

namespace HookahShisha\Customization\Model\Pdf;

use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Draw invoice pdf
 */
class Invoice extends \Magetrend\PdfTemplates\Model\Pdf\Invoice
{
    /**
     * Draw invoice
     */
    public function draw()
    {
        $elementsData = $this->getGroupedElementsData();
        if (empty($elementsData)) {
            return;
        }
        $this->newPage();
        $this->drawFirstPageElements();
        $this->predictSpaceForLastPage();
        $this->drawItems();
        $this->drawLastPageElements();
        $this->drawAdditionalElements();
    }
    /**
     * Draw count 
     */
    public function drawAdditionalElements()
    {
        $elementsData = $this->getGroupedElementsData();
        if (!isset($elementsData['other'])) {
            return;
        }
        $subtemp = $this->template->getAdditionalPage();

        if (!empty($subtemp)) {

            $new = count($this->pdf->pages);
            $count = $new+1;
            $this->coreRegistry->register('pdf_page_count', $count);

            $itemsElement = false;
            foreach ($elementsData['other'] as $key => $element) {
                if (in_array($element['type'], ['element_items', 'element_total', 'element_track'])) {
                    continue;
                }

                foreach ($this->pdf->pages as $pageId => $page) {
                    $this->currentPage = $page;
                    $this->coreRegistry->unregister('pdf_page_current');
                    $this->coreRegistry->register('pdf_page_current', $pageId);
                    $this->drawElement($element, $pageId);
                }
            }

        } else {

            $check = $this->coreRegistry->registry('pdf_page_count');
            $itemsElement = false;
            foreach ($elementsData['other'] as $key => $element) {
                if (in_array($element['type'], ['element_items', 'element_total', 'element_track'])) {
                    continue;
                }

                foreach ($this->pdf->pages as $pageId => $page) {
                    $this->currentPage = $page;
                    $this->coreRegistry->unregister('pdf_page_current');
                    $this->coreRegistry->register('pdf_page_current', $check);
                    $this->drawElement($element, $pageId);
                }
            }
            $this->coreRegistry->unregister('pdf_page_count');
        }
    }
}
