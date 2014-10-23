<?php
/**
 * Multiton class needed to build ascii file
 * @author Adrien Loyant <adrien.loyant@te-laval.fr>
 * @abstract
 */
abstract class eZViewCounterMultitonASCII {

    const DEFAULT_INSTANCE_NAME = '__DEFAULT__';

    /**
     * Array of eZViewCounterMultitonASCII instance
     * @staticvar
     * @access private
     * @var array
     */
    private static $_instances = array();

    /**
     * The path of the directory witch contain the file
     * @var unknown_type
     */
    protected $directory;

    /**
     * The name of the file
     * @access protected
     * @var string
     */
    protected $filename;

    /**
     * You must define a constructor witch initialize directory and default filename
     * @abstract
     * @access private
     */
    abstract protected function __construct();

    /**
     * Return the instance of a particular ascii file (text, xml...)
     * @static
     * @access public
     * @param string $filename
     * @return eZViewCounterMultitonASCII
     */
    public static function instance( $filename ) {
        $_filename = static::DEFAULT_INSTANCE_NAME;
        $ini = eZINI::instance('ezviewcounter.ini');
        $CounterAdvanced = ($ini->hasVariable('Configuration', 'CounterAdvanced')) ? $ini->variable('Configuration', 'CounterAdvanced') : 'disabled';
        if ($CounterAdvanced == 'enabled') {
            $_filename = $filename;
        }
        if (!isset(self::$_instances[$_filename]) || !(self::$instances[$_filename] instanceof eZViewCounterMultitonASCII) ) {
            self::$_instances[$_filename] = new static($filename);
        }
        return self::$_instances[$_filename];
    }

    /**
     * Do not authorize external copy of the object
     * @final
     * @access public
     * @return error
     */
    final public function __clone() {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

    /**
     * Do not authorize unserialize object
     * @final
     * @access public
     * @return error
     */
    final public function __wakeup() {
        trigger_error('Unserialize is not allowed.', E_USER_ERROR);
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
            case 'file':
                $result = $this->directory . $this->filename;
                break;

            default:
                // Nothing to get
            break;
        }
        return $result;
    }

}
