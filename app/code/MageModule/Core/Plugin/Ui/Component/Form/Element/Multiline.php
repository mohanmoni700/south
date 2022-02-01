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

namespace MageModule\Core\Plugin\Ui\Component\Form\Element;

/**
 * Class Multiline
 *
 * @package MageModule\Core\Plugin\Ui\Component\Form\Element
 */
class Multiline
{
    /**
     * This plugin fixes an issue with multiline component where it does not
     * display label, scope label, or notice under field
     *
     * @param \Magento\Ui\Component\Form\Element\Multiline $subject
     * @param \Closure                                     $procede
     *
     * @return null
     */
    public function aroundPrepare($subject, $procede)
    {
        $result = $procede();

        $config     = $subject->getConfiguration();
        $usePlugin  = is_array($config) && isset($config['isMageModuleForm']);
        $components = $subject->getChildComponents();

        if ($usePlugin && is_array($components)) {
            /** @var \Magento\Ui\Component\Form\Field $component */
            $count = count($components);
            foreach ($components as $identifier => $component) {
                $data = $component->getData();

                if (isset($config['label'])) {
                    $data['config']['label'] = $config['label'];
                }

                if (isset($config['scopeLabel'])) {
                    $data['config']['scopeLabel'] = $config['scopeLabel'];
                }

                if (isset($config['notice']) && $count === 1) {
                    $data['config']['notice'] = $config['notice'];
                }

                $component->setData($data);
                $subject->addComponent($identifier, $component);
                $count--;
            }
        }

        return $result;
    }
}
