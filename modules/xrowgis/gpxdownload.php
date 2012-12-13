<?php

$Module = $Params["Module"];

if ( ! isset( $Params['ObjectID'] ) )
{
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
}



while ( @ob_end_clean() );

echo $xml;

eZExecution::cleanExit();

?>