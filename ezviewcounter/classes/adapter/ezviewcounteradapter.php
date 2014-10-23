<?php
class eZViewCounterAdapter implements ieZViewCounter {

    /**
     * The adapted object
     * @access protected
     * @var eZViewCounter
     */
    protected $adapted;

    /**
     * Constructor
     * @access public
     * @param eZViewCounterDB $adapted
     */
    public function __construct( eZViewCounter $adapted ) {
        $this->adapted = $adapted;
    }

    /**
     * Increment the counter
     * @access public
     * @see ieZViewCounter::increase()
     */
    public function increase() {
        if ($this->adapted instanceof eZViewCounter) {
            $ini = eZINI::instance('ezviewcounter.ini');
            $advanced = ($ini->hasVariable('Configuration', 'AdvancedCounter')) ? $ini->variable('Configuration', 'AdvancedCounter') : 'disabled';
            $delay = ($ini->hasVariable('Configuration', 'CounterDelay')) ? (int)$ini->variable('Configuration', 'CounterDelay') : 0;
            $node_id = $this->adapted->attribute('node_id');
            $update = true;

            if ($advanced != 'disabled' && $delay>0) {
                if ( !isset($_COOKIE['unique_counter']) ) {
                    setcookie('unique_counter', $node_id, time()+$delay);
                } else {
                    $cookie = explode( '/', $_COOKIE['unique_counter'] );
                    if (in_array($node_id, $cookie)) {
                        $update = false;
                    } else {
                        array_push($cookie, $node_id);
                        setcookie('unique_counter', implode( '/', $node_id ), time()+$delay);
                    }
                }
            }

            if ($update) {
                $this->adapted->increase();
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
        $result = 0;
        if ($this->adapted instanceof eZViewCounter) {
            $result = $this->adapted->Count;
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
     */
    public function fetchTopList( $classID=false, $sectionID=false, $offset=false, $limit=false ) {
        $result = array();
        if ($this->adapted instanceof eZViewCounter) {
            $result = call_user_func(array($this->adapted, 'fetchTopList'), $classID, $sectionID, $offset, $limit);
        }
        return $result;
    }

}
