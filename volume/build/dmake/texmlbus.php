<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 * Distributed make for TeX files.
 *
 * allows make jobs to be distributed among several hosts
 * needs password less ssh login (use public/private key with
 * EMPTY passphrase, the directory on which is worked should
 * be mounted on each host
 *
 * written by Heinrich Stamerjohanns, June 5th, 2007
 *            heinrich@stamerjohanns.de
 *
 */

ini_set("memory_limit", "512M");

require_once "IncFiles.php";

use Dmake\Config;
use Dmake\Dao;
use Dmake\DbUpdate;
use Dmake\Dmake;
use Dmake\DmakeStatus;
use Dmake\InotifyHandler;
use Dmake\StatEntry;
use Dmake\UtilHost;
use Dmake\UtilStage;

// configHosts needs to be included
$cfg = Config::getConfig(null, true);

// setup process control

/*
function tick_handler() {
	pcntl_signal_dispatch();
}

register_tick_function('tick_handler');
*/

// Older php versions still need declare ticks...
if (version_compare(PHP_VERSION, '7.1.0', '>=')) {
    echo "Using pcntl_async_signals..." . PHP_EOL;
    pcntl_async_signals(true);
} else {
    echo "Using declare(ticks=1)..." . PHP_EOL;
    declare(ticks=1);
}

$dmake = new Dmake();
// install signal handler
pcntl_signal(SIGCHLD, array($dmake, 'sigChild'));
pcntl_signal(SIGINT, array($dmake, 'sigInt'));

UtilHost::checkHosts($cfg->hosts);

$proc_count = count($cfg->hosts);
$hostGroups = UtilStage::getHostGroups();
foreach ($hostGroups as $hostGroupName) {
    $dmake->activeHosts[$hostGroupName] = [];
}

$tries = 0;
$secondsToSleep = 5;
while (!$dao = Dao::getInstance(false)) {
    $tries++;
    if ($tries > 20) {
        die("Failed to get database connection!");
    }
    echo "Database not yet ready? Sleeping $secondsToSleep sec..." . PHP_EOL;
    sleep($secondsToSleep);
}

// check for Updates
$du = new DbUpdate();
$du->execute();

$ds = new DmakeStatus;
$ds->directory = '';
$ds->num_files = StatEntry::wqGetNumEntries();
$ds->num_hosts = count($cfg->hosts);

$str = '';
foreach ($cfg->hosts as $hostGroupName => $hostGroup) {
    foreach ($hostGroup as $hostkey => $val) {
        $str .= $hostkey . ', ';
    }
}
$str = preg_replace('/, $/', '', $str);
$ds->hostnames = $str;
$ds->timeout = $cfg->timeout->default;
$ds->save(TRUE);

function mainLoop($inotify, $hostGroupName, $dmake, $ds)
{
    $cfg = Config::getConfig();

    $possibleCleanActions = array('clean');

    foreach ($cfg->stages as $stage => $value) {
        $possibleActions[] = $stage;
        $possibleCleanActions[] = $stage.'clean';
    }

    $wqSleepSeconds = 60;

    while (1) {
        $count = 0;

        /*
         *  First we fetch several entries from the queue, which are then
         *  processed in a seperate while loop.
         *
        */
        while ($db_entries = StatEntry::wqGetNextEntries($hostGroupName, $ds->num_hosts)) {
            // process fetched entries

            while ($entry = array_pop($db_entries)) {
                $count++;
                if (DBG_LEVEL & DBG_DIRECTORIES) {
                    echo $entry->filename . "..." . PHP_EOL;
                }

                StatEntry::wqRemoveEntry($entry->id);

                // update every 20 entries
                if (($count % 20) == 0) {
                    $ds->num_files = StatEntry::wqGetNumEntries();
                    $ds->save(false);
                }

                $entryDone = false;
                $stage = $entry->wq_stage;
                $action = $entry->wq_action;
                echo "Action: $action" . PHP_EOL;

                if (!isset($cfg->stages[$stage])) {
                    echo "Unregistered Action: $stage" . PHP_EOL;
                    echo "Skipping file " . $entry->filename . PHP_EOL;
                    continue;
                }

                if (in_array($action, $possibleCleanActions)) {
                    echo "Cleaning up...\n";

                    $directory = $entry->filename;
                    echo "Dir: " . $directory . "\n";
                    // ARTICLEDIR./.$directory need quotes!
                    $systemCmd = 'cd "' . ARTICLEDIR . '/' . $directory . '" && /usr/bin/make ' . $action;
                    if (DBG_LEVEL & DBG_DELETE) {
                        echo "Make $action $directory...\n";
                    }
                    system($systemCmd);
                    $entry->wq_action = StatEntry::WQ_ACTION_NONE;
                    $entry->updateWq();
                    continue;
                    //UtilFile::cleanupDir($directory, $action);
                }

                $timeout = $cfg->stages[$stage]->timeout;

                // $entryDone = StatEntry::alreadyDone($action, $entry->id);

                // only try to make if these conditions are met
                if (!$entryDone || $action == StatEntry::WQ_ACTION_FORCE) {
                    $hostkey = '';
                    while ($hostkey == '') {
                        $hostkey = UtilHost::getFreeHost($cfg->hosts[$hostGroupName], $dmake->activeHosts[$hostGroupName]);

                        // close DB connection, so connection is not shared among children
                        Dao::dropInstance();

                        if ($hostkey != '') {
                            if (DBG_LEVEL & DBG_HOSTS) {
                                echo "Got $hostkey..." . PHP_EOL;
                            }

                            // fork new child
                            $pid = pcntl_fork();
                            switch ($pid) {
                                case -1:
                                    echo "Fork failed!" . PHP_EOL;
                                    exit;
                                    break;
                                case 0:
                                    // child
                                    // it is important that the child has its own connection to the database, so on close
                                    // the connection will not be gone for the parent.
                                    // https://stackoverflow.com/questions/3668615/pcntl-fork-and-the-mysql-connection-is-gone
                                    // https://www.electrictoolbox.com/mysql-connection-php-fork/
                                    $result = $dmake->childMain(
                                        $hostGroupName,
                                        $cfg->hosts[$hostGroupName][$hostkey],
                                        $entry,
                                        $stage,
                                        $action,
                                        $timeout
                                    );

                                    // trigger done, so sse script gets triggered
                                    // @TODO
                                    // check pipe, so information about finished jobs
                                    // could be send directly to sse.
                                    $inotify->trigger($hostGroupName, InotifyHandler::doneTrigger);
                                    exit($result);
                                    break;
                                default:
                                    // parent
                                    // install signal handler in parent
                                    // does not seem to work as it is supposed to
                                    pcntl_signal(SIGHUP, array($dmake, 'sigHup'));
                                    pcntl_signal(SIGINT, array($dmake, 'sigInt'));
                                    if (DBG_LEVEL & DBG_CHILD) {
                                        echo "Created child $pid" . PHP_EOL;
                                    }
                                    $dmake->activeHosts[$hostGroupName][$hostkey] = $pid;
                            }
                        } else {
                            // No more hosts available
                            if (DBG_LEVEL & DBG_HOSTS) {
                                echo "No more hosts..." . PHP_EOL;
                            }
                            if (DBG_LEVEL & DBG_SLEEP) {
                                echo "Parent waits..." . PHP_EOL;
                            }

                            $pid = pcntl_wait($status, WUNTRACED);
                            // remove returned pid from active hosts.
                            $dmake->activeHosts[$hostGroupName] = array_diff($dmake->activeHosts[$hostGroupName], array($pid));
                        }
                    }
                } elseif (DBG_LEVEL & DBG_DIRECTORIES) {
                    echo "Skipping " . $entry->filename . ", entry has already been processed..." . PHP_EOL;
                }
            }
        }

        // Just make sure not to finish to early
        // so we do not get tcsetattr() errors.
        /*while (($pid = pcntl_wait($status, WUNTRACED)) != -1) {
            echo "Waiting for children to finish...".PHP_EOL;
        }
         *
         */

        $ds->num_files = StatEntry::wqGetNumEntries();
        $ds->save(false);

        if ($inotify->isActive()) {
            echo "Waiting on inotify trigger: " . $inotify->getTriggerFile($hostGroupName, InotifyHandler::wqTrigger) . PHP_EOL;
            $inotify->wait($hostGroupName, InotifyHandler::wqTrigger);
        } else {
            echo "Waitqueue empty waiting " . $wqSleepSeconds . " seconds..." . PHP_EOL;
            sleep($wqSleepSeconds);
        }
    }
}

$inotify = new InotifyHandler();

foreach ($cfg->hosts as $hostGroupName => $hostGroup) {

    echo "HostGroup: $hostGroupName" . PHP_EOL;
    if ($inotify->isActive()) {
        echo "Setting up inotifyWatcher for $hostGroupName ...\n";
        $inotify->setupWatcher($hostGroupName, InotifyHandler::wqTrigger);
    }
    // close DB connection, so connection is not shared among children
    Dao::dropInstance();

    // fork new child
    $pid = pcntl_fork();
    switch ($pid) {
        case -1:
            echo "Fork failed!" . PHP_EOL;
            exit;
            break;
        case 0:
            // child
            $result = mainLoop($inotify, $hostGroupName, $dmake, $ds);
            exit($result);
            break;
        default:
            // parent
            // install signal handler in parent
            // does not seem to work as it is supposed to
            pcntl_signal(SIGHUP, array($dmake, 'sigHup'));
            pcntl_signal(SIGINT, array($dmake, 'sigInt'));
            if (DBG_LEVEL & DBG_CHILD) {
                echo "Created child $pid" . PHP_EOL;
            }
    }
}
$pid = pcntl_wait($status, WUNTRACED);

echo "Finished workqueue...".PHP_EOL;





