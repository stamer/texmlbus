<?php
/**
 * MIT License
 * (c) 2019 - 2020 Heinrich Stamerjohanns
 *
 * Updates a row in retval_detail.php
 */

require_once "../../include/IncFiles.php";

use Dmake\Config;
use Dmake\InotifyHandler;
use Dmake\RetvalDao;
use Dmake\StatEntry;
use Dmake\UtilStage;
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

$stages = array_keys($cfg->stages);

$columns = View::getColumnsByRetval($stage, $retval);
if (in_array($stage, $stages)) {
    $target = $cfg->stages[$stage]->target;
    $joinTable = $cfg->stages[$stage]->dbTable;
    $tableTitle = $cfg->stages[$stage]->tableTitle;
    if (!empty($cfg->stages[$stage]->destFile)) {
        $cfgDestFile = $cfg->stages[$stage]->destFile;
    } else {
        $cfgDestFile = '';
    }
    $cfgStdoutLog = $cfg->stages[$stage]->stdoutLog;
    $cfgStderrLog = $cfg->stages[$stage]->stderrLog;
    $hostGroupName = $cfg->stages[$stage]->hostGroup;
} else {
    exit;
}

$inotify = new InotifyHandler();
if ($inotify->isActive()) {
    if ($debug) {
        error_log("Setting up inotifyWatcher for $hostGroupName...");
    }
    $inotify->setupWatcher($hostGroupName, InotifyHandler::doneTrigger);
    if ($debug) {
        error_log("...Done");
    }
} else {
    $wqSleepSeconds = 60;
}



while (1) {
    $curDate = date(DATE_ATOM);
    echo "event: ping\n",
        'data: {"time": "' . $curDate . '"}', "\n\n";
    while (ob_get_level() > 0) {
        ob_end_flush();
    }
    flush();
    if ($inotify->isActive()) {
        if ($debug) {
            error_log("Waiting on inotify trigger: " . $inotify->getTriggerFile($hostGroupName, InotifyHandler::doneTrigger));
        }
        $inotify->wait($hostGroupName, InotifyHandler::doneTrigger);
        if ($debug) {
            error_log("triggered: " . InotifyHandler::doneTrigger);
        }
    } else {
        sleep($wqSleepSeconds);
    }

    $statEntries = StatEntry::getLastStat('wq.date_modified', 'DESC', 0, 5);

    $ids = [];
    foreach ($statEntries as $entry) {
        $ids[] = $entry['id'];
    }

    $rows = RetvalDao::getDetailsByIds($ids, $stage, $joinTable, $columns);

    foreach ($rows as $row) {

        $prefix = basename($row['sourcefile'], '.tex');

        $date_modified = $row['date_modified'];
        $newRetval = $row['retval'] ?? 'unknown';

        $id = $row['id'];

        //  %MAINFILEPREFIX%, will be replaced by basename of maintexfile
        $destFile = str_replace('%MAINFILEPREFIX%', $prefix, $cfgDestFile);
        $stdoutLog = str_replace('%MAINFILEPREFIX%', $prefix, $cfgStdoutLog);
        $stderrLog = str_replace('%MAINFILEPREFIX%', $prefix, $cfgStderrLog);

        // $directory = 'files/' . $row['filename'] . '/';
        $directory = UtilStage::getSourceDir('files', $row['filename'], $hostGroupName) . '/';

        if ($destFile != '') {
            $destFileLink = $directory.$destFile;
        }
        $stdoutFileLink = $directory.$stdoutLog;
        $stderrFileLink = $directory.$stderrLog;

        if ($row['wq_action'] === $target) {
            if ($row['wq_priority']) {
                $queued = 'queued';
            } else {
                $queued = 'running';
            }
        } else {
            $queued = '';
        }

        $retvalRow = View::renderDetailRow(
                $id,
                '__COUNT__',
                $directory,
                $stage,
                $target,
                $newRetval,
                $stderrFileLink,
                $destFileLink,
                $row,
                $columns);

        $data = [
            'fieldid' => 'tr_' . $id . '_' . $stage,
            'countid' => 'td_count_' . $id,
            'html' => $retvalRow
        ];
        // The retval changed from e.g unknown to no_problems,
        // therefore the row should not be listed any more on this page.
        if ($newRetval != $retval) {
            echo 'event: deleterow' . "\n";
        } else {
            echo 'event: updaterow' . "\n";
        }
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
