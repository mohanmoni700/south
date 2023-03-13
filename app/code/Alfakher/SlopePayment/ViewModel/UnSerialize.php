<?php
namespace Alfakher\SlopePayment\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Serialize\SerializerInterface;

class UnSerialize implements ArgumentInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Constructor
     *
     * @param SerializerInterface $serializer
     */

    public function __construct(
        SerializerInterface $serializer
    ) {
        $this->serializer = $serializer;
    }

    /**
     * Unserialize the given string
     *
     * @param string $slopeInfo
     * @return string|int|float|bool|array|null
     * @throws \InvalidArgumentException
     */
    public function getUnSerializedInfo($slopeInfo)
    {
        return  $this->serializer->unserialize($slopeInfo);
    }
}
