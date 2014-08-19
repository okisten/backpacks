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

class Ciklum_ProductGrid_Block_Widget_Grid_Column_Renderer_Frontend
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
    protected function _getStore()
    {
        $storeId = (int) Mage::app()->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    public function getUrl($route = '', $params = array())
    {
        return $this->_getStore()->getUrl($route, $params);
    }

}