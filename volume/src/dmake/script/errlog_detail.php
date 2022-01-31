<?php
/**
 * MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 */
require_once dirname(__DIR__, 1) . "/IncFiles.php";
require_once dirname(__DIR__, 1) . "/ErrDetEntry.php";

use Dmake\Config;
use Dmake\Dao;
use Dmake\StatEntry;
use Dmake\ErrDetEntry;

$cfg = Config::getConfig();
$dao = Dao::getInstance();

$restrict = [];

if (isset($argv[1])) {
    //$restrict['set'] = $argv[1];
}

if (isset($argv[2])) {
    $stage = $argv[2];
} else {
    $stage = 'xml';
}

/**
 * @TODO check valid target
 */
//$restrict['dir'] = 'Misgeld';
$restrict['retval'] = 'error';
$restrict['retval_target'] = $stage;

$dirs = StatEntry::getFilenamesByRestriction($stage, $restrict);

/**
 * loop through all given directories
 */

//$dirs = array("/math-ph/papers/9912016");
foreach ($dirs as $directory) {

	echo $directory."..." . PHP_EOL;

	$statEntry = StatEntry::getByDir($directory);
    $document_id = $statEntry->getId();

	$datestamp = date("Y-m-d H:i:s", time());

	ErrDetEntry::deleteByIdAndTarget($document_id, $stage);

    $classname = $cfg->stages[$stage]->classname;
    $hostGroup = $cfg->stages[$stage]->hostGroup;

    if (!class_exists($classname)) {
        die("Stage: $stage, Trying to load $classname, but it does not exist");
    }

    $retvalInstance = new $classname;

    // parses and creates entries in errlog_detail for each single entry in the logfile.
    $retvalInstance->parseDetail($hostGroup, $statEntry);

}




