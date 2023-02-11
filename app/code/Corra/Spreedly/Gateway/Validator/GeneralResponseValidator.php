<?php
/**
 * @author  CORRA
 */
namespace Corra\Spreedly\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Corra\Spreedly\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

/**
 * Spreedly general response validator
 */
class GeneralResponseValidator extends AbstractValidator
{
    private const RESULT_SUCCESS = 'succeeded';

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
        if ($this->isSuccessfulTransaction($response)) {
            return $this->createResult(
                true,
                []
            );
        } else {
            return $this->createResult(
                false,
                [__('Gateway rejected the transaction.')]
            );
        }
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
