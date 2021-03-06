<?php
/**
* @copyright Amasty.
*/  
class Amasty_Shopby_Adminhtml_FilterController extends Mage_Adminhtml_Controller_Action
{
    // show grid
    public function indexAction()
    {
        foreach (Mage::app()->getStores() as $store){
            // check images, js and css
            $path = rtrim(Mage::getBaseDir('skin') . '/frontend', ' /');

            $package = Mage::getStoreConfig('design/package/name', $store);
            if (!$package)
                $package = 'default';
            $path .= '/' . trim($package, ' /');
            
            $theme = Mage::getStoreConfig('design/theme/skin', $store);
            if (!$theme)
                $theme = 'default';
                
            $defaultTheme = Mage::getStoreConfig('design/theme/default', $store);
            if (!$defaultTheme)
                $defaultTheme = 'default';
                
            $defaultPath = $path . '/' . trim($defaultTheme, ' /');
            $path        .= '/' . trim($theme, ' /');
            
            $files = array('/images/amshopby-cb1.gif');
            foreach ($files as $fileName){
                $file        = $path . $fileName;
                $defaultFile = $defaultPath . $fileName;
                if (!file_exists($file) && !file_exists($defaultFile)){
                    $msg = $this->__('Please make sure your custom skin %s contains %s file.', $package . '/' . $theme, $file);
                    Mage::getSingleton('adminhtml/session')->addNotice($msg);    
                    break;
                }
            }
            
            $category = Mage::getModel('catalog/category')
                ->setStoreId($store->getId())
                ->load($store->getRootCategoryId());
            if (!$category->getIsAnchor()){
                $msg = $this->__('Please open Catalog > Manage Categories and set property "Is Anchor" to "Yes" for the store root category.');
                Mage::getSingleton('adminhtml/session')->addNotice($msg);    
                break;
            }
        }
        
        $classes = array(
            'model' => array(
                'catalog/layer_filter_price',
                'catalog/layer_filter_decimal',
                'catalog/layer_filter_attribute',
                'catalog/layer_filter_category',
                'catalog/layer_filter_item',
                'catalogsearch/layer_filter_attribute',
                            
            ),
            'block' => array(
                'catalog/layer_filter_attribute',
                'catalog/product_list_toolbar',
                'catalogsearch/layer_filter_attribute',
            ),
        );
        
        foreach ($classes as $type => $names){
            foreach ($names as $name){
                $name = Mage::getConfig()->getGroupedClassName($type, $name);
                if (substr($name, 0, 6) != 'Amasty'){
                    $msg = $this->__('There is a conflict(s) with some other extension: class %s. If the module works incorrect, consider our <a href="http://amasty.com/installation-service.html">Installation Service</a>.', $name);
                    Mage::getSingleton('adminhtml/session')->addNotice($msg); 
                    break(2);   
                }
            }
        }
        
        $this->loadLayout(); 
        $this->_setActiveMenu('catalog/amshopby');
        $this->_addBreadcrumb($this->__('Filters'), $this->__('Filters')); 
        $this->_addContent($this->getLayout()->createBlock('amshopby/adminhtml_filter'));         
        $this->renderLayout();
    }

    // load filters and their options
    // todo - syncronize options
    public function newAction() 
    {
        try {
            Mage::getResourceModel('amshopby/filter')->createFilters();
            $msg = Mage::helper('amshopby')->__('Filters and their options have been loaded');
            Mage::getSingleton('adminhtml/session')->addSuccess($msg);
        }
        catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());    
        }
        $this->_redirect('*/*/');
    }
    
    // edit filters (uses tabs)
    public function editAction() 
    {
        $id     = (int) $this->getRequest()->getParam('id');
        $model  = Mage::getModel('amshopby/filter')->load($id);

        if ($id && !$model->getId()) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amshopby')->__('Filter does not exist'));
            $this->_redirect('*/*/');
            return;
        }
        
        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        
        Mage::register('amshopby_filter', $model);

        $this->loadLayout();
        
        $this->_setActiveMenu('catalog/amshopby');
        $this->_addContent($this->getLayout()->createBlock('amshopby/adminhtml_filter_edit'))
             ->_addLeft($this->getLayout()->createBlock('amshopby/adminhtml_filter_edit_tabs'));
        
        $this->renderLayout();
    }

    public function saveAction() 
    {
        $id     = $this->getRequest()->getParam('id');
        $model  = Mage::getModel('amshopby/filter');
        $data = $this->getRequest()->getPost();
        if ($data) {
            $model->setData($data);
            $model->setId($id);

            if ($model->getData('display_type') == Amasty_Shopby_Model_Catalog_Layer_Filter_Price::DT_FROMTO) {
                $model->setData('from_to_widget', true);
            }

            try {
                $model->save();
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                
                $msg = Mage::helper('amshopby')->__('Filter properties have been successfully saved');
                Mage::getSingleton('adminhtml/session')->addSuccess($msg);

                $this->_redirect('*/*/');
                
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $id));
            }
                        
            return;
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amshopby')->__('Unable to find a filter to save'));
        $this->_redirect('*/*/');
    } 
        
    public function massDeleteAction()
    {
        $ids = $this->getRequest()->getParam('filter_id');
        if(!is_array($ids)) {
             Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amshopby')->__('Please select filter(s)'));
        } 
        else {
            try {
                foreach ($ids as $id) {
                    $model = Mage::getModel('amshopby/filter')->load($id);
                    $model->delete();
                    // todo delete values or add a foreign key
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($ids)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/');
    }

    //for ajax
    public function valuesAction() 
    {
        $id = (int) $this->getRequest()->getParam('id');
        $model = Mage::getModel('amshopby/filter');

        if ($id) {
            $model->load($id);
        }

        Mage::register('amshopby_filter', $model);

        $this->getResponse()->setBody($this->getLayout()
            ->createBlock('amshopby/adminhtml_filter_edit_tab_values')->toHtml());
    }
}