<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 */

namespace Dmake;

class UtilHost
{
    public const STAT_DEACTIVATED = 0;
    public const STAT_IDLE = 1;
    public const STAT_ACTIVE = 2;

    /**
     * Are the hosts available, is the directory accessible?
     */
    public static function checkHosts(array &$hosts): void
    {
        $cfg = Config::getConfig();

        foreach ($hosts as $hostGroupName => $hostGroup) {
            foreach ($hostGroup as $hostkey => $val) {
                // only check enabled hosts
                if (empty($val['enabled'])) {
                    unset($hosts[$hostGroupName][$hostkey]);
                    continue;
                }

                // some hosts may not yet be available, try several times and wait.
                $count = 1;
                $maxTries = 12;
                $shellReturnVar = 1;
                $output = '';
                while ($count < $maxTries) {
                    $apr = new ApiWorkerRequest();
                    $apr->setWorker($val['hostname'])
                        ->setCommand('meminfo');
                    echo "$count: Testing availability of " . $hostkey . '[@' . $val['hostname'] . "]..." . PHP_EOL;
                    $apiResult = $apr->sendRequest();
                    $output = $apiResult->getOutput();
                    $shellReturnVar = $apiResult->getShellReturnVar();
                    if (!$shellReturnVar) {
                        // Success
                        break;
                    } else {
                        $count++;
                        if ($count === $maxTries) {
                            // no need to wait any more
                            break;
                        }
                        $sleepSeconds = 2;
                        echo "$count: Cannot connect to $hostkey, sleeping $sleepSeconds seconds..." . PHP_EOL;
                        sleep($sleepSeconds);
                    }
                }
                // echo $hostkey . ': ' . $shellReturnVar . ' ' . $output[0] . PHP_EOL;

                if ($shellReturnVar) {
                    echo "Cannot connect, disabling $hostkey for hosts." . PHP_EOL;
                    //$hosts[$hostkey]['status'] = self::STAT_DEACTIVATED;
                    unset($hosts[$hostGroupName][$hostkey]);
                    echo "Disabling all stages that use $hostkey..." . PHP_EOL;
                    $disabled = UtilStage::disableStagesByHostGroup($hostkey);
                    foreach ($disabled as $stage) {
                        echo "Disabled $stage." . PHP_EOL;
                    }
                } else {
                    $memLimit = self::determineMemLimit($output, $cfg->memory->factor);
                    $hosts[$hostGroupName][$hostkey]['memlimitRss'] = $memLimit['rss'];
                    $hosts[$hostGroupName][$hostkey]['memlimitVirtual'] = $memLimit['virtual'];

                    echo "Testing whether " . $val['dir'] . " exists..." . PHP_EOL;
                    $apr = new ApiWorkerRequest();
                    $apr->setWorker($val['hostname'])
                        ->setCommand('checkDir')
                        ->setDirectory($val['dir']);
                    $apiResult = $apr->sendRequest();
                    $output = $apiResult->getOutput();

                    echo $hostkey . ': '
                        . $apiResult->getShellReturnVar()
                        . ' '
                        . $output[0]
                        . PHP_EOL;

                    if ($apiResult->getShellReturnVar()) {
                        echo "\nUnable to change to " . $val['dir'] . ", removing $hostkey for hosts." . PHP_EOL;
                        //$hosts[$hostkey]['status'] = self::STAT_DEACTIVATED;
                        unset($hosts[$hostGroupName][$hostkey]);
                    } else {
                        echo "OK" . PHP_EOL;
                    }
                }
            }
        }
    }

    /**
     * compares hosts and active hosts to find next available
     * machine
     */
    public static function getFreeHost(array &$hosts, array &$active_hosts): ?string
    {
        if (!count($hosts)) {
            die ("No more hosts available!");
        }

        $free_hosts = array_diff(array_keys($hosts), array_keys($active_hosts));
        if (is_array($free_hosts)) {
            // slow down if there are too many free hosts
            // at startup
            if (count($free_hosts) > 8) {
                $random = random_int(200, 500);
                echo "Slowing down, sleeping $random microseconds...".PHP_EOL;
                usleep($random);
            }
            $host = array_pop($free_hosts);

            /*
            echo "Want to return ".$host."...\n";
            echo "Press return";
            $line = fgets(STDIN, 20);
            */
            return $host;
        } else {
            return '';
        }
    }

    /**
     * finds the current workers in a dockerized environment
     */
    public static function getDockerWorkers(
        array $hostGroups // list of hostGroups (typically ['worker'])
        ): array
    {
        $hostnames = [];
        echo "Determining active hostGroups..." . PHP_EOL;
        foreach ($hostGroups as $hostGroup) {
            echo "    nslookup $hostGroup. ..." . PHP_EOL;
            // completely safe to ignore
            // nslookup: can't resolve '(null)': Name does not resolve
            // this is the lookup to the DNS server
            // Bahaviour and output of nslookup regarding different versions of
            // alpine 3.11 is a source of pain.
            // alpine 3.11.2: no dot at end needed, everything is fine.
            // alpine 3.11.3: tries to expand hostname via search of /etc/resolv.conf and fails depending on setup.
            // Therefore dot at end needed, otherwise not able to resolve.
            $output = [];
            exec("/usr/bin/nslookup $hostGroup. 2>&1", $output, $return_var);

            if ($return_var != 0) {
                echo '    ' . __METHOD__ . ': nslookup failed.' . PHP_EOL;
                echo '    OK, if that hostGroup is not enabled.' . PHP_EOL;
                print_r($output);
            }

            $hostnames[$hostGroup] = [];
            foreach ($output as $line) {
                if (!preg_match('/^Address/', $line)) {
                    continue;
                }
                if (preg_match('/^Address.*:53/', $line)) {
                    // this is the nameserver output from 3.11.3
                    continue;
                }

                // a line like
                // 3.11.2: "Address 1: 172.20.0.6 compose2_latexml_dmake_3.compose2"
                // 3.11.3: "Address: 172.20.0.6"
                // is expected.
                preg_match('/^(.*?):\s([\d\.]+)\s{0,1}(.*)/', $line, $matches);
                if (!empty($matches[3])) {
                    $hostnames[$hostGroup][] = $matches[3];
                } elseif (isset($matches[2])) {
                    $hostnames[$hostGroup][] = $matches[2];
                } else {
                    echo "Failed to determine host." . PHP_EOL;
                    echo "Output by nslookup is: $line" . PHP_EOL;
                    continue;
                }
            }
        }

        foreach ($hostnames as $hostGroup => $hostArr) {
            /*
             * if specific hosts cannot be found, then corresponding
             * dockerfile has not been used, it is useless to have such stages enabled.
             */
            if (count($hostnames[$hostGroup]) === 0) {
                echo "Disabling all stages that use $hostGroup..." . PHP_EOL;
                $disabled = UtilStage::disableStagesByHostGroup($hostGroup);
                foreach ($disabled as $stage) {
                    echo "Disabled $stage." . PHP_EOL;
                }
                continue;
            }
        }

        return $hostnames;
    }

    /**
     * Parses the output of /proc/meminfo, determines
     * the total amount (MemTotal + SwapTotal) and multiplies this with factor.
     * /proc/meminfo output is in kB and ulimit also uses kB
     *
     */
    public static function determineMemLimit(array|string $meminfo, float $factor = 1.0): array
    {
        $cfg = Config::getConfig();

        if (is_array($meminfo)) {
            $meminfo = implode("\n", $meminfo);
        }
        preg_match('/(MemTotal:\s*)(\d+)(.*)/', $meminfo, $matches);
        if ($matches && isset($matches[2])) {
            $memTotal = intval($matches[2]);
        } else {
            error_log('Failed to determine MemTotal!');
        }

        preg_match('/(SwapTotal:\s*)(\d+)(.*)/', $meminfo, $matches);
        if ($matches && isset($matches[2])) {
            $swapTotal = intval($matches[2]);
        }
        echo "MemTotal: $memTotal, SwapTotal: $swapTotal" . PHP_EOL;

        $allMemory = $memTotal + $swapTotal;
        $doubleRssMemory = $memTotal * 2;

        // never allow more than double of real memory
        $allMemory = min($allMemory, $doubleRssMemory);

        // this is all in Kb
        $memLimit['virtual'] = (int) floor($allMemory * $factor);
        $memLimit['rss'] = (int) floor($memTotal * $factor);

        // set lower value if an absolute memory limit is set.
        if (!empty($cfg->memory->absolute)) {
            $memAbsolute = self::parseMemoryValue($cfg->memory->absolute, 'Kb');
            $memLimit['virtual'] = min($memLimit['virtual'], $memAbsolute);
            $memLimit['rss'] = min($memLimit['virtual'], $memAbsolute);
        }

        return $memLimit;
    }

    /**
     * Returns amount of bytes for given string like "4 G";
     */
    public static function parseMemoryValue(string $value, string $destUnit = 'Kb'): int
    {
        $value = trim($value);
        preg_match('/(\d+)\s*(\w*)/', $value, $matches);
        if (!isset($matches[1])) {
            error_log("cannot determine scalar!");
            $scalar = 0;
            $unit = '';
        } else {
            $scalar = $matches[1];
            if (!empty($matches[2])) {
                $unit = strtolower($matches[2]);
            } else {
                $unit = '';
            }
        }

        switch ((string)$unit) {
            case 't':
            case 'tb':
                $scalar *= 1024;
            /* fall through */
            case 'g':
            case 'gb':
                $scalar *= 1024;
            /* fall through */
            case 'm':
            case 'mb':
                $scalar *= 1024;
            /* fall through */
            case 'k':
            case 'kb':
                $scalar *= 1024;
        }

        $destUnit = strtolower($destUnit);

        switch ((string)$destUnit) {
            case 't':
            case 'tb':
                $scalar /= 1024;
            /* fall through */
            case 'g':
            case 'gb':
                $scalar /= 1024;
            /* fall through */
            case 'm':
            case 'mb':
                $scalar /= 1024;
            /* fall through */
            case 'k':
            case 'kb':
                $scalar /= 1024;
        }

        return (int) ceil($scalar);
    }
}
