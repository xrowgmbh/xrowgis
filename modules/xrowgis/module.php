<?php
$Module = array( 
    "name" => "xrowgis" 
);
$ViewList["georss"] = array( 
    "script" => "georss.php" , 
    'params' => array( 
        'NodeID',
        'Tree' 
    ) 
);


$ViewList['upload'] = array( 
    'functions' => array( 
        'editor' 
    ) , 
    'ui_context' => 'edit' , 
    'script' => 'upload.php' , 
    'params' => array( 
        'ObjectID' , 
        'ObjectVersion' , 
        'ContentType' , 
        'AttributeID' 
    ) 
);

$ViewList["gpxdownload"] = array( 
    "script" => "gpxdownload.php" , 
    'params' => array( 
        'ObjectID' 
    ) 
);

$FunctionList = array();
$FunctionList['editor'] = array();
$FunctionList['create_empty'] = array();

?>