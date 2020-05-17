<?php
/**
 * Released under MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 */
require_once "../include/IncFiles.php";
use Dmake\Dao;
use Server\Config;
use Server\Page;

require_once "include/header.php";

$page = new Page('Tex BUild System');
$page->showHeader('index');

$cfg = Config::getConfig();

$min = $page->getRequest()->getQueryParam('min', 0);
// make sure no negatives
$min = max(0, (int) $min);

$errClass = $page->getRequest()->getQueryParam('errclass', '');

if (empty($errClass)) {
	echo "No error class given!";
	exit;
}

$max_pp = $cfg->db->perPage;

$numrows = ErrDetEntry::getFileCountByErrClass($errClass);
$rows = ErrDetEntry::getFileByErrClass($errClass, $min, $max_pp);

echo '<h3>Files that contain error of class: <em>'.htmlspecialchars($errClass).'</em></h3>';

echo '<table border="1">';

echo '<tr><th>No.</th><th>Date</th><th>Files</th><th>Errmsg</th></tr>';
$count = $min;
foreach ($rows as $row) {

	$count++;
	$directory = 'files'.$row['filename'].'/';
	echo "<tr>\n";
	echo '<td align="right">'.$count.'</td><td>'.$row['date_created'].'</td>';
	if (!$IS_CRAWLER) {
		echo '<td><a href="'.$directory.'">'.htmlspecialchars($row['filename']).'</a></td><td>'.htmlspecialchars($row['errmsg'])."\n";
	} else {
		echo '<td>'.htmlspecialchars($row['filename']).'</td><td>'.htmlspecialchars($row['errmsg'])."\n";
	}
	echo "</tr>\n";
}
echo "</table>";

UtilMisc::navigator($min, $min, $max_pp, $numrows);

$page->showFooter();
