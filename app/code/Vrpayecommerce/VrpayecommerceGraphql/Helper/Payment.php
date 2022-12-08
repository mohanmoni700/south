<?php

namespace Vrpayecommerce\VrpayecommerceGraphql\Helper;

use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\View\Result\PageFactory;
use Vrpayecommerce\Vrpayecommerce\Controller\Payment as VrPaymentController;
use Vrpayecommerce\Vrpayecommerce\Helper\Curl;
use Vrpayecommerce\Vrpayecommerce\Helper\Payment as PaymentHelper;
use Vrpayecommerce\Vrpayecommerce\Model\Customer\Customer;
use Vrpayecommerce\Vrpayecommerce\Model\Method\AbstractMethod;
use Vrpayecommerce\Vrpayecommerce\Model\Payment\Information;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Quote\Api\CartRepositoryInterface;

class Payment extends VrPaymentController
{
    /**
     * @var CartRepositoryInterface
     */
    protected CartRepositoryInterface $cart;

    /**
     * @var OrderSender
     */
    protected OrderSender $orderSender;
    /**
     * @var AssetRepository
     */
    private AssetRepository $assetRepo;

    /**
     * @param Context $context
     * @param Session $checkoutSession
     * @param ResolverInterface $localeResolver
     * @param PageFactory $resultPageFactory
     * @param PaymentHelper $helperPayment
     * @param Curl $curl
     * @param Information $information
     * @param Customer $customer
     * @param CartRepositoryInterface $cart
     * @param OrderSender $orderSender
     * @param AssetRepository $assetRepo
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        ResolverInterface $localeResolver,
        PageFactory $resultPageFactory,
        PaymentHelper $helperPayment,
        Curl $curl,
        Information $information,
        Customer $customer,
        CartRepositoryInterface $cart,
        OrderSender $orderSender,
        AssetRepository $assetRepo
    ) {
        $this->cart = $cart;
        $this->orderSender = $orderSender;

        parent::__construct(
            $context,
            $checkoutSession,
            $localeResolver,
            $resultPageFactory,
            $helperPayment,
            $curl,
            $information,
            $customer
        );
        $this->assetRepo = $assetRepo;
    }

    /**
     * Process payment
     *
     * @param string $paymentMethod
     * @param string $checkoutId
     * @param string $orderId
     * @param int $customerId
     * @param bool $isServerToServer
     * @return array
     * @throws GraphQlInputException
     */
    public function processPayment(
        string $paymentMethod,
        string $checkoutId,
        string $orderId,
        int $customerId,
        bool $isServerToServer = false
    ): array {
        $this->paymentMethod = $this->createPaymentMethodObjectByPaymentMethod($paymentMethod);
        $credentials = $this->paymentMethod->getCredentials();
        $paymentStatus = $this->helperPayment->getPaymentStatus($checkoutId, $credentials, $isServerToServer);

        if (isset($paymentStatus['isValid']) && $paymentStatus['isValid']) {
            $returnCode = $paymentStatus['response']['result']['code'];
            $returnMessage = $this->helperPayment->getErrorIdentifier($returnCode);
            $transactionResult = $this->helperPayment->getTransactionResult($returnCode);

            $this->order = $this->getOrderByIncerementId($orderId);

            $this->saveOrderAdditionalInformation($paymentStatus['response']);

            if ($transactionResult == 'ACK') {
                $isRecurringPayment = $this->paymentMethod->isRecurringPayment();
                if ($isRecurringPayment) {
                    $this->processRecurringPayment($paymentStatus['response'], $customerId);
                }
                $this->processSuccessPayment($paymentStatus['response']);
            } elseif ($transactionResult == 'NOK') {
                throw new GraphQlInputException(__($returnMessage));
            } elseif ($transactionResult == 'PD'
                && $paymentStatus['response']['paymentBrand'] == "SOFORTUEBERWEISUNG") {
                $this->order->cancel()->save();
            } else {
                throw new GraphQlInputException(__('Something went wrong'));
            }
        } else {
            $this->order = $this->getOrderByIncerementId($orderId);
            if (isset($paymentStatus['response'])) {
                throw new GraphQlInputException(__($paymentStatus['response']));
            } else {
                throw new GraphQlInputException(__('Something went wrong'));
            }
        }

        return [
            'returnCode' => $returnCode,
            'transactionResult' => $transactionResult
        ];
    }

    /**
     * Process a payment recurring
     *
     * @param &$paymentStatus
     * @param int $customerId
     * @return void
     */
    public function processRecurringPayment(&$paymentStatus, int $customerId)
    {
        $infoParameter = $this->getInformationParamatersByPayment($customerId, $this->paymentMethod);
        $isRegistrationExist =
            $this->information->
            isRegistrationExist($infoParameter, $paymentStatus['registrationId']);
        if (!$isRegistrationExist) {
            $registrationParameters = array_merge(
                $infoParameter,
                $paymentStatus,
                $this->paymentMethod->getAccount($paymentStatus)
            );
            $this->information->insertRegistration($registrationParameters);
        }
    }

    /**
     * Process a success payment
     *
     * @param array $paymentStatus
     * @return void
     * @throws Exception
     */
    public function processSuccessPayment(array $paymentStatus)
    {
        $this->orderSender->send($this->order);

        $orderStatus = $this->setOrderStatus($paymentStatus);
        if ($orderStatus) {
            $this->order->setState('new')->setStatus($orderStatus)->save();
            $this->order->addStatusToHistory($orderStatus, '', true)->save();
        } else {
            $this->createInvoice();
        }

        $this->deActiveQuote();
    }

    /**
     * Set an order status
     *
     * @param array $paymentStatus
     * @return false|string
     */
    protected function setOrderStatus(array $paymentStatus)
    {
        $isInReview = $this->helperPayment->isSuccessReview($paymentStatus['result']['code']);

        if ($isInReview) {
            $orderStatus = AbstractMethod::STATUS_IR;
        } else {
            $orderStatus = false;
            if ($paymentStatus['paymentType'] == 'PA') {
                $orderStatus = AbstractMethod::STATUS_PA;
            }
        }

        return $orderStatus;
    }

    /**
     * Deactive quote
     *
     * @return void
     * @throws Exception
     */
    protected function deActiveQuote()
    {
        $quoteId = $this->order->getQuoteId();
        $quote = $this->cart->get($quoteId);
        $quote->setReservedOrderId($this->order->getId());
        $quote->setIsActive(false);
        $this->cart->save($quote);
    }

    /**
     * Get all saved Cards
     *
     * @param int $customerId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSavedCards(int $customerId): array
    {
        return [
            "CCSaved" => $this->getCcSavedCardDetails($customerId),
            "DDSaved" => $this->getDdSavedCardDetails($customerId)
        ];
    }

    /**
     * Get DD Saved Cards
     *
     * @param int $customerId
     * @return array|array[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDdSavedCardDetails(int $customerId): array
    {
        $ddSavedPayment = $this->createPaymentMethodObjectByPaymentMethod('vrpayecommerce_ddsaved');
        $ddSaved = $this->information->getPaymentInformation(
            $this->getInformationParamatersByPayment($customerId, $ddSavedPayment),
        );
        return array_map(function ($item) {
            return [
                "cardInfo" => __('FRONTEND_MC_ACCOUNT') . ": **** " . $item['last_4digits'],
                "image" => $this->getImageFullPath("Vrpayecommerce_Vrpayecommerce::images/sepa.png"),
                "brand" => $item['brand'],
                "formId" => $item['information_id'],
                "cardType" => "vrpayecommerce_ddsaved"
            ];
        }, (array)$ddSaved);
    }

    /**
     * Delete Saved card
     *
     * @param int $customerId
     * @param string $paymentMethod
     * @param string $informationId
     * @return array
     * @throws GraphQlInputException
     */
    public function deleteSavedCard(
        int $customerId,
        string $paymentMethod,
        string $informationId
    ) {
        $payment = $this->createPaymentMethodObjectByPaymentMethod($paymentMethod);
        $informationParameters = $this->getInformationParamatersByPayment($customerId, $payment);
        if ($this->deleteRegistrationByInformationId($informationId, $informationParameters, $payment)) {
            return  $this->getSavedCards($customerId);
        }
        throw new GraphQlInputException(__('Could not delete the saved card'));
    }

    /**
     * Delete a payment registered by information id
     *
     * @param string $informationId
     * @param array $informationParameters
     * @param object $paymentMethod
     * @return bool
     */
    protected function deleteRegistrationByInformationId(
        string $informationId,
        array  $informationParameters,
        object $paymentMethod
    ) {
        $registration = $this->information->getRegistrationByInformationId($informationParameters, $informationId);
        /** @var array $registration */
        if (!count($registration)) {
            throw new GraphQlInputException(__('Card Doesn\'t exist'));
        }

        $registrationId = $registration[0]['registration_id'];

        $deleteParameters = $paymentMethod->getCredentials();
        $deleteParameters['transactionId'] = $this->customer->getId();

        $deleteStatus = $this->helperPayment->deleteRegistration($registrationId, $deleteParameters);

        if ($deleteStatus['isValid']) {
            $returnCode = $deleteStatus['response']['result']['code'];
            $transactionResult = $this->helperPayment->getTransactionResult($returnCode);

            if ($transactionResult == 'ACK') {
                $this->information->deletePaymentInformationById($informationId);
                return true;
            }
        }
        return false;
    }

    /**
     * Get CC Saved Cards
     *
     * @param int $customerId
     * @return array|array[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCcSavedCardDetails(int $customerId): array
    {
        $ccSavedPayment = $this->createPaymentMethodObjectByPaymentMethod('vrpayecommerce_ccsaved');
        $ccSaved = $this->information->getPaymentInformation(
            $this->getInformationParamatersByPayment($customerId, $ccSavedPayment)
        );
        return array_map(function ($item) {
            return [
                "cardInfo" => __('ending in') . " " .$item['last_4digits'],
                "expiry" =>  __('expires on') . " " . $item['expiry_month'] . "/" .
                    substr(
                        $item['expiry_year'],
                        -2
                    ),
                "image" => $this->getImageFullPath(
                    "Vrpayecommerce_Vrpayecommerce/images" . '/' . strtolower($item['brand']) . '.png'
                ),
                "brand" => $item['brand'],
                "formId" => $item['information_id'],
                "cardType" => "vrpayecommerce_ccsaved"
            ];
        }, (array)$ccSaved);
    }

    /**
     * Get information parameters
     *
     * @param int $customerId
     * @param object $payment
     * @return array
     */
    public function getInformationParamatersByPayment(int $customerId, $payment): array
    {
        $informationParameters = [];
        $informationParameters['customerId'] = $customerId;
        $informationParameters['serverMode'] = $payment->getServerMode();
        $informationParameters['channelId'] = $payment->getChannelId();
        $informationParameters['paymentGroup'] =  $payment->getPaymentGroup();

        return $informationParameters;
    }

    /**
     * Get Image full path from view directory
     *
     * @param string $fileId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getImageFullPath(string $fileId): string
    {
        $params = [
            'area' => 'frontend'
        ];

        $asset = $this->assetRepo->createAsset($fileId, $params);

        try {
            return $asset->getUrl();
        } catch (Exception $exception) {
            return "";
        }
    }
}
