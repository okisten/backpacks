<?php
/**
 * @copyright   Copyright (c) 2010 Amasty (http://www.amasty.com)
 */  
class Amasty_Shopby_Helper_Attributes
{
	protected $_optionsHash;
	protected $_attributes;
	protected $_options;
    protected $_requestedFilterCodes;
	protected $_optionsLabels = array();
	
	/**
	 * @return array
	 */
	public function getAllFilterableOptionsAsHash()
	{
		if (is_null($this->_optionsHash)) {
            $xAttributeValuesUnique = array();
			$hash = array();
            $attributes = $this->getFilterableAttributes();
            
            /* @var $helper Amasty_Shopby_Helper_Url */
            $helper = Mage::helper('amshopby/url');
            
            $options = $this->getAllOptions();
            
            foreach ($attributes as $a){
                $code        = $a->getAttributeCode();
                $code = str_replace(array('_', '-'), Mage::getStoreConfig('amshopby/seo/special_char'), $code);
                $hash[$code] = array();
                foreach ($options as $o){
                    if ($o['value'] && $o['attribute_id'] == $a->getId()) { // skip first empty
                        $unKey = $helper->createKey($o['value']);
                        while (isset($hash[$code][$unKey])
                            || (Mage::getStoreConfig('amshopby/seo/hide_attributes') && isset($xAttributeValuesUnique[$unKey]))
                        ) {
                            $unKey .= Mage::getStoreConfig('amshopby/seo/special_char');
                        }
                        $hash[$code][$unKey] = $o['option_id'];
                        $xAttributeValuesUnique[$unKey] = true;
                        /*
                         * Keep original label for further use
                         */
                        $this->_optionsLabels[$o['option_id']] = $o['value'];
                    }
                }
            }
            $xAttributeValuesUnique = null;
            $this->_optionsHash = $hash;
		}
		return $this->_optionsHash;
	}
	
	public function getFilterableAttributes()
    {
        if (is_null($this->_attributes)) {
			$collection = Mage::getResourceModel('catalog/product_attribute_collection');
          	$collection
	            ->setItemObjectClass('catalog/resource_eav_attribute')            
	            ->addStoreLabel(Mage::app()->getStore()->getId())
	            ->addIsFilterableFilter()
	            ->setOrder('position', 'ASC');        
            $collection->load();
            $this->_attributes = $collection;
        }
        return $this->_attributes;
    }
    
    /**
     * Get option for specific attribute
     * @param string $attributeCode
     * @return array
     */
    public function getAttributeOptions($attributeCode)
    {
    	$options = array();
    	$all = $this->getAllFilterableOptionsAsHash();    	
    	$attributeCode = str_replace(array('_', '-'), Mage::getStoreConfig('amshopby/seo/special_char'), $attributeCode);
    	if (isset($all[$attributeCode])) {
    		$attributeOptions = $all[$attributeCode];
    		foreach ($attributeOptions as $label => $value) {
    			$options[] = array(
    				'value' => $value,
    				'label' => $this->_optionsLabels[$value]
    			);
    		}
    	}
    	return $options;
    }

	protected function getAllOptions()
	{
		if (is_null($this->_options)) {
			$valuesCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
				->setStoreFilter();

			$valuesCollection->getSelect()->order('sort_order', 'ASC');

			$v = $valuesCollection->toArray();
			$this->_options = $v['items'];

			$valuesCollection = null;
			$v = null;
		}
		return $this->_options;
	}


    public function getRequestedFilterCodes()
    {
        if (!isset($this->_requestedFilterCodes)) {
            $this->_requestedFilterCodes = array();
            $requestedParams = Mage::app()->getRequest()->getParams();

            $attributes = $this->getFilterableAttributes();

            foreach ($attributes as $attribute) {
                /** @var Mage_Eav_Model_Attribute $attribute*/

                $code = $attribute->getData('attribute_code');
                if (array_key_exists($code, $requestedParams)) {
                    $this->_requestedFilterCodes[$code] = $requestedParams[$code];
                }
            }
        }
        return $this->_requestedFilterCodes;
    }
}