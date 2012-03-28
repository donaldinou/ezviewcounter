<?php 
class eZViewCounterFileAdapter implements ieZViewCounter {
	
	/**
	*
	* Enter description here ...
	* @var eZViewCounterFile
	*/
	public $adapted;
	
	/**
	* Constructor
	* @access public
	*/
	public function __construct( eZViewCounterFile $adapted ) {
		$this->adapted = $adapted;
	}
	
	/**
	* Increment the counter
	* @access public
	* @see ieZViewCounter::increase()
	*/
	public function increase() {
		$this->adapted->increase();
	}
	
	/**
	* Return the total number of count for the current node_id
	* @access public
	* @return integer
	* @see ieZViewCounter::countView()
	*/
	public function countView() {
		return $this->adapted->countView();
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
	 */
	public static function fetchTopList( $classID=false, $sectionID=false, $offset=false, $limit=false ) {
		// TODO
		return array();
	}
	
}
?>