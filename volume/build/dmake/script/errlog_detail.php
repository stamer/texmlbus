<?php
/**
 * MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 */
require_once "IncFiles.php";
require_once "ErrDetEntry.php";

$cfg = Config::getConfig();
$dao = Dao::getInstance();

$restrict = [];

if (isset($argv[1])) {
    //$restrict['set'] = $argv[1];
}

if (isset($argv[2])) {
    $action = $argv[2];
} else {
    $action = 'xml';
}

/**
 * @TODO check valid target
 */
$restrict['dir'] = 'Misgeld';
$restrict['retval'] = 'error';
$restrict['retval_target'] = $action;

$dirs = StatEntry::getFilenamesByRestriction($action, $restrict);

/**
 * loop through all given directories
 */

//$dirs = array("/math-ph/papers/9912016");
foreach ($dirs as $directory) {

	echo $directory."..." . PHP_EOL;

	$statEntry = StatEntry::getByDir($directory);
    $document_id = $statEntry->getId();

	$datestamp = date("Y-m-d H:i:s", time());

	ErrDetEntry::deleteByIdAndTarget($document_id, $action);

    $classname = $cfg->stages[$action]->classname;

    if (!class_exists($classname)) {
        die ("Action: $action, Trying to load $classname, but it does not exist");
    }

    $retvalInstance = new $classname;

    // parses and creates entries in errlog_detail for each single entry in the logfile.
    $retvalInstance->parseDetail($statEntry);

}




