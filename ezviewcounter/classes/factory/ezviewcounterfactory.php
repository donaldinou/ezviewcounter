<?php 
abstract class eZViewCounterFactory {
	
	/**
	 * 
	 * Enter description here ...
	 * @param string $type
	 * @param eZContentObject
	 * @return ieZViewCounter
	 * @throws Exception unknown type
	 */
	public static function factory( $type, eZPersistentObject $node ) {
		$result = null;
		switch ($type) {
			case 'ez':
				$node_id = $node->attribute('node_id');
				$adapted = self::buildeZViewCounter($node_id);
				$result = new eZViewCounterAdapter($adapted);
				break;
			
			case 'db':
				$node_id = $node->attribute('node_id');
				$adapted = self::buildeZViewCounterDB($node);
				$result = new eZViewCounterDBAdapter($adapted);
				break;
				
			case 'xml':
				$adapted = self::buildeZViewCounterXML($node);
				$result = new eZViewCounterXMLAdapter($adapted);
				break;
			
			case 'file':
				$adapted = self::buildeZViewCounterFile($node);
				$result = new eZViewCounterFileAdapter($adapted);
				break;
			
			default:
				throw new Exception('Unexpected counter type');
			break;
		}
		return $result;
	}
	
	private static function buildeZViewCounter($node_id) {
		$eZViewCounter = eZViewCounter::fetch( $node_id );
		if ( !($eZViewCounter instanceof eZViewCounter ) ) {
			$eZViewCounter = eZViewCounter::create($node_id);
			$eZViewCounter->store();
		}
		return $eZViewCounter;
	}
	
	private static function buildeZViewCounterDB($node_id) {
		$eZViewCounter = eZViewCounterDB::fetch( $node_id, eZSys::clientIP() );
		if ( !($eZViewCounter instanceof eZViewCounterDB) ) {
			$eZViewCounter = eZViewCounterDB::create($node_id, eZSys::clientIP());
			$eZViewCounter->store();
		}
		return $eZViewCounter;
	}
	
	private static function buildeZViewCounterXML(eZPersistentObject $node) {
		$node_id = $node->attribute('node_id');
		$object_id = $node->attribute('contentobject_id');
		try {
			$eZViewCounter = eZViewCounterXML::instance($object_id);
			if ( !$eZViewCounter->isExist( $node_id ) ) {
				$eZViewCounter->create($node_id);
				$eZViewCounter->store();
			}
		} catch (Exception $e) {
			eZDebug::writeError($e->getMessage());
			$eZViewCounter = null;
		}
		return $eZViewCounter;
	}
	
	private static function buildeZViewCounterFile(eZPersistentObject $node) {
		$eZViewCounter = eZViewCounterFile::fetch( $node_id );
		if ( !($eZViewCounter instanceof eZViewCounterFile) ) {
			//$eZViewCounter = eZViewCounterFile::create($node_id);
			//$eZViewCounter->store();
		}
		return $eZViewCounter;
	}
	
}
?>