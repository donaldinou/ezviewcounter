<?php 
class eZViewCounterXMLAdapter implements ieZViewCounter {
	
	/**
	 *
	 * Enter description here ...
	 * @var eZViewCounterXML
	 */
	public $adapted;
	
	/**
	 * Constructor
	 * @access public
	 */
	public function __construct( eZViewCounterXML $adapted ) {
		$this->adapted = $adapted;
	}
	
	/**
	 * Increment the counter
	 * @access public
	 * @see ieZViewCounter::increase()
	 */
	public function increase() {
		if ($this->adapted instanceof eZViewCounterXML) {
			$node_id = null;
			if (func_num_args() > 0 ) {
				$args = func_get_args();
				if ( is_int($args[0]) ) {
					$node_id = $args[0];
				}
			}
			
			$ini = eZINI::instance('ezviewcounter.ini');
			$advanced = ($ini->hasVariable('Configuration', 'AdvancedCounter')) ? $ini->variable('Configuration', 'AdvancedCounter') : 'disabled';
			$delay = ($ini->hasVariable('Configuration', 'CounterDelay')) ? (int)$ini->variable('Configuration', 'CounterDelay') : 0;
			$update = true;
			
			if ($delay>0) {
				$timestamp = 0;
				try {
					$date = new DateTime( $this->adapted->attribute('modified', $node_id) );
					$timestamp = $date->getTimestamp();
				} catch (Exception $e) {
					eZDebug::writeError($e->getMessage());
				}
				if ($timestamp+$delay > time()) {
					$update = false;
				}
			}
			
			if ($update) {
				$this->adapted->increase( $node_id );
			}
		}
	}
	
	/**
	 * Return the total number of count for the current node_id
	 * @access public
	 * @return integer
	 * @see ieZViewCounter::countView()
	 */
	public function countView() {
		$node_id = null;
		$result = 0;
		if ($this->adapted instanceof eZViewCounterXML) {
			if (func_num_args() > 0 ) {
				$args = func_get_args();
				if ( is_int($args[0]) ) {
					$node_id = $args[0];
				}
			}
			if ($this->adapted instanceof eZViewCounterXML) {
				$result = $this->adapted->countView( $node_id );
			}
		}
		return $result;
	}
	
	/**
	 * Fetch Object list with some parameters
	 * @static
	 * @access public
	 * @param mixed $classID
	 * @param mixed $sectionID
	 * @param mixed $offset
	 * @param mixed $limit
	 * @return array
	 * @see ieZViewCounter::fetchTopList()
	 * @todo
	 */
	public static function fetchTopList( $classID=false, $sectionID=false, $offset=false, $limit=false ) {
		$result = array();
		if ($this->adapted instanceof eZViewCounterXML) {
			$result = array();
		}
		return $result;
	}
	
}
?>