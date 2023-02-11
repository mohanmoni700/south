<?php
/**
 * @author  CORRA
 */
namespace Corra\Spreedly\Model\Ui\Adminhtml;

use Corra\Spreedly\Model\Ui\ConfigProvider;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterfaceFactory;
use Magento\Vault\Model\Ui\TokenUiComponentProviderInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Gets Ui component configuration for Spreedly Vault
 */
class TokenUiComponentProvider implements TokenUiComponentProviderInterface
{
    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var TokenUiComponentInterfaceFactory
     */
    private $componentFactory;

    /**
     * @param TokenUiComponentInterfaceFactory $componentFactory
     * @param Json|null $serializer
     */
    public function __construct(TokenUiComponentInterfaceFactory $componentFactory, Json $serializer = null)
    {
        $this->componentFactory = $componentFactory;
        $this->serializer = $serializer ?: ObjectManager::getInstance()
            ->get(Json::class);
    }

    /**
     * @inheritdoc
     */
    public function getComponentForToken(PaymentTokenInterface $paymentToken): TokenUiComponentInterface
    {
        $data = $this->serializer->unserialize($paymentToken->getTokenDetails() ?: '{}');

        return $this->componentFactory->create(
            [
                'config' => [
                    'code' => ConfigProvider::CC_VAULT_CODE,
                    TokenUiComponentProviderInterface::COMPONENT_DETAILS => $data,
                    TokenUiComponentProviderInterface::COMPONENT_PUBLIC_HASH => $paymentToken->getPublicHash(),
                    'template' => 'Corra_Spreedly::form/vault.phtml'
                ],
                'name' => Template::class
            ]
        );
    }
}
