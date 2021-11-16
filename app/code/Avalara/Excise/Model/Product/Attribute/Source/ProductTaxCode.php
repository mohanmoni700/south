<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Avalara\Excise\Model\Product\Attribute\Source;

class ProductTaxCode extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * getAllOptions
     *
     * @return array
     */
    public function getAllOptions()
    {
        $this->_options = [
            ['value' => 0,'label' => __('Not Applicable')],
            ['value' => 1,'label' => __('CBDNT')],
            ['value' => 2,'label' => __('CBDT')],
            ['value' => 3,'label' => __('CGR')],
            ['value' => 4,'label' => __('CGRB')],
            ['value' => 5,'label' => __('CGRC')],
            ['value' => 6,'label' => __('CGRCLASSD')],
            ['value' => 7,'label' => __('CGRCLASSF')],
            ['value' => 8,'label' => __('CGRF')],
            ['value' => 9,'label' => __('CHEW')],
            ['value' => 10,'label' => __('CIG')],
            ['value' => 11,'label' => __('CIG3')],
            ['value' => 12,'label' => __('CIG10')],
            ['value' => 13,'label' => __('CIG1120')],
            ['value' => 14,'label' => __('CIGAR')],
            ['value' => 15,'label' => __('CIGARC')],
            ['value' => 16,'label' => __('CIGP')],
            ['value' => 17,'label' => __('CIGPOVER3')],
            ['value' => 18,'label' => __('COILS')],
            ['value' => 19,'label' => __('ECIGPCKLQ')],
            ['value' => 20,'label' => __('ELQ')],
            ['value' => 21,'label' => __('ELQN')],
            ['value' => 22,'label' => __('ELQNPOD')],
            ['value' => 23,'label' => __('ELQPOD')],
            ['value' => 24,'label' => __('ELQSN')],
            ['value' => 25,'label' => __('ELQSNPOD')],
            ['value' => 26,'label' => __('LARCIGAR')],
            ['value' => 27,'label' => __('LOOSE')],
            ['value' => 28,'label' => __('LOOSE4TO8')],
            ['value' => 29,'label' => __('LOOSEOVER8')],
            ['value' => 30,'label' => __('LTLCGR')],
            ['value' => 31,'label' => __('LTLCGRPAC')],
            ['value' => 32,'label' => __('NICOTINE')],
            ['value' => 33,'label' => __('NORMSNUFF')],
            ['value' => 34,'label' => __('NPFREE')],
            ['value' => 35,'label' => __('NPXX')],
            ['value' => 36,'label' => __('OTP')],
            ['value' => 37,'label' => __('PC')],
            ['value' => 38,'label' => __('PIPE')],
            ['value' => 39,'label' => __('RYO')],
            ['value' => 40,'label' => __('SMK')],
            ['value' => 41,'label' => __('SMKL')],
            ['value' => 42,'label' => __('SMKL1')],
            ['value' => 43,'label' => __('SNUFF')],
            ['value' => 44,'label' => __('VAPEDB')],
            ['value' => 45,'label' => __('VAPEDC')],
            ['value' => 46,'label' => __('VAPEDEVICE')],
            ['value' => 47,'label' => __('VAPEKIT')],
            ['value' => 48,'label' => __('VAPEKITEB')],
            ['value' => 49,'label' => __('VAPEKITIB')],
            ['value' => 50,'label' => __('VAPELEAF')],
            ['value' => 51,'label' => __('VAPEMOD')],
            ['value' => 52,'label' => __('VAPEMODEB')],
            ['value' => 53,'label' => __('VAPEMODIB')],
            ['value' => 54,'label' => __('VAPEOTHER')],
            ['value' => 55,'label' => __('VAPEPOD')],
            ['value' => 56,'label' => __('VAPES')],
            ['value' => 57,'label' => __('VAPESELQ')],
            ['value' => 58,'label' => __('VAPESELQN')],
            ['value' => 59,'label' => __('VAPESELQSN')],
            ['value' => 60,'label' => __('VAPETANK')],
            ['value' => 61,'label' => __('VAPOR')],
            ['value' => 62,'label' => __('SKU1')],
            ['value' => 63,'label' => __('SKU2')],
            ['value' => 64,'label' => __('SKU3')],
            ['value' => 65,'label' => __('SKU4')],
        ];
        return $this->_options;
    }
}
