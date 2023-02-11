<?php
/**
 * @author  CORRA
 */
declare(strict_types=1);


namespace Corra\Spreedly\Model\Adminhtml\Source;

/**
 *  Payment CC Types Source Model
 */
class Cctype extends \Magento\Payment\Model\Source\Cctype
{
    /**
     * Get Allowed CC types
     *
     * @return array
     */
    public function getAllowedTypes()
    {
        return ['VI', 'MC', 'AE', 'DI', 'JCB','DN' , 'MI' ];
    }
}
