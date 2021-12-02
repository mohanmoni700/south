<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_Catalog
 * @author    Janis Verins <info@corra.com>
 */

namespace HookahShisha\Catalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Related;
use Magento\Ui\Component\Form\Fieldset;

/**
 * Data provider for Shisha/Charcoal panel
 */
class ShishaAndCharcoal extends Related
{
    public const DATA_SCOPE_SHISHA = 'shisha';

    public const DATA_SCOPE_CHARCOAL = 'charcoal';

    /**
     * @var string
     */
    private static $previousGroup = 'search-engine-optimization';

    /**
     * @var int
     */
    private static $sortOrder = 90;

    /**
     * @inheritdoc
     */
    public function modifyMeta(array $meta): array
    {
        return array_replace_recursive(
            $meta,
            [
                static::GROUP_RELATED => [
                    'children' => [
                        $this->scopePrefix . static::DATA_SCOPE_SHISHA => $this->getShishaFieldset(),
                        $this->scopePrefix . static::DATA_SCOPE_CHARCOAL => $this->getCharcoalFieldset()
                    ],
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __(
                                    'Related Products, Up-Sells, Cross-Sells,Shisha Products and Charcoal Products'
                                ),
                                'collapsible' => true,
                                'componentType' => Fieldset::NAME,
                                'dataScope' => static::DATA_SCOPE,
                                'sortOrder' => $this->getNextGroupSortOrder(
                                    $meta,
                                    self::$previousGroup,
                                    self::$sortOrder
                                ),
                            ],
                        ],
                    ],
                ]
            ]
        );
    }

    /**
     * Prepares config for the Shisha type products fieldset
     *
     * @return array
     */
    protected function getShishaFieldset(): array
    {
        return $this->getFieldSet(static::DATA_SCOPE_SHISHA);
    }

    /**
     * Prepares config for the Charcoal type products fieldset
     *
     * @return array
     */
    protected function getCharcoalFieldset(): array
    {
        return $this->getFieldSet(static::DATA_SCOPE_CHARCOAL);
    }

    /**
     * Returns field set for Shisha/Charcoal product type
     *
     * @param string $dataScope
     *
     * @return array
     */
    private function getFieldSet(string $dataScope): array
    {
        $type = ucfirst($dataScope);

        $content = __(
            "{$type} type are shown to customers in addition to the item the customer is looking at."
        );

        return [
            'children' => [
                'button_set' => $this->getButtonSet(
                    $content,
                    __('Add %type Products', ['type' => $type]),
                    $this->scopePrefix . $dataScope
                ),
                'modal' => $this->getGenericModal(
                    __('Add %type Products', ['type' => $type]),
                    $this->scopePrefix . $dataScope
                ),
                $dataScope => $this->getGrid($this->scopePrefix . $dataScope),
            ],
            'arguments' => [
                'data' => [
                    'config' => [
                        'additionalClasses' => 'admin__fieldset-section',
                        'label' => __('%type Products', ['type' => $type]),
                        'collapsible' => false,
                        'componentType' => Fieldset::NAME,
                        'dataScope' => '',
                        'sortOrder' => 90
                    ],
                ],
            ]
        ];
    }

    /**
     * Retrieve all data scopes
     *
     * @return array
     */
    protected function getDataScopes(): array
    {
        return [
            static::DATA_SCOPE_SHISHA,
            static::DATA_SCOPE_CHARCOAL
        ];
    }
}
