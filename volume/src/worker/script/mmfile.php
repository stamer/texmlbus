<?php
/**
 * MIT License
 * (c) 2008 - 2022 Heinrich Stamerjohanns
 *
 */
/*
The script will extract the files that have missing macros (xml target)
parses them to find out what style files are being included
and looks then for the specified macro.

The script should be called with a set name as parameter, then this script
will update all missing_macros entries for this set.
Existing entries will be deleted (filename, macro) and then recreated, so this
script can be called again and again.

*/
require_once dirname(__DIR__, 2) . "/dmake/IncFiles.php";

use Dmake\Config;
use Dmake\Dao;
use Dmake\UtilFile;
use Dmake\UtilStylefile;

$cfg = Config::getConfig();

$dao = Dao::getInstance();

if (isset($argv[1])) {
	$set = $argv[1];
} else {
	$set = '';
}

if ($set !== '') {
    $setCond = ' AND s.`set` = :set ';
} else {
    $setCond = '';
}

$query = "
	SELECT
        rx.missing_macros,
		s.filename
	FROM
		statistic as s
    JOIN
        retval_xml as rx
    ON
        s.id = rx.id
    WHERE
        rx.missing_macros != ''
        $setCond
	ORDER BY
		filename";

$mm = array();

$stmt = $dao->prepare($query);
if ($set !== '') {
    $stmt->bindValue('set', $set);
}

$stmt->execute();

while ($row = $stmt->fetch()) {

	$filename = $row['filename'];
	$str = $row['missing_macros'];

	preg_match('/^(\[)(.*?)(\]).*$/', $str, $matches);

	//print_r($matches);

	$macros = preg_split('/,\s*/', $matches[2]);

	$filename = ARTICLEDIR.'/'.$filename;

	$texsourcefile = UtilFile::getSourcefileInDirViaMake($filename);

	echo "$texsourcefile".PHP_EOL;
	$stylefilesArr = UtilStylefile::getStylefiles($texsourcefile);

	print_r($stylefilesArr);

	if ($stylefilesArr[0] == "NOFILE") {
		echo "Could not open $filename!".PHP_EOL;
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

    $us = new UtilStylefile();
    foreach ($macros as $macro) {
        echo "MMacro: $macro\n";
        if (!isset($mm[$filename][$macro])) {
            echo "Updating $filename: $macro".PHP_EOL;
            $sty_filename = $us->mmFindMacroInStylefiles($set, $filename, $macro, $stylefilesArr);
            $mm[$filename][$macro] = $sty_filename;
        }
    }
}
