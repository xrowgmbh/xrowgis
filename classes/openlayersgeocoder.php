<?php

//
// Definition of OpenLayersGeoCoder Methods
//
// OpenLayersGeoCoder Methods
//
// Created on: <Feb-2012 Stephan Bogansky>
// Version: 0.0.1
//
// Copyright (C) 2012 xrow GmbH. All rights reserved.
//
// This source file is part of an extension for the eZ publish (tm)
// Open Source Content Management System.
//
// This file may be distributed and/or modified under the terms of the
// "GNU General Public License" version 2 (or greater) as published by
// the Free Software Foundation and appearing in the file LICENSE
// included in the packaging of this file.
//
// This file is provided AS IS with NO WARRANTY OF ANY KIND, INCLUDING
// THE WARRANTY OF DESIGN, MERCHANTABILITY AND FITNESS FOR A PARTICULAR
// PURPOSE.
//
// The "GNU General Public License" (GPL) is available at
// http://www.gnu.org/copyleft/gpl.html
//
// Contact service@xrow.de if any conditions
// of this licencing isn't clear to you.


class OpenLayersGeoCoder extends GeoCoder
{
    public $street;
    public $zip;
    public $city;
    public $state;
    public $country;
    public $longitude; // Dezimalgrad der geographischen Länge
    public $latitude; // Dezimalgrad der geographischen Breite

    
    function OpenLayersGeoCoder()
    {
        parent::GeoCoder();
    }

    /*!
      Uses the google API to update the GeoCoder Object with a given search request
      If no valid data is found returns false.
	*/
    
    function request()
    {
        $searchstring = array();
        if ( $this->query_string )
        {
            $searchstring = $this->query_string . " ";//hack to be sure, that all needles will be found right...
        }
        else
        {
            if ( $this->country )
                $searchstring[] = $this->country;
            if ( $this->state )
                $searchstring[] = $this->state;
            if ( $this->street )
                $searchstring[] = $this->street;
            if ( $this->zip and $this->city )
                $searchstring[] = $this->zip . ' ' . $this->city;
            elseif ( $this->zip )
                $searchstring[] = $this->zip;
            elseif ( $this->city )
                $searchstring[] = $this->city;
            
            $searchstring = implode( ' ', $searchstring );
        }
        
        // ini values
        $gisini = eZINI::instance( "xrowgis.ini" );
        $url = $gisini->variable( "OpenLayers", "Url" );
        $search = $gisini->variable( "search", "needle" );
        $replace = $gisini->variable( "replace", "needle" );
        
        $searchstring = preg_replace( $search, $replace, $searchstring );
        
        if ( $this->reverse )
        {
            $reverseUrl = $gisini->variable( "OpenLayers", "ReverseUrl" );
            $requestUrl = $reverseUrl . "?latlng=" . urlencode( $this->latitude ) . "," . urlencode( $this->longitude ) . "&sensor=false";
            
            $kml = new SimpleXMLElement( file_get_contents( $requestUrl ) );
            
            if ( ! empty( $kml ) && $kml->status == 'OK' )
            {
                foreach ( $kml->result->address_component as $item )
                {
                    $type = sprintf( "%s", $item->type );
                    $retVal[$type] = array( 
                        'short' => sprintf( "%s", $item->short_name ) , 
                        'long' => sprintf( "%s", $item->long_name ) 
                    );
                }
                $this->street = $retVal['route']['long'] . ' ' . $retVal['street_number']['long'];
                $this->district = $retVal['sublocality']['long'];
                $this->zip = $retVal['postal_code']['long'];
                $this->city = $retVal['locality']['long'];
                $this->state = $retVal['administrative_area_level_1']['long'];
                $this->country = $retVal['country']['short'];
                $this->longitude = $this->longitude;
                $this->latitude = $this->latitude;
                
                return true;
            }
            else
                return false;
        }
        $bounds = $gisini->variable( "GISSettings", "bounds" );
        $requestUrl = $url . "?address=" . urlencode( $searchstring ) . "&sensor=false&bounds=" . $bounds . "";
        
        eZDebug::writeDebug( $requestUrl, 'Openlayers GeoCoder Request' );
        //request the google kml result
        $kml = new SimpleXMLElement( file_get_contents( $requestUrl ) );
        
        if ( ! empty( $kml ) && $kml->status == 'OK' )
        {
            foreach ( $kml->result->address_component as $item )
            {
                $type = sprintf( "%s", $item->type );
                $retVal[$type] = array( 
                    'short' => sprintf( "%s", $item->short_name ) , 
                    'long' => sprintf( "%s", $item->long_name ) 
                );
            }
            
            $this->street = $retVal['route']['long'] . ' ' . $retVal['street_number']['long'];
            $this->district = $retVal['sublocality']['long'];
            $this->zip = $retVal['postal_code']['long'];
            $this->city = $retVal['locality']['long'];
            $this->state = $retVal['administrative_area_level_1']['long'];
            $this->country = $retVal['country']['short'];
            $this->longitude = sprintf( "%s", $kml->result->geometry->location->lng );
            $this->latitude = sprintf( "%s", $kml->result->geometry->location->lat );
            return true;
        }
        else
        {
            return false;
        }
    }
}
?>
