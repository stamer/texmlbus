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

$cfg = Config::getConfig();
$dao = Dao::getInstance();

$page = new Page();
$page->showHeader('index');

$min = $page->getRequest()->getQueryParam('min', 0);
// make sure no negatives
$min = max(0, (int) $min);

$stage = $page->getRequest()->getQueryParam('stage', 'xml');
$set = $page->getRequest()->getQueryParam('set', '');
$retval = $page->getRequest()->getQueryParam('retval', 'fatal_error');

$page->setTitle('Top messages for ' . htmlspecialchars($retval));

$stages = array_keys($cfg->stages);

if (in_array($stage, $stages)) {
    $joinTable = $cfg->stages[$stage]->dbTable;
    $tableTitle = $cfg->stages[$stage]->tableTitle;
} else {
    echo "Unknown stage: ".htmlspecialchars($stage);
    exit;
}

$max_pp = $cfg->db->perPage;

$numrows = RetvalDao::getCountErrMsgByRetval($retval, $joinTable, $set);
$rows = RetValDao::getErrMsgByRetval($retval, $joinTable, $set);

$arr = array();

foreach ($rows as $row) {
	$str = preg_replace('/\S+papers.*/', '', $row['errmsg']);

	// Try to munge some specific error messages...
	$str = preg_replace('/aTeXML::Tokens=ARRAY.*/', 'aTeXML::Tokens=ARRAY', $str);
	$str = preg_replace('/Undefined subroutine &LaTeXML::Package::Pool::refStepID called at.*/', 'Undefined subroutine &LaTeXML::Package::Pool::refStepID called at', $str);
	if (!isset($arr[$str])) {
		$arr[$str] = 1;
	} else {
		$arr[$str]++;
	}
}

$numrows = count($arr);

arsort ($arr);

if (!empty($set)) {
?>
<h3 style="margin-bottom:15px"><em><?=htmlspecialchars($set) ?></em></h3>
<?php
}
?>
<h4>Top <?=$retval ?>s on stage <?=htmlspecialchars($tableTitle) ?></h4>
<table border="1">
<tr>
	<th>No.</th>
    <th>Count</th>
    <th>Error Message</th>
</tr>
<?php
$count = 0;
$max_pp = $cfg->db->perPage;

foreach ($arr as $str=>$num) {
	// We need to do the page handling in such a stupid way,
	// because we need to parse the full result.
	$count++;
	if ($count > $min + $max_pp) {
		break;
	}
	if ($count > $min) {
		echo "<tr>\n";
		echo '<td align="right">'.$count."</td>\n";
		echo '<td align="right">'.$num."</td>\n";
		$str = substr($str, 0, 80);
		$link = 'fatal_detail.php?set='.urlencode($set).'&stage='.urlencode($stage).'&amp;errmsg='.urlencode($str);
		echo '<td><a href="'.$link.'">'.htmlspecialchars($str).'</a>'."</td>\n";
		echo "</tr>\n";
	}
}

echo "</table>";

UtilMisc::navigator($min, $min, $max_pp, $numrows);

$page->showFooter();
