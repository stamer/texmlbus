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
use Server\UtilMisc;

$IS_CRAWLER = false;

$cfg = Config::getConfig();
$dao = Dao::getInstance();

$page = new Page('Top missing macros');
$page->showHeader('stylefiles');

$stage = $page->getRequest()->getQueryParam('stage', 'xml');
$set = $page->getRequest()->getQueryParam('set', '');

$stages = array_keys($cfg->stages);

if (in_array($stage, $stages)) {
    $joinTable = $cfg->stages[$stage]->dbTable;
    $tableTitle = $cfg->stages[$stage]->tableTitle;
} else {
    echo "Unknown type: ".htmlspecialchars($stage);
    exit;
}

if (!empty($set)) {
?>
<h3 style="margin-bottom:15px"><em><?=htmlspecialchars($set) ?></em></h3>
<?php
}
?>
<h4>Top missing macros <?=htmlspecialchars($tableTitle) ?></h4>
<?php

$min = $page->getRequest()->getQueryParam('min', 0);
// make sure no negatives
$min = max(0, (int) $min);

$max_pp = $cfg->db->perPage;

$rows = RetvalDao::getMissingMacros($joinTable, $set);

$mm = array();

foreach ($rows as $row) {
	$str = $row['missing_macros'];

	preg_match('/^(\[)(.*?)(\]).*$/', $str, $matches);

    // @TODO
    if (isset($matches[2])) {
        $macros = preg_split('/,\s*/', $matches[2]);

        foreach ($macros as $macro) {
            $macro = substr($macro, 0, 60);
            if (!isset($mm[$macro])) {
                $mm[$macro] = 1;
            } else {
                $mm[$macro]++;
            }
        }
    }
}

$numrows = count($mm);

arsort($mm, SORT_NUMERIC);

echo '<table border="1">';

echo '<tr>';
echo '<th>No.</th>';
echo '<th>Macro (and used in files...)</th>';
echo '<th>Count</th>';
echo '<th>Defined in</th>';
echo '</tr>';

$max_pp = $cfg->db->perPage;
$count = 0;
foreach ($mm as $macro=>$num) {
    // We need to do the page handling in such a stupid way,
    // because we need to parse the full result.
	$count++;
	if ($count > $min + $max_pp) {
		break;
	}
	if ($count > $min) {
		echo '<tr>';
		echo '<td align="right">'.$count."</td>\n";
		if (!$IS_CRAWLER) {
			echo '<td><a href="macro_tex_detail.php?stage='.$stage.'&amp;macro='.htmlspecialchars($macro).'">'.$macro.'</a></td>';
		} else {
			echo '<td>'.$macro.'</td>';
		}
		echo '<td align="right">'.$num.'</td>';
		if (!$IS_CRAWLER) {
			echo '<td><a href="macro_sty_detail.php?stage='.$stage.'&amp;macro='.htmlspecialchars($macro).'">defined in files...</a></td>';
		} else {
			echo '<td>defined in files...</td>';
		}
		echo "</tr>\n";
	}
}

echo "</table>";

UtilMisc::navigator($min, $min, $max_pp, $numrows);

$page->showFooter();

