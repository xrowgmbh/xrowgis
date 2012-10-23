<?php

class attributeListOperator
{
    /*!
     Constructor
    */
    function attributeListOperator()
    {
        $this->Operators = array( 'list_by_attribute' );
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
        return array( 'list_by_attribute' => array(   'attribute' => array( 'type' => 'string',
                                                                     'required' => true,
                                                                     'default' => '' ),
		                                              'limit' => array( 'type' => 'string',
		                                                                     'required' => true,
		                                                                     'default' => '' ),
        											  'source' => array( 'type' => 'string',
	                                                                     'required' => true,
	                                                                     'default' => '' ),
		                                            ) );
		    }

    /*!
     Executes the needed operator(s).
     Checks operator names, and calls the appropriate functions.
    */
    function modify( &$tpl, &$operatorName, &$operatorParameters, &$rootNamespace,
                     &$currentNamespace, &$operatorValue, &$namedParameters )
    {
    	$db = eZDB::instance();
    	$params = array( 'limit' => $namedParameters['limit'], 'offset' => 0, 'column' => 'city' );
    	$node = eZContentObjectTreeNode::fetch($namedParameters['source']);
        $attribute_list= xrowGISTools::citiesBySubtree($node, $params);
		$operatorValue = $attribute_list;

    }
}

?>