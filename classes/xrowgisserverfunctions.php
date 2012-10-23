<?php

class xrowGISServerfunctions extends ezjscServerFunctions
{

    public static function userData()
    {
        return true;
    }

    public static function updateMap()
    {
        $ini = eZINI::instance( 'xrowgis.ini' );
        $result['name'] = $ini->variable( 'GISSettings', 'Interface' );

        $data = $_POST;

        $geocoder = GeoCoder::getActiveGeoCoder();
        
        if ( $data['reverse'] )
        {
            if ( empty( $data['lat'] ) || empty( $data['lon'] ) )
            {
                $lon = $data['data']['ContentObjectAttribute_xrowgis_longitude_' . $data['attr_id']];
                $lat = $data['data']['ContentObjectAttribute_xrowgis_latitude_' . $data['attr_id']];
                
                $geocoder->setLonLat( $lon, $lat );
            
            }
            else
            {
                $lon = $data['lon'];
                $lat = $data['lat'];
                
                $geocoder->setLonLat( $lon, $lat );
            }
        }
        else
        {
            if ( $data['mapsearch'] )
            {
                
                $geocoder->setAddress( $street, $zip, $city, $state, $country, false, $data['input'] );
            }
            else
            {
                $attributeID = $data['attr_id'];
                $street = $data['ContentObjectAttribute_xrowgis_street_' . $attributeID];
                $zip = $data['ContentObjectAttribute_xrowgis_zip_' . $attributeID];
                $city = $data['ContentObjectAttribute_xrowgis_city_' . $attributeID];
                $state = $data['ContentObjectAttribute_xrowgis_state_' . $attributeID];
                $country = $data['ContentObjectAttribute_xrowgis_country_' . $attributeID];
                $longitude = $data['ContentObjectAttribute_xrowgis_longitude_' . $attributeID];
                $latitute = $data['ContentObjectAttribute_xrowgis_latitude_' . $attributeID];
                
                $geocoder->setAddress( $street, $zip, $city, $state, $country );
            }
        
        }
        if ( $geocoder->request() )
        {
            if ( $data['reverse'] )
            {
                $result['street'] = $geocoder->street;
                $result['zip'] = $geocoder->zip;
                $result['city'] = ( $geocoder->city == 'Hanover' ) ? 'Hannover' : $geocoder->city;
                $result['district'] = $geocoder->district;
                $result['state'] = $geocoder->state;
                $result['lon'] = $geocoder->longitude;
                $result['lat'] = $geocoder->latitude;
            }
            else
            {
                $result['street'] = $geocoder->street;
                $result['zip'] = $geocoder->zip;
                $result['city'] = ( $geocoder->city == 'Hanover' ) ? 'Hannover' : $geocoder->city;
                $result['district'] = $geocoder->district;
                $result['state'] = $geocoder->state;
                $result['lon'] = $geocoder->longitude;
                $result['lat'] = $geocoder->latitude;
            }
        }
        else
        {
            $result['lon'] = ( empty( $longitude ) ) ? $lon : $longitude;
            $result['lat'] = ( empty( $latitude ) ) ? $lat : $latitude;
        }
        return $result;
    }

    public static function getAlpha2()
    {
        $ini = eZINI::instance( 'xrowgis.ini' );
        $result['name'] = $ini->variable( 'GISSettings', 'Interface' );
        
        $data = $_POST;
        
        $attributeID = $data['attr_id'];
        
        $geocoder = GeoCoder::getActiveGeoCoder();
        $geocoder->setLonLat( $data['lon'], $data['lat'] );
        $geocoder->request();
        
        $result['country'] = $geocoder->country;
        
        return $result;
    }

    public static function getMapCenter()
    {
        $ini = eZINI::instance( 'xrowgis.ini' );
        $result['name'] = $ini->variable( 'GISSettings', 'Interface' );
        $result['lon'] = $ini->variable( 'GISSettings', 'longitude' );
        $result['lat'] = $ini->variable( 'GISSettings', 'latitude' );
        return $result;
    }

    public static function addRelation()
    {
        $data = $_POST;
        $tpl = eZTemplate::factory();
        
        $ini = eZINI::instance( 'xrowgis.ini' );
        $result['name'] = $ini->variable( 'GISSettings', 'Interface' );
        $object = eZContentObject::fetchByNodeID( $data['node_id'] );
        
        foreach ( $object->attribute( 'contentobject_attributes' ) as $key => $relCoa )
        {
            if ( $relCoa->attribute( 'data_type_string' ) === xrowGIStype::DATATYPE_STRING )
            {
                $attribute = eZContentObjectAttribute::fetch( (int) $data['attributeID'], (int) $data['version'] );
                $attribute->setAttribute( 'data_int', $object->ID );
                $attribute->store();
                $tpl->setVariable( 'attribute', $attribute );
                $tpl->setVariable( 'GISRelation', true );
                $tpl->setVariable( 'relAttribute', $relCoa );
                
                if ( $relCoa->content() )
                {
                    $result['lon'] = $relCoa->content()->attribute( 'longitude' );
                    $result['lat'] = $relCoa->content()->attribute( 'latitude' );
                }
                
                $result['template'] = $tpl->fetch( 'design:xrowgis/xrowgis.tpl' );
                
                return $result;
            }
        }
    }

    public static function releaseRelation()
    {
        $ini = eZINI::instance( 'xrowgis.ini' );
        $result['name'] = $ini->variable( 'GISSettings', 'Interface' );
        $data = $_POST;
        
        $attribute = eZContentObjectAttribute::fetch( (int) $data['attributeID'], (int) $data['version'] );
        $attribute->setAttribute( 'data_int', null );
        $attribute->store();
        $tpl = eZTemplate::factory();
        
        if ( $attribute->hasContent() )
        {
            $result['lon'] = ( $attribute->content()->attribute( 'longitude' ) == 0 ) ? '' : $attribute->content()->attribute( 'longitude' );
            $result['lat'] = ( $attribute->content()->attribute( 'latitude' ) == 0 ) ? '' : $attribute->content()->attribute( 'latitude' );
        }
        else
        {
            foreach ( eZContentObject::fetch( $data['relObjectID'] )->attribute( 'contentobject_attributes' ) as $key => $relCoa )
            {
                if ( $relCoa->attribute( 'data_type_string' ) === xrowGIStype::DATATYPE_STRING )
                {
                    $relCoa->content()->setAttribute( 'contentobject_attribute_id', $attribute->attribute( 'id' ) );
                    $relCoa->content()->setAttribute( 'contentobject_attribute_version', $attribute->attribute( 'version' ) );
                    
                    $attribute = $relCoa;
                    
                    $result['lon'] = ( $relCoa->content()->attribute( 'longitude' ) == 0 ) ? '' : $relCoa->content()->attribute( 'longitude' );
                    $result['lat'] = ( $relCoa->content()->attribute( 'latitude' ) == 0 ) ? '' : $relCoa->content()->attribute( 'latitude' );
                
                }
            }
        
        }
        $tpl->setVariable( 'attribute', $attribute );
        $result['template'] = $tpl->fetch( 'design:xrowgis/xrowgis.tpl' );
        
        return $result;
    }

}