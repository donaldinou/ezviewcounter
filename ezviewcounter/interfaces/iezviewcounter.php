<?php 
/**
 * Interface to implements object like eZViewCounter
 * @author Adrien Loyant <adrien.loyant@te-laval.fr>
 * @version 0.0.1
 * @copyright Copyright (C) 2011
 * @license GNU General Public License v2.0
 * @since 2011-12-12
 * @package ezviewcounter
 */
interface ieZViewCounter {
	
	/**
	 * Increment the counter
	 * @access public
	 */
	public function increase();
	
	/**
	 * Return the total number of count for the current node_id
	 * @access public
	 * @return integer
	 */
	public function countView();

	/**
	 * Fetch Object list with some parameters
	 * @static
	 * @access public
	 * @param mixed $classID
	 * @param mixed $sectionID
	 * @param mixed $offset
	 * @param mixed $limit
	 * @return array
	 */
	public static function fetchTopList( $classID=false, $sectionID=false, $offset=false, $limit=false );
	
}
?>