<?php

namespace Avalara\Excise\Framework\Interaction\MetaData;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Zend\Validator\NotEmpty;

/**
 * @codeCoverageIgnore
 */
class MetaDataObject
{
    /**
     * All name value
     */
    const ALL_NAME = '*';

    /**
     * @var array
     */
    protected $classMetaDataMap = [
        '\AvaTax\Address' => [\Avalara\Excise\Framework\Interaction\Address::class, 'validFields'],
        '\AvaTax\TaxOverride' => [\Avalara\Excise\Framework\Interaction\Tax::class, 'validTaxOverrideFields'],
        '\AvaTax\Tax' => [\Avalara\Excise\Framework\Interaction\Tax::class, 'validFields'],
        '\AvaTax\Line' => [\Avalara\Excise\Framework\Interaction\Line::class, 'validFields'],
    ];

    /**
     * @var MetaDataObjectFactory
     */
    protected $metaDataObjectFactory;

    /**
     * @var ArrayTypeFactory
     */
    protected $arrayTypeFactory;

    /**
     * @var BooleanTypeFactory
     */
    protected $booleanTypeFactory;

    /**
     * @var DoubleTypeFactory
     */
    protected $doubleTypeFactory;

    /**
     * @var IntegerTypeFactory
     */
    protected $integerTypeFactory;

    /**
     * @var DataObjectTypeFactory
     */
    protected $dataObjectTypeFactory;

    /**
     * @var StringTypeFactory
     */
    protected $stringTypeFactory;

    /**
     * Stores all of the validation fields
     *
     * @var array
     */
    protected $metaDataProperties = [];

    /**
     * Stores all required fields
     *
     * @var MetaDataAbstract[]
     */
    protected $requiredRules = [];

    /**
     * @param MetaDataObjectFactory $metaDataObjectFactory
     * @param ArrayTypeFactory $arrayTypeFactory
     * @param BooleanTypeFactory $booleanTypeFactory
     * @param DoubleTypeFactory $doubleTypeFactory
     * @param IntegerTypeFactory $integerTypeFactory
     * @param DataObjectTypeFactory $dataObjectTypeFactory
     * @param StringTypeFactory $stringTypeFactory
     * @param array $metaDataProperties
     */
    public function __construct(
        MetaDataObjectFactory $metaDataObjectFactory,
        ArrayTypeFactory $arrayTypeFactory,
        BooleanTypeFactory $booleanTypeFactory,
        DoubleTypeFactory $doubleTypeFactory,
        IntegerTypeFactory $integerTypeFactory,
        DataObjectTypeFactory $dataObjectTypeFactory,
        StringTypeFactory $stringTypeFactory,
        array $metaDataProperties
    ) {
        $this->metaDataObjectFactory = $metaDataObjectFactory;
        $this->arrayTypeFactory = $arrayTypeFactory;
        $this->booleanTypeFactory = $booleanTypeFactory;
        $this->doubleTypeFactory = $doubleTypeFactory;
        $this->integerTypeFactory = $integerTypeFactory;
        $this->dataObjectTypeFactory = $dataObjectTypeFactory;
        $this->stringTypeFactory = $stringTypeFactory;
        foreach ($metaDataProperties as $name => $metaDataRule) {
            if (in_array($metaDataRule[MetaDataAbstract::ATTR_TYPE], MetaDataAbstract::$types)) {
                $subtype = isset($metaDataRule[MetaDataAbstract::ATTR_SUBTYPE]) ?
                    $metaDataRule[MetaDataAbstract::ATTR_SUBTYPE] :
                    null;
                if (isset($subtype) && !($subtype instanceof $this)) {
                    $metaDataRule[MetaDataAbstract::ATTR_SUBTYPE] = $this->metaDataObjectFactory->create(
                        ['metaDataProperties' => $subtype]
                    );
                }

                $factoryVariableName = $metaDataRule[MetaDataAbstract::ATTR_TYPE] . 'TypeFactory';

                /** @var $rule MetaDataAbstract */
                $rule = $this->$factoryVariableName->create(
                    ['name' => $name, 'data' => $metaDataRule]
                );
                if ($rule instanceof ObjectType && isset($this->classMetaDataMap[$rule->getClass()])) {
                    $className = $this->classMetaDataMap[$rule->getClass()][0];
                    $propertyName = $this->classMetaDataMap[$rule->getClass()][1];
                    $rule->setSubtype($this->metaDataObjectFactory->create(
                        ['metaDataProperties' => $className::$$propertyName]
                    ));
                }
                $this->metaDataProperties[$rule->getName()] = $rule;

                if ($rule->getRequired()) {
                    $this->requiredRules[$rule->getName()] = $rule;
                }
            }
        }
    }

    /**
     * Validates an array of values according to the initializing rules
     *
     * @param $data
     * @return array
     * @throws LocalizedException
     */
    public function validateData(array $data)
    {
        $validatedData = [];

        /** @var $defaultValidator MetaDataAbstract */
        $defaultValidator = isset($this->metaDataProperties[self::ALL_NAME]) ?
            $this->metaDataProperties[self::ALL_NAME] :
            null;

        foreach ($data as $name => $item) {
            /** @var $validator MetaDataAbstract */
            $validator = isset($this->metaDataProperties[$name]) ? $this->metaDataProperties[$name] : $defaultValidator;
            if ($validator!==null) {
                $validatedData[$name] = $validator->validateData($item);
            }
        }
        $requiredFieldValidator = new NotEmpty([
            NotEmpty::INTEGER,
            NotEmpty::BOOLEAN,
            NotEmpty::SPACE,
            NotEmpty::NULL,
        ]);
        foreach ($this->requiredRules as $requiredRule) {
            if (!array_key_exists($requiredRule->getName(), $validatedData) ||
                !$requiredFieldValidator->isValid($validatedData[$requiredRule->getName()])) {
                throw new LocalizedException(__(
                    '%1 is a required field and was either not passed in or did not pass validation.',
                    [
                        $requiredRule->getName()
                    ]
                ));
            }
        }
        return $validatedData;
    }

    /**
     * Returns an hashed cache key representing a combination of all relevant data on the object as defined by metadata
     *
     * @param array $data
     * @return string
     */
    public function getCacheKey(array $data)
    {
        $cacheKey = '';

        /** @var $defaultKeyGenerator MetaDataAbstract */
        $defaultKeyGenerator = isset($this->metaDataProperties[self::ALL_NAME]) ?
            $this->metaDataProperties[self::ALL_NAME] :
            null;

        foreach ($data as $name => $item) {
            /** @var $keyGenerator MetaDataAbstract */
            $keyGenerator =
                isset($this->metaDataProperties[$name]) ? $this->metaDataProperties[$name] : $defaultKeyGenerator;
            if ($keyGenerator!==null) {
                $cacheKey .= $keyGenerator->getCacheKey($item);
            }
        }

        return hash('sha256', $cacheKey);
    }

    /**
     * Returns an hashed cache key representing a combination of all relevant data on the object as defined by metadata
     *
     * @param DataObject $object
     * @return string
     */
    public function getCacheKeyFromObject($object)
    {
        $cacheKey = '';

        foreach ($this->metaDataProperties as $name => $keyGenerator) {
            /** @var $keyGenerator MetaDataAbstract */
            if ($object->hasData($name)) {
                $cacheKey .= $keyGenerator->getCacheKey($object->getData($name));
            }
        }

        return hash('sha256', $cacheKey);
    }
}
