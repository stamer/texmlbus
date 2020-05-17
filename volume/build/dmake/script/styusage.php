<?
/*
Heinrich Stamerjohanns, April 29th, 2008

The script will extract the files that have missing macros,
parses them to find out what style  files are being included
and looks then for the specified macro.

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
        `set` = :set
	ORDER BY
		filename";

    $stmt = $dao->prepare($query);
    $stmt->bindValue('set', $set);

    $stmt->execute();

$mm = array();

while ($row = $stmt->fetch()) {
	$filename = $row['filename'];
	$str = $row['missing_macros'];

	preg_match('/^(\[)(.*?)(\]).*$/', $str, $matches);

    echo "Filename: $filename".PHP_EOL;
	echo "Matching macros:".PHP_EOL;
	print_r($matches);

	$macros = preg_split('/,\s*/', $matches[2]);
	echo "Macros".PHP_EOL;
	print_r($macros);


	$filename = PAPERDIR.'/'.$filename;

	$texsourcefile = UtilFile::getSourcefileInDirViaMake($filename);

	print "$texsourcefile\n";
	$stylefilesArr = UtilStyleFile::getStylefiles($texsourcefile);

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

	foreach ($macros as $macro) {
        echo "MMacro: $macro\n";
        if (!isset($mm[$filename][$macro])) {
            $sty_filename = UtilStylefile::findMacroInStylefiles($set, $filename, $macro, $stylefilesArr);
            $mm[$filename][$macro] = $sty_filename;
        }
	}
}





