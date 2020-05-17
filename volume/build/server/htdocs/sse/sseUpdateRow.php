<?php
/**
 * Updates a row in retval_detail.php
 */

require_once "../../include/IncFiles.php";

use Dmake\InotifyHandler;
use Dmake\StatEntry;
use Server\RequestFactory;
use Server\View;

header("Content-Type: text/event-stream");

$cfg = Config::getConfig();
$request = RequestFactory::create();

$set = $request->getQueryParam('set', '');
$retval = $request->getQueryParam('retval', '');
if (empty($retval)) {
    echo "No return value given!";
    exit;
}

$stage = $request->getQueryParam('stage', 'xml');

$debug = false;

$inotify = new InotifyHandler();
if ($inotify->isActive()) {
    if ($debug) {
        error_log("Setting up inotifyWatcher...");
    }
    $inotify->setupWatcher(InotifyHandler::doneTrigger);
    if ($debug) {
        error_log("...Done");
    }
} else {
    $wqSleepSeconds = 60;
}

$stages = array_keys($cfg->stages);

$columns = View::getColumnsByRetval($stage, $retval);
if (in_array($stage, $stages)) {
    $joinTable = $cfg->stages[$stage]->dbTable;
    $tableTitle = $cfg->stages[$stage]->tableTitle;
    if (!empty($cfg->stages[$stage]->destFile)) {
        $cfgDestFile = $cfg->stages[$stage]->destFile;
    } else {
        $cfgDestFile = '';
    }
    $cfgStdoutLog = $cfg->stages[$stage]->stdoutLog;
    $cfgStderrLog = $cfg->stages[$stage]->stderrLog;
} else {
    exit;
}


while (1) {
    $curDate = date(DATE_ISO8601);
    echo "event: ping\n",
        'data: {"time": "' . $curDate . '"}', "\n\n";
    while (ob_get_level() > 0) {
        ob_end_flush();
    }
    flush();
    if ($inotify->isActive()) {
        if ($debug) {
            error_log("Waiting on inotify trigger: " . $inotify->getTriggerFile(InotifyHandler::doneTrigger));
        }
        $inotify->wait(InotifyHandler::doneTrigger);
    } else {
        sleep($wqSleepSeconds);
    }

    $statEntries = StatEntry::getLastStat('s.date_modified', 'DESC', 0, 5);

    $ids = [];
    foreach ($statEntries as $entry) {
        $ids[] = $entry['id'];
    }

    $rows = RetvalDao::getDetailsByIdsAndRetval($ids, $retval, $joinTable, $columns);

    //error_log(print_r($cfg->stages, 1));

    foreach ($rows as $row) {

        $prefix = basename($row['sourcefile'], '.tex');

        $date_modified = $row['date_modified'];
        $id = $row['id'];

        //  %MAINFILEPREFIX%, will be replaced by basename of maintexfile
        $destFile = str_replace('%MAINFILEPREFIX%', $prefix, $cfgDestFile);
        $stdoutLog = str_replace('%MAINFILEPREFIX%', $prefix, $cfgStdoutLog);
        $stderrLog = str_replace('%MAINFILEPREFIX%', $prefix, $cfgStderrLog);

        $directory = 'files/' . $row['filename'] . '/';
        if ($destFile != '') {
            $destFileLink = $directory.$destFile;
        }
        $stdoutFileLink = $directory.$stdoutLog;
        $stderrFileLink = $directory.$stderrLog;

        if ($row['wq_priority'] && $row['wq_action'] === $stage) {
            $queued = 'queued';
        } else {
            $queued = '';
        }

        $retvalRow = View::renderDetailRow(
                $id,
                '__COUNT__',
                $directory,
                $stage,
                $retval,
                $stderrFileLink,
                $destFileLink,
                $row,
                $columns);

        $data = [
            'fieldid' => 'tr_' . $id . '_' . $stage,
            'countid' => 'td_count_' . $id,
            'html' => $retvalRow
        ];
        echo 'event: updaterow' . "\n";
        echo 'data: ' . json_encode($data) .  "\n\n";
    }

    while (ob_get_level() > 0) {
        ob_end_flush();
    }
    flush();

    if (connection_aborted()) {
        break;
    }
}
