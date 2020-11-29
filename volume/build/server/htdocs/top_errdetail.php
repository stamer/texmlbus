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

$page = new Page('Build System');
$page->showHeader('index');

$cfg = Config::getConfig();

$min = $page->getRequest()->getQueryParam('min', 0);
// make sure no negatives
$min = max(0, (int) $min);

$max_pp = $cfg->db->perPage;

$numrows = ErrDetEntry::getCountByErrType('Error');
$rows = ErrDetEntry::getByErrType('Error', $min, $max_pp);

?>

<h3>Top Detail Errors</h3>
<table border="1">
<tr>
	<th>No.</th>
    <th>Count</th>
	<th>Type</th>
    <th>Error Message</th>
</tr>
<?php
$count = $min;

foreach ($rows as $row) {
	// We need to do the page handling in such a stupid way,
	// because we need to parse the full result.
	$count++;

	if ($count > $min + $max_pp) {
		break;
	}
	$line = ErrDetEntry::getErrMsgByMd5($row['md5_errmsg']);

	if ($count > $min) {
		echo "<tr>\n";
		echo '<td align="right">'.$count."</td>\n";
		echo '<td align="right">'.$row['num']."</td>\n";
		echo '<td align="right">'.$row['errtype']."</td>\n";
		$str = substr($line['errmsg'], 0, 80);
		$link = 'error_detail.php?errmsg='.urlencode($row['md5_errmsg']);
		echo '<td><a href="'.$link.'">'.htmlspecialchars($str).'</a>'."</td>\n";
		echo "</tr>\n";
	}
}

echo "</table>";

UtilMisc::navigator($min, $min, $max_pp, $numrows);
$page->showFooter();
