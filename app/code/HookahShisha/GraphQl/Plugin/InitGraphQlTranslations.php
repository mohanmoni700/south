<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_GraphQl
 * @author    Janis Verins <info@corra.com>
 */

declare(strict_types=1);

namespace HookahShisha\GraphQl\Plugin;

use Exception;
use Magento\Framework\App\AreaInterface;
use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\AreaList;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;

class InitGraphQlTranslations
{
    /**
     * Application
     *
     * @var AreaList
     */
    protected AreaList $areaList;

    /**
     * State
     *
     * @var State
     */
    protected State $appState;

    /**
     * @param AreaList $areaList
     * @param State $appState
     */
    public function __construct(
        AreaList $areaList,
        State $appState
    ) {
        $this->areaList = $areaList;
        $this->appState = $appState;
    }

    /**
     * Initialize translation area part
     * Similarly to how SOAP and REST controllers initialize translation area part
     * or how frontend abstract action plugin loads design that initializes the same part
     * For frontend-specific areas, are emulation methods later would switch translation data as necessary
     * However, emulation does not initialize translation area part,
     * and as it is never called for graphQl, it is never loaded without such a plugin
     *
     * @param FrontControllerInterface $subject
     * @param RequestInterface $request
     *
     * @return void
     * @throws Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDispatch(
        FrontControllerInterface $subject, // NOSONAR
        RequestInterface $request // NOSONAR
    ) {
        $area = $this->areaList->getArea($this->appState->getAreaCode());
        if ($area) {
            $area->load(AreaInterface::PART_TRANSLATE);
        }
    }
}
