<?php
require_once('LogTailerListener.php');

/**
 * Listens for a log file and triggers event when a new log line is
 * is added to the file.
 */
class LogTailer {

	// how frequently check for file changes
	private $sampleInterval;

	// the path of the log file to tail
	private $filename;

	// define if the tailer should start at the beginning including all lines
	private $startAtBeginning;

	// is the tailer currently listening the log file?
	private $tailing;

	// array of listeners
	private $listeners;

	/**
	 * Constructor. Initializes class attributes
	 * @param string $filename the path of the log file to tail
	 * @param int $sampleInterval the number of seconds to wait before checking
	 * 		for file changes (default is 5 secs)
	 * @param boolean $startAtBeginning whether to start at the beginning of the
	 * 		log file or not
	 * @throws Exception if file does not exist or is not readable
	 */
	public function __construct($filename, $sampleInterval=5, $startAtBeginning=true) {
		$this->sampleInterval = $sampleInterval;
		$this->filename = $filename;
		$this->startAtBeginning = $startAtBeginning;
		$this->tailing = false;
		$this->listeners = array();
		
		// check if file exists and is readable
		if (!file_exists($this->filename) || !is_readable($this->filename)) {
			throw new Exception('Error: File does not exist or is not readable.');
		}
	}

	/**
	 * Starts the tailing proccess. Listens to the log file for current and new changes
	 * @throws Exception if file does not exist or is not readable
	 */
	public function start() {

		// check if file exists and is readable
		if (!file_exists($this->filename) || !is_readable($this->filename)) {
			throw new Exception('Error: File does not exist or is not readable.');
		}

		$filePointer = 0;

		if($this->startAtBeginning) {
			$filePointer = 0;
		}
		else {
			$filePointer = filesize($this->filename) - 1;
		}

		$this->tailing = true;

		while($this->tailing) {
			clearstatcache(); // remove files cache
			$fileLength = filesize($this->filename);
			$logfile = fopen($this->filename, "r");
			
			if ($fileLength < $filePointer) {
				// file was deleted or rotated, reset pointer
				$filePointer = 0;
			}
			
			if ($fileLength > $filePointer) {
				// there is data to read
				fseek($logfile, $filePointer);
				
				// TODO: check if line is complete
				while(!feof($logfile)) {
					$line = fgets($logfile);
					$this->fireNewLineAdded($line);
				}
				$filePointer = ftell($logfile);
				fclose($logfile);
			}

			sleep($this->sampleInterval);
		}
	}

	/**
	 * Stops the tailing process
	 */
	public function stop() {
		$this->tailing = false;
	}

	/**
	 * Calls the newLineAdded method on every listener in the listeners array
	 * @param string $line the new line added to log file
	 */
	protected function fireNewLineAdded($line) {
		foreach($this->listeners as $listener) {
			$listener->newLineAdded($line);
		}
	}

	/**
	 * Adds a new listener to the listeners array
	 * @param object a LogFileListener instance
	 * @return int the index of the added
	 */
	public function addListener($listener) {
		$this->listeners[] = $listener;
		
		return max(array_keys($this->listeners));
	}

	/**
	 * Removes the listener with given index
	 * @param int $index the index of the listener to remove
	 */
	public function removeListener($index) {
		unset($this->listeners[$index]);
	}

}
