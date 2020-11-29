<?php
/**
 * Released under MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 */
require_once "../include/IncFiles.php";
use Dmake\Dao;
use Dmake\RetvalDao;
use Server\Config;
use Server\Page;
use Server\UtilMisc;

$IS_CRAWLER = false;
$page = new Page('Macro usage');
$page->showHeader('index');

$cfg = Config::getConfig();
$dao = Dao::getInstance();

$min = $page->getRequest()->getQueryParam('min', 0);
// make sure no negatives
$min = max(0, (int) $min);

$stage = $page->getRequest()->getQueryParam('stage', 'xml');
$set = $page->getRequest()->getQueryParam('set', '');

$stages = array_keys($cfg->stages);

if (in_array($stage, $stages)) {
    $joinTable = $cfg->stages[$stage]->dbTable;
    $tableTitle = $cfg->stages[$stage]->tableTitle;
} else {
    echo "Unknown stage: ".htmlspecialchars($stage);
    exit;
}

$macro = $page->getRequest()->getQueryParam('macro', '');

if (empty($macro)) {
	echo "No macro given!";
	exit;
}

$max_pp = $cfg->db->perPage;

$numrows = RetvalDao::getCountByMacro($macro, $joinTable);
$rows = RetvalDao::getByMacro($macro, $joinTable, $set, $min, $max_pp);

echo '<h3>Files that use macro <em>'.htmlspecialchars($macro).'</em></h3>';
echo '<table border="1">';

echo '<tr><th>No.</th><th>Date</th><th>Macro</th><th>Errmsg</th></tr>';

$count = $min;
foreach ($rows as $row) {
	$count++;
	$directory = 'files/'.$row['filename'].'/';
	echo "<tr>\n";
	echo '<td align="right">'.$count.'</td><td>'.$row['date_created'].'</td>';
	if (!$IS_CRAWLER) {
		echo '<td><a href="'.$directory.'">'.$row['filename'].'</a></td><td>'.$row['missing_macros']."\n";
	} else {
		echo '<td>'.$row['filename'].'</td><td>'.$row['missing_macros']."\n";
	}
	echo "</tr>\n";
}
echo "</table>";

UtilMisc::navigator($min, $min, $max_pp, $numrows);

$page->showFooter();
