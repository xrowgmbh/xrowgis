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
        
        $subselect = ($object_state)?"JOIN ezcobj_state_link AS ezlink ON ezlink.contentobject_id = ezobj.id AND ezlink.contentobject_state_id = {$params['object_state']}" : "";

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
            $list = $db->arrayQuery( "SELECT city, count(city) as 'count' FROM ezxgis_position AS gisp
                                          JOIN ezcontentobject_attribute AS ezatt
                                              ON gisp.contentobject_attribute_id = ezatt.id
                                          JOIN ezcontentobject as ezobj
                                              ON ezatt.contentobject_id = ezobj.id
                                              AND ezobj.current_version = gisp.contentobject_attribute_version
                                          JOIN ezcontentobject_tree as eztree
                                              ON ezobj.id = eztree.contentobject_id
                                              AND eztree.path_string LIKE '{$node->attribute('path_string')}%'
                                              AND eztree.is_hidden = 0
                                              AND eztree.is_invisible = 0
                                          {$subselect}
                                       WHERE city != ''
                                       GROUP BY city
                                       ORDER BY count desc", array('column' => 'city') );
            $cacheFile->storeContents( serialize( $list ), 'query_cache', 'json' );
        }
        else
        {
            $list = unserialize( $cacheFile->fetchContents() );
        }
        return $list;
    }
}