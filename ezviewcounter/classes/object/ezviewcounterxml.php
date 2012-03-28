<?php 
/**
 * 
 * Enter description here ...
 * @author aloyant
 * @todo class for node functions?
 */
class eZViewCounterXML extends eZViewCounterMultitonASCII {
	
	/**
	 * 
	 * Enter description here ...
	 * @var DOMDocument
	 */
	protected $domDocument;
	
	/**
	 * 
	 * Enter description here ...
	 * @var unknown_type
	 */
	protected $objectId;
	
	/**
	 * Constructor
	 * @access private
	 * @param int $objectid
	 * @throws Exception
	 * @todo own exception
	 */
	protected function __construct( $objectId ) {
		$ini = eZINI::instance('ezviewcounter.ini');
		$CounterAdvanced = ($ini->hasVariable('Configuration', 'CounterAdvanced')) ? $ini->variable('Configuration', 'CounterAdvanced') : 'disabled';
		$this->directory = ($ini->hasVariable('xml', 'CounterDirectoryPath')) ? $ini->variable('xml', 'CounterDirectoryPath') : 'var/counters/';
		$this->filename = 'counters.xml';
		$this->objectId = (int)$objectId;
		if ($CounterAdvanced == 'enabled') {
			$this->filename = $this->objectId.'.xml';
		} elseif ($ini->hasVariable('xml', 'CounterFilename')) {
			$this->filename = $ini->variable('xml', 'CounterFilename');
		}
		if ($this->init()) {
			try {
				$this->domDocument = new DOMDocument();
				$this->domDocument->preserveWhiteSpace = false;
				$this->domDocument->load($this->file, LIBXML_COMPACT|LIBXML_NOCDATA);
				
				
				$schemaFilePath = __DIR__ . '/../../xml/xsd/counter.xsd';
				// TODO put this in a helper
				if (!is_file($schemaFilePath) || !file_exists($schemaFilePath)) {
					throw new Exception('Not a file');
				}
				if ( !is_readable($schemaFilePath) || !is_writable($schemaFilePath)) {
					throw new Exception('Not readable');
				}
				
				if (!$this->domDocument->schemaValidate($schemaFilePath)) {
					throw new Exception('XML schema is not valid');
				}
			} catch (Exception $e) {
				eZDebug::writeError($e->getMessage());
			}
		} else {
			throw new Exception('XML initialization file failed');
		}
	}
	
	/**
	 * Initialize folder and file if needed
	 * @access private
	 * @todo throw error
	 */
	private function init() {
		$result = true;
		
		// Check directory rights
		if (!is_dir($this->directory)) {
			if (!mkdir($this->directory, 0755, true)) {
				$this->directory = 'var/counters/';
				eZDebug::writeError('Error while creating '.$this->directory.' directories.');
			}
		}
	
		// check file rights
		if (!file_exists($this->file) || !is_file($this->file)) {
			$source = realpath(__DIR__ . '/../../xml/src/counters.xml');
			$dest = $this->file;
			if (!copy($source, $dest)) {
				eZDebug::writeError('Error while creating '.$this->filename.' file.');
				$result = false;
			}
		}
		if ( (!is_readable($this->file) || !is_writable($this->file)) && $result) {
			if (!chmod($this->file, 0777)) {
				eZDebug::writeError('Error while changing right on '.$this->filename.' file.');
				$result = false;
			}
		}
		
		return $result;
	}
	
	/**
	 * Enter description here ...
	 * @param unknown_type $node_id
	 * @todo generic method to create counter_object
	 */
	public function create( $node_id=null ) {
		$now = new DateTime();
		
		if (!$this->isExist()) {
			// create counter element
			$counter = $this->domDocument->createElement('counter');
			$counter->setAttribute('id', 'object_'.$this->objectId);
			$counter->setAttribute('sid', $this->objectId);
			$counter->setIdAttribute('id', true);
			
			// create counter sequence
			$countElement = $this->domDocument->createElement('count', 0);
			$createdElement = $this->domDocument->createElement('created', $now->format('c'));
			$modifiedElement = $this->domDocument->createElement('modified', $now->format('c'));
			$remotesElement = $this->domDocument->createElement('remotes');
			$remoteAddressElement = $this->domDocument->createElement('remote_address', eZSys::clientIP());
			$remoteAddressElement->setAttribute('count', 0);
			
			$counter->appendChild($countElement);
			$counter->appendChild($createdElement);
			$counter->appendChild($modifiedElement);
			$remotesElement->appendChild($remoteAddressElement);
			$counter->appendChild($remotesElement);
			$this->domDocument->documentElement->appendChild($counter);
		}
		
		// retrieve nodes element
		if ( !$this->evaluate('count(//counters/counter[@id=\''.$this->objectId.'\']/nodes)') ) {
			$nodesElement = $this->domDocument->createElement('nodes');
			$nodesNode = $this->domDocument->getElementById('object_'.$this->objectId)->appendChild($nodesElement);
		} else {
			$nodesNode = $this->domDocument->getElementsByTagName('nodes')->item(0);
		}
		
		if ( !empty($node_id) && !$this->isExist($node_id) ) {
			// create elements
			$nodeElement = $this->domDocument->createElement('node');
			$nodeElement->setAttribute('id', 'node_'.$node_id);
			$nodeElement->setAttribute('sid', $node_id);
			$counter->setIdAttribute('id', true);
			$countElement = $this->domDocument->createElement('count', 0);
			$createdElement = $this->domDocument->createElement('created', $now->format('c'));
			$modifiedElement = $this->domDocument->createElement('modified', $now->format('c'));
			$remotesElement = $this->domDocument->createElement('remotes');
			$remoteAddressElement = $this->domDocument->createElement('remote_address', eZSys::clientIP());
			$remoteAddressElement->setAttribute('count', 0);
		
			// append child
			$nodeElement->appendChild($countElement);
			$nodeElement->appendChild($createdElement);
			$nodeElement->appendChild($modifiedElement);
			$remotesElement->appendChild($remoteAddressElement);
			$nodeElement->appendChild($remotesElement);
			$nodesNode->appendChild($nodeElement);
		}
		
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function store() {
		$this->domDocument->save($this->file, LIBXML_NOEMPTYTAG);
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @todo clear IP eather
	 */
	public function removeCounter( $node_id=null ) {
		$element = $this->getElementById($node_id);
		if ($element instanceof DOMNode) {
			if (!empty($node_id)) {
				$counter = $this->getElementById(null);
				$count = $element->getElementsByTagName('count')->item(0)->nodeValue;
				$counter->getElementsByTagName('count')->item(0)->nodeValue -= $count;
				// remove the entire nodes element when there is just one node elements
				if ((int)$this->evaluate('count(//counters/counter[@id=\'object'.$this->objectId.'\']/nodes/node)')>1) {
					$counter->getElementsByTagName('nodes')->item(0)->removeChild($element);
				} else {
					$nodes = $counter->getElementsByTagName('nodes')->item(0);
					$counter->removeChild($nodes);
				}
			} else {
				$this->domDocument->documentElement->removeChild($element);
			}
		}
		$this->store();
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @todo clear IP eather
	 */
	public function clear( $node_id=null ) {
		$element = $this->getElementById($node_id);
		if ($element instanceof DOMNode) {
			if (!empty($node_id)) {
				$counter = $this->getElementById(null);
				$count = $element->getElementsByTagName('count')->item(0)->nodeValue;
				$counter->getElementsByTagName('count')->item(0)->nodeValue -= $count;
			}
			$element->getElementsByTagName('count')->item(0)->nodeValue = 0;
		}
		$this->store();
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @todo generic method to modify counter_objet
	 */
	public function increase( $node_id=null ) {
		$now = new DateTime();
		
		// FIXME : There are some problems to get ids when object has just created
		$counter = $this->domDocument->getElementById('object_'.$this->objectId);
		if ($counter instanceof DOMNode && $counter->hasChildNodes()) {
			if (!empty($node_id)) {
				$node = $this->domDocument->getElementById('node_'.$node_id);
				$node->getElementsByTagName('count')->item(0)->nodeValue += 1;
				$node->getElementsByTagName('modified')->item(0)->nodeValue = $now->format('c');
				
				// increment count element for remote_address node
				$wasFound = false;
				$remotes_address = $node->getElementsByTagName('remotes')->item(0)->childNodes;
				foreach ( $remotes_address as $remote_address ) {
					if ($remote_address->nodeValue == eZSys::clientIP()) {
						$count = (int)$remote_address->getAttribute('count')+1;
						$remote_address->setAttribute( 'count', $count );
						$wasFound = true;
						break;
					}
				}
				if (!$wasFound) {
					$remote = $this->domDocument->createElement('remote_address', eZSys::clientIP());
					$remote->setAttribute('count', 1);
					$node->getElementsByTagName('remotes')->item(0)->appendChild($remote);
				}
			}
			
			$counter->getElementsByTagName('count')->item(0)->nodeValue += 1;
			$counter->getElementsByTagName('modified')->item(0)->nodeValue = $now->format('c');
			
			// increment count element for remote_address node
			$wasFound = false;
			$remotes_address = $counter->getElementsByTagName('remotes')->item(0)->childNodes;
			foreach ( $remotes_address as $remote_address ) {
				if ($remote_address->nodeValue == eZSys::clientIP()) {
					$count = (int)$remote_address->getAttribute('count')+1;
					$remote_address->setAttribute( 'count', $count );
					$wasFound = true;
					break;
				}
			}
			if (!$wasFound) {
				$remote = $this->domDocument->createElement('remote_address', eZSys::clientIP());
				$remote->setAttribute('count', 1);
				$counter->getElementsByTagName('remotes')->item(0)->appendChild($remote);
			}
		}
		
		$this->store();
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function countView( $node_id=null, $isByIP=false ) {
		$result = 0;
		$element = $this->getElementById($node_id);
		if ($element instanceof DOMNode) {
			if ($isByIP) {
				foreach ($element->getElementsByTagName('remote_address') as $remote_address) {
					if ($remote_address->nodeValue == eZSys::clientIP()) {
						$result = (int)$remote_address->getAttribute('count');
						break;
					}
				}
			} else {
				$result = (int)$element->getElementsByTagName('count')->item(0)->nodeValue;
			}
		}
		return $result;
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param int $node_id
	 */
	public function isExist( $node_id=null ) {
		$result = false;
		$element = $this->getElementById($node_id);
		if ($element instanceof DOMNode) {
			$result = true;
		}
		return $result;
	}
	
	/**
	 * Evaluates the given XPath expression
	 * @see DOMXPath::query
	 * @param unknown_type $query
	 * @example $result = $eZViewCounterXML->xpath('//counter[@id=\'object_1\']');
	 */
	public function xpath( $query ) {
		$xpath = new DOMXPath($this->domDocument);
		return $xpath->query( $query );
	}
	
	/**
	 * Evaluates the given XPath expression and returns a typed result if possible 
	 * @see DOMXPath::evaluate
	 * @param unknown_type $query
	 * @example $result = $eZViewCounterXML->xpath('count(//counter[@id=\'object_1\'])');
	 */
	public function evaluate( $query ) {
		$xpath = new DOMXPath($this->domDocument);
		return $xpath->evaluate( $query );
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $name
	 * @param unknown_type $node_id
	 */
	public function attribute( $name, $node_id=null) {
		$result = null;
		$element = $this->getElementById($node_id);
		$elements = $element->getElementsByTagName($name);
		if ($elements->length>0) {
			$result = $elements->item(0)->nodeValue;
		}
		return $result;
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $node_id
	 * @return unknown
	 */
	private function getElementById( $node_id=null ) {
		$element = (empty($node_id) && is_int($node_id)) ? $this->domDocument->getElementById('object_'.$this->objectId) : $this->domDocument->getElementById('node_'.$node_id);
		return $element;
	} 
}
?>