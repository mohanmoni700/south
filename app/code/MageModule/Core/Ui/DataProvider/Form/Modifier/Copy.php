<?php
/**
 * Copyright (c) 2018 MageModule, LLC: All rights reserved
 *
 * LICENSE: This source file is subject to our standard End User License
 * Agreeement (EULA) that is available through the world-wide-web at the
 * following URI: https://www.magemodule.com/end-user-license-agreement/.
 *
 *  If you did not receive a copy of the EULA and are unable to obtain it through
 *  the web, please send a note to admin@magemodule.com so that we can mail
 *  you a copy immediately.
 *
 * @author        MageModule admin@magemodule.com
 * @copyright    2018 MageModule, LLC
 * @license        https://www.magemodule.com/end-user-license-agreement/
 */

namespace MageModule\Core\Ui\DataProvider\Form\Modifier;

/**
 * Applies javascript to allow copying of value from one field into another field on keyup
 *
 * Class Copy
 *
 * @package MageModule\Core\Ui\DataProvider\Form\Modifier
 */
class Copy implements \Magento\Ui\DataProvider\Modifier\ModifierInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Magento\Framework\Stdlib\ArrayManager
     */
    private $arrayManager;

    /**
     * @var array
     */
    private $links;

    /**
     * @var string
     */
    private $registryKey;

    /**
     * @var string
     */
    private $dataScopeKey;

    /**
     * Copy constructor.
     *
     * @param \Magento\Framework\Registry            $registry
     * @param \Magento\Framework\Stdlib\ArrayManager $arrayManager
     * @param array                                  $links
     * @param string                                 $registryKey
     * @param string                                 $dataScopeKey
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\ArrayManager $arrayManager,
        array $links,
        $registryKey,
        $dataScopeKey = 'data'
    ) {
        $this->registry     = $registry;
        $this->arrayManager = $arrayManager;
        $this->links        = $links;
        $this->registryKey  = $registryKey;
        $this->dataScopeKey = $dataScopeKey;
    }

    /**
     * @param array $meta
     *
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        if ($this->getDataObject()->isObjectNew()) {
            foreach ($this->links as $link) {
                $toPath   = $this->arrayManager->findPath($link['to'], $meta, null, 'children');
                $fromPath = $this->arrayManager->findPath($link['from'], $meta, null, 'children');
                if ($fromPath && $toPath) {
                    $config = [
                        'mask'        => '{{' . $link['from'] . '}}',
                        'component'   => 'MageModule_Core/js/components/import-handler',
                        'allowImport' => true
                    ];

                    $meta = $this->arrayManager->merge(
                        $toPath . '/arguments/data/config',
                        $meta,
                        $config
                    );

                    $meta = $this->arrayManager->merge(
                        $fromPath . '/arguments/data/config',
                        $meta,
                        [
                            'valueUpdate' => 'keyup'
                        ]
                    );
                }
            }
        }

        return $meta;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @return \MageModule\Core\Model\AbstractExtensibleModel
     */
    private function getDataObject()
    {
        return $this->registry->registry($this->registryKey);
    }
}
