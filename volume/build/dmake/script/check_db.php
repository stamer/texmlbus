<?php
/**
 * MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 */
ini_set("display_errors", 1);
ini_set("memory_limit", "512M");

require_once "IncFiles.php";

/**
 * Main program
 */
$depth = 0;
$flc = 0;

$dirs = array();
$prefix = '/arXMLiv/tars_untarred/arxiv/papers';

$restrict['dir'] = '';

// just scan DB
$dirs = StatEntry::getFilenamesByRestrictionXml($restrict);

/**
 * loop through all given directories
 */
foreach ($dirs as $directory) {

	echo "Trying $directory... ";
	if (!is_dir("$prefix$directory")) {
		echo "$prefix$directory not found!\n";
		StatEntry::delete($directory);
	}
	else {
		echo " OK.\n";
	}
}




