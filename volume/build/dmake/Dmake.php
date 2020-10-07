<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 * Distributed make for ArXiv TeX files.
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
    public function sigChild($signo)
    {
        // reinstall, older OS might need it
        pcntl_signal(SIGCHLD, array($this, 'sigChild'));

        if (DBG_LEVEL & DBG_ALARM) {
            echo "Parent caught SIGCHLD\n";
        }
    }

    /**
     * signal handler for returning grandchildren
     */
    public function sigGrandchild($signo)
    {
        global $pid;

        // reinstall, older OS might need it
        pcntl_signal(SIGCHLD, array($this, 'sigGrandchild'));

        if (DBG_LEVEL & DBG_SIGNAL) {
            echo "Child caught grantchild SIGCHLD\n";
        }
    }


    // install handler for master being killed
    public function sigHup($signo)
    {
        // reinstall, older OS might need it
        pcntl_signal(SIGHUP, array($this, 'sigHup'));
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
        pcntl_signal(SIGINT, array($this, 'sigInt'));

        echo "Caught SIGINT" . PHP_EOL;
        foreach ($this->activeHosts as $hostGroupName => $hostGroup) {
            foreach ($hostGroup as $hostkey => $pid) {
                echo "Killing job on $hostkey...\n";
                posix_kill($pid, SIGTERM);
            }
        }
        exit;
    }


    // alarm handler for child
    function sigAlrm($signo)
    {
        // this variable should be unique in each process
        global $cpid;
        // this variable should be unique in each process
        global $gpid;

        if (DBG_LEVEL & DBG_ALARM) {
            echo "$cpid child alarmed\n";
        }

        // kill grandchildren and therefore remote job
        if (DBG_LEVEL & DBG_EXEC) {
            echo "$cpid child: killing grandchild $gpid...\n";
        }
        flush();
        $retval = posix_kill($gpid, SIGTERM);
        if ($retval) {
            if (DBG_LEVEL & DBG_ALARM) {
                echo "SIGTERM SUCCESS\n";
            }
        } else {
            if (DBG_LEVEL & DBG_ALARM) {
                echo "SIGTERM FAILED\n";
            }
            $retval = posix_kill($gpid, SIGKILL);
            if ($retval) {
                if (DBG_LEVEL & DBG_ALARM) {
                    echo "SGKILL SUCCESS\n";
                }
            } else {
                if (DBG_LEVEL & DBG_ALARM) {
                    echo "SIGKILL FAILED\n";
                }
            }
        }
    }

    /*
     * the code the grandchild runs
     */
    public function grandchildMain($hostGroup, $host, $entry, $stage, $action)
    {
        $cfg = Config::getConfig();
        $execstr = $cfg->app->ssh;

        // we want to have a pty, otherwise remote killing will NOT work.
        $args[0] = '-tt';
        if (isset($host['user'])) {
            $args[1] = $host['user'] . '@' . $host['hostname'];
        } else {
            $args[1] = $host['hostname'];
        }

        $makeAction = 'make_' . $action;

        // if there is a defined command like MAKE_PDF, use that otherwise just "make action"
        if (isset($host[$makeAction])) {
            $makeCommand = $host[$makeAction];
        } else {
            $makeCommand = $host['make_default'] . ' ' . $action;
        }

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

        if (DBG_LEVEL & DBG_EXEC) {
            echo $execstr . PHP_EOL;
            echo $args[1] . PHP_EOL;
            echo $args[2] . PHP_EOL;
        }

        // must use pcntl_ since we want to REPLACE current process
        pcntl_exec($execstr, $args);

        // pcntl_exec only returns on error
        echo "Error executing $execstr!\n";

    }

    /*
     * the code the child runs
     */
    public function childMain($hostGroup, $host, $entry, $stage, $action, $timeout)
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

        pcntl_signal(SIGCHLD, array($this, 'sigGrandchild'));
        $cpid = posix_getpid();

        $pid = pcntl_fork();

        switch ($pid) {
            case -1:
                echo "$cpid child_main: fork failed";
                break;

            case 0:
                // child (grandchild)
                exit($this->grandchildMain($hostGroup, $host, $entry, $stage, $action));
                break;
            default:
                // parent (this child)
                if (DBG_LEVEL & DBG_ALARM) {
                    echo "$cpid child_main: Installing sigAlrm..." . PHP_EOL;
                }
                // $gpid is pid of grandchild
                $gpid = $pid;
                pcntl_signal(SIGALRM, array($this, 'sigAlrm'), true);
                pcntl_alarm($timeout);

                // either alarm or child finishes
                $pid = pcntl_wait($status);
                pcntl_signal_dispatch();

                // determine whether alarm went off..
                if (pcntl_alarm(0)) {
                    // if alarm still running, remaining seconds are returned...
                    $child_alarmed = FALSE;
                } else {
                    $child_alarmed = TRUE;
                }

                if (!$child_alarmed) {
                    while (!pcntl_wifexited($status)) {
                        $pid = pcntl_wait($status);
                        pcntl_signal_dispatch();
                    }
                }

                if (DBG_LEVEL & DBG_ALARM) {
                    echo "state of child_alarmed: $child_alarmed\n";
                }

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
                            $classname::parse($hostGroup, $entry, $child_alarmed);
                        } else {
                            die ("Parsing dependent stages: $action, Trying to load $classname, but it does not exist");
                        }
                    }
                }

                /*
                 * Parse the result logfile
                 */
                $classname = $cfg->stages[$stage]->classname;
                echo "About to parse " . $cfg->stages[$stage]->stderrLog . PHP_EOL;
                if (DBG_LEVEL & DBG_PARSE_ERRLOG) {
                    echo "CLASSNAME: $classname" . PHP_EOL;
                    echo "About to parse " . $cfg->stages[$stage]->stderrLog . PHP_EOL;
                }
                if (class_exists($classname)) {
                    $classname::parse($hostGroup, $entry, $child_alarmed);
                } else {
                    die ("Action: $action, Trying to load $classname, but it does not exist");
                }

                $entry->wq_priority = 0;
                $entry->wq_action = StatEntry::WQ_ACTION_NONE;
                $entry->hostgroup = $hostGroup;
                $entry->updateWq();

                if (DBG_LEVEL & DBG_CHILD) {
                    echo "$cpid child_main: Finishing" . PHP_EOL;
                }
        }
    }
}






