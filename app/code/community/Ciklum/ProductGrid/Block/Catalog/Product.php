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
class Ciklum_ProductGrid_Block_Catalog_Product extends Mage_Adminhtml_Block_Catalog_Product
{

    /**
     * Set template
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('ciklum/productgrid/catalog/product.phtml');
    }

    /**
     * Prepare button and grid
     *
     * @return Mage_Adminhtml_Block_Catalog_Product
     */
    protected function _prepareLayout()
    {
        if (Mage::getStoreConfig(Ciklum_ProductGrid_Helper_Data::XMP_PATH_ACTIVE) == 1) {
            $this->_addButton('productgrid_list_edit', array(
                'label' => Mage::helper('ciklum_productgrid')->__('Edit in list'),
                'onclick' => "Ciklum.ProductGrid.editProductsInList()",
                'class' => 'productgrid_list_edit',
            ));
            $storeId = Mage::app()->getRequest()->getParam('store', 0);
            $this->_addButton('productgrid-list-save', array(
                'label' => Mage::helper('ciklum_productgrid')->__('Save list'),
                'onclick' => "Ciklum.ProductGrid.saveProductsInList('" . $this->getUrl('ciklum_productgrid/adminhtml_index', array('store' => $storeId)) . "')",
                'class' => 'productgrid-list-save no-display',
            ));
            $this->_addButton('productgrid-list-reset', array(
                'label' => Mage::helper('ciklum_productgrid')->__('Reset list changes'),
                'onclick' => "Ciklum.ProductGrid.resetProductsInList()",
                'class' => 'productgrid-list-reset no-display',
            ));
            $this->_addButton('productgrid-list-cancel', array(
                'label' => Mage::helper('ciklum_productgrid')->__('Cancel list changes'),
                'onclick' => "Ciklum.ProductGrid.cancelProductsInList()",
                'class' => 'productgrid-list-cancel no-display',
            ));
        }
        return parent::_prepareLayout();
    }

}