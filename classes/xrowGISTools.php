<?php
//TODO: Check if this is anywhere in use...if not mark it as deprecated or better remove it. SB
class xrowGISTools
{

    static function citiesBySubtree( eZContentObjectTreeNode $node, $params )
    {
        if ( $node === null )
        {
            return array();
        }
        $db = eZDB::instance();
        $list = $db->arrayQuery( "SELECT city, count(city) as 'count' FROM ezxgis_position, ezcontentobject, ezcontentobject_attribute, ezcontentobject_tree
            WHERE ezxgis_position.contentobject_attribute_id = ezcontentobject_attribute.id
            AND ezcontentobject_attribute.contentobject_id = ezcontentobject.id
            AND ezcontentobject_tree.contentobject_id = ezcontentobject.id
            AND ezcontentobject.current_version = ezxgis_position.contentobject_attribute_version
            AND city != ''
            AND ezcontentobject_tree.path_string LIKE '{$node->attribute('path_string')}%'
            GROUP BY city
            ORDER BY count desc", $params );
        
        return $list;
    }
}