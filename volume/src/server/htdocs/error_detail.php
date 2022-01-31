<?php
/**
 * Released under MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 */
require_once "../include/IncFiles.php";
use Dmake\ErrDetEntry;
use Server\Config;
use Server\Page;
use Server\UtilMisc;

$page = new Page('Tex BUild System');
$page->showHeader('index');

$cfg = Config::getConfig();

$min = $page->getRequest()->getQueryParam('min', 0);
// make sure no negatives
$min = max(0, (int) $min);

$md5ErrMsg = $page->getRequest()->getQueryParam('errmsg', '');

if (empty($md5ErrMsg)) {
	echo "No error given!";
	exit;
}

$max_pp = $cfg->db->perPage;

$numrows = ErrDetEntry::getCountByMd5ErrMsg($md5ErrMsg);
$rows = ErrDetEntry::getByMd5ErrMsg($md5ErrMsg, $min, $max_pp);

$count = $min;
foreach ($rows as $row) {
    // we need to have the error message
    if ($count == $min) {
        echo '<h3>Files that contain this error: <em>'.htmlspecialchars(stripslashes($row['errmsg'])).'</em></h3>';
        echo '<table border="1">';

        echo '<tr><th>No.</th><th>Date</th><th>Files</th><th>Errmsg</th></tr>';
    }
	$count++;
	$directory = 'files/' . $row['filename'];
	echo "<tr>\n";
	echo '<td align="right">'.$count.'</td><td>'.$row['date_created'].'</td>';
    echo '<td><a href="'.$directory.'">'.htmlspecialchars($row['filename']).'</a></td><td>'.htmlspecialchars($row['errmsg'])."\n";
	echo "</tr>\n";
}
echo "</table>";

UtilMisc::navigator($min, $min, $max_pp, $numrows);

$page->showFooter();
