<?php

class attributeListOperator
{

    /*!
     Constructor
    */
    function attributeListOperator()
    {
        $this->Operators = array( 
            'list_by_attribute' 
        );
    }

    /*!
     Returns the operators in this class.
    */
    function operatorList()
    {
        return $this->Operators;
    }

    /*!
     \return true to tell the template engine that the parameter list
    exists per operator type, this is needed for operator classes
    that have multiple operators.
    */
    function namedParameterPerOperator()
    {
        return true;
    }

    /*!
     The first operator has two parameters, the other has none.
     See eZTemplateOperator::namedParameterList()
    */
    function namedParameterList()
    {
        return array( 
            'list_by_attribute' => array( 
                'attribute' => array( 
                    'type' => 'string' , 
                    'required' => true , 
                    'default' => '' 
                ) , 
                'limit' => array( 
                    'type' => 'string' , 
                    'required' => false , 
                    'default' => '' 
                ) , 
                'source' => array( 
                    'type' => 'string' , 
                    'required' => true , 
                    'default' => '' 
                )
            ) 
        );
    }

    /*!
     Executes the needed operator(s).
     Checks operator names, and calls the appropriate functions.
    */
    function modify( &$tpl, &$operatorName, &$operatorParameters, &$rootNamespace, &$currentNamespace, &$operatorValue, &$namedParameters )
    {
        $node = eZContentObjectTreeNode::fetch( $namedParameters['source'] );
        $resultArray = eZContentObjectTreeNode::subTreeByNodeID( array(), $node->attribute( 'node_id' ) );

        $cityArray = array();
        foreach ( $resultArray as $item )
        {
            $dm = $item->object()->dataMap();
            $geoAttribute = $dm[xrowGIStype::DATATYPE_STRING]->attribute( 'content' );
            if ( $geoAttribute instanceof xrowGISPosition )
            {
                if ( ! empty( $geoAttribute->city ) )
                    $cityArray[] = $geoAttribute->city;
            }
        }
        $operatorValue = array_unique( $cityArray );
    

    }
}

?>