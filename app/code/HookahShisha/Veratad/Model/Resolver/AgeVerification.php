<?php

declare(strict_types=1);

namespace HookahShisha\Veratad\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use HookahShisha\Veratad\Helper\Api as apiHelper;

class AgeVerification implements ResolverInterface
{
    /**
     * @var apiHelper
     */
    protected $apiHelper;

    /**
     * Veratad AgeVerification constructor.
     * @param ApiHelper $apiHelper
     */
    public function __construct(
        ApiHelper $apiHelper
    ) {
        $this->apiHelper = $apiHelper;
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
        return ($this->veratadPost($this->validatedParams($args['input'])));
    }

    /**
     * Calling the Age verification API
     *
     * @param array $post
     * @return array
     */
    private function veratadPost($post)
    {
        $responseMsg = [];
        $ageVerifyResponse =  $this->apiHelper->veratadPost($post);
        if ($ageVerifyResponse) {
            $responseMsg['action'] = $ageVerifyResponse['action'];
            $responseMsg['detail'] = $ageVerifyResponse['detail'];
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
    public function validatedParams($params)
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
