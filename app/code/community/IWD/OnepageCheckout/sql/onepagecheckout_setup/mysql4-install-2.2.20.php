<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$installer->setConfigData('customer/address_templates/oneline',
'{{depend prefix}}{{var prefix}} {{/depend}}{{var firstname}} {{depend middlename}}{{var middlename}} {{/depend}}{{var lastname}}{{depend suffix}} {{var suffix}}{{/depend}}, {{depend street}} {{var street}}, {{/depend}}{{depend city}} {{var city}}, {{/depend}}{{depend region}} {{var region}} {{/depend}}{{depend postcode}} {{var postcode}}, {{/depend}}{{depend company}} {{var company}}, {{/depend}}{{var country}}');
Mage::log('success');
$installer->endSetup();