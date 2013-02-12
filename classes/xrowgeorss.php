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
        $parent = self::fetchParent();
        
        if ( $parent instanceof eZContentObject )
        {
            $treeNodes = self::fetchTreeNode();
            $this->feed = new ezcFeed();
            $this->point = new gPoint();
            
            $this->feed->generator = eZSys::serverURL();
            $link = '/xrowgis/georss/' . $this->nodeID;
            $this->feed->id = self::transformURI( null, true, 'full' );
            $this->feed->title = $parent->attribute( 'name' );
            $this->feed->link = eZSys::serverURL();
            $this->feed->description = 'GEORSS Feed Channel';
            $this->feed->language = eZLocale::currentLocaleCode();
            
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
                    
                    if ( $dm[$this->cache['cache'][$node->classIdentifier()]['default']]->attribute( 'has_content' ) )
                    {
                        $this->cache['cache'][$node->classIdentifier()]['text'] = $this->cache['cache'][$node->classIdentifier()]['default'];
                    }
                    else
                    {
                        if ( ! empty( $this->cache['cache'][$node->classIdentifier()]['alternative'] ) )
                        {
                            $this->cache['cache'][$node->classIdentifier()]['text'] = $this->cache['cache'][$node->classIdentifier()]['alternative'];
                        }
                        else
                        {
                            $this->cache['cache'][$node->classIdentifier()]['text'] = $this->cache['cache'][$node->classIdentifier()]['default'];
                        }
                    }
                    //add ContentobjectID to Description 
                    $item->description = htmlspecialchars( '<span style="display:none;" class="coID" data-id="'.$node->attribute('contentobject_id').'"></span>' );
                    
                    if ( $dm[$this->cache['cache'][$node->classIdentifier()]['image']] instanceof eZContentObjectAttribute )
                    {
                        if ( $dm[$this->cache['cache'][$node->classIdentifier()]['image']]->attribute( 'has_content' ) )
                        {
                            if ( ( $content = $dm[$this->cache['cache'][$node->classIdentifier()]['image']]->attribute( 'content' ) ) instanceof eZContentObject )
                            {
                                $imageID = $content->attribute( 'id' );
                            }
                            else
                            {
                                $imageID = $content['relation_list'][0]['contentobject_id'];
                            }
                            if ( ( $imageObject = eZContentObject::fetch( $imageID ) ) instanceof eZContentObject && $imageObject->canRead() )
                            {
                                $imageObject = $imageObject->dataMap();
                                foreach ( $imageObject as $coAttribute )
                                {
                                    if ( $coAttribute->attribute( 'data_type_string' ) == eZImageType::DATA_TYPE_STRING )
                                    {
                                        if ( $coAttribute->content()->attribute( 'is_valid' ) )
                                        {
                                            $content = $coAttribute->content();
                                            if ( ! empty( $this->cache['cache'][$node->classIdentifier()]['imageAlias'] ) || $this->cache['cache'][$node->classIdentifier()]['image'] != 'original' )
                                            {
                                                $prefix = "_{$this->cache['cache'][$node->classIdentifier()]['imageAlias']}";
                                                $content->imageAlias( $this->cache['cache'][$node->classIdentifier()]['imageAlias'] );
                                            }
                                            $image = eZSys::instance()->serverURL() . '/' . $content->ContentObjectAttributeData["DataTypeCustom"]["alias_list"]["original"]["dirpath"] . '/' . $content->ContentObjectAttributeData["DataTypeCustom"]["alias_list"]["original"]["basename"] . $prefix . '.' . $content->ContentObjectAttributeData["DataTypeCustom"]["alias_list"]["original"]["suffix"];
                                            $item->description .= '<image class="' . $this->cache['cache'][$node->classIdentifier()]['imageStyle'] . '" alt="' . $coAttribute->content()->attribute( 'alternative_text' ) . '" src="' . $image . '">';
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if ( $dm[$this->cache['cache'][$node->classIdentifier()]['text']]->attribute( 'data_type_string' ) == eZXMLTextType::DATA_TYPE_STRING )
                    {
                        $outputHandler = new xrowRSSOutputHandler( $dm[$this->cache['cache'][$node->classIdentifier()]['text']]->attribute( 'data_text' ), false );
                        $htmlContent = $outputHandler->outputText();
//                        $item->description .= htmlspecialchars( trim( $htmlContent ).'<span style="display:none;" class="coID" data-id="'.$node->attribute('contentobject_id').'"></span>' );
                    }
                    else
                    {
                        $item->description .= htmlspecialchars( $dm[$this->cache['cache'][$node->classIdentifier()]['text']]->attribute( 'content' ) );
                    }
                    
                    $this->point->setLongLat( $dm[$this->cache['cache'][$node->classIdentifier()]['gis']]->attribute( 'content' )->longitude, $dm[$this->cache['cache'][$node->classIdentifier()]['gis']]->attribute( 'content' )->latitude );
                    $this->point->convertLLtoTM();
                    
                    ezcFeed::registerModule( 'GeoRss', 'ezcFeedGeoRssModule', 'georss' );
                    $module = $item->addModule( 'GeoRss' );
                    
                    $module->lat = $this->point->utmNorthing;
                    $module->long = $this->point->utmEasting;
                }
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

        if ( ( is_array( $treeNode = eZContentObjectTreeNode::subTreeByNodeID( $params, $this->nodeID ) ) ) && ! empty( $treeNode ) )
        {
            return $treeNode;
        }
        else
        {
            if ( ( $treeNode = eZContentObjectTreeNode::fetch( $this->nodeID ) ) instanceof eZContentObjectTreeNode )
            {
                return array( 
                    $treeNode 
                );
            }
        }
        return null;
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
        $gisini = eZINI::instance( "xrowgis.ini" );
        $defaultIdentifier = $gisini->variable( "GeoRSSAttributes", "AttributeIdentifier" );
        $alternativeIdentifier = $gisini->variable( "GeoRSSAttributes", "AlternativeIdentifier" );
        $imageIdentifier = $gisini->variable( "GeoRSSImage", "ImageIdentifier" );
        $imageAlias = $gisini->variable( "GeoRSSImage", "ImageAlias" );
        $imageStyle = $gisini->variable( "GeoRSSImage", "ImageStyle" );
        
        foreach ( $results as $key => $result )
        {
            $retVal['class_identifier'][] = $results[$key]['identifier'];
            
            $retVal['cache'][$results[$key]['identifier']]['default'] = $defaultIdentifier[$results[$key]['identifier']];
            $retVal['cache'][$results[$key]['identifier']]['alternative'] = $alternativeIdentifier[$results[$key]['identifier']];
            $retVal['cache'][$results[$key]['identifier']]['image'] = $imageIdentifier[$results[$key]['identifier']];
            $retVal['cache'][$results[$key]['identifier']]['imageAlias'] = $imageAlias;
            $retVal['cache'][$results[$key]['identifier']]['imageStyle'] = $imageStyle;
            
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