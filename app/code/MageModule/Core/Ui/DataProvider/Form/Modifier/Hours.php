<?php

namespace MageModule\Core\Ui\DataProvider\Form\Modifier;

use Magento\Framework\Stdlib\ArrayManager;

/**
 * Class Hours
 *
 * @package MageModule\Core\Ui\DataProvider\Form\Modifier
 */
class Hours implements \Magento\Ui\DataProvider\Modifier\ModifierInterface
{
    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var string
     */
    private $scope;

    /**
     * @var string
     */
    private $field;

    /**
     * @var array
     */
    private $days = [
        'sunday',
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday'
    ];

    /**
     * Hours constructor.
     *
     * @param ArrayManager $arrayManager
     * @param string       $field - the attribute code of the field
     * @param string|null  $scope - the parent data scope
     */
    public function __construct(
        ArrayManager $arrayManager,
        $field,
        $scope = null
    ) {
        $this->arrayManager = $arrayManager;
        $this->field        = $field;
        $this->scope        = $scope;
    }

    /**
     * @param array $meta
     *
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        $path = $this->arrayManager->findPath($this->field, $meta);
        if ($path) {
            $field = &$meta['additional-information']['children']['container_' . $this->field]['children'][$this->field];

            $field['arguments']['data']['config'] = [
                'label'         => __('Hours'),
                'componentType' => \Magento\Ui\Component\DynamicRows::NAME,
                'columnsHeader' => true,
                'addButton'     => false,
                'dndConfig'     => [
                    'enabled' => false
                ]
            ];

            $record = &$field['children']['record'];

            $record['arguments']['data']['config'] = [
                'componentType' => \Magento\Ui\Component\Container::NAME,
                'isTemplate'    => true,
                'is_collection' => false
            ];

            $record['children']['day']['arguments']['data']['config'] = [
                'componentType' => \Magento\Ui\Component\Form\Field::NAME,
                'component'     => 'Magento_Ui/js/form/element/text',
                'elementTmpl'   => 'ui/dynamic-rows/cells/text',
                'formElement'   => \Magento\Ui\Component\Form\Element\Input::NAME,
                'dataType'      => \Magento\Ui\Component\Form\Element\DataType\Text::NAME,
                'dataScope'     => 'day',
                'sortOrder'     => 1
            ];

            $record['children']['open']['arguments']['data']['config'] = [
                'label'         => __('Open'),
                'componentType' => \Magento\Ui\Component\Form\Field::NAME,
                'component'     => 'MageModule_Core/js/form/element/time',
                'elementTmpl'   => 'MageModule_Core/form/element/time',
                'formElement'   => 'date',
                'dataType'      => \Magento\Ui\Component\Form\Element\DataType\Date::NAME,
                'dataScope'     => 'open',
                'sortOrder'     => 10,
                'options'       => [
                    'timeFormat' => 'h:mm TT',
                    'timezone'   => 'Etc/GMT',
                    'stepMinute' => 5
                ],
                'imports'       => [
                    'disabled' => '${ $.parentName }.closed:checked'
                ]
            ];

            $record['children']['close']['arguments']['data']['config'] = [
                'label'         => __('Close'),
                'componentType' => \Magento\Ui\Component\Form\Field::NAME,
                'component'     => 'MageModule_Core/js/form/element/time',
                'elementTmpl'   => 'MageModule_Core/form/element/time',
                'formElement'   => 'date',
                'dataType'      => \Magento\Ui\Component\Form\Element\DataType\Date::NAME,
                'dataScope'     => 'close',
                'sortOrder'     => 20,
                'stepMinute'    => 5,
                'options'       => [
                    'timeFormat' => 'h:mm TT',
                    'timezone'   => 'Etc/GMT',
                    'stepMinute' => 5
                ],
                'imports'       => [
                    'disabled' => '${ $.parentName }.closed:checked'
                ]
            ];

            $record['children']['closed']['arguments']['data']['config'] = [
                'label'         => __('Closed'),
                'componentType' => \Magento\Ui\Component\Form\Field::NAME,
                'formElement'   => \Magento\Ui\Component\Form\Element\Checkbox::NAME,
                'dataType'      => \Magento\Ui\Component\Form\Element\DataType\Number::NAME,
                'dataScope'     => 'closed',
                'sortOrder'     => 30,
                'value'         => '1',
                'valueMap'      => [
                    'true'  => '1',
                    'false' => '0',
                ],
            ];
        }

        return $meta;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function modifyData(array $data)
    {
        if ($this->scope) {
            $field = &$data[key($data)][$this->scope][$this->field];
        } else {
            $field = &$data[key($data)][$this->field];
        }

        foreach ($this->days as $key => $day) {
            if (!isset($field[$key])) {
                $field[$key] = ['day' => __(ucfirst($day)), 'open' => null, 'close' => null, 'closed' => '0'];
            }
        }

        return $data;
    }
}
