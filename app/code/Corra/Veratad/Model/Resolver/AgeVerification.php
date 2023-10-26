<?php

declare(strict_types=1);

namespace Corra\Veratad\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Corra\Veratad\Model\VeratadApi;
use Corra\Veratad\Model\UpdateCustomerAgeVerification;
use Magento\GraphQl\Model\Query\ContextInterface;

class AgeVerification implements ResolverInterface
{
    /**
     * @var VeratadApi
     */
    protected $veratadApi;

    /**
     * @var UpdateCustomerAgeVerification
     */
    private $updateCustomerAgeVerification;

    /**
     * Veratad AgeVerification constructor.
     *
     * @param VeratadApi $veratadApi
     * @param UpdateCustomerAgeVerification $updateCustomerAgeVerification
     */
    public function __construct(
        VeratadApi $veratadApi,
        UpdateCustomerAgeVerification $updateCustomerAgeVerification
    ) {
        $this->veratadApi = $veratadApi;
        $this->updateCustomerAgeVerification = $updateCustomerAgeVerification;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        return ($this->veratadAgeVerify($this->validatedParams($args['input']), $context));
    }

    /**
     * Calling the Age verification API
     *
     * @param array $post
     * @param ContextInterface|null $context
     * @return array
     * @throws GraphQlAuthorizationException
     * @throws GraphQlInputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\GraphQl\Exception\GraphQlAlreadyExistsException
     * @throws \Magento\Framework\GraphQl\Exception\GraphQlAuthenticationException
     * @throws \Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException
     */
    private function veratadAgeVerify($post, ContextInterface $context = null)
    {
        $responseMsg = [];
        $ageVerifyResponse =  $this->veratadApi->veratadPost($post);
        $responseMsg['action'] = $ageVerifyResponse;
        $responseMsg['detail'] = ($ageVerifyResponse)?
            $this->veratadApi->getSuccessMessageInfo():$this->veratadApi->getFailureMessageInfo();
        //my account save
        if ($post && !empty($post['dob']) &&
            $context && ($context->getExtensionAttributes()->getIsCustomer() === true) &&
            $this->veratadApi->saveAgeVerificationCustomer()) {
                $this->updateCustomerAgeVerification->updateAgeVerification(
                    $responseMsg['action'],
                    $context,
                    $post['dob']
                );
        }
        return $responseMsg;
    }

    /**
     * Validate the input data
     *
     * @param array $params
     * @return array
     * @throws GraphQlInputException
     */
    private function validatedParams($params)
    {
        if (trim($params['firstname']) === '') {
            throw new GraphQlInputException(
                __('Enter the First Name and try again.')
            );
        }
        if (trim($params['lastname']) === '') {
            throw new GraphQlInputException(
                __('Enter the Last Name and try again.')
            );
        }
        if (trim($params['street']) === '') {
            throw new GraphQlInputException(
                __('Enter the Street and try again.')
            );
        }
        if (trim($params['postcode']) === '') {
            throw new GraphQlInputException(
                __('Enter the Postcode/ Zip and try again.')
            );
        }
        if (trim($params['dob']) === '') {
            throw new GraphQlInputException(
                __('Enter the DOB and try again.')
            );
        }
        return $params;
    }
}
