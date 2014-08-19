<?php
/**
 * Ciklum.com
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file license.txt
 *
 * @category    Ciklum
 * @package     Ciklum_ProductGrid
 * @copyright   Copyright (c) 2014 Ciklum (http://www.ciklum.com)
 * @author 	    Oleksii Chkhalo <olech@ciklum.com>, Olena Kisten <oki@ciklum.com>
 * @license     license.txt
 */

class Ciklum_ProductGrid_Block_Widget_Grid_Column_Renderer_Options
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Options
{


    public function render(Varien_Object $row)
    {
        $options = $this->getColumn()->getOptions();
        $showMissingOptionValues = (bool)$this->getColumn()->getShowMissingOptionValues();
        $savedProductFields = Ciklum_ProductGrid_Helper_Data::$_savedProductFields;
        $lastSavedHtml = '';
        $id = $row->getId();
        $index = $this->getColumn()->getIndex();

        if (!empty($options) && is_array($options)) {

            if(is_array($savedProductFields)
                    && array_key_exists($id, $savedProductFields)
                    && in_array($index, $savedProductFields[$id])) {
                    $lastSavedHtml = ' lastsaved-field';
            }

            $value = $row->getData($this->getColumn()->getIndex());
            if (is_array($value)) {
                $res = array();
                foreach ($value as $item) {
                    if (isset($options[$item])) {
                        $res[] = $this->escapeHtml($options[$item]);
                    }
                    elseif ($showMissingOptionValues) {
                        $res[] = $this->escapeHtml($item);
                    }
                }
                return implode(', ', $res);
            } elseif (isset($options[$value])) {
                //return $this->escapeHtml($options[$value]);
                return '<span class="original-field'.$lastSavedHtml.'">' . $this->escapeHtml($options[$value]) . '</span>' . $this->getEditableFieldHtml($row);
            } elseif (in_array($value, $options)) {
                //return $this->escapeHtml($value);
                return '<span class="original-field'.$lastSavedHtml.'">' . $this->escapeHtml($value) . '</span>' . $this->getEditableFieldHtml($row);
            }
        }
    }


    public function getEditableFieldHtml(Varien_Object $row)
    {
        $index = $this->getColumn()->getIndex();
        $id = $row->getId();
        $value = $row->getData($this->getColumn()->getIndex());
        $html = '<select name="product['.$id. ']['.$index.']" data-original="'.$value.'" data-editable-groupe="'.$index.'" class="editable-field editable-text-field editable-groupe-'.$index.' no-display">';        
        foreach ($this->getColumn()->getOptions() as $val => $label){
            $selected = ( ($val == $value && (!is_null($value))) ? ' selected="selected"' : '' );
            $html .= '<option value="' . $this->escapeHtml($val) . '"' . $selected . '>';
            $html .= $this->escapeHtml($label) . '</option>';
        }
        $html.='</select>';
        return $html;
    }

}
