<?php
/**
 * MIT License
 * (c) 2019 - 2020 Heinrich Stamerjohanns
 *
 * updates columns in retval_abc.php
 */
require_once "../../include/IncFiles.php";
/**
 * @var string $srcDir
 */
require_once $srcDir . '/dmake/InotifyHandler.php';
require_once $srcDir . '/dmake/StatEntry.php';

use Dmake\InotifyHandler;
use Dmake\RetvalDao;
use Dmake\StatEntry;
use Dmake\UtilStage;
use Server\Config;
use Server\View;

header("Content-Type: text/event-stream");

$cfg = Config::getConfig();

$debug = false;

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
    $curDate = date(DATE_ATOM);
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
        if ($debug) {
            error_log("triggered: " . InotifyHandler::doneTrigger);
        }

    } else {
        sleep($wqSleepSeconds);
    }

    $statEntries = StatEntry::getLastStat('wq.date_modified', 'DESC', 0, 5);

    // find the columns that should be updated
    // the column of the stage itself and the column of the dependent targets
    $collect = [];
    foreach ($statEntries as $entry) {
        $stage = $entry['wq_stage'];
        if (empty($stage)) {
            $stage = $entry['wq_prev_action'];
        }
        error_log("Stage: $stage");

        if (!in_array($stage, array_keys($cfg->stages))
            && $stage !== 'unknown'
        ) {
            continue;
        }
        $collect[$stage][] = $entry['id'];
        if ($stage !== 'unknown') {
            foreach ($cfg->stages[$stage]->dependentStages as $dependentStage) {
                $collect[$dependentStage][] = $entry['id'];
                error_log("Stage: $stage, DependentStage: $dependentStage, Id: " . $entry['id']);
            }
        }
    }

    $entries = [];
    foreach ($collect as $stage => $ids) {
        if ($stage === 'unknown') {
            // all stages are reset, fill entries for all existing stages
            $stages = array_keys($cfg->stages);
            foreach ($stages as $mystage) {
                foreach ($ids as $id) {
                    $entries[] = ['stage' => $mystage, 'id' => $id];
                }
            }
        } else {
            $entries = array_merge($entries, RetvalDao::getByIds($ids, $stage, 's.date_modified', 'asc', 0, 100));
        }
    }

    foreach ($entries as $entry) {
        $prefix = basename($entry['sourcefile'] ?? '', '.tex');

        $stage = $entry['stage'];
        $date_modified = $entry['date_modified'] ?? '';
        $id = $entry['id'];
        $target = $cfg->stages[$stage]->target;

        if (!empty($cfg->stages[$stage]->destFile)) {
            $cfgDestFile[$stage] = $cfg->stages[$stage]->destFile;
        } else {
            $cfgDestFile[$stage] = '';
        }
        $cfgStdOutLog[$stage] = $cfg->stages[$stage]->stdOutLog;
        $cfgStdErrLog[$stage] = $cfg->stages[$stage]->stdErrLog;

        if (isset($entry['filename'])) {
            //  %MAINFILEPREFIX%, will be replaced by basename of maintexfile
            $destFile = str_replace('%MAINFILEPREFIX%', $prefix, $cfgDestFile[$stage]);
            $stdOutLog = str_replace('%MAINFILEPREFIX%', $prefix, $cfgStdOutLog[$stage]);
            $stdErrLog = str_replace('%MAINFILEPREFIX%', $prefix, $cfgStdErrLog[$stage]);

            //$directory = 'files/' . $entry['filename'] . '/';
            $directory = UtilStage::getSourceDir('files', $entry['filename'], $cfg->stages[$stage]->hostGroup) . '/';

            if ($destFile != '') {
                $destFileLink = $directory . $destFile;
            }
            $stdOutFileLink = $directory . $stdOutLog;
            $stdErrFileLink = $directory . $stdErrLog;
        } else {
            $destFileLink = '';
            $stdOutFileLink = '';
            $stdErrFileLink = '';
        }

        if (isset($entry['wq_action'])
            && $entry['wq_action'] === $target
        ) {
            if ($entry['wq_priority']) {
                $queued = 'queued';
            } else {
                $queued = 'running';
            }
        } else {
            $queued = '';
        }

        $retvalColumn = View::renderRetvalCell(
            $entry['retval'] ?? 'unknown',
            $stdErrFileLink,
            $destFileLink,
            $entry['id'],
            $stage,
            $target,
            $date_modified,
            $queued
        );

        $prevRetvalColumn = View::renderPrevRetvalCell(
            $entry['prev_retval'] ?? 'unknown',
            $entry['id'],
            $stage,
        );

        $dateColumn = View::renderDateCell(
            $entry['id'],
            $entry['s_date_modified'] ?? ''
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
