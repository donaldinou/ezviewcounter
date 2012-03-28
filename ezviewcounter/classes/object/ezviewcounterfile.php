<?php 
class eZViewCounterFile extends eZViewCounterMultitonASCII {
	
	protected function __construct( $filename ) {
		$ini = eZINI::instance('ezviewcounter.ini');
		$this->directory = ($ini->hasVariable('file', 'CounterDirectoryPath')) ? $ini->variable('file', 'CounterDirectoryPath') : 'var/counters/';
		$this->fileName = $filename;
		$this->init();
	}
	
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
			if (!touch($this->file)) {
				eZDebug::writeError('Error while creating '.$this->fileName.' file.');
				$result = false;
			}
		}
		if (!is_readable($this->file) || !is_writable($this->file)) {
			if (!chmod($this->file, 0777)) {
				eZDebug::writeError('Error while changing right on '.$this->fileName.' file.');
				$result = false;
			}
		}
		
		return $result;
	}
	
	public function removeCounter() {
		if (!unlink($this->file)) {
			eZDebug::writeError('Error while removing '.$this->fileName.' file.');
		}
	}
	
	public function clear() {
		if (!file_put_contents($this->file, '0', LOCK_EX)) {
			
		}
	}
	
	public function increase() {
		$file = file_get_contents($this->file);
		if ($file) {
			$count = (int)file;
			$count++;
			if (!file_put_contents($this->file, $count, LOCK_EX)) {
				eZDebug::writeError('Error while increase '.$this->fileName.' file.');
			}
		} else {
			eZDebug::writeError('Error while opening '.$this->fileName.' file.');
		}
	}
	
	public function countView() {
		$result = 0;
		$file = file_get_contents($this->file);
		if ($file) {
			$result = (int)$file;
		} else {
			eZDebug::writeError('Error while opening '.$this->fileName.' file.');
		}
		return $result;
	}
	
	public static function fetch( $node_id ) {
		return self::instance($node_id);
	}
	
}
?>