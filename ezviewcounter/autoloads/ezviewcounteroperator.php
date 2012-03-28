<?php
/**
 * Class operator to manage functions in template
 * @author Adrien Loyant <adrien.loyant@te-laval.fr>
 * @version 0.0.1
 * @copyright Copyright (C) 2011
 * @license GNU General Public License v2.0
 * @since 2011-12-12
 * @package ezviewcounter
 */
class eZViewCounterOperator {
	
	/**
	 * The operators
	 * @access protected
	 * @var array
	 */
	protected $Operators;
	
	/**
	 * Return the operators names
	 * @static
	 * @access public
	 * @return array
	 */
	public static function operators() {
		return array( 'view_count' );
	}
    
	/**
	 * Constructor
	 * @access public
	 */
    public function __construct() {
    	$this->Operators = self::operators();
	}
	
	/**
	 * Return an array with the template operator name.
	 * @access public
	 * @return array
	 */
    public function operatorList() {
        return $this->Operators;
    }
    
    /**
     * Return true to tell the template engine that the parameter list exists per operator type,
     * this is needed for operator classes that have multiple operators.
     * @access public
     * @return boolean
     */
    public function namedParameterPerOperator() {
        return true;
    }
    
    /**
     * Returns an array of named parameters, this allows for easier retrieval of operator parameters.
     * @see eZTemplateOperator::namedParameterList
     * @access public
     * @return multitype:multitype:multitype:string boolean number  multitype:string boolean
     */
    public function namedParameterList() {
        return array( 'view_count' => array( 'node' => array( 'type' => 'object',
        													  'required' => true,
        													  'default' => null ),
											 'update' => array(  'type' => 'boolean',
																 'required' => false,
																 'default' => true ) )
		);
    }
    
    /**
     * Executes the PHP function for the operator cleanup and modifies \a $operatorValue
     * @access public
     * @param mixed $tpl
     * @param mixed $operatorName
     * @param mixed $operatorParameters
     * @param mixed $rootNamespace
     * @param mixed $currentNamespace
     * @param mixed $operatorValue
     * @param array $namedParameters
     * @param mixed $placement
     */
    public function modify( $tpl, $operatorName, $operatorParameters, $rootNamespace, $currentNamespace, &$operatorValue, $namedParameters, $placement ) {
        switch ( $operatorName ) {
            case 'view_count':
            	if (count($operatorParameters == 2) && empty($operatorValue)) {
            		$node = $namedParameters['node'];
            		$update = $namedParameters['update'];
            	} else {
            		$node_id = $operatorValue;
            		$update = $tpl->elementValue( $operatorParameters[0], $rootNamespace, $currentNamespace, $placement );
            	}
                $operatorValue = $this->viewCount( $node, $update );
                break;
            default:
            	// Nothing
            	break;
        }
    }
    
    /**
     * Update and return view count
     * @access public
     * @param eZContentObject $node_id
     * @param boolean $update
     * @return number
     */
	public function viewCount( eZPersistentObject $node, $update=true ) {
		$result = 0;
		$ini = eZINI::instance( 'ezviewcounter.ini' );
		$CounterType = 'ez';
		if ( $ini->hasVariable('Configuration', 'CounterType') ) {
			$CounterType = $ini->variable('Configuration', 'CounterType');
		}
		try {
			$eZViewCounter = eZViewCounterFactory::factory($CounterType, $node);
			if ($eZViewCounter instanceof ieZViewCounter) {
				if ($update) {
					$eZViewCounter->increase( $node->attribute('node_id') );
				}
				$result = $eZViewCounter->countView( $node->attribute('node_id') );
			}
		} catch (Exception $e) {
			eZDebug::writeError($e->getMessage());
		}
		return $result;
	}
	
}
?>