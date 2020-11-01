<?php
/**
 * Released under MIT License
 * (c) 2007 - 2020 Heinrich Stamerjohanns
 *
 */
require_once "../include/IncFiles.php";
use Dmake\StatEntry;
use Dmake\WorkqueueEntry;

use Server\Config;
use Server\Page;
use Server\UtilMisc;

$page = new Page('Current queue entries');
$page->addScript('/js/dequeueDocument.js');
$page->showHeader('general');

$cfg = Config::getConfig();

$min = $page->getRequest()->getQueryParam('min', 0);
// make sure no negatives
$min = max(0, (int) $min);
$set = $page->getRequest()->getQueryParam('set', '');

$requestDir = $page->getRequest()->getQueryParam('dir', 'ASC');
// possible SqlInjection, set $sqlSortBy explicitly
if ($requestDir == 'desc') {
    $sqlSortBy = 'DESC';
} else {
    $sqlSortBy = 'ASC';
}

// possible SqlInjection, set $sqlOrderBy explicitly
$requestSort = $page->getRequest()->getQueryParam('sort', 's.filename');
if ($requestSort == 'date') {
    $sqlOrderBy = 's.date_modified';
} else {
    $sqlOrderBy = 's.filename';
}

$stages = array_keys($cfg->stages);

// build Urls
parse_str($_SERVER['QUERY_STRING'], $query_data);

$query_data['sort'] = 'date';
$query_data['dir'] = 'asc';
$urlSortDateAsc = $_SERVER['SCRIPT_NAME'].'?'.http_build_query($query_data, '', '&amp;');

$query_data['dir'] = 'desc';
$urlSortDateDesc = $_SERVER['SCRIPT_NAME'].'?'.http_build_query($query_data, '', '&amp;');

$query_data['sort'] = 'name';
$query_data['dir'] = 'asc';
$urlSortNameAsc = $_SERVER['SCRIPT_NAME'].'?'.http_build_query($query_data, '', '&amp;');

$query_data['dir'] = 'desc';
$urlSortNameDesc = $_SERVER['SCRIPT_NAME'].'?'.http_build_query($query_data, '', '&amp;');


if (!empty($set)) {
?>
<h3 style="margin-bottom:15px"><em><?=htmlspecialchars($set) ?></em></h3>
<?php
}
?>
<h4>Current queue entries <?=$page->info('manageQueue') ?></h4>

<?php

$max_pp = $cfg->db->perPage;

$stages = array_keys($cfg->stages);

foreach ($stages as $stage) {
    if (in_array($stage, $stages)) {
        $joinTable = $cfg->stages[$stage]->dbTable;
        $tableTitle = $cfg->stages[$stage]->tableTitle;
        if (!empty($cfg->stages[$stage]->destFile)) {
            $cfgDestFile[$stage] = $cfg->stages[$stage]->destFile;
        } else {
            $cfgDestFile[$stage] = '';
        }
        $cfgStdoutLog[$stage] = $cfg->stages[$stage]->stdoutLog;
        $cfgStderrLog[$stage] = $cfg->stages[$stage]->stderrLog;
    } else {
        echo "Unknown stage: " . htmlspecialchars($stage);
        exit;
    }
}

$numrows = WorkqueueEntry::getQueuedEntries();

$stat = StatEntry::wqGetNextEntries('', 20, false);

?>
<table border="1">
<tr>
	<th style="min-width:70px">No.</th>
    <th>Date&nbsp;queued&nbsp;<a title="Sort by ascending date" href="<?=$urlSortDateAsc ?>">&#9662;</a><a title="Sort by descending date" href="<?=$urlSortDateDesc ?>">&#9652;</a></th>
    <th>Directory&nbsp;&nbsp;<a title="Sort by ascending name" href="<?=$urlSortNameAsc ?>">&#9662;</a><a title="Sort by descending name" href="<?=$urlSortNameDesc ?>">&#9652;</a></th>
<?php
	echo '<th>Stage</th>';
?>

</tr>
<?php
$count = 0;
foreach ($stat as $wq_id => $entry) {
    $stage = $entry->getWqStage();

    $directory = 'files/'.$entry->getFilename().'/';
    if (!preg_match('/\.tex$/', $entry->getSourcefile())) {
        $sourcefile = $entry->getSourcefile().'.tex';
        $sourcefileLink = $directory.$entry->getSourcefile().'.tex';
    } else {
        $sourcefile = $entry->getSourcefile();
        $sourcefileLink = $directory.$entry->getSourcefile();
    }

    $prefix = basename($entry->getSourcefile(), '.tex');


	echo "<tr>\n";
	$count++;
	$no = $count + $min;
	if (!empty($entry->getDateModified())) {
		$date_modified = $entry->getDateModified();
	} else {
		$date_modified = '';
	}
	if (!empty($entry->getFilename())) {
		$filename = $entry->getFilename();
	} else {
		$filename = '';
	}

	echo '<td align="right" rowspan="1">'.$no;
    echo '<button type="button" class="btn btn-warning warning queue_warning" onclick="dequeueDocument(this, ' . $entry->getId() . ', \'' . $stage .'\')">';
    echo '<i class="fas fa-ban" title="dequeue document"></i>';
    echo '<span></span></button>';
    echo '</td>' . PHP_EOL;
	echo '<td rowspan="1">'.$date_modified."</td>\n";
	echo '<td rowspan="1"><a href="'.$directory.'">'.$filename."</a></td>\n";
    echo '<td rowspan="1">'.$stage."</td>\n";


    //  %MAINFILEPREFIX%, will be replaced by basename of maintexfile
    $destFile = str_replace('%MAINFILEPREFIX%', $prefix, $cfgDestFile[$stage]);
    $stdoutLog = str_replace('%MAINFILEPREFIX%', $prefix, $cfgStdoutLog[$stage]);
    $stderrLog = str_replace('%MAINFILEPREFIX%', $prefix, $cfgStderrLog[$stage]);

    if ($destFile != '') {
        $destFileLink = $directory.$destFile;
    }
    $stdoutFileLink = $directory.$stdoutLog;
    $stderrFileLink = $directory.$stderrLog;

    echo '</tr>'.PHP_EOL;
}
?>
</table>
<?php

UtilMisc::navigator($min, $min, $max_pp, $numrows);

$page->showFooter();
