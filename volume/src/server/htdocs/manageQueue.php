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
$deferJs[] = 'selfUpdate(5000);';

$cfg = Config::getConfig();

$min = $page->getRequest()->getQueryParam('min', 0);
// make sure no negatives
$min = max(0, (int) $min);
$set = $page->getRequest()->getQueryParam('set', '');

$requestDir = $page->getRequest()->getQueryParam('dir', 'ASC');

$stages = array_keys($cfg->stages);

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
        $cfgStdOutLog[$stage] = $cfg->stages[$stage]->stdOutLog;
        $cfgStdErrLog[$stage] = $cfg->stages[$stage]->stdErrLog;
    } else {
        echo "Unknown stage: " . htmlspecialchars($stage);
        exit;
    }
}

$numrows = WorkqueueEntry::getNumQueuedEntries(true);

$stat = StatEntry::wqGetEntries('', 20);
?>
<table border="1">
<tr>
	<th style="min-width:70px">No.</th>
    <th>Document Id</th>
    <th>Date&nbsp;queued</th>
    <th>Directory</th>
	<th>Stage</th>
    <th>Action</th>
</tr>
<?php
$count = 0;
if (!$numrows) {
?>
<tr>
    <td colspan="6">No entries in queue.</td>
</tr>
<?php
}


foreach ($stat as $wq_id => $entry) {
    /** @var StatEntry $entry */
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
	if (!empty($entry->getWqDateModified())) {
		$date_modified = $entry->getWqDateModified();
	} else {
		$date_modified = '';
	}
	if (!empty($entry->getFilename())) {
		$filename = $entry->getFilename();
	} else {
		$filename = '';
	}

	$running = ($entry->getWqPriority() == 0 && $entry->getWqAction() !== 'none');

	echo '<td align="right" rowspan="1">'.$no;
    if ($running) {
        echo '<button type="button" class="btn btn-error error queue_error" onclick="dequeueDocument(this, ' . $entry->getId(
            ) . ', \'' . $entry->getWqStage() . '\')">';
        echo '<i class="fas fa-stop" title="stop conversion"></i>';
        echo '<span></span></button>';
    } else {
        echo '<button type="button" class="btn btn-warning warning queue_warning" onclick="dequeueDocument(this, ' . $entry->getId(
            ) . ', \'' . $entry->getWqStage() . '\')">';
        echo '<i class="fas fa-ban" title="dequeue document"></i>';
        echo '<span></span></button>';
    }
    echo '</td>' . PHP_EOL;
    echo '<td align="right" rowspan="1">'.$entry->getId()."</td>\n";
	echo '<td rowspan="1">'.$date_modified."</td>\n";
	echo '<td rowspan="1"><a href="'.$directory.'">'.$filename."</a></td>\n";
    echo '<td rowspan="1">'.$entry->getWqStage()."</td>\n";
    echo '<td rowspan="1">'.$entry->getWqAction();
    if ($running) {
        echo '<br /><em>running</em>';
    }
    echo "</td>\n";


    //  %MAINFILEPREFIX%, will be replaced by basename of maintexfile
    $destFile = str_replace('%MAINFILEPREFIX%', $prefix, $cfgDestFile[$stage]);
    $stdOutLog = str_replace('%MAINFILEPREFIX%', $prefix, $cfgStdOutLog[$stage]);
    $stdErrLog = str_replace('%MAINFILEPREFIX%', $prefix, $cfgStdErrLog[$stage]);

    if ($destFile != '') {
        $destFileLink = $directory.$destFile;
    }
    $stdOutFileLink = $directory.$stdOutLog;
    $stdErrFileLink = $directory.$stdErrLog;

    echo '</tr>'.PHP_EOL;
}
?>
</table>
<?php

UtilMisc::navigator($min, $min, $max_pp, $numrows);

$page->showFooter($deferJs);
