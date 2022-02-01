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

namespace MageModule\Core\Ui\Component\Form\Rule\Eav\Validation;

class RulesBuilder
{
    /**
     * @param \Magento\Eav\Api\Data\AttributeInterface $attribute
     * @param array                                    $data
     *
     * @return array
     */
    public function build(\Magento\Eav\Api\Data\AttributeInterface $attribute, array $data)
    {
        $rules = [];
        if (!empty($data['required']) || $attribute->getIsRequired()) {
            $rules['required-entry'] = true;
        }
        if ($attribute->getFrontendInput() === 'price') {
            $rules['validate-zero-or-greater'] = true;
        }

        $validationClasses = explode(' ', $attribute->getFrontendClass());

        foreach ($validationClasses as $class) {
            if (preg_match('/^maximum-length-(\d+)$/', $class, $matches)) {
                $rules = array_merge($rules, ['max_text_length' => $matches[1]]);
                continue;
            }
            if (preg_match('/^minimum-length-(\d+)$/', $class, $matches)) {
                $rules = array_merge($rules, ['min_text_length' => $matches[1]]);
                continue;
            }

            $rules = $this->mapRules($class, $rules);
        }

        return $rules;
    }

    /**
     * @param string $class
     * @param array  $rules
     *
     * @return array
     */
    protected function mapRules($class, array $rules)
    {
        switch ($class) {
            case 'validate-greater-than-zero':
            case 'validate-no-html-tags':
            case 'validate-number':
            case 'validate-digits':
            case 'validate-email':
            case 'validate-url':
            case 'validate-alpha':
            case 'validate-alphanum':
                $rules = array_merge($rules, [$class => true]);
                break;
        }

        return $rules;
    }
}
