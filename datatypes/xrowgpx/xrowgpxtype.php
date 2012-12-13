<?php

/**
 * File containing the eZTextType class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://ez.no/Resources/Software/Licenses/eZ-Business-Use-License-Agreement-eZ-BUL-Version-2.1 eZ Business Use License Agreement eZ BUL Version 2.1
 * @version 4.7.0
 * @package kernel
 */

/*!
  \class eZTextType eztexttype.php
  \ingroup eZDatatype
  \brief Stores a text area value

*/

class xrowGPXType extends eZTextType
{
    const DATA_TYPE_STRING = "xrowgpx";
    const COLS_FIELD = 'data_int1';
    const COLS_VARIABLE = '_xrowgpx_cols_';

    function xrowGPXType()
    {
        $this->eZDataType( self::DATA_TYPE_STRING, ezpI18n::tr( 'kernel/classes/datatypes', "GPX Text block", 'Datatype name' ), array( 
            'serialize_supported' => true , 
            'object_serialize_map' => array( 
                'data_text' => 'text' 
            ) 
        ) );
    }

    /*!
     Validates the input and returns true if the input was
     valid for this datatype.
    */
    function validateObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        $classAttribute = $contentObjectAttribute->contentClassAttribute();
        if ( $http->hasPostVariable( $base . '_data_text_' . $contentObjectAttribute->attribute( 'id' ) ) )
        {
            $data = $http->postVariable( $base . '_data_text_' . $contentObjectAttribute->attribute( 'id' ) );
            
            if ( $data == "" )
            {
                if ( ! $classAttribute->attribute( 'is_information_collector' ) and $contentObjectAttribute->validateIsRequired() )
                {
                    $contentObjectAttribute->setValidationError( ezpI18n::tr( 'kernel/classes/datatypes', 'Input required.' ) );
                    return eZInputValidator::STATE_INVALID;
                }
            }
            /*else
            {
                $xml = new DOMDocument();
                $xml->load( $data );
                if ( ! $xml->schemaValidate( 'extension/xrowgis/schemas/gpx_schema.xsd' ) )
                {
                    $contentObjectAttribute->setValidationError( ezpI18n::tr( 'kernel/classes/datatypes', 'GPX Track seems to be malformed.' ) );
                    return eZInputValidator::STATE_INVALID;
                }
            }*/
        }
        else 
            if ( ! $classAttribute->attribute( 'is_information_collector' ) and $contentObjectAttribute->validateIsRequired() )
            {
                $contentObjectAttribute->setValidationError( ezpI18n::tr( 'kernel/classes/datatypes', 'Input required.' ) );
                return eZInputValidator::STATE_INVALID;
            }
        
        return eZInputValidator::STATE_ACCEPTED;
    }

}

eZDataType::register( xrowGPXType::DATA_TYPE_STRING, "xrowGPXType" );

?>
