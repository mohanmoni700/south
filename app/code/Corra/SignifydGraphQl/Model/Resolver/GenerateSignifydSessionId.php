<?php
declare(strict_types=1);

namespace Corra\SignifydGraphQl\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Signifyd\Connect\Helper\DeviceHelper;

class GenerateSignifydSessionId implements ResolverInterface
{
    /**
     * @var DeviceHelper
     */
    private $deviceHelper;

    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private $maskedQuoteIdToQuoteId;

    /**
     * GenerateSignifydSessionId constructor.
     * @param DeviceHelper $deviceHelper
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     */
    public function __construct(
        DeviceHelper $deviceHelper,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
    ) {
        $this->deviceHelper = $deviceHelper;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
    }

    /**
     * Generate dataOrderSessionId for Signifyd Fingerprint implementation on frontend pages
     *
     * This is a unique ID to send to signifyd from frontend pages
     *
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (empty($args['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }
        if (!$this->deviceHelper->isDeviceFingerprintEnabled()) {
            return [
                'data_order_session_id' => '',
            ];
        }
        $maskedCartId = $args['cart_id'];
        $dataOrderSessionId = '';
        $quoteId = $this->getQuoteIdFromCartId($maskedCartId);
        if ($quoteId) {
            $dataOrderSessionId = $this->deviceHelper->generateFingerprint($quoteId);
        }

        return [
            'data_order_session_id' => $dataOrderSessionId,
        ];
    }

    /**
     * Method used to get the Quote Id from the Masked Cart ID.
     *
     * @param string $maskedCartId
     * @return int
     */
    private function getQuoteIdFromCartId(string $maskedCartId)
    {
        try {
            $quoteId = $this->maskedQuoteIdToQuoteId->execute($maskedCartId);
        } catch (NoSuchEntityException $e) {
            $quoteId = null;
        }
        return $quoteId;
    }
}
