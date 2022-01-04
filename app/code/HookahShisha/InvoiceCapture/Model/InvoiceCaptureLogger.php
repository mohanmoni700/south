<?php

declare(strict_types=1);

namespace HookahShisha\InvoiceCapture\Model;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Order Invoice Capture Logger Model
 */
class InvoiceCaptureLogger
{

    private const FAILED_STATUS = 'failed';
    private const SUCCESS_STATUS = 'success';
    private const TYPE = 'INVOICE CAPTURE';
    private const SUB_TYPE = 'CRON';
    private const WEBSITE_LOGGER = "hookahshisha_invoice_automatic.log";

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @var array
     */
    private $loggerMessage = [];

    /**
     * @param LoggerInterface $logger
     * @param TimezoneInterface $timezone
     * @param Json $serializer
     */
    public function __construct(
        LoggerInterface $logger,
        TimezoneInterface $timezone,
        Json $serializer
    ) {
        $this->logger = $logger;
        $this->localeDate = $timezone;
        $this->serializer = $serializer;
    }

    /**
     * Get Log information
     *
     * @param string $message
     * @param string $traceMessage
     * @param string $status
     */
    public function logInformation(
        $message,
        $traceMessage = null,
        $status = self::SUCCESS_STATUS
    ) {
        $message = [
            "message" => $message,
            "trace" => !empty($traceMessage)? implode("\n", $traceMessage):$traceMessage,
            "status" => $status,
        ];
        $context['logNalert'] =
            [
                'unique_id' => uniqid(),
                'message' =>
                    [
                        'type' => self::TYPE,
                        'subtype' => self::SUB_TYPE
                    ],
                'trace' =>
                    [
                        'filename' => self::WEBSITE_LOGGER
                    ],
            ];
        if ($status == self::FAILED_STATUS) {
            $this->logger->addError($this->serializer->serialize($message), $context);
        } else {
            $this->logger->addInfo($this->serializer->serialize($message), $context);
        }
    }

    /**
     * Log Exception Message
     *
     * @param string $message
     * @param string $traceMessage
     */
    public function logExceptionMessage($message, $traceMessage)
    {
        $this->loggerMessage['Message'] = $message;
        $this->loggerMessage['Ended At'] = $this->localeDate->date()->format('Y-M-d H:i:s A');
        $this->logInformation($this->loggerMessage, [$traceMessage], self::FAILED_STATUS);
    }
}
