<?php
class ezjscViewCounterServerFunctions extends ezjscServerFunctions {

    /**
     *
     * Enter description here ...
     * @param unknown_type $args
     * @return array
     * @throws Exception
     * @todo can_read
     */
    public static function count( $args ) {
        $result = array( 'id' => 0, 'update' => true, 'count' => 0 );
        if ( is_array($args) && isset($args[0]) && is_numeric($args[0]) ) {
            $result['id'] = (int)$args[0];
            $result['update'] = (isset($arg[1])) ? (bool)$args[1] : true;
            $ini = eZINI::instance( 'ezviewcounter.ini' );
            $CounterType = 'ez';
            if ( $ini->hasVariable('Configuration', 'CounterType') ) {
                $CounterType = $ini->variable('Configuration', 'CounterType');
            }
            $node = eZContentObjectTreeNode::fetch($result['id']);
            try {
                $eZViewCounter = eZViewCounterFactory::factory($CounterType, $node);
                if ($eZViewCounter instanceof ieZViewCounter) {
                    if ($result['update']) {
                        $eZViewCounter->increase( $result['id'] );
                    }
                    $result['count'] = $eZViewCounter->countView( $result['id'] );
                }
            } catch (Exception $e) {
                eZDebug::writeError($e->getMessage());
            }
        } else {
            throw new Exception('Bads arguments for viewCount function');
        }
        return $result;
    }

    /**
     * Cache time for retunrned data, only currently used by ezjscPacker
     * @see ezjscServerFunctions::getCacheTime
     * @static
     * @access public
     * @param string $functionName
     * @return int Uniq timestamp (can return -1 to signal that $functionName is not cacheable)
     */
    public static function getCacheTime( $functionName ) {
        return parent::getCacheTime( $functionName );
    }

}
