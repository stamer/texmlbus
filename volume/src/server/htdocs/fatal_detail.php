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

$cfg = Config::getConfig();
$dao = Dao::getInstance();

$page = new Page('Build System');
$page->showHeader('index');

$cfg = Config::getConfig();
$dao = Dao::getInstance();

$min = $page->getRequest()->getQueryParam('min', 0);
// make sure no negatives
$min = max(0, (int) $min);

$stage = $page->getRequest()->getQueryParam('stage', 'xml');

$stages = array_keys($cfg->stages);

if (in_array($stage, $stages)) {
    $joinTable = $cfg->stages[$stage]->dbTable;
    $tableTitle = $cfg->stages[$stage]->tableTitle;
} else {
    echo "Unknown stage: ".htmlspecialchars($stage);
    exit;
}

$errmsg = $page->getRequest()->getQueryParam('errmsg', '');
if (empty(errmsg)) {
	echo "No fatal error given!";
	exit;
}

$numrows = RetvalDao::getCountByErrMsg($joinTable, $errmsg);

$max_pp = $cfg->db->perPage;

$rows = RetvalDao::getByErrMsg($joinTable, $errmsg, $min, $max_pp);

echo '<h3>Files that have given fatal error: <em>'.htmlspecialchars($errmsg).'</em></h3>';
echo '<table border="1">';

echo '<tr><th>No.</th><th>Date</th><th>Files</th><th>Errmsg</th></tr>';

$count = $min;
foreach ($rows as $row) {
	$count++;
	$directory = 'files'.$row['filename'].'/';
	echo "<tr>\n";
	echo '<td align="right">'.$count.'</td><td>'.$row['date_created'].'</td>';
	if (!$cfg->isCrawler) {
		echo '<td><a href="'.$directory.'">'.htmlspecialchars($row['filename']).'</a></td><td>'.htmlspecialchars($row['errmsg'])."\n";
	} else {
		echo '<td>'.htmlspecialchars($row['filename']).'</td><td>'.htmlspecialchars($row['errmsg'])."\n";
	}
	echo "</tr>\n";
}
echo "</table>";

UtilMisc::navigator($min, $min, $max_pp, $numrows);

$page->showFooter();
