<?php
class xrowGISTools
{

    static function citiesBySubtree( eZContentObjectTreeNode $node, $params )
    {
        if ( $node === null )
        {
            return array();
        }
        $db = eZDB::instance();
		$params['object_state'] = (int) $params['object_state'];
		
		$object_state = ($params['object_state']!=null) ? true : false;
		
		$subselect = ($object_state)?"AND (ezcobj_state_link.contentobject_id = ezcontentobject.id and ezcobj_state_link.contentobject_state_id = {$params['object_state']})" : "";
		$additional_tables = ($object_state) ? ", ezcobj_state_link" : "";

		$list = $db->arrayQuery( "SELECT city, count(city) as 'count' FROM ezxgis_position, ezcontentobject, ezcontentobject_attribute, ezcontentobject_tree {$additional_tables}
            WHERE ezxgis_position.contentobject_attribute_id = ezcontentobject_attribute.id
            AND ezcontentobject_attribute.contentobject_id = ezcontentobject.id
            AND ezcontentobject_tree.contentobject_id = ezcontentobject.id
            AND ezcontentobject.current_version = ezxgis_position.contentobject_attribute_version
            AND city != ''
            AND ezcontentobject_tree.path_string LIKE '{$node->attribute('path_string')}%'
			AND ezcontentobject_tree.is_hidden = 0
			AND ezcontentobject_tree.is_invisible = 0
			{$subselect}
            GROUP BY city
            ORDER BY count desc", array('column' => 'city') );

        return $list;
    }
}
