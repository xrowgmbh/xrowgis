<?php

class xrowGIStype extends eZDataType
{
    const DATATYPE_STRING = 'xrowgis';

    function xrowGIStype()
    {
        $this->eZDataType( self::DATATYPE_STRING, ezpI18n::tr( 'kernel/classes/datatypes', "Geographic Information Systems", 'Datatype name' ), array( 
            'serialize_supported' => true , 
            'translation_allowed' => false 
        ) );
    }

    /*!
    Validates all variables given on content class level
     \return EZ_INPUT_VALIDATOR_STATE_ACCEPTED or EZ_INPUT_VALIDATOR_STATE_INVALID if
             the values are accepted or not
    */
    function validateClassAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        return eZInputValidator::STATE_ACCEPTED;
    }

    /*!
     Fetches all variables inputed on content class level
     \return true if fetching of class attributes are successfull, false if not
    */
    function fetchClassAttributeHTTPInput( $http, $base, $classAttribute )
    {
        return true;
    }

    /*!
     Validates input on content object level
     \return EZ_INPUT_VALIDATOR_STATE_ACCEPTED or EZ_INPUT_VALIDATOR_STATE_INVALID if
             the values are accepted or not
    */
    function validateObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        if ( ( $http->hasPostVariable( $base . '_xrowgis_longitude_' . $contentObjectAttribute->attribute( 'id' ) ) and $http->hasPostVariable( $base . '_xrowgis_latitude_' . $contentObjectAttribute->attribute( 'id' ) ) ) || $http->hasPostVariable( $base . '_xrowgis_data_object_relation_id_' . $contentObjectAttribute->attribute( 'id' ) ) )
        {
            $longitude = $http->postVariable( $base . '_xrowgis_longitude_' . $contentObjectAttribute->attribute( 'id' ) );
            $latitude = $http->postVariable( $base . '_xrowgis_latitude_' . $contentObjectAttribute->attribute( 'id' ) );
            $street = $http->postVariable( $base . '_xrowgis_street_' . $contentObjectAttribute->attribute( 'id' ) );
            $zip = $http->postVariable( $base . '_xrowgis_zip_' . $contentObjectAttribute->attribute( 'id' ) );
            $district = $http->postVariable( $base . '_xrowgis_district_' . $contentObjectAttribute->attribute( 'id' ) );
            $city = $http->postVariable( $base . '_xrowgis_city_' . $contentObjectAttribute->attribute( 'id' ) );
            $state = $http->postVariable( $base . '_xrowgis_state_' . $contentObjectAttribute->attribute( 'id' ) );
            $country = $http->postVariable( $base . '_xrowgis_country_' . $contentObjectAttribute->attribute( 'id' ) );
            $relatedObjectID = $http->hasPostVariable( $base . '_xrowgis_data_object_relation_id_' . $contentObjectAttribute->attribute( 'id' ) ) ? $http->postVariable( $base . '_xrowgis_data_object_relation_id_' . $contentObjectAttribute->attribute( 'id' ) ) : null;
            
            if ( ( $contentObjectAttribute->validateIsRequired() && ( ! empty( $relatedObjectID ) || ( ! empty( $street ) && ! empty( $zip ) && ! empty( $city ) && ! empty( $state ) && ! empty( $latitude ) && ! empty( $longitude ) ) ) ) or ( ! $contentObjectAttribute->validateIsRequired() and ( empty( $street ) && empty( $zip ) && empty( $city ) && empty( $state ) && empty( $latitude ) && empty( $longitude ) ) or ( $street && $zip && $city && $state && $latitude && $longitude ) or $relatedObjectID ) )
            {
                $gp = new xrowGISPosition( array( 
                    'contentobject_attribute_id' => $contentObjectAttribute->attribute( 'id' ) , 
                    'contentobject_attribute_version' => $contentObjectAttribute->attribute( 'version' ) , 
                    'latitude' => $latitude , 
                    'longitude' => $longitude , 
                    'street' => $street , 
                    'zip' => $zip , 
                    'district' => $district , 
                    'city' => $city , 
                    'state' => $state , 
                    'country' => $country 
                ) );
                
                if ( $http->hasPostVariable( 'PublishButton' ) )
                {
                    $contentObjectAttribute->Content = $gp;
                    $contentObjectAttribute->setAttribute( 'data_int', $relatedObjectID );
                    $contentObjectAttribute->setAttribute( 'sort_key_int', $relatedObjectID );
                    $contentObjectAttribute->store();
                    
                    $id_array = array();
                    $content_class_ids = eZPersistentObject::fetchObjectList( eZContentClassAttribute::definition(), array( 
                        'id' 
                    ), array( 
                        'data_type_string' => xrowgistype::DATATYPE_STRING 
                    ), null, null, false, false, null, null, null );
                    
                    foreach ( $content_class_ids as $id )
                    {
                        $id_array[] = $id['id'];
                    }
                    $content_class_ids = implode( ',', $id_array );
                    
                    $custom_conds = " AND contentclassattribute_id IN ({$content_class_ids})";
                    
                    for ( $i = 0; $i < 4; $i ++ )
                    {
                        if ( $i == 0 )
                        {
                            $coID = $contentObjectAttribute->attribute( 'contentobject_id' );
                        }
                        
                        $list = eZPersistentObject::fetchObjectList( eZContentObjectAttribute::definition(), null, array( 
                            'sort_key_int' => $coID 
                        ), null, null, true, false, null, null, $custom_conds );
                        $ids = array();
                        foreach ( $list as $item )
                        {
                            $GISCo = eZPersistentObject::fetchObject( xrowGISPosition::definition(), null, array( 
                                'contentobject_attribute_id' => $item->attribute( 'id' ) , 
                                'contentobject_attribute_version' => $item->attribute( 'version' ) 
                            ), true );
                            
                            $GISCo = $contentObjectAttribute->Content;
                            
                            $GISCo->setAttribute( 'contentobject_attribute_id', $item->attribute( 'id' ) );
                            $GISCo->setAttribute( 'contentobject_attribute_version', $item->attribute( 'version' ) );
                            $GISCo->store();
                            
                            $coID = $item->attribute( 'contentobject_id' );
                        }
                    
                    }
     #self::updateRelAttributes( $contentObjectAttribute );
                }
                
                return eZInputValidator::STATE_ACCEPTED;
            }
        }
        $contentObjectAttribute->setValidationError( ezpI18n::tr( 'kernel/classes/datatypes', 'Missing geographic information' ) );
        return eZInputValidator::STATE_INVALID;
    }

    function sortKey( $contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( 'sort_key_int' );
    }

    function sortKeyType()
    {
        return 'int';
    }
/*
    private static function updateRelAttributes( $contentObjectAttribute )
    {
        $id_array = array();
        $content_class_ids = eZPersistentObject::fetchObjectList( eZContentClassAttribute::definition(), array( 
            'id' 
        ), array( 
            'data_type_string' => xrowgistype::DATATYPE_STRING 
        ), null, null, false, false, null, null, null );
        
        foreach ( $content_class_ids as $id )
        {
            $id_array[] = $id['id'];
        }
        $content_class_ids = implode( ',', $id_array );
        
        $custom_conds = " AND contentclassattribute_id IN ({$content_class_ids})";
        
        for ( $i = 0; $i < 4; $i ++ )
        {
            if ( $i == 0 )
            {
                $coID = $contentObjectAttribute->attribute( 'contentobject_id' );
            }
            
            $list = eZPersistentObject::fetchObjectList( eZContentObjectAttribute::definition(), null, array( 
                'sort_key_int' => $coID 
            ), null, null, true, false, null, null, $custom_conds );
            $ids = array();
            foreach ( $list as $item )
            {
                $GISCo = eZPersistentObject::fetchObject( xrowGISPosition::definition(), null, array( 
                    'contentobject_attribute_id' => $item->attribute( 'id' ) , 
                    'contentobject_attribute_version' => $item->attribute( 'version' ) 
                ), true );
                
                $GISCo = $contentObjectAttribute->Content;
                
                $GISCo->setAttribute( 'contentobject_attribute_id', $item->attribute( 'id' ) );
                $GISCo->setAttribute( 'contentobject_attribute_version', $item->attribute( 'version' ) );
                $GISCo->store();
                
                $coID = $item->attribute( 'contentobject_id' );
            }
        
        }
    }
*/
    /*!
     Stores relation to the ezcontentobject_link table
    */
    function storeObjectAttribute( $contentObjectAttribute )
    {
        $gp = $contentObjectAttribute->Content;
        if ( is_object( $gp ) )
        {
            $gp->store();
        }
        
        $contentClassAttributeID = $contentObjectAttribute->ContentClassAttributeID;
        $contentObjectID = $contentObjectAttribute->ContentObjectID;
        $contentObjectVersion = $contentObjectAttribute->Version;
        
        $obj = $contentObjectAttribute->object();
        //get eZContentObjectVersion
        $currVerobj = $obj->version( $contentObjectVersion );
        // get array of language codes
        $transList = $currVerobj->translations( false );
        $countTsl = count( $transList );
        
        if ( ( $countTsl == 1 ) )
        {
            eZContentObject::fetch( $contentObjectID )->removeContentObjectRelation( false, $contentObjectVersion, $contentClassAttributeID, eZContentObject::RELATION_ATTRIBUTE );
        }
        
        $objectID = $contentObjectAttribute->attribute( "data_int" );
        
        if ( $objectID )
        {
            eZContentObject::fetch( $contentObjectID )->addContentObjectRelation( $objectID, $contentObjectVersion, $contentClassAttributeID, eZContentObject::RELATION_ATTRIBUTE );
        }
    }

    /*!
     Fetches the http post var string input and stores it in the data instance.
    */
    function fetchObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        $longitude = $http->postVariable( $base . '_xrowgis_longitude_' . $contentObjectAttribute->attribute( 'id' ) );
        $latitude = $http->postVariable( $base . '_xrowgis_latitude_' . $contentObjectAttribute->attribute( 'id' ) );
        $street = $http->postVariable( $base . '_xrowgis_street_' . $contentObjectAttribute->attribute( 'id' ) );
        $zip = $http->postVariable( $base . '_xrowgis_zip_' . $contentObjectAttribute->attribute( 'id' ) );
        $district = $http->postVariable( $base . '_xrowgis_district_' . $contentObjectAttribute->attribute( 'id' ) );
        $city = $http->postVariable( $base . '_xrowgis_city_' . $contentObjectAttribute->attribute( 'id' ) );
        $state = $http->postVariable( $base . '_xrowgis_state_' . $contentObjectAttribute->attribute( 'id' ) );
        $country = $http->postVariable( $base . '_xrowgis_country_' . $contentObjectAttribute->attribute( 'id' ) );
        $relatedObjectID = $http->hasPostVariable( $base . '_xrowgis_data_object_relation_id_' . $contentObjectAttribute->attribute( 'id' ) ) ? $http->postVariable( $base . '_xrowgis_data_object_relation_id_' . $contentObjectAttribute->attribute( 'id' ) ) : null;
        
        $gp = new xrowGISPosition( array( 
            'contentobject_attribute_id' => $contentObjectAttribute->attribute( 'id' ) , 
            'contentobject_attribute_version' => $contentObjectAttribute->attribute( 'version' ) , 
            'latitude' => $latitude , 
            'longitude' => $longitude , 
            'street' => $street , 
            'zip' => $zip , 
            'district' => $district , 
            'city' => $city , 
            'state' => $state , 
            'country' => $country 
        ) );
        $contentObjectAttribute->Content = $gp;
        return true;
    }

    /*!
     Sets the default value.
    */
    function initializeObjectAttribute( $contentObjectAttribute, $currentVersion, $originalContentObjectAttribute )
    {
        if ( $currentVersion != false )
        {
            $data = $originalContentObjectAttribute->attribute( "content" );
            $contentObjectID = $contentObjectAttribute->attribute( 'contentobject_id' );
            $originalContentObjectID = $originalContentObjectAttribute->attribute( 'contentobject_id' );
            
            if ( is_object( $data ) )
            {
                if ( $contentObjectID != $originalContentObjectID )
                {
                    $gp = new xrowGISPosition( array( 
                        'contentobject_attribute_id' => $contentObjectAttribute->attribute( 'id' ) , 
                        'contentobject_attribute_version' => $contentObjectAttribute->attribute( 'version' ) , 
                        'latitude' => $data->attribute( 'latitude' ) , 
                        'longitude' => $data->attribute( 'longitude' ) , 
                        'street' => $data->attribute( 'street' ) , 
                        'zip' => $data->attribute( 'zip' ) , 
                        'district' => $data->attribute( 'district' ) , 
                        'city' => $data->attribute( 'city' ) , 
                        'state' => $data->attribute( 'state' ) , 
                        'country' => $data->attribute( 'country' ) 
                    ) );
                    $contentObjectAttribute->Content = $gp;
                }
                else
                {
                    
                    if ( $originalContentObjectAttribute->attribute( 'data_int' ) )
                    {
                        $contentObjectAttribute->setAttribute( 'data_int', $originalContentObjectAttribute->attribute( 'data_int' ) );
                        $contentObjectAttribute->setAttribute( 'sort_key_int', $originalContentObjectAttribute->attribute( 'data_int' ) );
                    }
                    $data->setAttribute( 'contentobject_attribute_id', $contentObjectAttribute->attribute( 'id' ) );
                    $data->setAttribute( 'contentobject_attribute_version', $contentObjectAttribute->attribute( 'version' ) );
                    $contentObjectAttribute->setContent( $data );
                }
                $contentObjectAttribute->store();
            }
            else
            {
                $contentObjectAttribute->setContent( null );
            }
        }
        else
        {
            $contentObjectAttribute->setContent( null );
        }
    }

    function arrayToXML( $name, $val )
    {
        $node = $this->createElementNodeFromArray( $name, $val );
        $doc = new DOMDocument();
        $doc->importNode( $node );
        return $doc->saveXML();
    }

    function xmlToArray( $string )
    {
        $doc = DOMDocument::loadXML( $string );
        if ( is_object( $doc ) )
            return $this->createArrayFromDOMNode( $doc->documentElement );
    }

    function attributeXMLToArray( $name )
    {
        $doc = DOMDocument::loadXML( $this->attribute( $name ) );
        if ( is_object( $doc ) )
            return $this->createArrayFromDOMNode( $doc->documentElement );
    }

    function dataArray()
    {
        $doc = DOMDocument::loadXML( $this->attribute( 'data' ) );
        if ( is_object( $doc ) )
            return $this->createArrayFromDOMNode( $doc->documentElement );
    }

    function createArrayFromDOMNode( $domNode )
    {
        $retArray = array();
        
        foreach ( $domNode->childNodes as $childNode )
        {
            if ( ! isset( $retArray[$childNode->nodeName] ) )
            {
                $retArray[$childNode->nodeName] = array();
            }
            
            // If the node has children we create an array for this element
            // and append to it, if not we assign it directly
            if ( $childNode->hasChildNodes() )
            {
                $retArray[$childNode->nodeName][] = $this->createArrayFromDOMNode( $childNode );
            }
            else
            {
                $retArray[$childNode->nodeName] = $this->createArrayFromDOMNode( $childNode );
            }
        }
        foreach ( $domNode->attributes as $attributeNode )
        {
            $retArray[$attributeNode->nodeName] = $attributeNode->nodeValue;
        }
        
        return $retArray;
    }

    function createElementNodeFromArray( $name, $array )
    {
        $doc = new DOMDocument( '1.0', 'utf-8' );
        
        $node = $doc->createElement( $name );
        
        foreach ( $array as $arrayKey => $value )
        {
            if ( is_array( $value ) and count( $valueKeys = array_keys( $value ) ) > 0 )
            {
                if ( is_int( $valueKeys[0] ) )
                {
                    foreach ( $value as $child )
                    {
                        $node->appendChild( $this->createElementNodeFromArray( $arrayKey, $child ) );
                    }
                }
                else
                {
                    $node->appendChild( $this->createElementNodeFromArray( $arrayKey, $value ) );
                }
            }
            else
            {
                $attr = $doc->createAttribute( $arrayKey );
                $attr->value = $value;
                $node->appendChild( $attr );
            }
        }
        
        return $node;
    }

    /*!
     Store the content. Since the content has been stored in function 
     fetchObjectAttributeHTTPInput(), this function is with empty code.
    */
    /*    function storeObjectAttribute( $contentObjectAttribute )
    {
        $gp = $contentObjectAttribute->Content;
        if ( is_object( $gp ) )
        {
            $gp->store();
            return true;
        }
        return false;
    }
*/
    function deleteStoredObjectAttribute( $contentObjectAttribute, $version = null )
    {
        $gp = xrowGISPosition::fetch( $contentObjectAttribute->attribute( "id" ), $contentObjectAttribute->attribute( "version" ) );
        if ( is_object( $gp ) )
            $gp->remove();
    }

    /*!
     Store the content. Since the content has been stored in function 
     fetchObjectAttributeHTTPInput(), this function is with empty code.
    */
    function getDataArray( $contentObjectattribute )
    {
        $cv = eZContentObjectVersion::fetchVersion( $contentObjectattribute->attribute( 'version' ), $contentObjectattribute->attribute( 'contentobject_id' ) );
        $data_map = $cv->attribute( 'data_map' );
        
        $ini = eZINI::instance( 'content.ini' );
        $key_map = $ini->variable( 'GISSettings', 'attributes' );
        $keys = array_keys( $key_map );
        
        foreach ( $keys as $key )
        {
            if ( is_object( $data_map[$key] ) )
                $return[$key_map[$key]] = $data_map[$key]->content();
        }
        return $return;
    }

    /*!
     Returns the content.
    */
    function objectAttributeContent( $contentObjectAttribute )
    {
        return xrowGISPosition::fetch( $contentObjectAttribute->attribute( "id" ), $contentObjectAttribute->attribute( "version" ) );
    }

    /*!
     \return \c true if the datatype finds any content in the attribute \a $contentObjectAttribute.
    */
    function hasObjectAttributeContent( $contentObjectAttribute )
    {
        $gis_info = self::objectAttributeContent( $contentObjectAttribute );
        if ( is_null( $gis_info ) || ( ( $gis_info->longitude == 0 || is_null( $gis_info->longitude ) ) && ( $gis_info->latitude == "0" || is_null( $gis_info->latitude ) ) ) )
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    /*!
     Returns the meta data used for storing search indeces.
    */
    function metaData( $contentObjectAttribute )
    {
        $content = $contentObjectAttribute->content();
        $result = "";
        if ( $content instanceof xrowGISPosition )
        {
            $attributeArray = array( 
                'latitude' , 
                'longitude' , 
                'street' , 
                'zip' , 
                'district' , 
                'city' , 
                'state' , 
                'country' 
            );
            $result = array();
            foreach ( $attributeArray as $key )
            {
                $result[] = array( 
                    'id' => $key , 
                    'text' => $content->$key 
                );
            }
        }
        return $result;
    }

    /*!
     Returns the value as it will be shown if this attribute is used in the object name pattern.
    */
    function title( $contentObjectAttribute, $name = null )
    {
        $content = $contentObjectAttribute->content();
        $result = "";
        if ( $content instanceof xrowGISPosition )
        {
            $attributeArray = array( 
                'country' , 
                'state' , 
                'zip' , 
                'district' , 
                'city' , 
                'street' , 
                'latitude' , 
                'longitude' 
            );
            foreach ( $attributeArray as $key )
            {
                $result .= " " . $content->$key;
            }
        }
        return trim( $result );
    }

    /*!
     \return true if the datatype can be indexed
    */
    function isIndexable()
    {
        return true;
    }
}

eZDataType::register( xrowgistype::DATATYPE_STRING, "xrowgistype" );