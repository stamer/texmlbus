<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 *
 */
namespace Worker;

use Dmake\ApiWorkerRequest;
use Dmake\ApiResult;
use Dmake\ApiResultArray;
use Server\ServerRequest;

class ApiWorkerHandler
{
    private $request;
    private $awr;

    private $path;
    private $action;

    public function __construct(
        ServerRequest $request,
        ApiWorkerRequest $awr,
        bool $resultAsJson = false
    ) {
        $this->request = $request;
        $this->awr = $awr;
        $this->resultAsJson = $resultAsJson;

        $this->path = parse_url($request->getServerParam('REQUEST_URI'), PHP_URL_PATH);

        $matches = array();
        preg_match('/.*?api\/(.*)$/', $this->path, $matches);

        if (empty($matches[1])) {
            $this->exitError('No action given.');
        }

        $this->action = $matches[1];
    }

    /**
     *
     */
    public function execute(): void
    {
        switch ((string)$this->action) {
            case '':
                $apiResult = new ApiResult(self::NO_ACTION, 'No action given.');
                break;
            case 'checkDir':
                $apiResult = $this->checkDir();
                break;
            case 'latexmlversion':
                $apiResult = $this->latexmlversion();
                break;
            case 'make':
                $apiResult = $this->make();
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
            header("Content-Type: application/json");
            echo json_encode($apiResult, JSON_OBJECT_AS_ARRAY);
        } else {
            // create debug output in browser
            header("Content-Type: text/plain");
            echo 'Success: ' . (int)$apiResult->getSuccess() . PHP_EOL;
            echo 'Output: ' . implode(PHP_EOL, $apiResult->getOutput()) . PHP_EOL;
            echo 'ShellReturn: ' . $apiResult->getShellReturnVar() . PHP_EOL;
        }
    }

    public function exitBadRequest($message)
    {
        header($this->request->getServerParam('SERVER_PROTOCOL') . ' 400 Bad Request');
        echo $message;
        exit;
    }

    public function exitError($message)
    {
        header("Content-Type: text/plain");
        echo $message . PHP_EOL;
        exit;
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

        $execStr = 'PATH=/bin:/usr/bin; export PATH;';
        $execStr .= 'cd ' . ARTICLEDIR . '/' . $this->awr->getDirectory() . ';';
        $execStr .= $cfg->stages[$stage]->command . ' ' . $action;
        if (isset($cfg->stage[$stage]->makelog)) {
            $execStr .= '2>&1 | tee ' . $cfg->stage[$stage]->makelog;
        }

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
