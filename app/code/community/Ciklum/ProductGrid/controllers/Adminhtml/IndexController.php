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

include_once("Mage/Adminhtml/Controller/Action.php");

class Ciklum_ProductGrid_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{

    protected function _validateFormKey()
    {
        return true;
        if (!($formKey = $this->getRequest()->getParam('form_key', null)) || $formKey != Mage::getSingleton('core/session')->getFormKey()) {
            return false;
        }
        return true;
    }

    /**
     * Index action saved product fields
     */
    public function indexAction()
    {
        if ($this->getRequest()->isPost()) { 
            $products = $this->getRequest()->getParam('product', array());
            $ids = array_keys($products);
            $collection = Mage::getModel('catalog/product')->getCollection()
                    ->addFieldToFilter('entity_id', array('in' => $ids));
            $saved = array();
            $storeId = Mage::app()->getRequest()->getParam('store', 0);

            Mage::helper('ciklum_productgrid')->saveCollectionData($collection, $products , $storeId);
            /*For show markers on saved*/
            foreach ($collection as $product) {
                $id = $product->getId();
                $data = $products[$id];
                $saved[$id] = array();
                foreach ($data as $key => $value) {
                    $saved[$id][] = $key;
                }
            }
            Mage::getModel('core/session')->setData('saved_product_fields', $saved);
            $this->_getSession()->addSuccess($this->__('Products have been saved. Product ids: '. join(', ', $ids)));
        }
        $this->_redirectReferer();
    }

    /**
     * Index action
     */
    public function applyHideFieldsAction()
    {
        if ($this->getRequest()->isPost()) {
            $hidefields = $this->getRequest()->getParam('hidefields', array());
            Mage::getModel('core/session')->setData('hided_product_fields', $hidefields);
            if (!empty($hidefields)) {
                $this->_getSession()->addSuccess($this->__('Hidden fields: '. join(', ', $hidefields)));
            }

            $additionalfields = $this->getRequest()->getParam('additionalfields', false);
            $filtredAdditionalFields = array();
            if($additionalfields){
                $additionalfields = explode(',', $additionalfields);
                foreach ($additionalfields as $code) {
                    $attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', trim($code));
                    if(null === $attribute->getId()){
                        continue;
                    }
                    if(in_array(trim($code), Ciklum_ProductGrid_Helper_Data::$_defaultCodes)){
                        continue;
                    }
                    $filtredAdditionalFields[] = trim($code);
                }

            }
            $additionalfieldCode = $this->getRequest()->getParam('additionalfield_add', false);
            if($additionalfieldCode){
                    $filtredAdditionalFields[] = trim($additionalfieldCode);
            }

            $additionalfieldCode = $this->getRequest()->getParam('additionalfield_remove', false);
            if($additionalfieldCode){
                $key = array_search($additionalfieldCode, $filtredAdditionalFields);
                if($key !== false){
                    unset($filtredAdditionalFields[$key]);
                }
            }

            Mage::getModel('core/session')->setData('additional_product_fields', $filtredAdditionalFields);
            Mage::getModel('core/session')->setData('additional_product_fields_set', true);
            if (!empty($filtredAdditionalFields)) {
                $this->_getSession()->addSuccess($this->__('Added fields: '. join(', ', $filtredAdditionalFields)));
            }
        }
        $this->_redirectReferer();
    }



}