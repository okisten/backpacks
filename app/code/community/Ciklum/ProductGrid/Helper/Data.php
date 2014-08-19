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

class Ciklum_ProductGrid_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XMP_PATH_ACTIVE = "ciklum_productgrid/settings/active";
    public static $_savedProductFields;
    public static $_changedInventoryProducts = array();
    public static $_defaultCodes = array('id','name','type','set_name','price','sku','qty','visibility', 'status', 'is_in_stock', 'websites');
    public static $_disableCodes = array('price_view');

    /**
     * @param array $disableCodes
     */
    public static function setDisableCodes($disableCodes)
    {
        self::$_disableCodes = $disableCodes;
    }

    /**
     * @return array
     */
    public static function getDisableCodes()
    {
        return array();
        return self::$_disableCodes;
    }

    /**
     * @param array $defaultCodes
     */
    public static function setDefaultCodes($defaultCodes)
    {
        self::$_defaultCodes = $defaultCodes;
    }

    /**
     * @return array
     */
    public static function getDefaultCodes()
    {
        return self::$_defaultCodes;
    }
    public static $_avaliableAdditionalAttributeTypes = array('select','textarea','text');
    public static $_excludeAdditionalAttributeNames = array('select','textarea','text');

    /**
     * @param array $avaliableAdditionalAttributeTypes
     */
    public static function setAvaliableAdditionalAttributeTypes($avaliableAdditionalAttributeTypes)
    {
        self::$_avaliableAdditionalAttributeTypes = $avaliableAdditionalAttributeTypes;
    }

    /**
     * @return array
     */
    public static function getAvaliableAdditionalAttributeTypes()
    {
        return self::$_avaliableAdditionalAttributeTypes;
    }



    public function saveProductData($product, $data, $storeId = 0)
    {
        if ($storeId == 0) {
            $storeProduct = $product;
        } else {
            $storeProduct = Mage::getModel('catalog/product')
                    ->setStoreId($storeId)
                    ->load($product->getId());
        }

        foreach ($data as $key => $value) {
            $storeProduct->setData($key, $value);
            if ($key == 'qty' || $key == 'is_in_stock') {
                self::$_changedInventoryProducts[$product->getId()] = $product;
            }
            if ($key == 'custom_name') {
                $storeProduct->setData('name', $value);
            }
            if ($key == 'name') {
                $product->setData('name', $value);
            }
        }

        if ($storeId != 0) {
            $storeProduct->save();
        }
    }

    public function saveCollectionData($collection, $products, $storeId = 0)
    {
        Varien_Profiler::start('Ciklum_ProductGrid SAVE BY PRODUCTS');
        foreach ($collection as $product) {
            $id = $product->getId();
            $data = $products[$id];
            Mage::helper('ciklum_productgrid')->saveProductData($product, $data, $storeId);

            $saved[$id] = array();
            foreach ($data as $key => $value) {
                $saved[$id][] = $key;
            }

            /* @var $product Mage_Catalog_Model_Product */
            //Mage::getModel('catalogrule/rule')->applyAllRulesToProduct($product->getId());

        }
        //Varien_Profiler::start('Ciklum_ProductGrid SAVE COLLECTION');
        $collection->save();
        //Varien_Profiler::stop('Ciklum_ProductGrid SAVE COLLECTION');
        //Mage::log('Ciklum_ProductGrid SAVE COLLECTION: '.Varien_Profiler::fetch('Ciklum_ProductGrid SAVE COLLECTION'));

        //Varien_Profiler::start('Ciklum_ProductGrid SAVE INVENTORY');

        if (count(self::$_changedInventoryProducts) > 0) {
            $stockItems = Mage::getModel('cataloginventory/stock_item')
                    ->getCollection()
                    ->addProductsFilter(self::$_changedInventoryProducts);
            foreach ($stockItems as $stockItem) {
                $data = $products[$stockItem->getProductId()];
                if(array_key_exists('is_in_stock', $data)){
                    $stockItem->setData('is_in_stock', $data['is_in_stock']);
                }
                if(array_key_exists('qty', $data)){
                    $stockItem->setData('qty', $data['qty']);
                    if(intval($data['qty'])==0){
                        $stockItem->setData('is_in_stock', 0);
                    }
                }
            }
            $stockItems->save();

        }

        //Varien_Profiler::stop('Ciklum_ProductGrid SAVE INVENTORY');
        //Mage::log('Ciklum_ProductGrid SAVE INVENTORY: '.Varien_Profiler::fetch('Ciklum_ProductGrid SAVE INVENTORY'));
    }

    public function isFieldHide($code)
    {
        $hidefields = Mage::getModel('core/session')->getData('hided_product_fields');
        if (is_array($hidefields) && in_array($code, $hidefields)) {
            return true;
        }
        return false;
    }

    public function getAdditionalFields()
    {
        $filtredAdditionalFields = Mage::getModel('core/session')->getData('additional_product_fields');
        if (is_array($filtredAdditionalFields)) {
            return $filtredAdditionalFields;
        }
        return array();
    }

    public function outputAdditionalFields()
    {
        $filtredAdditionalFields = $this->getAdditionalFields();
        if (is_array($filtredAdditionalFields)) {
            return join(', ', $filtredAdditionalFields);
        }
        return '';
    }

    public function getCollectionAsOptions($collection)
    {
        $array = $collection->getAllOptions();
        $result = array();
        foreach ($array as $key => $item) {
            $result[$item['value']] = $item['label'];
        }
        return $result;
    }


    public function getDefaultAttributes(){
        $collection = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addVisibleFilter( )
            ->addFieldToFilter('attribute_code',array('in'=>Ciklum_ProductGrid_Helper_Data::getDefaultCodes()));
        return $collection;
    }

    public function getAvaliableAttributes(){
        $collection = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addVisibleFilter( )
            ->addFieldToFilter('frontend_input',array('in'=>Ciklum_ProductGrid_Helper_Data::getAvaliableAdditionalAttributeTypes()))
            ->addFieldToFilter('attribute_code',array('nin'=>Ciklum_ProductGrid_Helper_Data::getDefaultCodes()))
            //->addFieldToFilter('attribute_code',array('nin'=>Ciklum_ProductGrid_Helper_Data::getDisableCodes()))
        ;



        return $collection;
    }

    public function getAddedAttributes(){
        $collection = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addVisibleFilter( )
            ->addFieldToFilter('attribute_code',array('in'=>$this->getAdditionalFields()));
        return $collection;
    }

    public function isEditedCollumns(){
        if(null !== Mage::getModel('core/session')->getData('additional_product_fields_set')){
            Mage::getModel('core/session')->unsetData('additional_product_fields_set');
            return true;
        }
        return false;
    }

}