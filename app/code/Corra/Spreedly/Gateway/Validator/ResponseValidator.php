<?php
/**
 * @author  CORRA
 */
namespace Corra\Spreedly\Gateway\Validator;

use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Corra\Spreedly\Gateway\Helper\SubjectReader;

class ResponseValidator extends AbstractValidator
{
    private const RESULT_SUCCESS = 'succeeded';
    private const ERROR_MESSAGE = 'message';

    /**
     * @var SubjectReader
     */
    protected $subjectReader;

    /**
     * Constructor
     *
     * @param ResultInterfaceFactory $resultFactory
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        SubjectReader $subjectReader
    ) {
        parent::__construct($resultFactory);
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function validate(array $validationSubject)
    {
        /** @var Successful|Error $response */
        $response = $this->subjectReader->readResponse($validationSubject);
        $errorMessages = [];
        $validationResult = $this->isSuccessfulTransaction($response);
        if (!$validationResult) {
            $errorMessages = (isset($response['transaction']['response'][self::ERROR_MESSAGE])) ?
                [__($response['transaction']['response'][self::ERROR_MESSAGE])]:
                [__('Transaction has been declined, please, try again later.')];
        }
        return $this->createResult($validationResult, $errorMessages);
    }

    /**
     * Check Successful Transaction or not
     *
     * @param array $response
     * @return bool
     */
    private function isSuccessfulTransaction(array $response)
    {
        return isset($response['transaction'][self::RESULT_SUCCESS])
            && $response['transaction'][self::RESULT_SUCCESS] === true;
    }
}
