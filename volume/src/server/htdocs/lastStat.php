<?php
/**
 * Released under MIT License
 * (c) 2007 - 2020 Heinrich Stamerjohanns
 *
 */
require_once "../include/IncFiles.php";
use Dmake\RetvalDao;
use Dmake\StatEntry;
use Dmake\UtilSort;
use Dmake\UtilStage;
use Server\Config;
use Server\Page;
use Server\UtilMisc;

$page = new Page('Current Stats');
$page->showHeader('general');
$deferJs[] = 'selfUpdate(5000);';

$cfg = Config::getConfig();

$min = $page->getRequest()->getQueryParam('min', 0);
// make sure no negatives
$min = max(0, (int) $min);

$set = $page->getRequest()->getQueryParam('set', '');

$collapse = $page->getRequest()->getQueryParam('collapse', '');

$requestDir = $page->getRequest()->getQueryParam('dir', 'DESC');
// possible SqlInjection, explicitly set variable
if ($requestDir == 'asc') {
    $sqlSortBy = 'ASC';
} else {
    $sqlSortBy = 'DESC';
}

// possible SqlInjection, explicitly set variable directly
$requestSort = $page->getRequest()->getQueryParam('sort', 's.date_modified');
if ($requestSort == 'name') {
    $sqlOrderBy1 = 's.filename';
    $sqlOrderBy2 = 's.filename';
} else {
    $sqlOrderBy1 = 'wq.date_modified';
    $sqlOrderBy2 = 's.date_modified';
}

$stages = array_keys($cfg->stages);

// build Urls
parse_str($page->getRequest()->getQueryParam('QUERY_STRING', ''), $query_data);

$query_data['sort'] = 'date';
$query_data['dir'] = 'asc';
$phpSelf = $page->getRequest()->getQueryParam('PHP_SELF');
$urlSortDateAsc = $phpSelf .'?' . http_build_query($query_data, '', '&amp;');

$query_data['dir'] = 'desc';
$urlSortDateDesc = $phpSelf .'?' . http_build_query($query_data, '', '&amp;');

$query_data['sort'] = 'name';
$query_data['dir'] = 'asc';
$urlSortNameAsc = $phpSelf .'?' . http_build_query($query_data, '', '&amp;');

$query_data['dir'] = 'desc';
$urlSortNameDesc = $phpSelf .'?' . http_build_query($query_data, '', '&amp;');
?>

<h4>Current Statistics <?=$page->info('lastStat') ?></h4>
<a href="lastStat.php?collapse=1">Show document only once</a>&nbsp;&nbsp;&nbsp;<a href="lastStat.php">Show all entries</a>

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

$numrows = StatEntry::getCountLastStat($stage, $joinTable);

$rows = StatEntry::getLastStat($sqlOrderBy1, $sqlSortBy, $min, $max_pp);

$types = array();
foreach ($rows as $row) {
    if ($row['wq_prev_action'] != '' && $row['wq_prev_action'] != 'none') {
        $types[$row['wq_prev_action']][] = $row['id'];
    }
}

$stat = array();

foreach ($types as $stage => $ids) {
    $rows = RetvalDao::getByIds($ids, $stage, $sqlOrderBy2, $sqlSortBy, $min, $max_pp);

    foreach ($rows as $row) {
        if (!$collapse) {
            // each entry is shown
            $stat[] = $row;
        } else {
            // only newest entry of given document is shown
            if (empty($stat[$row['id']])
                || $stat[$row['id']['date_modified']] < $row['data_modified']
            ) {
                $stat[$row['id']] = $row;
            }
        }
    }
}

// We need to sort the merged array by filename or date_modified again
if ($requestSort == 'name') {
    $key = 'filename';
} else {
    $key = 'date_modified';
}
$stat = UtilSort::sortByKey($stat, $key, $sqlSortBy);


?>
<table border="1">
<tr>
	<th>No.</th>
    <th>Date&nbsp;&nbsp;<a title="Sort by ascending date" href="<?=$urlSortDateAsc ?>">&#9662;</a><a title="Sort by descending date" href="<?=$urlSortDateDesc ?>">&#9652;</a></th>
    <th>Directory&nbsp;&nbsp;<a title="Sort by ascending name" href="<?=$urlSortNameAsc ?>">&#9662;</a><a title="Sort by descending name" href="<?=$urlSortNameDesc ?>">&#9652;</a></th>
<?php
	echo '<th>Stage / Target</th>';
?>

</tr>
<?php
$count = 0;
foreach ($stat as $key => $entry) {
    $target = str_replace('clean', '', $entry['wq_prev_action']);
    $stage = $entry['stage'];
    $id = $entry['id'];

    $directory = 'files/'.$entry['filename'].'/';
    if (!preg_match('/\.tex$/', $entry['sourcefile'])) {
        $sourcefile = $entry['sourcefile'].'.tex';
        $sourcefileLink = $directory.$entry['sourcefile'].'.tex';
    } else {
        $sourcefile = $entry['sourcefile'];
        $sourcefileLink = $directory.$entry['sourcefile'];
    }

    $prefix = basename($entry['sourcefile'], '.tex');

	echo "<tr>\n";
	$count++;
	$no = $count + $min;
	if (isset($entry['s_date_modified'])) {
		$date_modified = $entry['s_date_modified'];
	} else {
		$date_modified = '';
	}
	if (isset($entry['filename'])) {
		$filename = $entry['filename'];
	} else {
		$filename = '';
	}

    if ($row['wq_action'] === $target) {
        if ($row['wq_priority']) {
            $queued = 'queued';
        } else {
            $queued = 'running';
        }
    } else {
        $queued = '';
    }


	echo '<td align="right" rowspan="2">'.$no."</td>\n";
	echo '<td rowspan="2">'.$date_modified."</td>\n";
	echo '<td rowspan="1"><a href="'.$directory.'">'.$filename."</a></td>\n";

    $directory = UtilStage::getSourceDir('files', $entry['filename'], $cfg->stages[$stage]->hostGroup) . '/';
    //  %MAINFILEPREFIX%, will be replaced by basename of maintexfile
    $destFile = str_replace('%MAINFILEPREFIX%', $prefix, $cfgDestFile[$stage]);
    $stdOutLog = str_replace('%MAINFILEPREFIX%', $prefix, $cfgStdOutLog[$stage]);
    $stdErrLog = str_replace('%MAINFILEPREFIX%', $prefix, $cfgStdErrLog[$stage]);

    if ($destFile != '') {
        $destFileLink = $directory.$destFile;
    }
    $stdOutFileLink = $directory.$stdOutLog;
    $stdErrFileLink = $directory.$stdErrLog;

    // the current retval for given stage
    if (isset($entry['retval'])) {
        $retval_class = $cfg->ret_class[$entry['retval']];

        $color = $cfg->ret_color[$cfg->ret_class[$entry['retval']]];
        echo '<td class="'.$color.'" style="font-size: 11px">'.PHP_EOL;
        echo $stage . ' / ' . $entry['wq_prev_action'].'<br />'.PHP_EOL;
        echo $entry['retval'].'<br />'.PHP_EOL;
        echo '<a href="'.htmlspecialchars($stdErrFileLink).'">ErrFile</a><br />'.PHP_EOL;
        echo '<a href="'.htmlspecialchars($destFileLink).'">DestFile</a><br />'.PHP_EOL;
        echo $entry['date_modified'].'<br />'.PHP_EOL;
        echo '<a href="javascript:rerunById('.$id.',\''.$stage.'\',\''.$target.'\')">queue</a>'.PHP_EOL;
        echo '<span id="rerun_'.$id.'_'.$stage.'">' . $queued .'</span>'.PHP_EOL;
        echo '</td>'.PHP_EOL;
    } else {
        $color = $cfg->ret_color[$cfg->ret_class['unknown']];
        echo '<td class="'.$color.'" style="font-size: 11px">'.PHP_EOL;
        echo '<a href="javascript:rerunById('.$id.',\''.$stage.'\',\''.$target.'\')">queue</a>'.PHP_EOL;
        echo '<span id="rerun_'.$id.'_'.$stage.'">' . $queued .'</span>'.PHP_EOL;
        echo '</td>'.PHP_EOL;
    }
    echo '</tr>'.PHP_EOL;

    // Line below for prev_retval for given stage
	echo '<tr style="height:6px">'.PHP_EOL;
    echo '<td style="text-align:right; font-size: 11px">previous</td>'.PHP_EOL;
    if (isset($entry['prev_retval'])) {
        $retval_class = $cfg->ret_class[$entry['prev_retval']];
        $color = $cfg->ret_color[$cfg->ret_class[$entry['prev_retval']]];
        echo '<td class="'.$color.'" style="font-size: 11px">'.$entry['prev_retval'].'</td>'.PHP_EOL;
    } else {
        $color = $cfg->ret_color[$cfg->ret_class['unknown']];
        echo '<td class="'.$color.'" style="font-size: 11px"></td>'.PHP_EOL;
    }

    echo '</tr>'.PHP_EOL;
}
?>
</table>
<?php

UtilMisc::navigator($min, $min, $max_pp, $numrows);

$page->showFooter($deferJs);
