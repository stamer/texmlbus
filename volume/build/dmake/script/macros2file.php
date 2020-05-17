<?php
/**
 * MIT License
 * (c) 2008 - 2018 Heinrich Stamerjohanns
 *
 * Recreates the macro table
 *
 * For missing macros this script tries to identify in which .cls/.sty file a macro
 * is being defined. Results are stored in the macro table.

 * It uses as source the sty.arxmliv directory
 * in which many sty files are collected.
 *
 * @TODO expand this to sty and other possible directories.
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

echo "prefix is $prefix".PHP_EOL;

$query = '
	SELECT
		r.missing_macros
	FROM
		statistic as s
    JOIN
        retval_xml as r
    ON
        s.id = r.id
	WHERE
        s.`set` like "'.$prefix.'%"
        AND r.missing_macros != ""
	ORDER BY
		s.filename';

$stmt = $dao->prepare($query);

$stmt->execute();

$mm = array();
while ($row = $stmt->fetch()) {

	$str = $row['missing_macros'];

	preg_match('/^(\[)(.*?)(\]).*$/', $str, $matches);

	if (empty($matches[2])) {
		print_r($matches);
		exit;
	}
	$macros = preg_split('/,\s*/', $matches[2]);

	foreach ($macros as $macro) {
		$macro = substr($macro, 0, 60);
		echo "Macro: $macro".PHP_EOL;
		if (!isset($mm[$macro])) {
			$mm[$macro] = 1;
		} else {
			$mm[$macro]++;
		}
	}
}

$numrows = count($mm);

arsort($mm, SORT_NUMERIC);

$count = 0;

$dirs = array(
            STYARXMLIVDIR
        );

foreach ($mm as $macro=>$num) {

	if ($count == 1000) {
		echo "Stopping after 1000 entries...\n";
		exit;
	}
	$count++;

	foreach ($dirs as $dir) {
        // this handles \newcommand and \renewcommand
        //$exec_str = 'cd /arXMLiv/repos/arXMLiv/trunk/sty; /bin/fgrep -l \'newcommand{\\'.$macro.'}\' *';
        $exec_str = 'cd '.$dir.'; /bin/egrep -l \'\\\\((future)?let|newcommand|(g|e|x)?def)[^\\\\]*[^a-zA-Z0-9_]*'.$macro.'[^a-zA-Z0-9_]\' *';
        //echo $exec_str."\n";

        $retstr = shell_exec($exec_str);

        $retstr = trim($retstr);

        $arr = explode("\n", $retstr);

        $query = '
                DELETE FROM
                    macro
                WHERE
                    `set` = :set
                    AND `macro` = :macro';

        $stmt = $dao->prepare($query);
        $stmt->bindValue('set', $set);
        $stmt->bindValue('macro', $macro);

        foreach ($arr as $filename) {
            $filename = trim($filename);

            //	echo $filename."\n";

            $query = '
                INSERT INTO
                    macro
                values(0, :set, :macro, :num, :filename)';

            $stmt = $dao->prepare($query);
            $stmt->bindValue('set', $set);
            $stmt->bindValue('macro', $macro);
            $stmt->bindValue('num', $num);
            $stmt->bindValue('filename', $filename);

            $stmt->execute();
        }
    }
}
