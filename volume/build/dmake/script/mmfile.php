<?php
/*
Heinrich Stamerjohanns, April 29th, 2008
                        May 7th, 2018

The script will extract the files that have missing macros (xml target)
parses them to find out what style files are being included
and looks then for the specified macro.

The script should be called with a set name as parameter, then this script
will update all missing_macros entries for this set.
Existing entries will be deleted (filename, macro) and then recreated, so this
script can be called again and again.

*/
require_once "../IncFiles.php";
$cfg = Config::getConfig();

$dao = DAO::getInstance();

/**
 * Main program
 */
if (isset($argv[1])) {
	$set = $argv[1];
} else {
	$set = 'samples-working';
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
        s.`set` = :set
        AND rx.missing_macros != ''
	ORDER BY
		filename";

$mm = array();

$stmt = $dao->prepare($query);
$stmt->bindValue('set', $set);

$stmt->execute();

while ($row = $stmt->fetch()) {

	$filename = $row['filename'];
	$str = $row['missing_macros'];

	preg_match('/^(\[)(.*?)(\]).*$/', $str, $matches);

	//print_r($matches);

	$macros = preg_split('/,\s*/', $matches[2]);

	$filename = PAPERDIR.'/'.$filename;

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

	foreach ($macros as $macro) {
        echo "MMacro: $macro\n";
        if (!isset($mm[$filename][$macro])) {
            echo "Updating $filename: $macro".PHP_EOL;
            $sty_filename = UtilStylefile::mmFindMacroInStylefiles($set, $filename, $macro, $stylefilesArr);
            $mm[$filename][$macro] = $sty_filename;
        }
	}
}





