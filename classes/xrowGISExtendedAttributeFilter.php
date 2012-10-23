<?php

class xrowGISExtendedAttributeFilter
{

    public function filter( $params )
    {
        $return = array( 
            'tables' => null , 
            'joins' => null , 
            'columns' => null , 
            'group_by' => null 
        );
        
        if ( isset( $params ) && is_array( $params ) )
        {
            foreach ( $params as $index => $subparams )
            {
                $fnc = array_shift( $subparams );
                if ( is_callable( $fnc ) )
                {
                    $subFilterResult = call_user_func( $fnc, $subparams );
                    foreach ( $return as $key => $value )
                    {
                        if ( isset( $subFilterResult[$key] ) && is_scalar( $subFilterResult[$key] ) )
                        {
                            $return[$key] = $value . $subFilterResult[$key];
                        }
                    }
                }
            }
        }
        
        if ( is_null( $return['group_by'] ) )
        {
            unset( $return['group_by'] );
        }
        
        return $return;
    }

    public static function city( $params )
    {
        if ( isset( $params['city'] ) === false )
        {
            return array();
        }
        $columns = null;
        $tables = ', ezxgis_position, ezcontentobject_attribute';
        $db = eZDB::instance();
        $joins = " ezxgis_position.city = '" . $db->escapeString( $params['city'] ) . "' AND ezcontentobject_attribute.id = ezxgis_position.contentobject_attribute_id AND ezcontentobject_attribute.version = ezxgis_position.contentobject_attribute_version AND ";
        $joins .= " ezcontentobject_attribute.contentobject_id = ezcontentobject.id  AND ezcontentobject_attribute.version = ezcontentobject.current_version AND ";
        return array( 
            'tables' => $tables , 
            'joins' => $joins , 
            'columns' => $columns 
        );
    }
}