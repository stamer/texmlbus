<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 * Distributed make for ArXiv TeX files.
 *
 * allows make jobs to be distributed among several hosts
 *
 * written by Heinrich Stamerjohanns, June 5th, 2007
 *            heinrich@stamerjohanns.de
 *
 */
namespace Dmake;

require_once "IncFiles.php";

class Dmake
{
    /**
     * holds the currently active hosts
     * @var int[], [hostkey] = pid
     */
    public $activeHosts;

    protected $status;

    /**
     * signal handler for returning children
     */
    public function sigChild($signo): void
    {
        // reinstall, older OS might need it
        pcntl_signal(SIGCHLD, [$this, 'sigChild']);

        if (DBG_LEVEL & DBG_ALARM) {
            echo "Parent caught SIGCHLD\n";
        }
    }

    // install handler for parent being killed
    public function sigHupParent($signo)
    {
        // reinstall, older OS might need it
        pcntl_signal(SIGHUP, [$this, 'sigHupParent']);
        echo "Caught SIGHUP (parent)" . PHP_EOL;

        foreach ($this->activeHosts as $hostGroupName => $hostGroup) {
            foreach ($hostGroup as $hostkey => $pid) {
                echo "Killing job on $hostkey...\n";
                posix_kill($pid, SIGTERM);
            }
        }
        exit;
    }

    // Install handler for parent being killed.
    public function sigIntParent($signo)
    {
        // reinstall, older OS might need it
        pcntl_signal(SIGINT, [$this, 'sigIntParent']);

        echo "Caught SIGINT (parent)" . PHP_EOL;
        foreach ($this->activeHosts as $hostGroupName => $hostGroup) {
            foreach ($hostGroup as $hostkey => $pid) {
                echo "Killing job on $hostkey...\n";
                posix_kill($pid, SIGTERM);
            }
        }
        exit;
    }

    // install handler for child being killed
    public function sigHupChild($signo)
    {
        $pid = posix_getpid();
        echo "Caught SIGHUP (child $pid)" . PHP_EOL;
        $wqEntry = WorkqueueEntry::getByPid($pid);
        if ($wqEntry) {
            $wqEntry::disableEntry($wqEntry->getStatisticId(), $wqEntry->getStage());
            RetvalDao::setRetval($wqEntry->getStage(), $wqEntry->getStatisticId(), 'unknown');
        }
        exit;
    }

    // Install handler for child being killed.
    public function sigIntChild($signo)
    {
        $pid = posix_getpid();
        echo "Caught SIGINT (child $pid)" . PHP_EOL;
        $wqEntry = WorkqueueEntry::getByPid($pid);
        if ($wqEntry) {
            $wqEntry::disableEntry($wqEntry->getStatisticId(), $wqEntry->getStage());
            RetvalDao::setRetval($wqEntry->getStage(), $wqEntry->getStatisticId(), 'unknown');
        }
        exit;
    }

    /*
     * Prepare request and run on worker.
     */
    public function runWorker($hostGroup, $host, $entry, $stage, $action)
    {
        $awr = new ApiWorkerRequest();
        $awr->setWorker($hostGroup)
            ->setCommand('make')
            ->setStage($stage)
            ->setHost($host)
            ->setMakeAction($action)
            ->setDirectory($entry->filename);

        $result = $awr->sendRequest();

        return $result;
    }

    /*
     * The code the child runs.
     */
    public function childMain($hostGroup, $host, StatEntry $entry, $stage, $action, $timeout)
    {
        $cfg = Config::getConfig();

        $cpid = posix_getpid();
        $pid = posix_getppid();
        $wqEntry = WorkqueueEntry::getByStatisticIdAndStage($entry->getId(), $stage);
        if ($wqEntry) {
            $wqEntry->setPid($cpid);
            $wqEntry->update();
        }

        $apiResult = $this->runWorker($hostGroup, $host, $entry, $stage, $action);
        $childAlarmed = ($apiResult->getShellReturnVar() == CURLE_OPERATION_TIMEDOUT);
        $status = $apiResult->getShellReturnVar();

        /*
         * Parse Logfiles of dependent stages
         */
        if ($cfg->stages[$stage]->dependentStages) {
            foreach ($cfg->stages[$stage]->dependentStages as $dependentStage) {
                // for now parse all the dependent Logfiles
                $classname = $cfg->stages[$dependentStage]->classname;
                if (class_exists($classname)) {
                    if (DBG_LEVEL & DBG_PARSE_ERRLOG) {
                        echo "DependentStage: parsing " . $cfg->stages[$dependentStage]->stdErrLog . PHP_EOL;
                    }
                    $classname::parse($hostGroup, $entry, $status, $childAlarmed);
                    if (method_exists($classname, 'parseDetail')) {
                        $retvalInstance = new $classname();
                        $retvalInstance->parseDetail($hostGroup, $entry);
                    }
                } else {
                    die ("Parsing dependent stages: $action, Trying to load $classname, but it does not exist");
                }
            }
        }

        /*
         * Parse the result logfile
         */
        $classname = $cfg->stages[$stage]->classname;
        if (DBG_LEVEL & DBG_PARSE_ERRLOG) {
            echo "CLASSNAME: $classname" . PHP_EOL;
            echo "About to parse " . $cfg->stages[$stage]->stdErrLog . PHP_EOL;
        }
        if (class_exists($classname)) {
            $classname::parse($hostGroup, $entry, $status, $childAlarmed);
            if (method_exists($classname, 'parseDetail')) {
                $retvalInstance = new $classname();
                $retvalInstance->parseDetail($hostGroup, $entry);
            }
        } else {
            die ("Action: $action, Trying to load $classname, but it does not exist");
        }

        $wqEntry = WorkqueueEntry::getByStatisticIdAndStage($entry->getId(), $stage);
        if (!$wqEntry) {
            $wqEntry = new WorkqueueEntry();
            $wqEntry->setStage($stage);
            $wqEntry->setStatisticId($entry->getId());
        }
        $wqEntry->setPid(0);
        $wqEntry->setPriority(0);
        $wqEntry->setAction(StatEntry::WQ_ACTION_NONE);
        $wqEntry->setHostGroup($hostGroup);
        $wqEntry->updateAndStat();

        if (DBG_LEVEL & DBG_CHILD) {
            echo "$cpid child_main: Finishing" . PHP_EOL;
        }
    }
}
