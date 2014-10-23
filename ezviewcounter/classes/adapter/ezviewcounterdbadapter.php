<?php
/**
 *
 * Enter description here ...
 * @author Adrien Loyant <adrien.loyant@te-laval.fr>
 *
 */
class eZViewCounterDBAdapter implements ieZViewCounter {

    /**
     * The adapted object
     * @access protected
     * @var eZViewCounterDB
     */
    protected $adapted;

    /**
     * Constructor
     * @access public
     * @param eZViewCounterDB $adapted
     */
    public function __construct( eZViewCounterDB $adapted ) {
        $this->adapted = $adapted;
    }

    /**
     * Increment the counter
     * @access public
     * @see ieZViewCounter::increase()
     */
    public function increase() {
        $ini = eZINI::instance('ezviewcounter.ini');
        $advanced = ($ini->hasVariable('Configuration', 'AdvancedCounter')) ? $ini->variable('Configuration', 'AdvancedCounter') : 'disabled';
        $delay = ($ini->hasVariable('Configuration', 'CounterDelay')) ? (int)$ini->variable('Configuration', 'CounterDelay') : 0;
        $update = true;

        if ($advanced != 'disabled' && $delay>0) {
            if ((int)($this->adapted->attribute('modified')+$delay) > time()) {
                $update = false;
            }
        }

        if ($update) {
            $this->adapted->increase();
        }
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
     * @access public
     * @param mixed $classID
     * @param mixed $sectionID
     * @param mixed $offset
     * @param mixed $limit
     * @return array
     * @see ieZViewCounter::fetchTopList()
     */
    public function fetchTopList( $classID=false, $sectionID=false, $offset=false, $limit=false ) {
        return $this->adapted::fetchTopList($classID, $sectionID, $offset, $limit);
    }

}
