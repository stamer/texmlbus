<?php
/**
 * MIT License
 * (c) 2018 - 2022 Heinrich Stamerjohanns
 *
 * This script will extract the packages an articles uses and saves them
 * to the table package_usage.
 */

require_once dirname(__DIR__, 2) . "/dmake/IncFiles.php";

use Dmake\Config;
use Dmake\Dao;
use Dmake\UtilStylefile;

$cfg = Config::getConfig();

$dao = DAO::getInstance();

if (isset($argv[1])) {
	$set = $argv[1];
} else {
	$set = '';
}

if (!empty($set)) {
    $where = '`set` = :set ';
} else {
    $where = '1 = 1 ';
}

$query = "
	SELECT
		s.filename,
		s.sourcefile
	FROM
		statistic as s
    WHERE
        $where
	ORDER BY
		filename";

$stmt = $dao->prepare($query);
if (!empty($set)) {
    $stmt->bindValue('set', $set);
}

$stmt->execute();

$mm = array();

while ($row = $stmt->fetch()) {

$filename = $row['filename'];
    $sourcefile = $row['sourcefile'];
    
    echo "Filename: $filename".PHP_EOL;

	$texsourcefile = ARTICLEDIR.'/'.$filename.'/'.$sourcefile;

	print "$texsourcefile\n";
	$stylefilesArr = UtilStylefile::getStylefiles($texsourcefile);
	
    $stylefilesArr = array_unique($stylefilesArr);
	
	echo "Stylefiles:".PHP_EOL;
	print_r($stylefilesArr);

	if ($stylefilesArr[0] == "NOFILE") {
		echo "Could not open $filename!\n";
		continue;
	}

	if ($stylefilesArr[0] == "NODOCUMENTCLASS") {
		echo "No document class!\n";
		continue;
	}

	if ($stylefilesArr[0] == "NODOCUMENTSTYLE") {
		echo "No document style!\n";
		continue;
	}

    UtilStylefile::saveStylefiles($set, $filename, $stylefilesArr);

}

