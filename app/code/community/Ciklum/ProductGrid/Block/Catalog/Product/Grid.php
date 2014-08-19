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
class Ciklum_ProductGrid_Block_Catalog_Product_Grid extends Mage_Adminhtml_Block_Catalog_Product_Grid
{
    public function __construct()
    {
        parent::__construct();
        if(Mage::getStoreConfig(Ciklum_ProductGrid_Helper_Data::XMP_PATH_ACTIVE) != 1){
            return;
        }
        Ciklum_ProductGrid_Helper_Data::$_savedProductFields = Mage::getModel('core/session')->getData('saved_product_fields');
        Mage::getModel('core/session')->unsetData('saved_product_fields');

    }
    
    public function addColumn($columnId, $column)
    {
        if(!Mage::helper('ciklum_productgrid')->isFieldHide($columnId)){
            parent::addColumn($columnId, $column);
        }
        
    }

    public function getMainButtonsHtml()
    {
        $html = '';
        if($this->getFilterVisibility()){
            if (Mage::getStoreConfig(Ciklum_ProductGrid_Helper_Data::XMP_PATH_ACTIVE) == 1) {
                $html.= $this->getChildHtml('additional_field_managment_button');
            }
            $html.= $this->getResetFilterButtonHtml();
            $html.= $this->getSearchButtonHtml();
        }
        return $html;
    }




    protected function _prepareLayout()
    {
        $this->setChild('export_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('ciklum_productgrid')->__('Export'),
                    'onclick'   => $this->getJsObjectName().'.doExport()',
                    'class'   => 'task'
                ))
        );

        $this->setChild('additional_field_managment_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('ciklum_productgrid')->__('Additional Columns'),
                    'onclick'   => 'Ciklum.ProductGrid.toggleAdditionalFields();',
                ))
        );

        $this->setChild('reset_filter_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('ciklum_productgrid')->__('Reset Filter'),
                    'onclick'   => 'Ciklum.ProductGrid.cancelProductsInList(); '.$this->getJsObjectName().'.resetFilter()',
                ))
        );
        $this->setChild('search_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('ciklum_productgrid')->__('Search'),
                    'onclick'   => 'Ciklum.ProductGrid.cancelProductsInList(); '.$this->getJsObjectName().'.doFilter()',
                    'class'   => 'task'
                ))
        );
    }

    protected function _prepareCollection()
    {
        $store = $this->_getStore();
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('attribute_set_id')
            ->addAttributeToSelect('type_id');

        if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
            $collection->joinField('qty',
                'cataloginventory/stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left');
            $collection->joinField('is_in_stock',
                'cataloginventory/stock_item',
                'is_in_stock',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left');
        }
        if ($store->getId()) {
            //$collection->setStoreId($store->getId());
            $adminStore = Mage_Core_Model_App::ADMIN_STORE_ID;
            $collection->addStoreFilter($store);
            $collection->joinAttribute(
                'name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $adminStore
            );
            $collection->joinAttribute(
                'custom_name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'status',
                'catalog_product/status',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'visibility',
                'catalog_product/visibility',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'price',
                'catalog_product/price',
                'entity_id',
                null,
                'left',
                $store->getId()
            );

        }
        else {
            $collection->addAttributeToSelect('price');
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
        }

        foreach (Mage::helper('ciklum_productgrid')->getAdditionalFields() as $code) {
            $collection->addAttributeToSelect($code);
            $collection->joinAttribute($code, 'catalog_product/'.$code, 'entity_id', null, 'left');
        }
        $this->setCollection($collection);

        if ($this->getCollection()) {

            $this->_preparePage();

            $columnId = $this->getParam($this->getVarNameSort(), $this->_defaultSort);
            $dir      = $this->getParam($this->getVarNameDir(), $this->_defaultDir);
            $filter   = $this->getParam($this->getVarNameFilter(), null);

            if (is_null($filter)) {
                $filter = $this->_defaultFilter;
            }

            if (is_string($filter)) {
                $data = $this->helper('adminhtml')->prepareFilterString($filter);
                $this->_setFilterValues($data);
            }
            else if ($filter && is_array($filter)) {
                $this->_setFilterValues($filter);
            }
            else if(0 !== sizeof($this->_defaultFilter)) {
                $this->_setFilterValues($this->_defaultFilter);
            }

            if (isset($this->_columns[$columnId]) && $this->_columns[$columnId]->getIndex()) {
                $dir = (strtolower($dir)=='desc') ? 'desc' : 'asc';
                $this->_columns[$columnId]->setDir($dir);
                $this->_setCollectionOrder($this->_columns[$columnId]);
            }

            if (!$this->_isExport) {
                $this->getCollection()->load();
                $this->_afterLoadCollection();
            }
        }
        $this->getCollection()->addWebsiteNamesToResult();
        return $this;
    }

    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        if(Mage::getStoreConfig(Ciklum_ProductGrid_Helper_Data::XMP_PATH_ACTIVE) != 1){
            return $this;
        }
        $store = $this->_getStore();
        $this->addColumn('name',
            array(
                'header'=> Mage::helper('catalog')->__('Name'),
                'index' => 'name',
                'renderer' => 'ciklum_productgrid/widget_grid_column_renderer_text',
        ));

        if ($store->getId()) {
            $this->addColumn('custom_name',
                array(
                    'header'=> Mage::helper('catalog')->__('Name in %s', $store->getName()),
                    'index' => 'custom_name',
                    'renderer' => 'ciklum_productgrid/widget_grid_column_renderer_text',
            ));
        }

        $this->addColumn('price',
            array(
                'header'=> Mage::helper('catalog')->__('Price'),
                'type'  => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'current_store' => $this->_getStore()->getCode(),
                'index' => 'price',
        ));

        if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
            $this->addColumn('qty',
                array(
                    'header'=> Mage::helper('catalog')->__('Qty'),
                    'width' => '100px',
                    'type'  => 'number',
                    'renderer' => 'ciklum_productgrid/widget_grid_column_renderer_qty',
                    'index' => 'qty',
            ));

            $this->addColumn('is_in_stock',
                array(
                    'header'=> Mage::helper('catalog')->__('Stock'),
                    'width' => '100px',
                    'type'  => 'options',
                    'options' => array(0=>Mage::helper('catalog')->__('Out of stock'), 1=>Mage::helper('catalog')->__('In Stock')),
                    'renderer' => 'ciklum_productgrid/widget_grid_column_renderer_options',
                    'index' => 'is_in_stock',
                ));
        }

        $this->addColumn('visibility',
            array(
                'header'=> Mage::helper('catalog')->__('Visibility'),
                'width' => '70px',
                'index' => 'visibility',
                'type'  => 'options',
                'renderer' => 'ciklum_productgrid/widget_grid_column_renderer_options',
                'options' => Mage::getModel('catalog/product_visibility')->getOptionArray(),
            ));

        $this->addColumn('status',
            array(
                'header'=> Mage::helper('catalog')->__('Status'),
                'width' => '70px',
                'index' => 'status',
                'type'  => 'options',
                'renderer' => 'ciklum_productgrid/widget_grid_column_renderer_options',
                'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
            ));

        foreach (Mage::helper('ciklum_productgrid')->getAdditionalFields() as $code) {
            $attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $code);
            $frontendInput = $attribute->getFrontendInput();
            if($frontendInput == 'select'){
                if ($attribute->usesSource()) {
                    $valuesCollection = $attribute->getSource();
                } else {
                    $valuesCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                        ->setAttributeFilter($attribute->getId())
                        ->setStoreFilter(0, false);
                }
                $this->addColumn($code,
                    array(
                        'header'=> $attribute->getFrontendLabel(),
                        'renderer' => 'ciklum_productgrid/widget_grid_column_renderer_options',
                        'index' => $code,
                        'type'  => 'options',
                        'options' =>  Mage::helper('ciklum_productgrid')->getCollectionAsOptions($valuesCollection),
                ));
                
            } elseif ($frontendInput == 'text') {
                $this->addColumn($code,
                    array(
                        'header'=> $attribute->getFrontendLabel(),
                        'renderer' => 'ciklum_productgrid/widget_grid_column_renderer_text',
                        'index' => $code,
                        'type'  => 'text',
                ));
            } elseif ($frontendInput == 'textarea') {
                $this->addColumn($code,
                    array(
                        'header'=> $attribute->getFrontendLabel(),
                        'renderer' => 'ciklum_productgrid/widget_grid_column_renderer_textarea',
                        'index' => $code,
                        'type'  => 'textarea',
                    ));
            }
        }

        $this->removeColumn('action');
        $this->addColumn('action',
            array(
                'header'    => Mage::helper('catalog')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('catalog')->__('Edit'),
                        'url'     => array(
                            'base'=>'*/*/edit',
                            'params'=>array('store'=>$this->getRequest()->getParam('store'))
                        ),
                        'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'order' => 100
        ));

        $this->addColumn('link',
            array(
                'header'    => Mage::helper('catalog')->__('Front'),
                'width'     => '50px',
                'renderer' => 'ciklum_productgrid/widget_grid_column_renderer_frontend',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('catalog')->__('View'),
                        'url'     => array(
                            'base'=>'catalog/product/view',
                        ),
                        'target' => '_blank',
                        'field'   => 'id',
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',

        ));
        $this->sortColumnsByOrder();
        return $this;
    }

    public function getRowUrl($row)
    {
        return '';
    }

}
