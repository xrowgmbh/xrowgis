<?php
$Module = array( 
    "name" => "xrowgis" 
);
$ViewList["georss"] = array( 
    "script" => "georss.php" , 
    'params' => array( 
        'NodeID' 
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

$FunctionList = array();
$FunctionList['georss'] = array();
$FunctionList['editor'] = array();
$FunctionList['search'] = array(); // only used by template code to see if user should see this feature in ezoe
$FunctionList['browse'] = array(); // only used by template code to see if user should see this feature in ezoe


?>
