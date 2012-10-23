<?php

class xrowGEORSS
{
    public $nodeID;
    public $feed;
    public $cache;
    public $point;

    function __construct( $nodeID )
    {
        $this->nodeID = $nodeID;
        self::generateGEORSSFeed();
    }

    function generateGEORSSFeed()
    {
        $treeNodes = self::fetchTreeNode();
        $parent = self::fetchParent();
        $this->feed = new ezcFeed();
        $this->point = new gPoint();
        
        $this->feed->generator = eZSys::serverURL();
        $link = '/xrowgis/georssserver/' . $this->nodeID;
        $this->feed->id = self::transformURI( null, true, 'full' );
        $this->feed->title = $parent->attribute( 'name' );
        $this->feed->link = eZSys::serverURL();
        $this->feed->description = 'GEORSS Feed Channel';
        $this->feed->language = eZLocale::currentLocaleCode();
        $list = eZContentClass::fetchAllClasses();
        
        foreach ( $treeNodes as $node )
        {
            $dm = $node->dataMap();
            if ( $dm[$this->cache['cache'][$node->classIdentifier()]['gis']]->attribute( 'has_content' ) && ( $dm[$this->cache['cache'][$node->classIdentifier()]['gis']]->attribute( 'content' )->latitude != 0 || $dm[$this->cache['cache'][$node->classIdentifier()]['gis']]->attribute( 'content' )->longitude != 0 ) )
            {
                $item = $this->feed->add( 'item' );
                $item->title = $node->getName();
                $link = $node->attribute( 'url_alias' );
                $item->link = self::transformURI( $link, true, 'full' );
                $item->id = self::transformURI( $link, true, 'full' );
                
                if($node->classIdentifier() == 'article')
                {
                    $this->cache['cache'][$node->classIdentifier()]['text']='teaser_text';
                }
                
                if ( $dm[$this->cache['cache'][$node->classIdentifier()]['text']]->attribute( 'data_type_string' ) == eZXMLTextType::DATA_TYPE_STRING )
                {
                    $outputHandler = new xrowRSSOutputHandler( $dm[$this->cache['cache'][$node->classIdentifier()]['text']]->attribute( 'data_text' ), false );
                    $htmlContent = $outputHandler->outputText();
                    $item->description = htmlspecialchars( trim( $htmlContent ) );
                }
                else
                {
                    $item->description = htmlspecialchars( $dm[$this->cache['cache'][$node->classIdentifier()]['text']]->attribute( 'content' ) );
                }
                $this->point->setLongLat( $dm[$this->cache['cache'][$node->classIdentifier()]['gis']]->attribute( 'content' )->longitude, $dm[$this->cache['cache'][$node->classIdentifier()]['gis']]->attribute( 'content' )->latitude );
                $this->point->convertLLtoTM();

                ezcFeed::registerModule( 'GeoRss', 'ezcFeedGeoRssModule', 'georss' );
                $module = $item->addModule( 'GeoRss' );
                //$module->point = $attribute->attribute( 'content' )->latitude;
                $module->lat = $this->point->utmNorthing;
                $module->long = $this->point->utmEasting;
            
            }
        }
        return $this->feed;
    }

    function fetchTreeNode()
    {
        self::getClasses();
        $params = array();
        $params['ClassFilterType'] = 'include';
        $params['ClassFilterArray'] = $this->cache['class_identifier'];
        #@TODO add custom filter to only select items with gis content
        

        return eZContentObjectTreeNode::subTreeByNodeID( $params, $this->nodeID );
    }

    function fetchParent()
    {
        return eZContentObject::fetchByNodeID( $this->nodeID );
    }

    function getClasses()
    {
        $db = eZDB::instance();
        $sql = "SELECT DISTINCT I.contentclass_id, N.identifier FROM `ezcontentclass_attribute` AS I INNER JOIN `ezcontentclass` AS N On I.contentclass_id = N.id WHERE I.data_type_string ='" . xrowGIStype::DATATYPE_STRING . "'";
        
        $results = $db->arrayQuery( $sql );
        $retVal = array();
        
        foreach ( $results as $key => $result )
        {
            $sql = "SELECT identifier FROM `ezcontentclass_attribute` WHERE (data_type_string = '" . eZXMLTextType::DATA_TYPE_STRING . "' OR data_type_string = '" . eZTextType::DATA_TYPE_STRING . "') AND contentclass_id = '" . $results[$key]['contentclass_id'] . "' ORDER BY data_type_string DESC";
            $res = $db->arrayQuery( $sql );
            $retVal['class_identifier'][] = $results[$key]['identifier'];
            $retVal['cache'][$results[$key]['identifier']]['text'] = $res[0]['identifier'];
            
            $sql = "SELECT identifier FROM `ezcontentclass_attribute` WHERE data_type_string = '" . xrowGIStype::DATATYPE_STRING . "'  AND contentclass_id = '" . $results[$key]['contentclass_id'] . "'";
            $res = $db->arrayQuery( $sql );
            $retVal['cache'][$results[$key]['identifier']]['gis'] = $res[0]['identifier'];
        }
        $this->cache = $retVal;
    }

    function transformURI( $href, $ignoreIndexDir = false, $serverURL = null )
    {
        if ( $serverURL === null )
        {
            $serverURL = eZURI::getTransformURIMode();
        }
        if ( preg_match( "#^[a-zA-Z0-9]+:#", $href ) || substr( $href, 0, 2 ) == '//' )
            return false;
        
        if ( strlen( $href ) == 0 )
            $href = '/';
        else 
            if ( $href[0] == '#' )
            {
                $href = htmlspecialchars( $href );
                return true;
            }
            else 
                if ( $href[0] != '/' )
                {
                    $href = '/' . $href;
                }
        
        $sys = eZSys::instance();
        $dir = ! $ignoreIndexDir ? $sys->indexDir() : $sys->wwwDir();
        $serverURL = $serverURL === 'full' ? $sys->serverURL() : '';
        $href = $serverURL . $dir . $href;
        if ( ! $ignoreIndexDir )
        {
            $href = preg_replace( "#^(//)#", "/", $href );
            $href = preg_replace( "#(^.*)(/+)$#", "\$1", $href );
        }
        $href = str_replace( '&amp;amp;', '&amp;', htmlspecialchars( $href ) );
        
        if ( $href == "" )
            $href = "/";
        
        return $href;
    }

}
?>