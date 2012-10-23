<?php

/*
DROP TABLE IF EXISTS ezxgis_position;
CREATE TABLE  `ezxgis_position` (
  `contentobject_attribute_id` int(11) NOT NULL default '0',
  `contentobject_attribute_version` int(11) NOT NULL default '0',
  `latitude` float NOT NULL default '0',
  `longitude` float NOT NULL default '0',
  `street` varchar(255) default NULL,
  `zip` varchar(20) default NULL,
  `district` varchar(255) default NULL,
  `city` varchar(255) default NULL,
  `state` varchar(255) default NULL,
  `country` varchar(255) default NULL
)  ENGINE=InnoDB DEFAULT CHARSET=utf8;
*/

class xrowGISPosition extends eZPersistentObject
{
    private $relation = null;

    /*!
     Initializes a new xrowGISPosition.
    */
    function xrowGISPosition( $row )
    {
        $this->eZPersistentObject( $row );
    }

    /*!
     \reimp
    */
    public static function definition()
    {
        return array( 
            "fields" => array( 
                "contentobject_attribute_id" => array( 
                    'name' => 'contentobject_attribute_id' , 
                    'datatype' => 'integer' , 
                    'default' => 0 , 
                    'required' => true 
                ) , 
                "contentobject_attribute_version" => array( 
                    'name' => 'contentobject_attribute_version' , 
                    'datatype' => 'integer' , 
                    'default' => 0 , 
                    'required' => true 
                ) , 
                "latitude" => array( 
                    'name' => "latitude" , 
                    'datatype' => 'float' , 
                    'default' => '' , 
                    'required' => true 
                ) , 
                "longitude" => array( 
                    'name' => "longitude" , 
                    'datatype' => 'float' , 
                    'default' => '' , 
                    'required' => true 
                ) , 
                "street" => array( 
                    'name' => "street" , 
                    'datatype' => 'string' , 
                    'default' => '' , 
                    'required' => false 
                ) , 
                "zip" => array( 
                    'name' => "zip" , 
                    'datatype' => 'string' , 
                    'default' => '' , 
                    'required' => false 
                ) , 
                "district" => array( 
                    'name' => "district" , 
                    'datatype' => 'string' , 
                    'default' => '' , 
                    'required' => false 
                ) , 
                "city" => array( 
                    'name' => "city" , 
                    'datatype' => 'string' , 
                    'default' => '' , 
                    'required' => false 
                ) , 
                "state" => array( 
                    'name' => "state" , 
                    'datatype' => 'string' , 
                    'default' => '' , 
                    'required' => false 
                ) , 
                "country" => array( 
                    'name' => "country" , 
                    'datatype' => 'string' , 
                    'default' => '' , 
                    'required' => false 
                ) 
            ) , 
            "keys" => array( 
                "contentobject_attribute_id" , 
                "contentobject_attribute_version" 
            ) , 
            'function_attributes' => array( 
                'is_valid' => 'isValid' , 
                'object' => 'object' 
            ) , 
            "class_name" => "xrowGISPosition" , 
            "name" => "ezxgis_position" 
        );
    }

    public function object()
    {
        if ( $this->relation !== null )
        {
            return $this->relation;
        }
        $contentObjectAttribute = eZContentObjectAttribute::fetch( $this->contentobject_attribute_id, $this->contentobject_attribute_version );
        if ( is_numeric( $contentObjectAttribute->DataInt ) )
        {
            #fetching related object
            $co = eZContentObject::fetch( $contentObjectAttribute->DataInt );
            if ( $co instanceof eZContentObject )
            {
                $this->relation = $co;
            }
            else
            {
                $this->relation = false;
            }
        }
        else
        {
            $this->relation = false;
        }
        return $this->relation;
    }

    public function isValid()
    {
        //todo
        return true;
    }

    //@TODO: Check if it is allready in use/ needed
    // works only with guass krüger
    public static function &fetchByDistance( $fromx, $fromy, $distance = 100000, $limit = null )
    {
        $asObject = true;
        $minx = $fromx - $distance / 2;
        $maxx = $fromx + $distance / 2;
        $miny = $fromy - $distance / 2;
        $maxy = $fromy + $distance / 2;
        $db = eZDB::instance();
        $list = eZPersistentObject::fetchObjectList( xrowGISPosition::definition(), null, array( 
            'x' => array( 
                false , 
                array( 
                    $minx , 
                    $maxx 
                ) 
            ) , 
            'y' => array( 
                false , 
                array( 
                    $miny , 
                    $maxy 
                ) 
            ) 
        ), null, $limit, $asObject );
        
        $list_count = eZPersistentObject::fetchObjectList( self::definition(), null, array( 
            'x' => array( 
                false , 
                array( 
                    $minx , 
                    $maxx 
                ) 
            ) , 
            'y' => array( 
                false , 
                array( 
                    $miny , 
                    $maxy 
                ) 
            ) 
        ), null, null, true );
        
        foreach ( $list as $row )
        {
            $coa = eZContentObjectAttribute::fetch( $row->attribute( 'contentobject_attribute_id' ), $row->attribute( 'contentobject_attribute_version' ) );
            if ( is_object( $coa ) )
                $co = eZContentObject::fetch( $coa->attribute( 'contentobject_id' ) );
            if ( is_object( $co ) )
                $result[] = $co->attribute( 'main_node' );
        }
        return array( 
            "SearchResult" => $result , 
            "SearchCount" => count( $list_count ) , 
            "StopWordArray" => $stopWordArray 
        );
    }

    // works only with guass krüger
    public static function search( $x, $y, $distance, $params = array(), $searchTypes = array() )
    {
        $x = (float) $x;
        $y = (float) $y;
        if ( isset( $params['SearchLimit'] ) )
            $limit["limit"] = $params['SearchLimit'];
        
        if ( isset( $params['SearchOffset'] ) )
            $limit["offset"] = $params['SearchOffset'];
        
        if ( isset( $limit ) )
            $result = xrowGISPosition::fetchByDistance( $x, $y, $distance, $limit );
        else
            $result = xrowGISPosition::fetchByDistance( $x, $y, $distance );
        return $result;
    }

    public static function fetch( $attribute_id, $version, $asObject = true )
    {
        $list = eZPersistentObject::fetchObjectList( self::definition(), null, array( 
            'contentobject_attribute_id' => $attribute_id , 
            'contentobject_attribute_version' => $version 
        ), null, null, $asObject );
        if ( isset( $list[0] ) )
            return $list[0];
    }
}
?>