<?php
/**
 * Released under MIT License
 * (c) 2007 - 2020 Heinrich Stamerjohanns
 *
 */
require_once "../include/IncFiles.php";
use Dmake\MacroDao;
use Dmake\Dao;
use Server\Config;
use Server\Page;
use Server\UtilMisc;

$page = new Page('Macro Sty usage');
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
    echo "Unknown type: ".htmlspecialchars($stage);
    exit;
}

?>

<script language="JavaScript">
function openPopup(url)
{
   var winpopup=open(url,"winpopup","toolbar,width=800,height=600,screenx=20,scrollbars,resizable=yes,screeny=100");
   winpopup.focus();
}
</script>

<?php
$macro = $page->getRequest()->getQueryParam('macro', '');
$styfilename = $page->getRequest()->getQueryParam('styfilename', '');

if (!empty($macro)) {
	$macro_mode = TRUE;
	$col = [1 => 'Macro', 'Filename', 'ltx'];
	$field = [1 => 'macro', 'styfilename'];
	$headline = '<h3>Files that define macro <em>'.htmlspecialchars($macro).'</em></h3>';
} elseif (!empty($styfilename)) {
	// if filename does not contain suffix, try to find one
	if (strpos($styfilename, '.') === FALSE) {
		$clsfile = 'sty/'.$styfilename.'.cls';
		if (is_readable(STYDIR.'/'.$clsfile)) {
			$styfilename .= '.cls';
		} else {
			$styfile = 'sty/'.$styfilename.'.sty';
			if (is_readable(STYDIR.'/'.$styfile)) {
				$styfilename .= '.sty';
			} else {
				echo "Cannot find ".htmlspecialchars($styfilename).".";
			}
		}
	}
	$macro_mode = FALSE;
	$col = [1 => 'Macro', 'Filename', 'ltx'];
	$field = [1 => 'styfilename', 'macro'];
	$headline = '<h3>Macros that are defined in file <em><a href="sty/'.htmlspecialchars($styfilename).'">'.htmlspecialchars($styfilename).'</em></h3>';
} else {
	echo "Unknown parameter!";
	exit;
}

$max_pp = $cfg->db->perPage;

$numrows = MacroDao::getCountField2($field[2], $field[1], ${$field[1]});

$rows = MacroDao::getField2($field[2], $field[1], ${$field[1]}, $min, $max_pp);

echo $headline;

if (!empty($set)) {
?>
<h3 style="margin-bottom:15px"><em><?=htmlspecialchars($set) ?></em></h3>
<?php
}

echo '<table border="1">';

echo '<tr>';
foreach ($col as $coltitle) {
	echo '<th>'.$coltitle.'</th>';
}
echo '</tr>';

$count = $min;

foreach ($rows as $row) {
	$count++;
	echo "<tr>\n";
	if ($macro_mode) {
		$ltxfile = $row[$field[2]];
	} else {
		$ltxfile = ${$field[1]};
	}

	$ltxlink = UtilMisc::getLtxmlLink($ltxfile);

	if ($macro_mode) {
		echo '<td align="right">'.${$field[1]}.'</td>';
        // local style files can be
		if (is_file(STYDIR . '/' . $row[$field[2]])) {
			echo '<td><a href="sty/'.$row[$field[2]].'">'.$row[$field[2]].'</a></td>';
		} else {
			echo '<td>'.$row[$field[2]].'</td>';
		}
		echo '<td>'.$ltxlink.'</td>';
	} else {
		echo '<td align="right">'.$row[$field[2]].'</td>';
		if (is_file(STYDIR . '/' . $row[$field[2]])) {
			echo '<td><a href="sty/'.${$field[1]}.'">'.${$field[1]}.'</a></td>';
		} else {
			echo '<td>'.${$field[1]}.'</td>';
		}

		echo '<td>'.$ltxlink.'</td>';
	}
	echo "</tr>\n";
}
echo "</table>";

UtilMisc::navigator($min, $min, $max_pp, $numrows);

$page->showFooter();
