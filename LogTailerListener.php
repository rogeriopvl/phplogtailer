<?php

/**
 * The interface that must be implemented by listeners
 */
interface LogTailerListener {

	/**
	 * This method is called when a new line is added to the log file
	 * @param string $line the new line added
	 */
	public function newLineAdded($line);
}
