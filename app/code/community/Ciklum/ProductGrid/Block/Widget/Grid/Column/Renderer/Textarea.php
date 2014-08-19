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

class Ciklum_ProductGrid_Block_Widget_Grid_Column_Renderer_Textarea
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{

    public function render(Varien_Object $row)
    {

        $lastSavedHtml = '';
        $id = $row->getId();
        $index = $this->getColumn()->getIndex();
        $savedProductFields = Ciklum_ProductGrid_Helper_Data::$_savedProductFields;

        if(is_array($savedProductFields)
            && array_key_exists($id, $savedProductFields)
            && in_array($index, $savedProductFields[$id])) {
            $lastSavedHtml = ' lastsaved-field';
        }

        if ($data = $row->getData($this->getColumn()->getIndex())) {
            return '<span class="original-field'.$lastSavedHtml.'">' . $data . '</span>' . $this->getEditableFieldHtml($row);
        }
        return '<span class="original-field'.$lastSavedHtml.'">' . $this->getColumn()->getDefault(). '</span>' . $this->getEditableFieldHtml($row);
    }




    public function getEditableFieldHtml(Varien_Object $row){
        $index = $this->getColumn()->getIndex();
        $id = $row->getId();
        $value = $this->_getValue($row);
        $html = '<textarea style="width:100%" name="product['.$id. ']['.$index.']" data-original="'.$value.'" data-editable-groupe="'.$index.'" class="editable-field editable-text-field editable-groupe-'.$index.' no-display">'.$value.'</textarea>';

        return $html;
    }
}
