<?php


namespace Avalara\Excise\Plugin\View\Layout;

use Avalara\Excise\Logger\ExciseLogger;
use Magento\Framework\View\Layout\Generic as LayoutGeneric;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Component\Form as ComponentForm;
use Avalara\Excise\Api\UiComponentV1Interface;
use Avalara\Excise\Api\UiComponentV2Interface;
use Magento\Customer\Ui\Component\Form\AddressFieldset as FormAddressFieldset;
use Magento\Ui\Component\Form\Fieldset as FormFieldset;

/**
 * @codeCoverageIgnore
 */
class GenericPlugin
{
    /**
     * @var ExciseLogger
     */
    private $exciseLogger;

    /**
     * GenericPlugin constructor.
     * @param AvaTaxLogger $exciseLogger
     */
    public function __construct(ExciseLogger $exciseLogger)
    {
        $this->exciseLogger = $exciseLogger;
    }

    /**
     * @param LayoutGeneric $subject
     * @param UiComponentInterface $component
     */
    public function beforeBuild(LayoutGeneric $subject, UiComponentInterface $component)
    {
        if ($component instanceof ComponentForm) {
            // magento <= 2.3.0
            if ("customer_address_form" === (string)$component->getName()
                && false === $this->isMarkerInterfaceExists()) {
                /** @var FormFieldset|null $child */
                $child = $component->getComponent('general');
                if (null !== $child) {
                    $this->processComponents($child, UiComponentV2Interface::class);
                }
            }
            // magento >= 2.3.1
            if ("customer_form" === (string)$component->getName() && true === $this->isMarkerInterfaceExists()) {
                /** @var FormAddressFieldset|null $child */
                $child = $component->getComponent('address');
                if (null !== $child) {
                    $this->processComponents($child, UiComponentV1Interface::class);
                }
            }
        }
    }

    /**
     * @param UiComponentInterface $childComponent
     * @param string $markerInterface
     */
    private function processComponents(UiComponentInterface $childComponent, $markerInterface = '')
    {
        try {
            if (!empty($markerInterface)) {
                /** @var \ReflectionClass $class */
                $class = new \ReflectionClass(get_class($childComponent));
                if (true === (bool)$class->hasProperty('components')) {
                    /** @var \ReflectionProperty $componentsProperty */
                    $componentsProperty = $class->getProperty('components');
                    $componentsProperty->setAccessible(true);
                    $components = $componentsProperty->getValue($childComponent);
                    /**
                     * @var string $name
                     * @var UiComponentInterface $object
                     */
                    foreach ($components as $name => $object) {
                        if ($object instanceof $markerInterface) {
                            unset($components[$name]);
                        }
                    }
                    $componentsProperty->setValue($childComponent, $components);
                }
            }
        } catch (\Throwable $exception) {
            $this->exciseLogger->error($exception->getMessage(), [
                'class' => self::class,
                'trace' => $exception->getTraceAsString()
            ]);
        }
    }

    /**
     * Check the existence of marker interface. It was introduced since Magento 2.3.1
     *
     * @return bool
     */
    private function isMarkerInterfaceExists(): bool
    {
        return (bool)interface_exists(\Magento\Framework\View\Element\ComponentVisibilityInterface::class);
    }
}
