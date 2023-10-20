<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_ReCaptcha
 */

namespace HookahShisha\ReCaptcha\Model\Plugin;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Resolver\PlaceOrder as MagentoPlaceOrder;
use Magento\ReCaptchaAdminUi\Model\ErrorMessageConfig;
use Magento\ReCaptchaUi\Model\IsCaptchaEnabledInterface;
use Magento\ReCaptchaUi\Model\ValidationConfigResolverInterface;
use Magento\ReCaptchaValidationApi\Api\ValidatorInterface;

class PlaceOrder
{
    private const CAPTCHA_ID = 'pwa_place_order';
    /**
     * @var IsCaptchaEnabledInterface
     */
    private IsCaptchaEnabledInterface $isEnabled;

    /**
     * @var ValidationConfigResolverInterface
     */
    private ValidationConfigResolverInterface $validationConfigResolver;

    /**
     * @var ValidatorInterface
     */
    private ValidatorInterface $captchaValidator;

    /**
     * @var ErrorMessageConfig
     */
    private ErrorMessageConfig $errorMessageConfig;

    /**
     * @param IsCaptchaEnabledInterface $isEnabled
     * @param ValidationConfigResolverInterface $validationConfigResolver
     * @param ValidatorInterface $captchaValidator
     * @param ErrorMessageConfig $errorMessageConfig
     */
    public function __construct(
        IsCaptchaEnabledInterface $isEnabled,
        ValidationConfigResolverInterface $validationConfigResolver,
        ValidatorInterface $captchaValidator,
        ErrorMessageConfig $errorMessageConfig
    ) {
        $this->isEnabled = $isEnabled;
        $this->validationConfigResolver = $validationConfigResolver;
        $this->captchaValidator = $captchaValidator;
        $this->errorMessageConfig = $errorMessageConfig;
    }

    /**
     * @inheritdoc
     */
    public function beforeResolve(
        MagentoPlaceOrder $subject, // NOSONAR
        Field $field, // NOSONAR
        $context, // NOSONAR
        ResolveInfo $info, // NOSONAR
        array $value = null, // NOSONAR
        array $args = null // NOSONAR
    ) {
        if ($this->isEnabled->isCaptchaEnabledFor(self::CAPTCHA_ID)) {
            if (empty($args['input']['captcha_token'])) {
                throw new GraphQlInputException(__('captcha_token is missing'));
            }
            $validationConfig = $this->validationConfigResolver->get(self::CAPTCHA_ID);
            $validationResult = $this->captchaValidator->isValid($args['input']['captcha_token'], $validationConfig);
            if (false === $validationResult->isValid()) {
                $this->processError(
                    $validationResult->getErrors()
                );
            }
        }
    }

    /**
     * Error message if captcha validation failed.
     *
     * @param array $errorMessages
     * @return void
     * @throws GraphQlInputException
     */
    public function processError(array $errorMessages): void
    {
        $validationErrorText = $this->errorMessageConfig->getValidationFailureMessage();
        $technicalErrorText = $this->errorMessageConfig->getTechnicalFailureMessage();
        $message = $errorMessages ? $validationErrorText : $technicalErrorText;
        throw new GraphQlInputException(__($message));
    }
}
