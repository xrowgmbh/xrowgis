<?php

$Module = $Params["Module"];

if ( ! isset( $Params['ObjectID'] ) )
{
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
}

$contentObject = eZContentObject::fetch( (int) $Params['ObjectID'] );

if ( $contentObject instanceof eZContentObject && $contentObject->canRead() )
{
    foreach ( $contentObject->dataMap() as $coattribute )
    {
        if ( $coattribute->attribute( 'data_type_string' ) == xrowGPXtype::DATA_TYPE_STRING )
        {
            $xml = $coattribute->attribute( 'data_text' );
            $file = $contentObject->attribute( 'name' ) . '.gpx';
            header( "Content-Type: gpx" );
            header( "Content-Disposition: attachment; filename=\"$file\"" );
            while ( @ob_end_clean() );
            echo $xml;
        }
    }
}
eZExecution::cleanExit();

?>