<?php
/*
 * updates columns in retval_abc.php
 */
require_once "../../include/IncFiles.php";
/**
 * @var $buildDir
 */
require_once $buildDir . '/dmake/InotifyHandler.php';
require_once $buildDir . '/dmake/StatEntry.php';

use Dmake\InotifyHandler;
use Dmake\RetvalDao;
use Dmake\StatEntry;
use Dmake\UtilStage;
use Server\Config;
use Server\View;

header("Content-Type: text/event-stream");

$cfg = Config::getConfig();

$debug = true;

$inotify = new InotifyHandler();
if ($inotify->isActive()) {
    if ($debug) {
        error_log("Setting up inotifyWatcher (anyHostGroup)...");
    }
    $inotify->setupWatcherAnyHostGroup(InotifyHandler::doneTrigger);
    if ($debug) {
        error_log("...Done");
    }
} else {
    $wqSleepSeconds = 60;
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
            error_log("Waiting on inotify trigger: " . InotifyHandler::doneTrigger);
        }
        $inotify->waitAnyHostGroup(InotifyHandler::doneTrigger);
    } else {
        sleep($wqSleepSeconds);
    }

    $statEntries = StatEntry::getLastStat('s.date_modified', 'DESC', 0, 5);

    // find the columns that should be updated
    // the column of the stage itself and the column of the dependent targets
    $collect = [];
    foreach ($statEntries as $entry) {
        $stage = $entry['wq_stage'];
        if (empty($stage)) {
            $stage = $entry['wq_prev_action'];
        }
        error_log("Stage: $stage");

        if (!in_array($stage, array_keys($cfg->stages))) {
            continue;
        }
        $collect[$stage][] = $entry['id'];
        foreach ($cfg->stages[$stage]->dependentStages as $dependentStage) {
            $collect[$dependentStage][] = $entry['id'];
            error_log("Stage: $stage, DependentStage: $dependentStage, Id: " . $entry['id']);
        }
    }

    $entries = [];
    foreach ($collect as $stage => $ids) {
        $entries = array_merge($entries, RetvalDao::getByIds($ids, $stage, 's.date_modified', 'asc', 0, 100));
    }

    foreach ($entries as $entry) {
        $prefix = basename($entry['sourcefile'], '.tex');

        $stage = $entry['stage'];
        $date_modified = $entry['s_date_modified'];
        $id = $entry['id'];
        $target = $cfg->stages[$stage]->target;

        if (!empty($cfg->stages[$stage]->destFile)) {
            $cfgDestFile[$stage] = $cfg->stages[$stage]->destFile;
        } else {
            $cfgDestFile[$stage] = '';
        }
        $cfgStdoutLog[$stage] = $cfg->stages[$stage]->stdoutLog;
        $cfgStderrLog[$stage] = $cfg->stages[$stage]->stderrLog;

        //  %MAINFILEPREFIX%, will be replaced by basename of maintexfile
        $destFile = str_replace('%MAINFILEPREFIX%', $prefix, $cfgDestFile[$stage]);
        $stdoutLog = str_replace('%MAINFILEPREFIX%', $prefix, $cfgStdoutLog[$stage]);
        $stderrLog = str_replace('%MAINFILEPREFIX%', $prefix, $cfgStderrLog[$stage]);

        //$directory = 'files/' . $entry['filename'] . '/';
        $directory = UtilStage::getSourceDir('files', $entry['filename'], $cfg->stages[$stage]->hostGroup) . '/';

        if ($destFile != '') {
            $destFileLink = $directory.$destFile;
        }
        $stdoutFileLink = $directory.$stdoutLog;
        $stderrFileLink = $directory.$stderrLog;

        if ($entry['wq_priority'] && $entry['wq_action'] === $target) {
            $queued = 'queued';
        } else {
            $queued = '';
        }

        if (isset($entry[$stage]['retval'])) {
            $retval = $entry[$stage]['retval'];
        } else {
            $retval = 'unknown';
        }

        $retvalColumn = View::renderRetvalColumn(
            $entry['retval'],
            $stderrFileLink,
            $destFileLink,
            $entry['id'],
            $stage,
            $target,
            $date_modified,
            $queued
        );

        $prevRetvalColumn = View::renderPrevRetvalColumn(
            $entry['prev_retval'],
            $entry['id'],
            $stage,
        );

        $dateColumn = View::renderDateColumn(
            $entry['id'],
            $date_modified
        );

        $data = [
            'fieldid' => 'td_' . $id . '_' . $stage,
            'html' => $retvalColumn
        ];
        echo 'event: updatecolumn' . "\n";
        echo 'data: ' . json_encode($data) .  "\n\n";

        $data = [
            'fieldid' => 'td_' . $id . '_prev' . $stage,
            'html' => $prevRetvalColumn
        ];
        echo 'event: updatecolumn' . "\n";
        echo 'data: ' . json_encode($data) .  "\n\n";

        $data = [
            'fieldid' => 'td_' . $id . '_date',
            'html' => $dateColumn
        ];
        echo 'event: updatecolumn' . "\n";
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
