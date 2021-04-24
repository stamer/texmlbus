<?php
/**
 * Released under MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 */
require_once "../include/IncFiles.php";
use Dmake\MacroDao;
use Server\Config;
use Server\Page;
use Server\UtilMisc;

$page = new Page('Top style files that need some work');
$page->showHeader('stylefiles');

$cfg = Config::getConfig();

$min = $page->getRequest()->getQueryParam('min', 0);
// make sure no negatives
$min = max(0, (int) $min);

$max_pp = $cfg->db->perPage;

$numrows = MacroDao::getCountTopStylefiles();
$rows = MacroDao::getTopStylefiles();

$mm = array();


echo '<h3>Top stylefiles that need some work</h3>';
echo '<table border="1">';

echo '<tr>';
echo '<th>No.</th>';
echo '<th>Filename</th>';
echo '<th>Num</th>';
echo '<th>Weight</th>';
echo '</tr>';

$count = 0;

foreach ($rows as $row) {

	$filename = $row['styfilename'];
	if ($filename != '') {
		$count++;
		echo '<tr>';
		echo '<td align="right">'.$count."</td>\n";
		echo '<td><a href="macro_sty_detail.php?filename='.htmlspecialchars($filename).'">'.$filename.'</a></td>';
		echo '<td align="right">'.$row['num'].'</td>';
		echo '<td align="right">'.$row['weight'].'</td>';
		echo "</tr>\n";
	}
}

echo "</table>";

$max_pp = $cfg->db->perPage;
UtilMisc::navigator($min, $min, $max_pp, $numrows);

$page->showFooter();
