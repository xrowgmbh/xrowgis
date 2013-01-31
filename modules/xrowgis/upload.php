<?php

$module = $Params['Module'];
$namedParameters = $module->NamedParameters;

$objectID = isset( $namedParameters['ObjectID'] ) ? (int) $namedParameters['ObjectID'] : 0;
$objectVersion = isset( $namedParameters['ObjectVersion'] ) ? (int) $namedParameters['ObjectVersion'] : 0;
$attributeID = isset( $namedParameters['AttributeID'] ) ? (int) $namedParameters['AttributeID'] : 0;

// Supported content types: image, media and file
// Media is threated as file for now
$contentType = 'images';

if ( isset( $namedParameters['ContentType'] ) && $namedParameters['ContentType'] !== '' )
{
    $contentType = $Params['ContentType'];
}

if ( $objectID === 0 || $objectVersion === 0 )
{
    echo ezpI18n::tr( 'design/standard/ezoe', 'Invalid or missing parameter: %parameter', null, array( 
        '%parameter' => 'ObjectID/ObjectVersion' 
    ) );
    eZExecution::cleanExit();
}

$user = eZUser::currentUser();
if ( $user instanceof eZUser )
{
    $result = $user->hasAccessTo( 'xrowgis', 'editor' );
}
else
{
    $result = array( 
        'accessWord' => 'no' 
    );
}

if ( $result['accessWord'] === 'no' )
{
    echo ezpI18n::tr( 'design/standard/error/kernel', 'Your current user does not have the proper privileges to access this page.' );
    eZExecution::cleanExit();
}

$object = eZContentObject::fetch( $objectID );
$http = eZHTTPTool::instance();

if ( ! $object )
{
    echo ezpI18n::tr( 'design/standard/ezoe', 'Invalid parameter: %parameter = %value', null, array( 
        '%parameter' => 'ObjectId' , 
        '%value' => $objectID 
    ) );
    eZExecution::cleanExit();
}

$siteIni = eZINI::instance( 'site.ini' );
$contentIni = eZINI::instance( 'content.ini' );

$groups = $contentIni->variable( 'RelationGroupSettings', 'Groups' );
$defaultGroup = $contentIni->variable( 'RelationGroupSettings', 'DefaultGroup' );
$imageDatatypeArray = $siteIni->variable( 'ImageDataTypeSettings', 'AvailableImageDataTypes' );

$classGroupMap = array();
$groupClassLists = array();
$groupedRelatedObjects = array();
$relatedObjects = $object->relatedContentObjectArray( $objectVersion );

foreach ( $groups as $groupName )
{
    $groupedRelatedObjects[$groupName] = array();
    $setting = ucfirst( $groupName ) . 'ClassList';
    $groupClassLists[$groupName] = $contentIni->variable( 'RelationGroupSettings', $setting );
    foreach ( $groupClassLists[$groupName] as $classIdentifier )
    {
        $classGroupMap[$classIdentifier] = $groupName;
    }
}

$groupedRelatedObjects[$defaultGroup] = array();

foreach ( $relatedObjects as $relatedObjectKey => $relatedObject )
{
    $srcString = '';
    $imageAttribute = false;
    $relID = $relatedObject->attribute( 'id' );
    $classIdentifier = $relatedObject->attribute( 'class_identifier' );
    $groupName = isset( $classGroupMap[$classIdentifier] ) ? $classGroupMap[$classIdentifier] : $defaultGroup;
    
    if ( $groupName === 'images' )
    {
        $objectAttributes = $relatedObject->contentObjectAttributes();
        foreach ( $objectAttributes as $objectAttribute )
        {
            $classAttribute = $objectAttribute->contentClassAttribute();
            $dataTypeString = $classAttribute->attribute( 'data_type_string' );
            if ( in_array( $dataTypeString, $imageDatatypeArray ) && $objectAttribute->hasContent() )
            {
                $content = $objectAttribute->content();
                if ( $content == null )
                    continue;
                
                if ( $content->hasAttribute( 'small' ) )
                {
                    $srcString = $content->imageAlias( 'small' );
                    $imageAttribute = $classAttribute->attribute( 'identifier' );
                    break;
                }
                else
                {
                    eZDebug::writeError( "Image alias does not exist: small, missing from image.ini?", __METHOD__ );
                }
            }
        }
    }
    $item = array( 
        'object' => $relatedObjects[$relatedObjectKey] , 
        'id' => 'eZObject_' . $relID , 
        'image_alias' => $srcString , 
        'image_attribute' => $imageAttribute , 
        'selected' => false 
    );
    $groupedRelatedObjects[$groupName][] = $item;
}

$tpl = eZTemplate::factory();
$tpl->setVariable( 'object', $object );
$tpl->setVariable( 'object_id', $objectID );
$tpl->setVariable( 'object_version', $objectVersion );
$tpl->setVariable( 'related_contentobjects', $relatedObjects );
$tpl->setVariable( 'grouped_related_contentobjects', $groupedRelatedObjects );
$tpl->setVariable( 'content_type', $contentType );
$tpl->setVariable( 'access', $result );

$contentTypeCase = ucfirst( $contentType );
if ( $contentIni->hasVariable( 'RelationGroupSettings', $contentTypeCase . 'ClassList' ) )
    $tpl->setVariable( 'class_filter_array', $contentIni->variable( 'RelationGroupSettings', $contentTypeCase . 'ClassList' ) );
else
    $tpl->setVariable( 'class_filter_array', array() );

$tpl->setVariable( 'content_type_name', rtrim( $contentTypeCase, 's' ) );

$tpl->setVariable( 'persistent_variable', array() );

$Result = array();
$Result['content'] = $tpl->fetch( 'design:xrowgis/upload/upload_' . $contentType . '.tpl' );
$Result['pagelayout'] = 'design:xrowgis/upload/popup_pagelayout.tpl';
$Result['persistent_variable'] = $tpl->variable( 'persistent_variable' );

return $Result;