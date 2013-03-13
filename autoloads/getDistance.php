<?php

class getDistanceOperator
{

    function operatorList()
    {
        return array( 
            'get_dist' 
        );
    }

    function namedParameterPerOperator()
    {
        return true;
    }
    
    function namedParameterList()
    {
        return array( 
            'get_dist' => array( 
                'lon1' => array( 
                    "type" => "integer" , 
                    "required" => true , 
                    "default" => 0 
                ) , 
                'lat1' => array( 
                    "type" => "integer" , 
                    "required" => true , 
                    "default" => 0 
                ) , 
                'lon2' => array( 
                    "type" => "integer" , 
                    "required" => true , 
                    "default" => 0 
                ) , 
                'lat2' => array( 
                    "type" => "integer" , 
                    "required" => true , 
                    "default" => 0 
                ) 
            ) 
        );
    }

    function modify( &$tpl, &$operatorName, &$operatorParameters, &$rootNamespace, &$currentNamespace, &$operatorValue, &$namedParameters )
    {
        switch ( $operatorName )
        {
            case 'get_dist':
                $gpoint = new gPoint();
                $gpoint->setLongLat( $namedParameters['lon1'], $namedParameters['lat1'] );
                $result = $gpoint->distanceFrom( $namedParameters['lon2'], $namedParameters['lat2'] );
                $operatorValue = round($result) . $operatorValue;
                break;
        }
    }
}
?>