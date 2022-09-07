<?php
namespace Alfakher\Blog\Plugin\Model\ResourceModel;

/**
 * Class Add date
 */
class PostModifiedDate
{

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * Construct
     *
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime
    ) {
        $this->_date = $date;
        $this->dateTime = $dateTime;
    }

    /**
     * Set modified date
     *
     * @param \Magefan\Blog\Model\ResourceModel\Post $subject
     * @param array $object
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave(
        \Magefan\Blog\Model\ResourceModel\Post $subject,
        $object
    ) {
        $value = $object->getData('modified_time') ?: null;
        $object->setData('modified_time', $this->dateTime->formatDate($value));

        $gmtDate = $this->_date->gmtDate();
        if ($object->isObjectNew() && !$object->getCreationTime()) {
            $object->setCreationTime($gmtDate);
        }

        if (!$object->getModifiedTime()) {
            $object->setModifiedTime($object->getCreationTime());
        }
    }
}
