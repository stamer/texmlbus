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

?>
<h2>Current statistics of make process</h2>

<?php

$min = $page->getRequest()->getQueryParam('min', 0);
// make sure no negatives
$min = max(0, (int) $min);

$max_pp = $cfg->db->perPage;

$numrows = ErrDetEntry::getCountErrTypeByErrClass('Error');
$rows = ErrDetEntry::getErrTypeByErrClass('Error', $min, $max_pp)
?>

<h3>Top Error Types</h3>
<table border="1">
<tr>
	<th>No.</th>
    <th>Count</th>
	<th>Type</th>
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
	if ($count > $min) {
		echo "<tr>\n";
		echo '<td align="right">'.$count."</td>\n";
		echo '<td align="right">'.$row['num']."</td>\n";
		$link = 'errtype_detail.php?errtype='.urlencode($row['errtype']);
		echo '<td><a href="'.$link.'">'.$row['errtype'].'</a>'."</td>\n";
		echo "</tr>\n";
	}
}

echo "</table>";

UtilMisc::navigator($min, $min, $max_pp, $numrows);

$page->showFooter();
