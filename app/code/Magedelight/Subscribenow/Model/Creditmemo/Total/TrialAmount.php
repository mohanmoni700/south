<?php

/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Subscribenow
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Subscribenow\Model\Creditmemo\Total;

use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;
use Magedelight\Subscribenow\Helper\Data as DataHelper;

/**
 * Class TrialAmount
 * @package Magedelight\Subscribenow\Model\Creditmemo\Total
 */
class TrialAmount extends AbstractTotal
{
    /**
     * @var DataHelper
     */
    protected $helper;

    /**
     * Fee constructor.
     *
     * @param DataHelper $helper
     * @param array $data
     */
    public function __construct(DataHelper $helper, array $data = [])
    {
        parent::__construct($data);
        $this->helper = $helper;
    }

    /**
     * @param Creditmemo $creditmemo
     * @return $this
     */
    public function collect(Creditmemo $creditmemo)
    {
        $creditmemo->setSubscribenowTrialAmount(0);
        $creditmemo->setBaseSubscribenowTrialAmount(0);
        
        $amount = $creditmemo->getOrder()->getSubscribenowTrialAmount();
        $creditmemo->setSubscribenowTrialAmount($amount);
        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $amount);

        $baseAmount = $creditmemo->getOrder()->getBaseSubscribenowTrialAmount();
        $creditmemo->setBaseSubscribenowTrialAmount($baseAmount);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseAmount);

        return $this;
    }
}
