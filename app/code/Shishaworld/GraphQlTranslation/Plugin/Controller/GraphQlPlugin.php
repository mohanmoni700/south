<?php
/**
 * @category  Shishaworld
 * @package   Shishaworld_GraphQlTranslation
 * @author    Codilar
 */
declare(strict_types=1);
namespace Shishaworld\GraphQlTranslation\Plugin\Controller;

use Magento\Framework\App\Area;
use Magento\Framework\App\AreaList;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\GraphQl\Controller\GraphQl;

class GraphQlPlugin
{
    /** @var AreaList $areaList */
    private $areaList;

    /** @var State $appState */
    private $appState;

    /**
     * @param AreaList $areaList
     * @param State $appState
     */
    public function __construct(
        AreaList $areaList,
        State    $appState
    ) {
        $this->areaList = $areaList;
        $this->appState = $appState;
    }

    /**
     * @param GraphQl $subject
     * @throws LocalizedException
     */
    public function beforeDispatch(GraphQl $subject)
    {
        $area = $this->areaList->getArea($this->appState->getAreaCode());
        $area->load(Area::PART_TRANSLATE);
    }
}
