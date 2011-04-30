# PHP Log Tailer

## Description

phplogtailer is a PHP5 library that tails a given log file, allowing to attach an event every time a new line is written into the log file.

## Installation

Just place the files wherever you prefer, and require them in your code.

## Usage examples

	<?php
	require_once('LogTailer.php');
	require_once('LogTailerListener.php');
	
	class MyListener implements LogTailerListener {
		public function newLineAdded($line) {
			echo "New line on log file: $line";
		}
	}
	
	$tailer = new LogTailer('/var/log/apache/access.log');
	$listener = new MyListener();
	$tailer->addListener($listener);
	$tailer->start();
	
	// when we're done we can stop the tailing process
	$tailer->stop();