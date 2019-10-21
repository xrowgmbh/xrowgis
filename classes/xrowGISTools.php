<?php
class xrowGISTools
{

    static function citiesBySubtree( eZContentObjectTreeNode $node, $params )
    {
        if ( $node === null )
        {
            return array();
        }
        $cacheTime = intval( 3600 * 3 );
        $db = eZDB::instance();
        $params['object_state'] = (int) $params['object_state'];

        $object_state = ($params['object_state']!=null) ? true : false;
        
        $subselect = ($object_state)?"JOIN ezcobj_state_link AS ezlink ON ezlink.contentobject_id = x.obj_id AND ezlink.contentobject_state_id = {$params['object_state']}" : "";

        $cacheDir = eZSys::cacheDirectory();
        $currentSiteAccessName = $GLOBALS['eZCurrentAccess']['name'];
        $cacheFilePath = $cacheDir . '/query_cache/' . md5( $currentSiteAccessName . $node->attribute('path_string') . $object_state ) . '.json';
        
        if ( !is_dir( dirname( $cacheFilePath ) ) )
        {
            eZDir::mkdir( dirname( $cacheFilePath ), false, true );
        }
        $cacheFile = eZClusterFileHandler::instance( $cacheFilePath );
        if ( !$cacheFile->exists() or ( time() - $cacheFile->mtime() > $cacheTime ) )
        {
            $list = $db->arrayQuery( "(SELECT city, count(city) as 'count' FROM ezxgis_position AS gisp,
                                          (SELECT ezatt.id AS obj_att_id, x.obj_id, x.obj_current_version FROM ezcontentobject_attribute AS ezatt,
                                              (SELECT ezobj.id AS obj_id, ezobj.current_version AS obj_current_version FROM ezcontentobject AS ezobj,
                                                  (SELECT contentobject_id FROM ezcontentobject_tree AS eztree
                                                       WHERE eztree.path_string LIKE '{$node->attribute('path_string')}%' AND eztree.is_hidden = 0 AND eztree.is_invisible = 0
                                                  ) AS z
                                               WHERE z.contentobject_id = ezobj.id
                                              ) AS x
                                              {$subselect}
                                           WHERE ezatt.contentobject_id = x.obj_id
                                          ) AS y
                                       WHERE city != '' AND gisp.contentobject_attribute_id = y.obj_att_id AND gisp.contentobject_attribute_version = y.obj_current_version
                                       GROUP BY city ORDER BY null) ORDER BY count desc", array('column' => 'city') );
            $cacheFile->storeContents( serialize( $list ), 'query_cache', 'json' );
        } else {
            $list = unserialize( $cacheFile->fetchContents() );
        }
        return $list;
    }
}