<?php
declare(strict_types=1);

namespace Corra\Veratad\Model;

use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\CustomerGraphQl\Model\Customer\UpdateCustomerAccount;
use Magento\CustomerGraphQl\Model\Customer\ExtractCustomerData;
use Magento\Framework\GraphQl\Exception\GraphQlAlreadyExistsException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthenticationException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Stdlib\DateTime;
use Magento\GraphQl\Model\Query\ContextInterface;

class UpdateCustomerAgeVerification
{
    /**
     * @var VeratadApi
     */
    protected $veratadApi;

    /**
     * @var UpdateCustomerAccount
     */
    private $updateCustomerAccount;

    /**
     * @var ExtractCustomerData
     */
    private $extractCustomerData;

    /**
     * @var GetCustomer
     */
    private $getCustomer;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * Veratad Process Customer Update
     *
     * @param VeratadApi $veratadApi
     * @param UpdateCustomerAccount $updateCustomerAccount
     * @param GetCustomer $getCustomer
     * @param DateTime $dateTime
     * @param ExtractCustomerData $extractCustomerData
     */
    public function __construct(
        VeratadApi $veratadApi,
        UpdateCustomerAccount $updateCustomerAccount,
        GetCustomer $getCustomer,
        DateTime $dateTime,
        ExtractCustomerData $extractCustomerData
    ) {
        $this->veratadApi = $veratadApi;
        $this->updateCustomerAccount = $updateCustomerAccount;
        $this->getCustomer = $getCustomer;
        $this->dateTime = $dateTime;
        $this->extractCustomerData = $extractCustomerData;
    }

    /**
     * Update customer veratad Information
     *
     * @param boolean $ageVerifiedResponse
     * @param ContextInterface $context
     * @param string $dob
     * @return array
     * @throws GraphQlAlreadyExistsException
     * @throws GraphQlAuthenticationException
     * @throws GraphQlAuthorizationException
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateAgeVerification($ageVerifiedResponse, $context, $dob)
    {
        $response = [];
        $enabled = $this->veratadApi->isEnabled();
        if ($enabled) {
            $customer = $this->getCustomer->execute($context);
            $this->updateCustomerAccount->execute(
                $customer,
                [
                    'is_ageverified' => $ageVerifiedResponse,
                    'dob' => $dob,
                    'age_verified_on' => $this->dateTime->formatDate(time())
                ],
                $context->getExtensionAttributes()->getStore()
            );
            $response['customer'] = $this->extractCustomerData->execute($customer);
        }
        return $response;
    }
}
