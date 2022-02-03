<?php

namespace MageModule\OrderImportExport\Model\ResourceModel;

/**
 * Class Config
 *
 * @package MageModule\OrderImportExport\Model\ResourceModel
 */
class Config extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var bool
     */
    protected $_isPkAutoIncrement = false;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('magemodule_order_import_export_config', 'type');
    }

    /**
     * @param string $type
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getConfig($type)
    {
        $connection = $this->getConnection();
        $select     = $connection->select()->from(
            $this->getMainTable(),
            'config'
        );

        $select->where('type =?', $type);

        $value = $connection->fetchOne($select);

        if (is_string($value)) {
            try {
                $value = json_decode($value, true);
            } catch (\Exception $e) {
                $value = [];
            }
        } else {
            $value = [];
        }

        return $value;
    }

    /**
     * @param string $type
     * @param array  $value
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveConfig($type, array $value)
    {
        $this->getConnection()->insertOnDuplicate(
            $this->getMainTable(),
            [
                'type' => $type,
                'config' => json_encode($value)
            ],
            ['config']
        );
    }
}
