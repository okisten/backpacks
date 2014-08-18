<?php

class Ciklum_Home_Block_FeaturedProducts extends Ciklum_Home_Block_Products
{
    const XML_PATH_PRODUCTS_NUMBER = 'nordiclite/home/featured_number';
    const FILTER_ATTRIBUTE_CODE = 'ciklum_home_is_featured';
    public function getPageSize(){
        return (int)Mage::getStoreConfig(self::XML_PATH_PRODUCTS_NUMBER);
    }
    public function getProducts()
    {
        $collection = parent::getProducts();
        //$collection->addAttributeToSelect(self::FILTER_ATTRIBUTE_CODE, 'left');// need if used_in_product_listing = false
        $collection->addFieldToFilter(self::FILTER_ATTRIBUTE_CODE, 1);
        return $collection;
    }
}
