<?php
/**
 * MIT License
 * (c) 2007-2021 Heinrich Stamerjohanns
 *
 *
 */

/**
 * File still needs to be 7.3 compatible, as it runs on worker.
 */
namespace Worker;

use Dmake\ApiWorkerRequest;
use Dmake\ApiResult;

use Dmake\UtilStage;
use Server\ServerRequest;

use stdClass;

class ApiWorkerHandler
{
    private $request;
    private $awr;
    private $resultAsJson;

    private $childTerminated = false;
    private $path;
    private $action;

    private $factorSeconds = 5;

    private $debug = true;


    public function __construct(
        ServerRequest $request,
        ApiWorkerRequest $awr,
        bool $resultAsJson = false
    ) {
        pcntl_async_signals(true);
        $this->request = $request;
        $this->awr = $awr;
        $this->resultAsJson = $resultAsJson;

        $this->path = parse_url($request->getServerParam('REQUEST_URI'), PHP_URL_PATH);

        $matches = [];
        preg_match('/.*?api\/(.*)$/', $this->path, $matches);

        if (empty($matches[1])) {
            $this->exitError('No action given.');
        }

        $this->action = $matches[1];
    }

    private function debug($message)
    {
        if ($this->debug) {
            error_log($message);
        }
    }
    /**
     * Possibly kill still running child if the client drops the connection.
     * @param stdClass $childData
     *
     */
    public function apiShutdown($childData)
    {
        $this->debug('apiShutdown...');

        if ($childData->signalChildren) {
            $this->terminateChildren($childData);
        }
        exit;
    }

    private function terminateChildren($childData)
    {
        // self and children
        error_log("Killing child processes...");
        $processes = array_reverse($this->getProcessChildren($childData->childPid));
        foreach ($processes as $pid) {
            $this->debug("Killing $pid");
            posix_kill($pid, SIGKILL);
        }
    }

    /**
     * Parse process list into array and return a list
     * of all children (any depth) of given pid.
     * @param $pid
     * @return mixed
     */
    private function getProcessChildren($pid)
    {
        // Possibly alpine specific.
        $output = shell_exec('/bin/ps -o pid,ppid');
        $output = trim($output);
        $lines = explode("\n", $output);
        $first = true;
        foreach ($lines as $line) {
            if ($first) {
                $first = false;
                continue;
            }
            preg_match('/\s+(\d+)\s+(\d+)/', $line, $matches);
            $pids[$matches[1]] = $matches[2];
        }
        $result = [];
        $processes = $this->getChildren($pid, $pids, $result);
        return $processes;
    }

    /**
     * Parse data recursively
     */
    private function getChildren(int $pid, array $pids, &$result) {
        if (isset($pids[$pid])) {
            foreach ($pids as $key => $ppid) {
                if ($ppid == $pid) {
                    $result[] = $key;
                    $this->getChildren($key, $pids, $result);
                }
            }
        }
        return $result;
    }

    /**
     * @param int $signo
     */
    private function sigUsr2($signo)
    {
        // reinstall, older OS might need it
        pcntl_signal(SIGUSR2, [$this, 'sigUsr2']);
        $this->debug( "Caught SIGUSR2");
        $this->childTerminated = true;
    }

    /**
     * Also creates output.
     */
    public function execute(): void
    {
        if ($this->resultAsJson) {
            header("Content-Type: application/json");
        } else {
            // create debug output in browser
            header("Content-Type: text/plain");
        }

        $this->debug("Action: $this->action");
        
        switch ((string)$this->action) {
            case '':
                $apiResult = new ApiResult(false, 'No action given.');
                break;
            case 'checkDir':
                $apiResult = $this->checkDir();
                break;
            case 'latexmlversion':
                $apiResult = $this->latexmlversion();
                break;
            case 'make':
                $apiResult = $this->runner();
                break;
            case 'meminfo':
                $apiResult = $this->meminfo();
                break;
            case 'pdftex':
                $apiResult = $this->execCommandWithParam();
                break;
            case 'testStyClsSupport':
                $apiResult = $this->testStyClsSupport();
                break;
            default:
                $apiResult = new ApiResult(false, 'Invalid action ' . htmlspecialchars($this->action ?? 'EMPTY') . '.');
        }

        if ($this->resultAsJson) {
            echo json_encode($apiResult, JSON_OBJECT_AS_ARRAY);
        } else {
            // create debug output in browser
            echo 'Success: ' . (int)$apiResult->getSuccess() . PHP_EOL;
            echo 'Output: ' . implode(PHP_EOL, $apiResult->getOutput()) . PHP_EOL;
            echo 'ShellReturn: ' . $apiResult->getShellReturnVar() . PHP_EOL;
        }
    }

    public function exitBadRequest(string $message)
    {
        header($this->request->getServerParam('SERVER_PROTOCOL') . ' 400 Bad Request');
        header("Content-Type: text/plain");
        echo $message;
        exit;
    }

    public function exitError(string $message)
    {
        header("Content-Type: text/plain");
        echo $message . PHP_EOL;
        exit;
    }

    public function runner() : ApiResult
    {
        $cfg = Config::getConfig();
        $childData = new stdClass();
        $childData->signalChildren = false;
        $childData->childPid = 0;
        register_shutdown_function([$this, 'apiShutdown'], $childData);

        $startTime = microtime(true);

        // Alternatively SharedTmpFile can be used, it does not
        // really matter.
        $shared = new SharedMem();
        $pid = pcntl_fork();

        switch ($pid) {
            case -1:
                error_log(__METHOD__  . "fork failed");
                break;

            case 0:
                // Child
                // yield()
                usleep(5);
                $this->debug("ChildPid: " . posix_getpid() . ', Parent: ' . posix_getppid());

                // do the actual conversion
                $apiResult = $this->make();
                $result = $shared->put($apiResult);

                // signal parent
                posix_kill(posix_getppid(), SIGUSR2);

                $shared->detach();
                // Wait to be killed by parent, this process may not
                // exit in php-fpm environment.
                // Keep running so grandchildren can be found.
                sleep(5);

                // Fallback if not yet killed by parent
                $this->debug('Process ' . posix_getpid() . ' killing itself...');
                posix_kill(posix_getpid(), SIGTERM);
                break; // make IDE happy..

            default:
                // Parent
                // Install signal handler, so child can signal termination.
                pcntl_signal(SIGUSR2, [$this, 'sigUsr2']);

                $childData->childPid = $pid;
                $this->debug('ChildPid: ' . $childData->childPid);

                // In order to detect a dropped connection by the client,
                // data needs to be sent to client.
                $i = 0;
                $childData->signalChildren = true;
                $this->debug("Waiting for children");

                // Simulate wait() NOHANG, because child may not die.
                // Therefore child sends SIGUSR2 when finished.
                // Signal handler sets childTerminated.
                while (!$this->childTerminated) {
                    $i++;

                    $this->debug($i . ': Running...');

                    /**
                     * The connection might be closed by client, long running scripts should finish then.
                     * In order to detect a dropped connection, data needs to be sent to client.
                     * php-fpm must be configured to disable output-buffering.
                     * <Directory ....>
                     * <FilesMatch "\.php$">
                     *     SetHandler "proxy:unix:/run/php-fpm.sock|fcgi://localhost"
                     * </FilesMatch>
                     * </Directory>
                     * <Proxy "fcgi://localhost/" enablereuse=on flushpackets=on max=10>
                     * </Proxy>
                     */
                    echo " "; // Send spaces, so json-data is not bothered.
                    ob_flush();
                    flush();

                    // Might be woken up by signal SIGUSR2
                    sleep($this->factorSeconds);

                    // This should never happen as connection will already have been closed by client.
                    if ($i * $this->factorSeconds > $cfg->timeout->default + 5) {
                        break;
                    }
                }

                if ($this->childTerminated) {
                    $this->debug('Child terminated...');
                    // Children do not need to be signaled any more,
                    // as all child processes have terminated.
                    $childData->signalChildren = false;
                } else {
                    $this->debug('Timeout waiting for child');
                }

                if ($this->childTerminated
                    && $shared->exists()
                ) {
                    $apiResult = $shared->get();
                } else {
                    $apiResult = new ApiResult(
                        false,
                        'Timout waiting for child',
                        ApiResult::TIMEOUT
                    );
                    // Children are explicitly terminated,
                    // apiShutdown() should not try again.
                    $childData->signalChildren = false;
                    $this->terminateChildren($childData);
                }

                posix_kill($childData->childPid, SIGKILL);

                // Child processes are already gone, but needs to be called to avoid zombies.
                // Parent will never actually wait, but child resources are cleaned up.
                pcntl_waitpid($childData->childPid, $status);
                $shared->remove();

                $usedTime = microtime(true) - $startTime;
                error_log("Time needed: $usedTime");

                return $apiResult;
        }
    }

    public function make() : ApiResult
    {
        $cfg = Config::getConfig();
        $apr = new ApiResult();

        $stage = $this->awr->getStage();
        if (empty($cfg->stages[$stage])) {
            $this->exitBadRequest("Invalid stage: $stage");
        }
        $action = $this->awr->getMakeAction();
        if (empty($action)) {
            $this->exitBadRequest("Empty action");
        }

        $host = (array) $this->awr->getHost();
        $makeCommand = UtilStage::getMakeCommand(
            $host,
            $action,
            $cfg->stages[$stage]->makeLog);

        $execStr = '';
        if (isset($host['path'])) {
            // We need to put this in front to make sure we get the right latexml
            $execStr .= 'export PATH=' . $host['path'] . ':$PATH;';
        } else {
            $execStr .= 'export PATH=/bin:/usr/bin' . ':$PATH;';
        }

        if (isset($host['memlimitRss'])) {
            // Limit the amount of memory the worker may use.
            $execStr .= 'ulimit -m ' . $host['memlimitRss'] . '; ';
        }
        if (isset($host['memlimitVirtual'])) {
            // Limit the amount of memory the worker may use.
            $execStr .= 'ulimit -v ' . $host['memlimitVirtual'] . '; ';
        }

        $sourceDir = UtilStage::getSourceDir(
            $host['dir'],
            $this->awr->getDirectory(),
            $this->awr->getWorker()
        );

        $execStr .= 'umask 0002; cd \'' . $sourceDir . '\';' . $makeCommand;

        $this->debug("Executing: $execStr");
        exec($execStr, $output, $shellReturnVar);

        $apr->setOutput($output);
        $apr->setShellReturnVar($shellReturnVar);
        $apr->setSuccess($shellReturnVar == 0);
        return $apr;
    }

    public function meminfo() : ApiResult
    {
        $apr = new ApiResult();

        $host = $this->request->getServerParam('HTTP_HOST');

        $execStr = 'PATH=/bin:/usr/bin; export PATH;';
        $execStr .= "echo \'$host\' OK; /bin/cat /proc/meminfo";

        exec($execStr, $output, $shellReturnVar);

        $apr->setOutput($output);
        $apr->setShellReturnVar($shellReturnVar);
        $apr->setSuccess($shellReturnVar == 0);
        return $apr;
    }

    public function execCommandWithParam() : ApiResult
    {
        $directory = $this->awr->getDirectory();
        if (empty($directory)) {
            $this->exitBadRequest('Empty directory');
        }
        $parameter = $this->awr->getParameter();
        if (empty($parameter)) {
            $this->exitBadRequest('Empty parameter');
        }

        $execStr = 'PATH=/bin:/usr/bin; export PATH;';
        $execStr .= 'cd ' . $directory . ';';
        $execStr .= $this->awr->getCommand() . ' ' . $parameter;

        exec($execStr, $output, $shellReturnVar);

        $apr = new ApiResult();
        $apr->setOutput($output);
        $apr->setShellReturnVar($shellReturnVar);
        $apr->setSuccess($shellReturnVar == 0);
        return $apr;
    }
    public function checkDir() : ApiResult
    {
        $directory = $this->awr->getDirectory();
        if (empty($directory)) {
            $this->exitBadRequest('Empty directory');
        }

        $execStr = 'PATH=/bin:/usr/bin; export PATH;';
        $execStr .= 'cd ' . $directory;

        exec($execStr, $output, $shellReturnVar);

        $apr = new ApiResult();
        $apr->setOutput($output);
        $apr->setShellReturnVar($shellReturnVar);
        $apr->setSuccess($shellReturnVar == 0);
        return $apr;
    }

    public function testStyClsSupport() : ApiResult
    {
        $parameter = $this->awr->getParameter();
        if (empty($parameter)) {
            $this->exitBadRequest('Empty Parameter');
        }

        $execStr = '/usr/bin/php ' . BUILDDIR . '/script/php/testStyClsSupport.php '
            . "\\\\\''" . base64_encode(json_encode($parameter)) . "'\\\\\'";

        exec($execStr, $output, $shellReturnVar);

        $apr = new ApiResult();
        $apr->setOutput($output);
        $apr->setShellReturnVar($shellReturnVar);
        $apr->setSuccess($shellReturnVar == 0);
        return $apr;
    }
    public function latexmlversion() : ApiResult
    {
        $cfg = Config::getConfig();
        $execStr = $cfg->app->latexml . ' --VERSION 2>&1';

        exec($execStr, $output, $shellReturnVar);

        $apr = new ApiResult();
        $apr->setOutput($output);
        $apr->setShellReturnVar($shellReturnVar);
        $apr->setSuccess($shellReturnVar == 0);
        return $apr;
    }
}
