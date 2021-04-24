<?php
/*
Heinrich Stamerjohanns, July 5th, 2018

This script will extract the packages an articles uses and saves them
to the table package_usage.

*/

require_once "../IncFiles.php";

$cfg = Config::getConfig();

$dao = DAO::getInstance();

/**
 * default set ist samples-working, 
 * special set 'all', is all files, 
 * with no set restriction
 */
if (isset($argv[1])) {
	$set = $argv[1];
} else {
	$set = 'samples-working';
}

if ($set === 'all') {
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

	$texsourcefile = PAPERDIR.'/'.$filename.'/'.$sourcefile;

	print "$texsourcefile\n";
	$stylefilesArr = UtilStyleFile::getStylefiles($texsourcefile);
	
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

