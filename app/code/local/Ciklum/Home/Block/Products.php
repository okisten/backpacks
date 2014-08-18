<?php

class Ciklum_Home_Block_Products extends Mage_Catalog_Block_Product_Abstract
{

    const XML_PATH_PRODUCTS_NUMBER = 'catalog/frontend/grid_per_page';

    public function getPageSize(){
        return (int)Mage::getStoreConfig(self::XML_PATH_PRODUCTS_NUMBER);
    }

    public function getProducts()
    {
        $collection = Mage::getResourceModel('catalog/product_collection')
            ->setStoreId(Mage::app()->getStore()->getId());

        $collection
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addUrlRewrite()
            ->setPageSize($this->getPageSize());

        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
        return $collection;
    }

    public function getImageLabel($product=null, $mediaAttributeCode='image')
    {
        if (is_null($product)) {
            return '';
        }

        $label = $product->getData($mediaAttributeCode.'_label');
        if (empty($label)) {
            $label = $product->getName();
        }

        return $label;
    }

    public function getAddToCartUrl($product, $additional = array())
    {
        if ($product->getTypeInstance(true)->hasRequiredOptions($product)) {
            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            if (!isset($additional['_query'])) {
                $additional['_query'] = array();
            }
            $additional['_query']['options'] = 'cart';

            return $this->getProductUrl($product, $additional);
        }
        return $this->helper('checkout/cart')->getAddUrl($product, $additional);
    }

}


