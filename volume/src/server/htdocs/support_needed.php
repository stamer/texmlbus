<?php
/**
 * Released under MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 */
require_once "../include/IncFiles.php";
use Dmake\Dao;
use Dmake\MmfileDao;
use Dmake\UtilStylefile;
use Dmake\UtilBindingFile;

use Server\Config;
use Server\Page;
use Server\UtilMisc;


$page = new Page('Macro usage');
$page->showHeader('stylefiles');

$cfg = Config::getConfig();
$dao = Dao::getInstance();

$set = $page->getRequest()->getQueryParam('set', '');

if (!empty($set)) {
?>
<h3 style="margin-bottom:15px"><em><?=htmlspecialchars($set) ?></em></h3>
<?php
}
?>

<a href="support_needed.php?choice=M&amp;set=<?=htmlspecialchars($set) ?>">Macro support</a>
&nbsp;&nbsp;
<a href="support_needed.php?choice=S&amp;set=<?=htmlspecialchars($set) ?>">Stylefile support</a>
&nbsp;&nbsp;
<a href="support_needed.php?choice=A&amp;set=<?=htmlspecialchars($set) ?>">Macros per Stylefile support</a>

<?php

$min = $page->getRequest()->getQueryParam('min', 0);
// make sure no negatives
$min = max(0, (int) $min);

$max_pp = $cfg->db->perPage;

$choice = $page->getRequest()->getQueryParam('choice', 'A');

switch ($choice) {

case 'A':

    $numrows = MMfileDao::getCountA($set);

    $rows = MmfileDao::getA($set, $min, $max_pp);

	echo '<h3>Macros and Stylefiles that need most support</h3>';
	echo '<table border="1">';

	echo '<tr><th>No.</th><th>Count<br />documents</th><th>Macro</th><th>File</th><th>Sim.</th><th title="Currently this only shows whether a .ltxml file is present.">LTX File</th></tr>';

	$count = $min;
	foreach ($rows as $row) {
		$count++;
		echo "<tr>\n";
		echo '<td align="right">'.$count.'</td><td><a href="mmfile_detail.php?set='.htmlspecialchars($set).'&amp;macro='.htmlspecialchars($row['macro']).'&amp;styfilename='.htmlspecialchars($row['styfilename']).'">'.$row['num'].'</a></td>';
		echo '<td><a href="macro_sty_detail.php?set='.htmlspecialchars($set).'&amp;macro='.htmlspecialchars($row['macro']).'">'.htmlspecialchars($row['macro']).'</a></td>'."\n";
		echo '<td><a href="macro_sty_detail.php?set='.htmlspecialchars($set).'&amp;styfilename='.htmlspecialchars($row['styfilename']).'">'.htmlspecialchars($row['styfilename']).'</a></td>'."\n";
		echo '<td><a href="sty_sim.php?set='.htmlspecialchars($set).'&amp;filename='.htmlspecialchars($row['styfilename']).'">'.htmlspecialchars($row['styfilename']).'</a></td>'."\n";

		$ltxfile = UtilMisc::getLtxmlLink($row['styfilename']);

		echo '<td>'.$ltxfile.'</td>';
		echo "</tr>\n";
	}
	echo "</table>";
	break;

case 'S':
    $numrows = MmfileDao::getCountS($set);
    $rows = MmfileDao::getS($set, $min, $max_pp);

	echo '<h3>Macro usage</h3>';
	echo '<table border="1">';

	echo '<tr><th>No.</th><th>Count<br />documents</th><th>Count<br />occurrences</th><th>File</th><th>LTX File</th></tr>';

	$count = $min;
	foreach ($rows as $row) {
		$count++;
		echo "<tr>\n";
		echo '<td align="right"><a href="macro_sty_detail.php?styfilename='.htmlspecialchars($row['styfilename']).'">'.$count.'</a></td>'.PHP_EOL;
		echo '<td><a href="/mmfile_detail.php?set='.htmlspecialchars($set).'&styfilename='.htmlspecialchars($row['styfilename']).'">'.$row['numdoc'].'</a></td><td>'.$row['num'].'</td>';
		echo '<td><a href="macro_sty_detail.php?styfilename='.htmlspecialchars($row['styfilename']).'">'.htmlspecialchars($row['styfilename']).'</a></td>'."\n";

		$ltxfile = UtilMisc::getLtxmlLink($row['styfilename']);

		echo '<td>'.$ltxfile.'</td>';

		echo "</tr>\n";
	}
	echo "</table>";
	break;

case 'M':
    $numrows = MmfileDao::getCountM($set);
    $rows = MmfileDao::getM($set, $min, $max_pp);

	echo '<h3>Macro usage</h3>';
	echo '<table border="1">';

	echo '<tr><th>No.</th><th>Count<br />documents</th><th> Count<br />occurrences<th>Macro</th></tr>';

	$count = $min;
	foreach ($rows as $row) {
		$count++;
		echo "<tr>\n";
		echo '<td align="right"><a href="mmfile_detail.php?macro='.htmlspecialchars($row['macro']).'">'.$count.'</a></td><td>'.$row['numdoc'].'</td><td>'.$row['num'].'</td>';
		echo '<td><a href="macro_sty_detail.php?macro='.htmlspecialchars($row['macro']).'">'.htmlspecialchars($row['macro']).'</a></td>'."\n";
		echo "</tr>\n";
	}
	echo "</table>";
	break;
}

UtilMisc::navigator($min, $min, $max_pp, $numrows);

$page->showFooter();
