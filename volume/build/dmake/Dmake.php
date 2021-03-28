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

ini_set("memory_limit", "512M");

require_once "IncFiles.php";

class Dmake
{
    /**
     * holds the currently active hosts
     * @var int[], [hostkey] = pid
     */
    public $activeHosts;

    protected $status;
    protected $pid;
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

    // install handler for master being killed
    public function sigHup($signo)
    {
        // reinstall, older OS might need it
        pcntl_signal(SIGHUP, [$this, 'sigHup']);
        echo "Caught SIGHUP" . PHP_EOL;

        foreach ($this->activeHosts as $hostGroupName => $hostGroup) {
            foreach ($hostGroup as $hostkey => $pid) {
                echo "Killing job on $hostkey...\n";
                posix_kill($pid, SIGTERM);
            }
        }
        exit;
    }

    // install handler for master being killed
    public function sigInt($signo)
    {
        // reinstall, older OS might need it
        pcntl_signal(SIGINT, [$this, 'sigInt']);

        echo "Caught SIGINT" . PHP_EOL;
        foreach ($this->activeHosts as $hostGroupName => $hostGroup) {
            foreach ($hostGroup as $hostkey => $pid) {
                echo "Killing job on $hostkey...\n";
                posix_kill($pid, SIGTERM);
            }
        }
        exit;
    }

    /*
     * prepare request and run on worker
     */
    public function runWorker($hostGroup, $host, $entry, $stage, $action)
    {
        $cfg = Config::getConfig();

        $makeCommand = UtilStage::getMakeCommand(
            $host,
            $action,
            $cfg->stages[$stage]->makeLog);

        $args[2] = '';
        if (isset($host['path'])) {
            // We need to put this in front to make sure we get the right latexml
            $args[2] .= 'export PATH=' . $host['path'] . ':$PATH;';
        }
        if (isset($host['memlimitRss'])) {
            // limit the amount of memory the worker may use
            $args[2] .= 'ulimit -m ' . $host['memlimitRss'] . '; ';
        }
        if (isset($host['memlimitVirtual'])) {
            // limit the amount of memory the worker may use
            $args[2] .= 'ulimit -v ' . $host['memlimitVirtual'] . '; ';
        }

        $sourceDir = UtilStage::getSourceDir($host['dir'], $entry->filename, $hostGroup);

        $args[2] .= 'umask 0002; cd \'' . $sourceDir . '\';' . $makeCommand;


        // must use pcntl_ since we want to REPLACE current process
        //pcntl_exec($execstr, $args);

        $awr = new ApiWorkerRequest();
        $awr->setWorker('worker')
            ->setCommand('make')
            ->setStage($stage)
            ->setMakeAction($action)
            ->setDirectory($entry->filename . '/__texmlbus_worker');

        $result = $awr->sendRequest();

        return $result;
        //// pcntl_exec only returns on error
        //echo "Error executing $execstr!\n";
        //return true;
    }

    /*
     * the code the child runs
     */
    public function childMain($hostGroup, $host, StatEntry $entry, $stage, $action, $timeout)
    {
        // this variable should be unique for each child
        global $cpid;
        // this variable should be unique for each child
        global $gpid;

        $cfg = Config::getConfig();

        $child_alarmed = FALSE;
        /*
         * we need to fork again
         * child will set up its own timer
         * while grandchild will remotely execute the code
         */

        $cpid = posix_getpid();

        //$pid = pcntl_fork();

        //switch ($pid) {
          //  case -1:
          //      echo "$cpid child_main: fork failed";
          //      break;

          //  case 0:
                // child (grandchild),
                $apiResult = $this->runWorker($hostGroup, $host, $entry, $stage, $action);
                $childAlarmed = ($apiResult->getShellReturnVar() == CURLE_OPERATION_TIMEDOUT);
                $status = $apiResult->getShellReturnVar();

          //      exit($apiResult->getShellReturnVar());

          //  default:
          //      // parent (this child)
          //      if (DBG_LEVEL & DBG_ALARM) {
          //          echo "$cpid child_main: Installing sigAlrm..." . PHP_EOL;
          //      }
          //      // $gpid is pid of grandchild
          //      $gpid = $pid;
          //      pcntl_signal(SIGALRM, [$this, 'sigAlrm'], true);
          //      pcntl_alarm($timeout);

                // either alarm or child finishes
          //      $pid = pcntl_wait($status);
          //      pcntl_signal_dispatch();

          //      // determine whether alarm went off..
          //      if (pcntl_alarm(0)) {
          //          // if alarm still running, remaining seconds are returned...
          //          $childAlarmed = false;
          //      } else {
          //          $childAlarmed = true;
          //      }

          //      if (!$childAlarmed) {
          //          // why was this needed?.. Spurious wakeups?
          //          while (!pcntl_wifexited($status)) {
          //              $pid = pcntl_wait($status);
          //              pcntl_signal_dispatch();
          //          }
          //      }

          //      $retval = pcntl_wexitstatus($status);
          //      echo "Child exit value: $retval" . PHP_EOL;

          //      if ($retval === CURLE_OPERATION_TIMEDOUT) {
          //          $childAlarmed = true;
          //      }

          //      if (DBG_LEVEL & DBG_CHILD_RETVAL) {
          //          echo "Child returns status: $status" . PHP_EOL;
          //      }
          //      if (DBG_LEVEL & DBG_ALARM) {
          //          echo "State of childAlarmed: " . (int) $childAlarmed . PHP_EOL;
          //      }

                /*
                 * Parse Logfiles of dependent stages
                 */
                if ($cfg->stages[$stage]->dependentStages) {
                    foreach ($cfg->stages[$stage]->dependentStages as $dependentStage) {
                        // for now parse all the dependent Logfiles
                        $classname = $cfg->stages[$dependentStage]->classname;
                        if (class_exists($classname)) {
                            if (DBG_LEVEL & DBG_PARSE_ERRLOG) {
                                echo "DependentStage: parsing " . $cfg->stages[$dependentStage]->stderrLog . PHP_EOL;
                            }
                            $classname::parse($hostGroup, $entry, $status, $childAlarmed);
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
                    echo "About to parse " . $cfg->stages[$stage]->stderrLog . PHP_EOL;
                }
                if (class_exists($classname)) {
                    $classname::parse($hostGroup, $entry, $status, $childAlarmed);
                } else {
                    die ("Action: $action, Trying to load $classname, but it does not exist");
                }

                $wqEntry = new WorkqueueEntry();
                $wqEntry->setStage($stage);
                $wqEntry->setStatisticId($entry->getId());
                $wqEntry->setPriority(0);
                $wqEntry->setAction(StatEntry::WQ_ACTION_NONE);
                $wqEntry->setHostGroup($hostGroup);
                $wqEntry->updateAndStat();

                if (DBG_LEVEL & DBG_CHILD) {
                    echo "$cpid child_main: Finishing" . PHP_EOL;
                }
        }
    //}
}






