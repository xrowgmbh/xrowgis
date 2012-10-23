<?php

$Module = $Params["Module"];

if ( ! isset( $Params['NodeID'] ) )
{
    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
}

$xml = new xrowGEORSS($Params['NodeID']);

$xml = $xml->feed->generate('rss2');

// Set header settings
$lastModified = gmdate( 'D, d M Y H:i:s', time() ) . ' GMT';
$expires = gmdate( 'D, d M Y H:i:s', time() + 300 ) . ' GMT';
$httpCharset = eZTextCodec::httpCharset();
header( 'Cache-Control: max-age=300, public, must-revalidate' );
header( 'Expires:' . $expires );
header( 'Pragma:' );
header( 'Last-Modified: ' . $lastModified );
header( 'Content-Type: application/xml; charset=' . $httpCharset );
header( 'Content-Length: ' . strlen( $xml ) );

while ( @ob_end_clean() );

echo $xml;
eZExecution::cleanExit();

?>