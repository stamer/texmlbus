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
use Server\Config;
use Server\Page;
use Server\UtilMisc;
use Server\View;

$page = new Page('Detailed return values');
$page->addScript('/js/sseUpdateRow.js');
$page->addScript('/js/handleComment.js');
$page->showHeader('index');

$cfg = Config::getConfig();
$dao = Dao::getInstance();

// controlled via switch, safe
$detail = $page->getRequest()->getQueryParam('detail', '');
$min = $page->getRequest()->getQueryParam('min', 0);
// make sure no negatives
$min = max(0, (int) $min);

$retval = $page->getRequest()->getQueryParam('retval', '');

// via bindParam, safe
if (empty($retval)) {
	echo "No return value given!";
	exit;
}
// via bindParam, safe
$set = $page->getRequest()->getQueryParam('set', '');

// possible SqlInjection, assign $sqlSortBy explicitly
$requestDir = $page->getRequest()->getQueryParam('dir', 'DESC');
if ($requestDir === 'asc') {
    $sqlSortBy = 'ASC';
} else {
    $sqlSortBy = 'DESC';
}

// possible SqlInjection, assign $sqlOrderBy explicitly
$requestSort = $page->getRequest()->getQueryParam('sort', '');
if ($requestSort === 'name') {
    $sqlOrderBy = 's.filename';
} else {
    $sqlOrderBy = 'j.date_modified';
}

// via bindParam, safe
$stage = $page->getRequest()->getQueryParam('stage', 'xml');
$stages = array_keys($cfg->stages);

if (in_array($stage, $stages)) {
    $target = $cfg->stages[$stage]->target;
    $joinTable = $cfg->stages[$stage]->dbTable;
    $tableTitle = $cfg->stages[$stage]->tableTitle;
    if (!empty($cfg->stages[$stage]->destFile)) {
        $cfgDestFile = $cfg->stages[$stage]->destFile;
    } else {
        $cfgDestFile = '';
    }
    $cfgStdOutLog = $cfg->stages[$stage]->stdOutLog;
    $cfgStdErrLog = $cfg->stages[$stage]->stdErrLog;
} else {
    echo "Unknown stage: ".htmlspecialchars($stage);
    exit;
}

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

$sseParams = "'" . $set . "','" . $stage . "','" . $retval . "'";
if (!empty($set)) {
?>
<h3 style="margin-bottom:15px"><em><?=htmlspecialchars($set) ?></em></h3>
<?php
}
?>
<h4>Detailed info for <?=htmlspecialchars($tableTitle) ?>, status <em><?=htmlspecialchars($retval) ?></em></h4>
    <script>sseUpdateRow(<?=$sseParams ?>)</script>

<?php

$columns = View::getColumnsByRetval($stage, $retval);

$numRows = RetvalDao::getCountByRetval($retval, $stage, $joinTable, $set, $detail);

$max_pp = $cfg->db->perPage;
$max_pp = 10;

$rows = RetvalDao::getDetailsByRetval(
        $retval,
        $stage,
        $joinTable,
        $set,
        null,
        $columns,
        $sqlOrderBy,
        $sqlSortBy,
        $min,
        $max_pp
);

if (!$numRows) {
    echo "No files found." . PHP_EOL;
    $page->showFooter();
    return;
}

?>

<table border="1">
<tr>
	<th>No.</th>
    <th>Date&nbsp;&nbsp;<a title="Sort by ascending date" href="<?=$urlSortDateAsc ?>">&#9662;</a><a title="Sort by descending date" href="<?=$urlSortDateDesc ?>">&#9652;</a></th>
    <th>Directory&nbsp;&nbsp;
        <a title="Sort by ascending name" href="<?=$urlSortNameAsc ?>">&#9662;</a><a title="Sort by descending name" href="<?=$urlSortNameDesc ?>">&#9652;</a><br />
    </th>
    <th><?= htmlspecialchars($stage) ?><br />
    <?php
    $ids = array_keys($rows);
    echo '<a style="font-size: 60%" href="/#" onclick="javascript:rerunByIds([' . implode(',', $ids) . '],\'' . $stage . '\',\'' . $target . '\'); return false">queue</a>'.PHP_EOL;
    ?>
    </th>
<?php
foreach ($columns as $field) {
	echo '<th><small>'.$field['html'].'</small></th>';
}
?>

</tr>
<?php
$count = 0;
foreach ($rows as $row) {
    $id = $row['id'];
    $directory = 'files/'.$row['filename'].'/';
    if (!preg_match('/\.tex$/', $row['sourcefile'])) {
        $sourcefile = $row['sourcefile'].'.tex';
        $sourcefileLink = $directory.$row['sourcefile'].'.tex';
    } else {
        $sourcefile = $row['sourcefile'];
        $sourcefileLink = $directory.$row['sourcefile'];
    }

    $prefix = basename($row['sourcefile'], '.tex');

    $directory = UtilStage::getSourceDir('files', $row['filename'], $cfg->stages[$stage]->hostGroup) . '/';
    //  %MAINFILEPREFIX%, will be replaced by basename of maintexfile
    $destFile = str_replace('%MAINFILEPREFIX%', $prefix, $cfgDestFile);
    $stdOutLog = str_replace('%MAINFILEPREFIX%', $prefix, $cfgStdOutLog);
    $stdErrLog = str_replace('%MAINFILEPREFIX%', $prefix, $cfgStdErrLog);

    if ($destFile != '') {
        $destFileLink = $directory.$destFile;
    }
    $stdOutFileLink = $directory.$stdOutLog;
    $stdErrFileLink = $directory.$stdErrLog;

    $count++;
    $no = $count + $min;

    echo View::renderDetailRow(
        $id,
        $no,
        $directory,
        $stage,
        $target,
        $retval,
        $stdErrFileLink,
        $destFileLink,
        $row,
        $columns
    );
}

echo "</table>";

UtilMisc::navigator($min, $min, $max_pp, $numRows);

$page->showFooter();
