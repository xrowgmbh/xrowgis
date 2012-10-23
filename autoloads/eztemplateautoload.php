<?php

// Operator autoloading

$eZTemplateOperatorArray = array();
$eZTemplateOperatorArray[] = array( 'script' => 'extension/xrowgis/autoloads/str_replace.php',
                                    'class' => 'strReplaceOperator',
                                    'operator_names' => array( 'str_replace' ) );

$eZTemplateOperatorArray[] = array( 'script' => 'extension/xrowgis/autoloads/attribute_list.php',
                                    'class' => 'attributeListOperator',
                                    'operator_names' => array( 'list_by_attribute' ) );

?>