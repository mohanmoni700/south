<?php

namespace Avalara\Excise\Framework\Interaction\MetaData;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * @codeCoverageIgnore
 */
class DataObjectType extends MetaDataAbstract
{
    /**
     * @param string $name
     * @param array $data
     */
    public function __construct($name, array $data = [])
    {
        parent::__construct('dataObject', $name, $data);
    }

    /**
     * Set valid options of metadata object
     * Valid for integer, string, double (float)
     * Returns true if valid options is valid for this type and false if not
     *
     * @param array $validOptions
     * @return boolean
     */
    public function setOptions(array $validOptions)
    {
        return false;
    }

    /**
     * Set children metadata objects of this metadata object
     * Valid only on array and object types
     * Returns true if children are valid for this type and false if not
     *
     * @param MetaDataObject $subtype
     * @return bool
     */
    public function setSubtype(MetaDataObject $subtype = null)
    {
        $this->data[self::ATTR_SUBTYPE] = $subtype;
        return true;
    }

    /**
     * Set class of metadata object
     * Valid only on object type
     * Returns true if class is valid for this type and false if not
     *
     * @param string $class
     * @return bool
     * @throws LocalizedException
     */
    public function setClass($class)
    {
        if (!is_string($class) || !class_exists($class)) {
            throw new LocalizedException(__('%1 is not a valid class', [$class]));
        }

        $this->data[self::ATTR_CLASS] = $class;
        return true;
    }

    /**
     * Pass in a value and get the validated value back
     *
     * @param mixed $value
     * @return mixed
     * @throws LocalizedException
     */
    public function validateData($value)
    {
        if ($value !== null) {
            return $value;
        }

        if ('object' != getType($value)) {
            if ($this->getRequired()) {
                throw new LocalizedException(
                    __('The value you passed in is not an object.')
                );
            }
            $value = null;
        }

        $class = $this->getClass();
        if ($value !== null && !($value instanceof $class)) {
            throw new LocalizedException(__(
                'The object you passed in is of type %1 and is required to be of type %2.',
                [
                    get_class($value),
                    $class
                ]
            ));
        }

        return $value;
    }

    /**
     * Returns the cacheable portion of the string version of this object
     *
     * @param \Magento\Framework\DataObject $value
     * @return mixed
     * @internal param $data
     */
    public function getCacheKey($value)
    {
        $cacheKey = '';
        if (!$this->getUseInCacheKey()) {
            return $cacheKey;
        }
        // If a subtype is defined, call this function for that contents of the array
        if ($this->getSubtype() !== null) {
            $cacheKey = $this->getSubtype()->getCacheKeyFromObject($value);
        } elseif ($value !== null) {
            foreach ($value->getData() as $item) {
                if (is_array($item)) {
                    $cacheKey .= $this->getCacheKey($item);
                } else {
                    $cacheKey .= (string) $item;
                }
            }
        }
        return $cacheKey;
    }
}
