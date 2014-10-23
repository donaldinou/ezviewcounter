<?php
/**
 * File containing the eZViewCounterIP class.
 * @author Adrien Loyant <adrien.loyant@te-laval.fr>
 * @version 0.0.1
 * @copyright Copyright (C) 2011
 * @license GNU General Public License v2.0
 * @since 2011-12-12
 * @package ezviewcounter
 * @link http://www.php.net/lsb
 */
class eZViewCounterDB extends eZPersistentObject {

    /**
     * The autoincrement identifier
     * @access protected
     * @var integer
     */
    protected $ID;

    /**
     * Date of the creation
     * @access protected
     * @var integer
     */
    protected $created;

    /**
     * Date of the modification
     * @access protected
     * @var integer
     */
    protected $modified;

    /**
     * The client remote adress
     * @access protected
     * @var string
     */
    protected $remoteAddress;

    /**
     * The node id foreign relation
     * @access protected
     * @var integer
     */
    protected $nodeId;

    /**
     * The counter
     * @access protected
     * @var integer
     */
    protected $count;

    /**
     * The user identifier
     * @access protected
     * @var integer
     */
    protected $userId;

    /**
     * The constructor
     * @access public
     * @param array $row
     */
    public function __construct( $row=array() ) {
        $now = new DateTime();
        if ( !empty($row) && is_array($row) ) {
            // init missing vars
            $row['created'] = (isset($row['created'])) ? $row['created'] : $now->getTimestamp();
            $row['modified'] = (isset($row['modified'])) ? $row['modified'] : $now->getTimestamp();
            $row['remote_address'] = (isset($row['remote_address'])) ? $row['remote_address'] : eZSys::clientIP();
            $row['user_id'] = (isset($row['user_id'])) ? $row['user_id'] : eZUser::currentUserID();
        } else {
            // init $row array to initialize class var (@see eZPersistentObject::fill)
            $row['id'] = null;
            $row['created'] = $now->getTimestamp();
            $row['modified'] = $now->getTimestamp();
            $row['nodeId'] = 0;
            $row['count'] = 0;
            $row['remote_address'] = eZSys::clientIP();
            $row['user_id'] = eZUser::currentUserID();
        }
        parent::__construct($row);
    }

    /**
     * The object definition
     * @static
     * @access public
     * @return array
     */
    public static function definition() {
        // definition
        static $definition = array(
                                    'fields' => array(
                                                         'id' => array( 'name' => 'ID',
                                                                        'datatype' => 'integer',
                                                                        'default' => 0,
                                                                        'required' => true ),
                                                         'created' => array( 'name' => 'created',
                                                                             'datatype' => 'integer',
                                                                             'default' => 0,
                                                                             'required' => true ),
                                                         'modified' => array( 'name' => 'modified',
                                                                              'datatype' => 'integer',
                                                                              'default' => 0,
                                                                              'required' => true ),
                                                         'node_id' => array( 'name' => 'nodeId',
                                                                             'datatype' => 'integer',
                                                                             'default' => 0,
                                                                             'required' => true,
                                                                             'foreign_class' => 'eZContentObjectTreeNode',
                                                                             'foreign_attribute' => 'node_id',
                                                                             'multiplicity' => '1..*' ),
                                                         'remote_address' => array( 'name' => 'remoteAdress',
                                                                                    'datatype' => 'string',
                                                                                    'default' => '',
                                                                                    'required' => true ),
                                                         'count' => array( 'name' => 'count',
                                                                           'datatype' => 'integer',
                                                                           'default' => 0,
                                                                           'required' => true ),
                                                         'user_id' => array( 'name' => 'userId',
                                                                             'datatype' => 'integer',
                                                                             'default' => 0,
                                                                             'required' => false,
                                                                             'foreign_class' => 'eZUser',
                                                                             'foreign_attribute' => 'contentobject_id',
                                                                             'multiplicity' => '1..*' )
                                           ),
                                    'keys' => array( 'id' ),
                                    'relations' => array( 'node_id' => array( 'class' => 'eZContentObjectTreeNode',
                                                                              'field' => 'node_id' ),
                                                          'user_id' => array( 'class' => 'eZUser',
                                                                              'field' => 'contentobject_id' ) ),
                                    'function_attributes' => array(),
                                    'increment_key' => 'id',
                                    'sort' => array( 'count' => 'desc' ),
                                    'class_name' => __CLASS__,
                                    'name' => 'ezview_counterip'
        );

        return $definition;
    }

    /**
     * Magic clone method
     * @access public
     * @link http://www.php.net/manual/en/language.oop5.cloning.php
     */
    public function __clone() {
        $this->ID = null;
    }

    /**
     * Creates an 'eZViewCounterDB' object
     * @static
     * @access public
     * @param integer $node_id
     * @param string $remote_address
     * @return eZViewCounterIP
     */
    public static function create( $node_id, $remote_address=null ) {
        $row = array(
                      'id' => null,
                      'node_id' => $node_id,
                      'remote_address' => $remote_address,
                      'count' => 1,
        );
        return new static( $row );
    }

    /**
     * Remove the line from the database
     * NOTE: Transaction unsafe. If you call several transaction unsafe methods you must enclose
     * the calls within a db transaction; thus within db->begin and db->commit.
     * @static
     * @param integer $node_id
     * @param string $remote_address
     */
    public static function removeCounter( $node_id, $remote_address ) {
        parent::removeObject( self::definition,
                              array( 'node_id' => $node_id,
                                     'remote_address' => $remote_address )
        );
    }

    /**
     * Reinitialize the counter
     * @static
     * @access public
     * @param integer $node_id
     * @param string $remote_address
     */
    public static function clear( $node_id, $remote_address ) {
        $counter = eZViewCounterIP::fetch($node_id, $remote_address);
        if ($counter instanceof eZViewCounterIP) {
            $counter->setAttribute( 'modified', time() );
            $counter->setAttribute( 'count', 0 );
            $counter->store();
        }
    }

    /**
     * Increment the counter
     * @access public
     */
    public function increase() {
        $counter = $this->count;
        $counter++;
        $this->setAttribute( 'modified', time() );
        $this->setAttribute( 'count', $counter );
        $this->store();
    }

    /**
     * Store the object to the database
     * @access public
     * @param array $fieldFilters
     */
    public function store( $fieldFilters=null ) {
        // first step : $db = eZDB::instance();
        // second step : $db->begin();
        parent::store($fieldFilters);
        // third step : $db->commit();
    }

    /**
     * Fetch the object with the node_id and remote_adress
     * @static
     * @access public
     * @param integer $node_id
     * @param string $remote_address
     * @param boolean $asObject
     * @return eZViewCounterIP
     */
    public static function fetch( $node_id, $remote_address, $asObject=true ) {
        return parent::fetchObject(
                                    self::definition(),
                                    null,
                                    array( 'node_id' => $node_id,
                                           'remote_address' => $remote_address ),
                                    $asObject
        );
    }

    /**
     * Fetch the object by the ID
     * @static
     * @access public
     * @param integer $id
     * @param boolean $asObject
     * @return eZViewCounterIP
     */
    public static function fetchByID( $id, $asObject=true ) {
        return parent::fetchObject(
                                    self::definition(),
                                    null,
                                    array( 'id' => $id ),
                                    $asObject
        );
    }

    /**
     * Fetch all eZViewCounterIP object
     * @static
     * @access public
     * @param boolean $asObject
     * @param boolean $offset
     * @param boolean $limit
     * @return array
     */
    public static function fetchList( $asObject=true, $offset=false, $limit=false ) {
        return eZPersistentObject::fetchObjectList(
                                                    self::definition(),
                                                    null,
                                                    null,
                                                    null,
                                                    array( 'offset' => $offset,
                                                           'length' => $limit ),
                                                    $asObject,
                                                    false,
                                                    null
        );
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
     */
    public static function fetchTopList( $classID=false, $sectionID=false, $offset=false, $limit=false ) {
        $result = null;
        if (!$classID && !$sectionID) {
            $result = self::fetchList( false, $offset, $limit );
        } else {

            // TODO optimize
            $conds = '';
            if ( $classID !== false ) {
                $conds .= 'ezcontentobject.contentclass_id='.(int)$classID.' ';
            }
            if ( $sectionID !== false ) {
                $sectionID .= 'ezcontentobject.section_id='.(int)$sectionID.' ';
            }
            $db = eZDB::instance();
            $query = 'SELECT ezview_counterip.* '.
                     'FROM ezcontentobject_tree, ezcontentobject, ezview_counterip '.
                     'WHERE ezview_counterip.node_id=ezcontentobject_tree.node_id '.
                     'AND '.$conds.
                     'AND ezcontentobject_tree.contentobject_id=ezcontentobject.id '.
                     'ORDER BY ezview_counterip.count DESC';
            if ( !$offset && !$limit ) {
                $result = $db->arrayQuery( $query );
            } else {
                $result = $db->arrayQuery( $query, array( 'offset' => $offset,
                                                          'limit' => $limit ) );
            }

        }
        return $result;
    }

    /**
     * Return the total number of count for the current node_id
     * @access public
     * @return integer
     */
    public function countView() {
        $customFields = array(
                                array( 'operation' => 'SUM(count)',
                                       'name' => 'sum_count' )
        );
        $sum = self::fetchObjectList(
                            self::definition(),
                            array(),
                            array( 'node_id' => $this->attribute( 'node_id' ),
                                   'remote_address' => $this->attribute( 'remote_address' ) ),
                            array(),
                            null,
                            false,
                            false,
                            $customFields
        );
        return $sum[0]['sum_count'];
    }

    /**
     * Magic getter
     * @access public
     * @param string $name
     * @return mixed
     */
    public function __get( $name ) {
        $result = null;
        switch ($name) {
            case 'Count':
                $result = $this->countView();
                break;

            case 'NodeID':
                $result = $this->attribute( 'node_id' );
                break;

            default:
                // nothing
                break;
        }
        return $result;
    }
}
