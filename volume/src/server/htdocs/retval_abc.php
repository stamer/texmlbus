<?php
/**
 * Released under MIT License
 * (c) 2007 - 2020 Heinrich Stamerjohanns
 *
 */
require_once "../include/IncFiles.php";

use Dmake\Dao;
use Dmake\RetvalDao;
use Dmake\UtilStage;
use Dmake\SharedMem;
use Server\Config;
use Server\Page;
use Server\UtilMisc;
use Server\View;

$page = new Page('Alphabetic list');
$page->addScript('/js/deleteDocument.js');
$page->addScript('/js/sseUpdateColumn.js');
$page->addScript('/js/pullDocument.js');
$page->showHeader('retval_abc');

$cfg = Config::getConfig();
$dao = Dao::getInstance();

$min = $page->getRequest()->getQueryParam('min', 0);
// make sure no negatives
$min = max(0, (int) $min);

$set = $page->getRequest()->getQueryParam('set', '');

$requestDir = $page->getRequest()->getQueryParam('dir', 'ASC');
// possible SqlInjection, assign explicitly
if ($requestDir == 'desc') {
    $sqlSortBy = 'DESC';
} else {
    $sqlSortBy = 'ASC';
}

// possible SqlInjection, assign explicitly
$requestSort = $page->getRequest()->getQueryParam('sort', 's.filename');
if ($requestSort == 'date') {
    $sqlOrderBy = 's.date_modified';
} else {
    $sqlOrderBy = 's.filename';
}

$targets = array_keys($cfg->stages);

// build Urls
parse_str($page->getRequest()->getQueryParam('QUERY_STRING', ''), $query_data);

$query_data['sort'] = 'date';
$query_data['dir'] = 'asc';
$phpSelf = $page->getRequest()->getQueryParam('PHP_SELF');
$urlSortDateAsc = $phpSelf . '?' . http_build_query($query_data, '', '&amp;');

$query_data['dir'] = 'desc';
$urlSortDateDesc = $phpSelf . '?' . http_build_query($query_data, '', '&amp;');

$query_data['sort'] = 'name';
$query_data['dir'] = 'asc';
$urlSortNameAsc = $phpSelf . '?' . http_build_query($query_data, '', '&amp;');

$query_data['dir'] = 'desc';
$urlSortNameDesc = $phpSelf . '?' . http_build_query($query_data, '', '&amp;');

if (!empty($set)) {
?>
<h3 style="margin-bottom:15px"><em><?=htmlspecialchars($set) ?></em></h3>
<?php
}
?>

<h4>Alphabetic list of documents <?=$page->info('retval_abc') ?></h4>
<script>sseUpdateColumn()</script>

<?php


$stages = array_keys($cfg->stages);

$stat = array();

foreach ($stages as $stage) {

    $joinTable = $cfg->stages[$stage]->dbTable;
    $tableTitle = $cfg->stages[$stage]->tableTitle;
    if (!empty($cfg->stages[$stage]->destFile)) {
        $cfgDestFile[$stage] = $cfg->stages[$stage]->destFile;
    } else {
        $cfgDestFile[$stage] = '';
    }
    $cfgStdoutLog[$stage] = $cfg->stages[$stage]->stdoutLog;
    $cfgStderrLog[$stage] = $cfg->stages[$stage]->stderrLog;

    $ext_query = '';

    if ($set != '') {
        $ext_query = '
            AND s.`set` = :set';
    }

    $max_pp = $cfg->db->perPage;

    $numRows = RetvalDao::getCount($joinTable, $set);
    $rows = RetvalDao::getEntries($stage, $joinTable, $set, $sqlOrderBy, $sqlSortBy, $min, $max_pp);

    foreach ($rows as $row) {
        // will be set several times, not a problem...
        $stat[$row['id']]['all']['s_date_modified'] = $row['s_date_modified'];
        $stat[$row['id']]['all']['filename'] = $row['filename'];
        $stat[$row['id']]['all']['sourcefile'] = $row['sourcefile'];
        $stat[$row['id']]['all']['project_id'] = $row['project_id'];
        $stat[$row['id']][$stage]['wq_priority'] = $row['wq_priority'];
        $stat[$row['id']][$stage]['wq_action'] = $row['wq_action'];
        $stat[$row['id']][$stage]['retval'] = $row['retval'];
        $stat[$row['id']][$stage]['prev_retval'] = $row['prev_retval'];
        //$stat[$row['id']][$stage]['num_error'] = $row['num_error'];
        //$stat[$row['id']][$stage]['num_warning'] = $row['num_warning'];
        $stat[$row['id']][$stage]['date_modified'] = $row['date_modified'];
    }
}
?>
<table border="0">
<tr>
    <th>No.</th>
    <th>Date&nbsp;&nbsp;<a title="Sort by ascending date" href="<?=$urlSortDateAsc ?>">&#9662;</a><a title="Sort by descending date" href="<?=$urlSortDateDesc ?>">&#9652;</a></th>
    <th>Directory&nbsp;&nbsp;<a title="Sort by ascending name" href="<?=$urlSortNameAsc ?>">&#9662;</a><a title="Sort by descending name" href="<?=$urlSortNameDesc ?>">&#9652;</a></th>
<?php
foreach ($stages as $stage) {
    $target = $cfg->stages[$stage]->target;
    echo '<th style="min-width:138px">';
    echo '<small>'.$stage.'</small><br />';
    $ids = array_keys($stat);
    echo '<a style="font-size: 60%" href="/#" onclick="javascript:rerunByIds([' . implode(',', $ids) . '],\'' . $stage . '\', \'' . $target.'\'); return false">queue</a>'.PHP_EOL;
    if ($set != '') {
        echo '&nbsp;&nbsp;<a style="font-size: 60%" href="/#" onclick="javascript:rerunBySet(\'' . $set . '\', \'' . $stage . '\', \'' . $target . '\'); return false">queue set</a>' . PHP_EOL;
    }
    echo '</th>';
}
?>

</tr>
<?php
$count = 0;

foreach ($stat as $id => $entry) {

    // sources are hostgroup independent
    $directory = 'files/'.$entry['all']['filename'].'/';
    if (!preg_match('/\.tex$/', $entry['all']['sourcefile'])) {
        $sourcefile = $entry['all']['sourcefile'].'.tex';
        $sourcefileLink = $directory.$entry['all']['sourcefile'].'.tex';
    } else {
        $sourcefile = $entry['all']['sourcefile'];
        $sourcefileLink = $directory.$entry['all']['sourcefile'];
    }

    $prefix = basename($entry['all']['sourcefile'], '.tex');

    echo "<tr>\n";
    $count++;
    $no = $count + $min;
    if (isset($entry['all']['s_date_modified'])) {
        $date_modified = $entry['all']['s_date_modified'];
    } else {
        $date_modified = '';
    }
    if (isset($entry['all']['filename'])) {
        $filename = $entry['all']['filename'];
    } else {
        $filename = '';
    }

    echo '<td style="position: relative" align="right" rowspan="2"><a name="'.$no.'">'.$no.'</a>';
    if (!empty($entry['all']['project_id'])) {
        echo '<button type="button" class="btn btn-overleaf abc_pull" onclick="pullDocument(this, ' . $id . ')">';
        echo '<img src="/css/img/overleaf16.svg" />';
        echo '<span></span></button>';
    }
    echo '<button type="button" class="btn btn-danger delete abc_delete" onclick="deleteDocument(this, ' . $id . ')">';
    echo '<i class="fas fa-trash"></i>';
    echo '<span></span></button>';
    echo '</td>' . PHP_EOL;
    echo View::renderDateCell($id, $date_modified);
    echo '<td rowspan="1"><a href="'.$directory.'">'.$filename.'</a></td>' . PHP_EOL;

    foreach ($stages as $stage) {
        $directory = UtilStage::getSourceDir('files', $entry['all']['filename'], $cfg->stages[$stage]->hostGroup) . '/';
        //  %MAINFILEPREFIX%, will be replaced by basename of maintexfile
        $destFile = str_replace('%MAINFILEPREFIX%', $prefix, $cfgDestFile[$stage]);
        $stdoutLog = str_replace('%MAINFILEPREFIX%', $prefix, $cfgStdoutLog[$stage]);
        $stderrLog = str_replace('%MAINFILEPREFIX%', $prefix, $cfgStderrLog[$stage]);

        if ($destFile != '') {
            $destFileLink = $directory.$destFile;
        }
        $stdoutFileLink = $directory.$stdoutLog;
        $stderrFileLink = $directory.$stderrLog;

        $target = $cfg->stages[$stage]->target;

        if ($entry[$stage]['wq_priority']) {
            $queued = 'queued';
        } else {
            $queued = '';
        }
        if ($entry[$stage]['wq_action'] === $target) {
            if ($entry[$stage]['wq_priority']) {
                $queued = 'queued';
            } else {
                $queued = 'running';
            }
        } else {
            $queued = '';
        }


        if (isset($entry[$stage]['retval'])) {
            $retval = $entry[$stage]['retval'];
        } else {
            $retval = 'unknown';
        }
        $date_modified = $entry[$stage]['date_modified'] ?? '';

        echo View::renderRetvalCell(
            $retval,
            $stderrFileLink,
            $destFileLink,
            $id,
            $stage,
            $target,
            $date_modified,
            $queued
        );
    }
    echo '</tr>'.PHP_EOL;

    // Line below for prev_retval for given stage
    echo '<tr style="height:6px">'.PHP_EOL;
    echo '<td style="text-align:right; font-size: 11px">previous</td>'.PHP_EOL;
    foreach ($stages as $stage) {
        if (isset($entry[$stage]['prev_retval'])) {
            $prevRetval = $entry[$stage]['prev_retval'];
        } else {
            $prevRetval = 'unknown';
        }
        echo View::renderPrevRetvalCell($prevRetval, $id, $stage);
    }
    echo '</tr>'.PHP_EOL;
}
?>
</table>
<?php

UtilMisc::navigator($min, $min, $max_pp, $numRows);

$page->showFooter();
