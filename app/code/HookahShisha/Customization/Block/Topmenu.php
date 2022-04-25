<?php

namespace HookahShisha\Customization\Block;

/**
 * Topmenu changes block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Topmenu extends \Smartwave\Megamenu\Block\Topmenu
{

    /**
     * @inheritDoc
     */
    public function getSubmenuItemsHtml($children, $level = 1, $max_level = 0, $column_width = 12, $menu_type = 'fullwidth', $columns = null)
    {
        $html = '';

        if (!$max_level || ($max_level && $max_level == 0) || ($max_level && $max_level > 0 && $max_level - 1 >= $level)) {
            $column_class = "";
            if ($level == 1 && $columns && ($menu_type == 'fullwidth' || $menu_type == 'staticwidth')) {
                $column_class = "col-md-" . $column_width . " ";
                $column_class .= "mega-columns columns" . $columns;
            }
            $html = '<ul class="subchildmenu ' . $column_class . '">';
            $i = 0;
            if ($level == 1) {
                $parentId = '';
            }
            $childShop = '';
            foreach ($children as $child) {
                if ($level == 2) {
                    $i++;
                }
                $cat_model = $this->getCategoryModel($child->getId());

                $sw_menu_hide_item = $cat_model->getData('sw_menu_hide_item');

                if (!$sw_menu_hide_item) {
                    $sub_children = $this->getActiveChildCategories($child);

                    $sw_menu_cat_label = $cat_model->getData('sw_menu_cat_label');
                    $sw_menu_icon_img = $cat_model->getData('sw_menu_icon_img');
                    $sw_menu_font_icon = $cat_model->getData('sw_menu_font_icon');

                    $item_class = 'level' . $level . ' ';
                    if (count($sub_children) > 0) {
                        $item_class .= 'parent ';
                    }
                    if ($level == 2 && $i % 7 == 1) {
                        $html .= '<ul class="custom">';
                    }
                    $html .= '<li class="ui-menu-item ' . $item_class . '">';
                    if (count($sub_children) > 0) {
                        $html .= '<div class="open-children-toggle"></div>';
                    }
                    if ($level == 1 && $sw_menu_icon_img) {
                        $html .= '<div class="menu-thumb-img"><a class="menu-thumb-link" href="' . $this->_categoryHelper->getCategoryUrl($child) . '"><img src="' . $this->_helper->getBaseUrl() . 'catalog/category/' . $sw_menu_icon_img . '" alt="' . $child->getName() . '"/></a></div>';
                    }
                    $html .= '<a href="' . $this->_categoryHelper->getCategoryUrl($child) . '" title="' . $child->getName() . '">';
                    if ($level > 1 && $sw_menu_icon_img) {
                        $html .= '<img class="menu-thumb-icon" src="' . $this->_helper->getBaseUrl() . 'catalog/category/' . $sw_menu_icon_img . '" alt="' . $child->getName() . '"/>';
                    } elseif ($sw_menu_font_icon) {
                        $html .= '<em class="menu-thumb-icon ' . $sw_menu_font_icon . '"></em>';
                    }

                    $html .= '<span>' . $child->getName();
                    if ($sw_menu_cat_label) {
                        $html .= '<span class="cat-label cat-label-' . $sw_menu_cat_label . '">' . $this->_megamenuConfig['cat_labels'][$sw_menu_cat_label] . '</span>';
                    }

                    $html .= '</span></a>';
                    if (count($sub_children) > 0) {
                        $html .= $this->getSubmenuItemsHtml($sub_children, $level + 1, $max_level, $column_width, $menu_type);
                    }
                    $html .= '</li>';

                    $parentId = $cat_model->getParentId();
                    if ($level == 2 && ($i == count($children) || $i % 7 == 0)) {
                        if ($i == count($children)) {
                            $catData = $this->getCategoryModel($cat_model->getParentId());
                            $childShop = '<li class="subcat-show-all"><a class="link-subcat" href=' . $catData->getUrl() . '>' . $catData->getData('show_all_label') . '</a></li>';
                        }
                        $html .= '</ul>';
                    }
                }
            }
            if ($level == 2) {
                $html .= $childShop;
            }
            if ($level == 1) {
                $parentId = (int) $parentId;
                $catData = $this->getCategoryModel($parentId);
                $html .= '<li class="subcat-show-all"><a class="link-subcat" href=' . $catData->getUrl() . '>' . $catData->getData('show_all_label') . '</a></li>';
            }
            $html .= '</ul>';
        }

        return $html;
    }
}
