<?php

namespace Avalara\Excise\Framework\Interaction\MetaData;

/**
 * @codeCoverageIgnore
 */
class MetaDataObjectFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Instance name to create
     *
     * @var string
     */
    protected $instanceName;

    protected static $instantiatedObjects = [];

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = \Avalara\Excise\Framework\Interaction\MetaData\MetaDataObject::class
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Avalara\Excise\Framework\Interaction\MetaData\MetaDataObject
     */
    public function create(array $data = [])
    {
        foreach (self::$instantiatedObjects as $objectData) {
            if ($objectData['data'] == $data) {
                return $objectData['object'];
            }
        }

        $object = $this->objectManager->create($this->instanceName, $data);
        self::$instantiatedObjects[] = ['data' => $data, 'object' => $object];
        return $object;
    }
}
