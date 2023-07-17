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
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\GraphQl\Controller\GraphQl;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\TranslateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\RequestInterface;

class GraphQlPlugin
{
    /** @var AreaList $areaList */
    private $areaList;

    /** @var State $appState */
    private $appState;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var ResolverInterface
     */
    private $localeResolver;
    /**
     * @var TranslateInterface
     */
    private $translation;

    /**
     * @param AreaList $areaList
     * @param State $appState
     * @param TranslateInterface $translation
     * @param ResolverInterface $localeResolver
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        AreaList              $areaList,
        State                 $appState,
        TranslateInterface    $translation,
        ResolverInterface     $localeResolver,
        StoreManagerInterface $storeManager
    ) {
        $this->areaList = $areaList;
        $this->appState = $appState;
        $this->translation = $translation;
        $this->localeResolver = $localeResolver;
        $this->storeManager = $storeManager;
    }

    /**
     * Before plugin to fix the store specific translation in graphql queries/mutations
     *
     * @param  GraphQl          $subject
     * @param  RequestInterface $request
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function beforeDispatch(GraphQl $subject, RequestInterface $request)
    {
        $area = $this->areaList->getArea($this->appState->getAreaCode());
        $storeIdOrCode = $request->getHeader('store') ?: 0;
        $locale = $this->storeManager->getStore($storeIdOrCode)->getConfig('general/locale/code');
        $this->localeResolver->setLocale($locale);
        $this->translation->setLocale($locale);
        $area->load(Area::PART_TRANSLATE);
    }
}
