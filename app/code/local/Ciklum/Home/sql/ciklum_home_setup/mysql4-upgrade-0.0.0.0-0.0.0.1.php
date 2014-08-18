<?php
/* @var $this Mage_Eav_Model_Entity_Setup */

$installer = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('core_setup');
$installer->startSetup();


$installer->removeAttribute(Mage_Catalog_Model_Product::ENTITY, 'ciklum_home_is_featured');
$installer->addAttribute( Mage_Catalog_Model_Product::ENTITY, 'ciklum_home_is_featured', array(
    'group' => 'General',
    'type' => 'int',
    'backend' => '',
    'frontend' => '',
    'label' => 'Show as featured on home page',
    'input' => 'boolean',
    'source' => 'eav/entity_attribute_source_boolean',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible' => true,
    'required' => false,
    'user_defined' => true,
    'default' => '',
    'searchable' => false,
    'filterable' => false,
    'comparable' => false,
    'visible_on_front' => true,
    'visible_in_advanced_search' => false,
    'used_in_product_listing' => true,
    'unique' => false,
    'sort_order' => 10,
    //'apply_to' => 'simple',
) );

$installer->endSetup();
