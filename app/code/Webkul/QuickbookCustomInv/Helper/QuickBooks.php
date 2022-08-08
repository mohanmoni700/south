<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_QuickbookCustomInv
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited(https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\QuickbookCustomInv\Helper;

use Magento\Framework\Exception\LocalizedException;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Data\IPPSalesReceipt;
use QuickBooksOnline\API\Data\IPPCreditMemo;
use QuickBooksOnline\API\Data\IPPLine;
use QuickBooksOnline\API\Data\IPPTaxLineDetail;
use QuickBooksOnline\API\Data\IPPTxnTaxDetail;
use QuickBooksOnline\API\Data\IPPDiscountLineDetail;
use QuickBooksOnline\API\Data\IPPSalesItemLineDetail;
use QuickBooksOnline\API\Exception\ServiceException;
use Magento\Framework\Json\Helper\Data as JsonHelperData;
use Webkul\MultiQuickbooksConnect\Helper\QuickBooks\Item as QuickBookItem;
use Webkul\MultiQuickbooksConnect\Helper\QuickBooks\Customer as QuickBookCustomer;
use Webkul\MultiQuickbooksConnect\Helper\QuickBooks\PaymentMethod as QuickBookPaymentMethod;
use Webkul\MultiQuickbooksConnect\Helper\Data as HelperData;
use Webkul\MultiQuickbooksConnect\Logger\Logger;

/**
 * QuickBooks data helper
 */
class QuickBooks extends \Webkul\MultiQuickbooksConnect\Helper\QuickBooks
{
    /**
     * @var \Magento\Framework\App\Helper\Context $context
     */
    private $context;

    /**
     * @var \Webkul\MultiQuickbooksConnect\Helper\Data $helperData
     */
    private $helperData;

    /**
     * @var Webkul\MultiQuickbooksConnect\Helper\QuickBooks\Item $quickBookItem
     */
    private $quickBookItem;

    /**
     * @var Webkul\MultiQuickbooksConnect\Helper\QuickBooks\Customer $quickBookCustomer
     */
    private $quickBookCustomer;

    /**
     * @var Webkul\MultiQuickbooksConnect\Helper\QuickBooks\Customer $dataService
     */
    private $dataService;

    /**
     * @var QuickBooksOnline\API\DataService\DataService $logger
     */
    private $logger;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param JsonHelperData $jsonHelperData
     * @param HelperData $helperData
     * @param QuickBookItem $quickBookItem
     * @param QuickBookCustomer $quickBookCustomer
     * @param QuickBookPaymentMethod $quickBookPaymentMethod
     * @param Logger $logger
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        JsonHelperData $jsonHelperData,
        HelperData $helperData,
        QuickBookItem $quickBookItem,
        QuickBookCustomer $quickBookCustomer,
        QuickBookPaymentMethod $quickBookPaymentMethod,
        Logger $logger
    ) {
        parent::__construct(
            $context,
            $messageManager,
            $jsonHelperData,
            $helperData,
            $quickBookItem,
            $quickBookCustomer,
            $quickBookPaymentMethod,
            $logger
        );
        $this->messageManager = $messageManager;
        $this->logger = $logger;
        $this->quickBookItem = $quickBookItem;
        $this->quickBookCustomer = $quickBookCustomer;
        $this->quickBookPaymentMethod = $quickBookPaymentMethod;
        $this->helperData = $helperData;
        $this->jsonHelperData = $jsonHelperData;
    }

    public function setDataServiceObject($accountId)
    {
        try {
            $this->helperData->getAccessToken($accountId);
            $this->configData = $config = $this->helperData->getQuickbooksAccountConfig($accountId);
            if (($config['app_integrates_with'] == 'oauth2') && $config['client_id']
                && $config['client_secret']) {
                // Prep Data Services
                $dataService = DataService::Configure([
                    'auth_mode' => 'oauth2',
                    'ClientID' => $config['client_id'],
                    'ClientSecret' => $config['client_secret'],
                    'accessTokenKey' => $config['oauth2_access_token'],
                    'refreshTokenKey' => $config['oauth2_refresh_token'],
                    'QBORealmID' => $config['realm_id'],
                    'baseUrl' => $config['account_type']
                ]);
                if (!$dataService) {
                    $this->logger->info("Problem while initializing DataService for accountId: ". $accountId);
                } else {
                    $dataService->throwExceptionOnError(true);
                }
                $this->dataService = $dataService;
            } else {
                $this->logger->info("Save Quickbooks Consumer Key and Consumer Secret.");
            }
        } catch (\Exception $e) {
            $this->logger->addError('QuickBooks helper -'.$e->getMessage());
            throw new LocalizedException($e->getMessage().'hello');
        }
    }

    /**
     * createSalesReceipt
     * @param array $salesReceiptData
     * @return
     */
    public function createSalesReceipt($salesReceiptData, $accountId)
    {
        try {
            $this->setDataServiceObject($accountId);

            $salesReceipt = new IPPSalesReceipt();
            $salesReceipt->DocNumber = $salesReceiptData['docNumber'];
            date_default_timezone_set('UTC');
            $salesReceipt->TxnDate = date('Y-m-d', time());
            $itemDataForQB = $this->getItemDataForQB(
                $salesReceiptData['items'],
                $salesReceiptData['tax_percent'],
                $accountId
            );
            $lineNum = $itemDataForQB['line_num'];
            $lineList = $itemDataForQB['line_list'];
            $totalAmt = $itemDataForQB['total_amt'];
            $customTax = $itemDataForQB['customTax'];
            $discountTotal = $itemDataForQB['discount_total'];
            $totalTax = $itemDataForQB['total_tax'];
            $splitedTax = $itemDataForQB['splited_tax'];
            $taxCodeRef = $itemDataForQB['tax_code_ref'];
            $taxLineList = [];

            if ($salesReceiptData['discount_on_order']) {
                ${'line'.$lineNum} = new IPPLine();
                ${'line'.$lineNum}->LineNum = $lineNum;
                ${'line'.$lineNum}->Amount = -$salesReceiptData['discount_on_order'];
                ${'line'.$lineNum}->DetailType = 'DiscountLineDetail';
                ${'line'.$lineNum}->DiscountLineDetail = new IPPDiscountLineDetail();
                ${'line'.$lineNum}->DiscountLineDetail->PercentBased = 'false';
                array_push($lineList, ${'line'.$lineNum});
                $totalAmt = ($totalAmt + $totalTax) - $salesReceiptData['discount_on_order'];
                $lineNum++;
            }

            if ($totalTax) {
                if (1 || isset($taxCodeRef['tax_code']) && $taxCodeRef['tax_code']) {
                    $salesReceipt->TxnTaxDetail = new IPPTxnTaxDetail();
                    //print_r($this->configData);
                    //die;
                    $salesReceipt->TxnTaxDetail->TxnTaxCodeRef = $this->configData['default_tax_class'];//$taxCodeRef['tax_code'];
                    $salesReceipt->TxnTaxDetail->TotalTax = $customTax;
                    $salesReceipt->TxnTaxDetail->UseAutomatedSalesTax = false;
                    /*$salesReceipt->TxnTaxDetail = new IPPTxnTaxDetail();
                    $salesReceipt->TxnTaxDetail->TxnTaxCodeRef = $taxCodeRef['tax_code'];
                    $salesReceipt->TxnTaxDetail->TotalTax = $totalTax;
                    $salesReceipt->TxnTaxDetail->TaxLine = [];
                    $taxLineNum = 1;
                    foreach ($taxCodeRef['tax_rate_list'] as $taxRateId => $taxRateValue) {
                        ${'taxLine'.$taxLineNum} =  new IPPLine();
                        $taxLine = new IPPLine();
                        ${'taxLine'.$taxLineNum}->Amount = $splitedTax[$taxRateId]['tax_amt'];
                        ${'taxLine'.$taxLineNum}->DetailType = "TaxLineDetail";
                        ${'taxLine'.$taxLineNum}->TaxLineDetail = new IPPTaxLineDetail();
                        ${'taxLine'.$taxLineNum}->TaxLineDetail->TaxRateRef = $taxRateId;
                        ${'taxLine'.$taxLineNum}->TaxLineDetail->PercentBased = 'true';
                        ${'taxLine'.$taxLineNum}->TaxLineDetail->TaxPercent = $taxRateValue;
                        ${'taxLine'.$taxLineNum}->TaxLineDetail->NetAmountTaxable =
                                                    $splitedTax[$taxRateId]['taxabl_amt'];
                        array_push($taxLineList, ${'taxLine'.$taxLineNum});
                        $taxLineNum++;
                    }
                    $salesReceipt->TxnTaxDetail->TaxLine = $taxLineList;*/
                } else {
                    $errorMsg = __('Tax class not created on quickbooks for applied tax rates on order. ');
                    $this->logger->addError(
                        $errorMsg.$this->jsonHelperData->jsonEncode($salesReceiptData['tax_percent'])
                    );
                    $response = ['error' => 1, 'msg' => $errorMsg];
                    return $response;
                }
            }
            $salesReceipt->Line = $lineList;
            $customer = $this->quickBookCustomer->getCustomer($this->dataService, $salesReceiptData['customerData']);
            $salesReceipt->CustomerRef = $customer->Id;
            $billAddress = $salesReceiptData['customerData']['bill_address'];
            $salesReceipt->BillAddr = $this->helperData->getPhysicalAddress($billAddress);
            $paymentMethod = $this->quickBookPaymentMethod->getPaymentMethod(
                $this->dataService,
                substr($salesReceiptData['paymentMethod'], 0, 31)
            );
            $taxApplyAfterDiscount = $this->scopeConfig->getValue('tax/calculation/apply_after_discount');
            $salesReceipt->PaymentMethodRef = $paymentMethod->Id;
            $salesReceipt->PONumber = $billAddress['telephone'];
            $salesReceipt->ApplyTaxAfterDiscount = $taxApplyAfterDiscount ? 'true' : 'false';
            $salesReceipt->TotalAmt = $totalAmt;
            $resultingSalesReceiptObj = $this->dataService->Add($salesReceipt);
            $response = ['error' => 0, 'salesReceiptData' => $resultingSalesReceiptObj];
            return $response;
        } catch (ServiceException $e) {
            $this->logger->addError('createSalesReceipt on ServiceException : '.$e->getMessage());
            return $this->getFilterErrorMsg($e->getMessage());
        } catch (\Exception $e) {
            $response = ['error' => 1, 'msg' => $e->getMessage()];
            $this->logger->addError('createSalesReceipt on sales receipt : '.$e->getMessage());
            return $response;
        }
    }

    /**
     * getFilterErrorMsg
     * @param string $errorContent
     * @return array
     */
    private function getFilterErrorMsg($errorContent)
    {
        try {
            preg_match('/body:\s\[(.*?)\]./', $errorContent, $match);
            $this->logger->addError('getFilterErrorMsg :'.$errorContent);
            $response = ['error' => 1, 'msg' => $errorContent];
            if (!empty($match)) {
                $match = isset($match[1]) ? $match[1] : $match[0];
                $errorContent = simplexml_load_string($match);
                $response = ['msg' => 'Please check log', 'error' => 1];
                if (isset($errorContent->Fault->Error)) {
                    $error = $this->jsonHelperData
                                    ->jsonDecode($this->jsonHelperData->jsonEncode($errorContent->Fault->Error));
                    $element = isset($error['@attributes']['element']) ?
                                    'Element - '.$error['@attributes']['element'].' ,' : '';
                    $response = [
                        'msg' => $error['Detail']. ' ( '.$element.' Error Code - '.$error['@attributes']['code'].')',
                        'error' => 1
                    ];
                }
            }
            return $response;
        } catch (\Exception $e) {
            $this->logger->addError('getFilterErrorMsg :'.$errorContent);
            return ['msg' => $e->getMessage(), 'error' => 1];
        }
    }

    /**
     * @param array $taxPercentageList
     * @return int
     */
    private function getTaxCode($taxPercentageList)
    {
        try {
            $qbTaxCode = [];
            $rateIds = [];
            $appliedTaxRates = [];
            foreach ($taxPercentageList as $taxPercent) {
                $condition = "Where RateValue = '".$taxPercent['tax_percent']."'";
                $allTaxRate = $this->dataService->Query("Select * From TaxRate ".$condition, 0, 1);
                if ($allTaxRate[0]) {
                    array_push($rateIds, $allTaxRate[0]->Id);
                    $appliedTaxRates[$allTaxRate[0]->Id] = $allTaxRate[0]->RateValue;
                }
            }
            $allTaxCodes = $this->dataService->Query("Select * From TaxCode", 0, 50);
            if ($allTaxCodes && !empty($rateIds)) {
                $qbTaxCode = $this->arrangedTaxCode($allTaxCodes, $rateIds, $appliedTaxRates);
            } else {
                $this->logger->addError(__(
                    'QuickBooks helper getTaxCode - s% % tax class not available on quickbooks',
                    $this->jsonHelperData->jsonEncode($taxPercentageList)
                ));
            }
            return !empty($qbTaxCode) ? $qbTaxCode : ['tax_code' => '', 'tax_rate_list' => []];
        } catch (\Exception $e) {
            $this->logger->addError('QuickBooks helper getTaxCode -'.$e->getMessage());
            return ['tax_code' => '', 'tax_rate_list' => []];
        }
    }

    /**
     * arrangedTaxCode
     * @param array $allTaxCodes
     * @param array $rateIds
     * @return array
     */
    private function arrangedTaxCode($allTaxCodes, $rateIds, $appliedTaxRates)
    {
        $qbTaxCode = [];
        foreach ($allTaxCodes as $taxCode) {
            if (isset($taxCode->SalesTaxRateList->TaxRateDetail)) {
                $salesTaxRateList = isset($taxCode->SalesTaxRateList->TaxRateDetail->TaxRateRef) ?
                        [0 => $taxCode->SalesTaxRateList->TaxRateDetail]
                        : $taxCode->SalesTaxRateList->TaxRateDetail;
                if (count($rateIds) == count($salesTaxRateList)) {
                    $rateCount = 0;
                    foreach ($salesTaxRateList as $salesTaxRate) {
                        $rateCount += in_array($salesTaxRate->TaxRateRef, $rateIds) ? 1 : 0;
                    }
                    if ($rateCount == count($rateIds) && $rateCount != 0) {
                        $qbTaxCode = ['tax_code' => $taxCode->Id, 'tax_rate_list' => $appliedTaxRates];
                    }
                }
            }
        }
        return $qbTaxCode;
    }

    /**
     * getAccounts
     * @param string $conditions
     * @return array
     */
    public function getAccounts($accountId, $conditions = '')
    {
        try {
            $this->setDataServiceObject($accountId);

            $allAccounts = [];
            if (isset($this->dataService)) {
                $allAccounts = $this->dataService->Query("Select * From Account ".$conditions, 0, 2);
            }
            return $allAccounts;
        } catch (\Exception $e) {
            $this->logger->addError('QuickBooks helper getAccounts -'.$e->getMessage());
            $response = $this->getFilterErrorMsg($e->getMessage());
            return $allAccounts;
        }
    }

    /**
     * arrangeItemDataForQB
     * @param array $orderItem
     * @param int $lineNum
     * @param array $taxDetails
     * @return array
     */
    private function arrangeItemDataForQB($orderItem, $lineNum, $taxDetails, $accountId)
    {
        $taxCodeRef = [];
        $taxAmt = 0;
        $description = $orderItem['TrackQtyOnHand'] == 'true'?
            $orderItem['Description'].' '.$orderItem['OptionsDetail'] : $orderItem['Name'];
        ${'line'.$lineNum} = new IPPLine();
        ${'line'.$lineNum}->LineNum = $lineNum;
        ${'line'.$lineNum}->Amount = $orderItem['AmountTotal'];
        ${'line'.$lineNum}->Description = substr($description, 0, 3990);
        ${'line'.$lineNum}->DetailType = "SalesItemLineDetail";
        ${'salesItemLineDetail'.$lineNum} = new IPPSalesItemLineDetail();
        $item = $this->quickBookItem->getItem($this->dataService, $orderItem, $accountId);
        ${'salesItemLineDetail'.$lineNum}->ItemRef = $item->Id;
        if (!empty($taxDetails)) {
            $taxCodeRef = $this->getTaxCode($taxDetails);
            foreach ($taxDetails as $key => $taxDetail) {
                $taxRateIndex = array_keys($taxCodeRef['tax_rate_list'], $taxDetail['tax_percent']);
                if (isset($taxRateIndex[0]) && isset($splitedTax[$taxRateIndex[0]])) {
                    $splitedTax[$taxRateIndex[0]]['tax_amt'] += $taxDetail['real_amount'];
                    $splitedTax[$taxRateIndex[0]]['taxabl_amt'] += $orderItem['AmountTotal'];
                } elseif (!empty($taxRateIndex)) {
                    $splitedTax[$taxRateIndex[0]]['tax_amt'] = $taxDetail['real_amount'];
                    $splitedTax[$taxRateIndex[0]]['taxabl_amt'] = $orderItem['AmountTotal'];
                }
                $taxAmt += $taxDetail['real_amount'];
            }
        }
        if (!empty($taxCodeRef['tax_rate_list']) && !$this->configData['us_store']) {
            ${'salesItemLineDetail'.$lineNum}->TaxCodeRef = $taxCodeRef['tax_code'];
        } else {
            ${'salesItemLineDetail'.$lineNum}->TaxCodeRef = $orderItem['Taxable'] ? 'TAX':'NON';
        }
        ${'salesItemLineDetail'.$lineNum}->UnitPrice = $orderItem['UnitPrice'];
        ${'salesItemLineDetail'.$lineNum}->Qty = $orderItem['Qty'];
        ${'line'.$lineNum}->SalesItemLineDetail = ${'salesItemLineDetail'.$lineNum};
        return ['list_item' => ${'line'.$lineNum}, 'tax_amt' => $taxAmt, 'tax_code_ref' => $taxCodeRef];
    }

    /**
     * getItemDataForQB
     * @param array $orderItemList
     * @param array $taxPercent
     * @return array
     */
    private function getItemDataForQB($orderItemList, $taxPercent, $accountId)
    {
        $lineNum = 1;
        $lineList = [];
        $totalAmt = 0;
        $discountTotal = 0;
        $totalTax = 0;
        $splitedTax = [];
        $taxCodeRef = [];
        $customTax = 0;
        foreach ($orderItemList as $orderItem) {
            if ($orderItem && $orderItem['Name']) {
                $taxDetails = $taxPercent[$orderItem['ItemId']] ?? [];
                $arrangedItem = $this->arrangeItemDataForQB($orderItem, $lineNum, $taxDetails, $accountId);
                if (!empty($arrangedItem['tax_code_ref'])) {
                    $taxCodeRef = $arrangedItem['tax_code_ref'];
                    foreach ($taxCodeRef['tax_rate_list'] as $taxRateId => $taxRateValue) {
                        if (!isset($splitedTax[$taxRateId])) {
                            $splitedTax[$taxRateId] = ['tax_amt' => 0, 'taxabl_amt' => 0];
                        }
                        $splitedTax[$taxRateId]['tax_amt'] += $arrangedItem['tax_amt'];
                        $splitedTax[$taxRateId]['taxabl_amt'] += $orderItem['AmountTotal'];
                    }
                }
                $totalAmt = $totalAmt+$orderItem['AmountTotal'];
                array_push($lineList, $arrangedItem['list_item']);
                $totalTax += $arrangedItem['tax_amt'];
                $discountTotal += $orderItem['discountAmt'];
                $customTax += $orderItem['taxAmt'];
                $lineNum++;
            }
        }
        return [
            'line_num' => $lineNum,
            'line_list' => $lineList,
            'total_amt' => $totalAmt,
            'discount_total' => $discountTotal,
            'total_tax' => $totalTax,
            'splited_tax' => $splitedTax,
            'tax_code_ref' => $taxCodeRef,
            'customTax' => $customTax
        ];
    }

    /**
     * Get TaxClassList
     * @param array $orderItemList
     * @param array $taxPercent
     * @return array
     */
    public function getTaxClassList($accountId)
    {
        try {
            $this->setDataServiceObject($accountId);

            $allTaxClass = [];
            if (isset($this->dataService)) {
                $allTaxClass = $this->dataService->Query("Select * From TaxCode", 0, 50);
            }
            return $allTaxClass;
        } catch (\Exception $e) {
            $this->logger->addError('QuickBooks helper getTaxClassList -'.$e->getMessage());
            $response = $this->getFilterErrorMsg($e->getMessage());
            return $allTaxClass;
        }
    }
}
