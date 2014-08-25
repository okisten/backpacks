<?php
/**
* @copyright Amasty.
*/ 
class Amasty_Shopby_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $filters = null;
    protected $attributesSettings = null;
    protected $icons   = null;
    
    const XML_PATH_SEO_PRICE_NOFOLLOW       = 'amshopby/seo/price_nofollow';
    const XML_PATH_SEO_PRICE_NOINDEX        = 'amshopby/seo/price_noindex';
    const XML_PATH_SEO_PRICE_RELNOFOLLOW    = 'amshopby/seo/price_rel_nofollow';

    const CACHE_LIFETIME = 10800; // half an hour // todo: move to config?
    
    public function getBlockCacheLifetime()
    {
        return self::CACHE_LIFETIME;
    }
    

    
    public function getSeoPriceNofollow()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_SEO_PRICE_NOFOLLOW);
    }
    
    public function getSeoPriceNoindex()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_SEO_PRICE_NOINDEX);
    }
    
    public function getSeoPriceRelNofollow()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_SEO_PRICE_RELNOFOLLOW);
    }
    
    protected function _getFilters()
    {
        if (is_null($this->filters)){
            //get all possible filters as collection
            $filterCollection = Mage::getResourceModel('amshopby/filter_collection')
                    ->addFieldToFilter('show_on_view', 1)
                    ->addTitles();
            // convert to array        
            $filters = array();    
            foreach ($filterCollection as $filter){
                $filters[$filter->getId()] = $filter;
            }   
            $this->filters = $filters;
        }
        return $this->filters;
    }

    public function getAttributesSettings()
    {
        if (is_null($this->attributesSettings))
        {
            $attributes = Mage::registry('amshopby_attributes');
            if (!$attributes)
            {
                //additional filter properties
                $attrCollection = Mage::getResourceModel('amshopby/filter_collection')
                    ->load();

                $attributes = array();
                foreach ($attrCollection as $row){
                    $attributes[$row->getAttributeId()] = $row;
                }
            }
            $this->attributesSettings = $attributes;
        }
        return $this->attributesSettings;
    }


    public function init()
    {
        // make sure we call this only once
        if (!is_null($this->icons))
            return;
            
        $filters = $this->_getFilters();

        $optionCollection = Mage::getResourceModel('amshopby/value_collection')
            ->addPositions()
            ->addFieldToFilter('img_medium', array('gt' => ''))  
            ->addValue();  
            
        $this->icons = array();
        if (!$filters)
            return;
            
        $hlp = Mage::helper('amshopby/url');
        foreach ($optionCollection as $opt){
            
            $filterId = $opt->getFilterId();
            // it is possible when "use on view" = "false"
            if (empty($filters[$filterId]))
                continue;
                
            $filter = $filters[$filterId];
                            
            // seo urls fix when different values        
            $opt->setTitle($opt->getValue() ? $opt->getValue() : $opt->getTitle());
                
            $img  = $opt->getImgMedium();
            $code = $filter->getAttributeCode();
            $url  = $hlp->getOptionUrl($code, $opt->getTitle(), $opt->getOptionId());

            $this->icons[$opt->getOptionId()] = array(
                'url'   => str_replace('___SID=U&','', $url),
                'title' => $opt->getTitle(),
                'descr' => $opt->getDescr(),
                'img'   => Mage::getBaseUrl('media') . 'amshopby/' . $img,  
                'pos'   => $filter->getPosition(),  
                'pos2'  => $opt->getSortOrder(),  
            );    
        }
        
    }    
    
    /**
     * Returns HTML with attribute images
     *
     * @param Mage_Catalog_Model_Product $product
     * @param string $mode (view, list, grid)
     * @param array $names arrtibute codes to show images for
     * @param bool $exclude flag to indicate taht we need to show all attributes beside specified in $names
     * @return unknown
     */
    public function showLinks($product, $mode='view', $names=array(), $exclude=false)
    {
        if ('view' == $mode){
            $this->init(array($product));    
        }
        $filters = $this->_getFilters();
        
        $items = array();
        foreach ($filters as $filter){
            $code = $filter->getAttributeCode(); 
            if (!$code){
                continue;
            }
            
            if ($names && in_array($code, $names) && $exclude)
                continue;
                
            if ($names && !in_array($code, $names) && !$exclude)
                continue;
            
            $optIds  = trim($product->getData($code), ','); 
            if (!$optIds && $product->isConfigurable()){
                $usedProds = $product->getTypeInstance(true)->getUsedProducts(null, $product);
                foreach ($usedProds as $child){
                    if ($child->getData($code)){
                        $optIds .= $child->getData($code) . ',';
                    }
                }
            }
            
            if ($optIds){
                $optIds = explode(',', $optIds);
                $optIds = array_unique($optIds);
                foreach ($optIds as $id){
                    if (!empty($this->icons[$id])){
                        $items[] = $this->icons[$id];
                    }
                }
            }
        }  
       
        //sort by position in the layered navigation
        usort($items, array('Amasty_Shopby_Helper_Data', '_srt'));
        
        //create block
        $block = Mage::getModel('core/layout')->createBlock('core/template')
            ->setArea('frontend')
            ->setTemplate('amshopby/links.phtml');
        $block->assign('_type', 'html')
            ->assign('_section', 'body')        
            ->setLinks($items)
            ->setMode($mode); // to be able to created different html
             
        return $block->toHtml();          
    }
    
    public static function _srt($a, $b)
     {
        $res = ($a['pos'] < $b['pos']) ? -1 : 1;
        if ($a['pos'] == $b['pos']){ 
            if ($a['pos2'] == $b['pos2'])
                $res = 0;
            else 
                $res = ($a['pos2'] < $b['pos2']) ? -1 : 1;
        }
        
        return $res;
     }
    
    public function isVersionLessThan($major=5, $minor=3)
    {
        $curr = explode('.', Mage::getVersion()); // 1.3. compatibility
        $need = func_get_args();
        foreach ($need as $k => $v){
            if ($curr[$k] != $v)
                return ($curr[$k] < $v);
        }
        return false;
    }
    
    /**
     * Gets params (6,17,89) from the request as array and sanitize them
     *
     * @param string $key attribute code
     * @return array
     */
    public function getRequestValues($key)
    {
       $v = Mage::app()->getRequest()->getParam($key);
       
       if (is_array($v)){//smth goes wrong
           return array();
       }
       
       if (preg_match('/^[0-9,]+$/', $v)){
            $v = array_unique(explode(',', $v));
       }
       else { 
            $v = array();
       }
       
       return $v;       
    } 
    
    /**
     * Check that amlanding is installed and filter enabled
     * @return boolean
     */
    public function landingNewFilter()
    {
        return ('true' == (string)Mage::getConfig()->getNode('modules/Amasty_Xlanding/active') && Mage::helper('amlanding')->newFilterActive());
    }

    public function error404()
    {
        Mage::app()->getResponse()
            ->setHeader('HTTP/1.1','404 Not Found')
            ->setHeader('Status','404 File not found');

        $pageId = Mage::getStoreConfig(Mage_Cms_Helper_Page::XML_PATH_NO_ROUTE_PAGE);
        if (!Mage::helper('cms/page')->renderPage(Mage::app()->getFrontController()->getAction(), $pageId)) {
            header('Location: /');
            exit;
        }
        Mage::app()->getResponse()->sendResponse();
        exit;
    }

    /**
     * Display 404 error if multiple values was selected for 'Single Choice Only' attributes
     */
    public function restrictMultipleSelection()
    {
        $settings = Mage::helper('amshopby')->getAttributesSettings();
        $codes = Mage::helper('amshopby/attributes')->getRequestedFilterCodes();
        $attributes = Mage::helper('amshopby/attributes')->getFilterableAttributes();

        $attributesHash = array();
        foreach ($attributes as $attribute)
        {
            $attributesHash[$attribute->getAttributeCode()] = $attribute;
        }

        foreach ($codes as $code => $value)
        {
            if (false !== strpos($value, ',')) // Multiple values
            {
                $attribute = $attributesHash[$code];
                if ($settings[$attribute->getId()]->getSingleChoice())
                {
                    $this->error404();
                }
            }
        }
    }
}