<?php
/**
 * Released under MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 */
require_once "../include/IncFiles.php";
use Dmake\MmfileDao;
use Server\UtilMisc;

$cfg = Config::getConfig();
$dao = Dao::getInstance();

$page = new Page();
$macro = $page->getRequest()->getQueryParam('macro', '');
$sty = $page->getRequest()->getQueryParam('styfilename', '');
$set = $page->getRequest()->getQueryParam('set', '');

$IS_CRAWLER = false;
$page->showHeader('index');
$page->setTitle('Files that use macro '.htmlspecialchars($macro));

$min = $page->getRequest()->getQueryParam('min', 0);
// make sure no negatives
$min = max(0, (int) $min);

$column = array();
$max_pp = $cfg->db->perPage;

$numrows = MmfileDao::getCount($set, $macro, $sty);


$sqllstr = '';
foreach ($column as $field) {
	$sqlstr .= $field['sql'].",\n";
}

$rows = MmfileDao::getFilenames($set, $macro, $sty, $min, $max_pp);

if (!empty($set)) {
?>
<h3 style="margin-bottom:15px"><em><?=htmlspecialchars($set) ?></em></h3>
<?php
}

if ($macro != '' && $sty != '') {
    $str = 'that use macro <em>'.htmlspecialchars($macro).'</em> in file <em>'.htmlspecialchars($sty).'</em>';
} elseif ($macro != '') {
    $str = 'that use macro <em>'.htmlspecialchars($macro).'</em>';
} elseif ($sty != '') {
    $str = 'that use file <em>'.htmlspecialchars($sty).'</em>';
} else {
    $str = '';
}
?>
<h3>List of files <?=$str ?></h3>
<table border="1">
<tr>
	<th>No.</th>
	<th>Filename</th>

</tr>
<?php
$count = 0;
foreach ($rows as $row) {

	echo "<tr>\n";
	$count++;
	$no = $count + $min;
	echo '<td align="right">'.$no."</td>\n";
	$filename = str_replace('/arXMLiv/tars_untarred', '', $row['filename']);
    $directory = 'files'.$filename.'/';

	if (!$IS_CRAWLER) {
		echo '<td><a href="'.$directory.'">'.$filename."</a></td>\n";
	} else {
		echo '<td>'.$filename."</td>\n";
	}

	foreach ($column as $field) {
		echo '<td align="'.$field['align'].'">'.$row[$field['sql']].'</td>';
	}
	echo "</tr>\n";
}

echo "</table>";

UtilMisc::navigator($min, $min, $max_pp, $numrows);

$page->showFooter();
