<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 *
 */
namespace Dmake;

require_once "IncFiles.php";

use Dmake\ApiResult;
use Dmake\HistoryAction;
use Dmake\InotifyHandler;
use Dmake\StatEntry;
use Server\RequestFactory;

class Api
{
    const NO_ACTION = 128;

    protected $path = '';
    protected $action = '';
    protected $priority = 1;
    protected $resultAsJson = false;
    protected $inotify;
    protected $request;

    public function __construct($uri, $resultAsJson = false)
    {
        $this->resultAsJson = $resultAsJson;
        $this->path = parse_url($uri, PHP_URL_PATH);

        $matches = array();
        preg_match('/.*?api\/(.*)$/', $this->path, $matches);

        if (empty($matches[1])) {
            echo 'No action given.' . PHP_EOL;
        }

        $this->action = $matches[1];

        $this->inotify = new InotifyHandler();

        $this->request = RequestFactory::create();
    }

    /**
     *
     * @return ApiResult
     */
    public function execute()
    {
        switch ((string)$this->action) {
            case '':
                $apiResult = new ApiResult(self::NO_ACTION, 'No action given.');
                break;
            case 'add':
                // adds an articles to the build system
                $apiResult = $this->add();
                break;
            case 'clean':
                // cleans up target for articles
                $target = $this->request->getParam('target', '');
                if (empty($target)) {
                    $apiResult = new ApiResult(false, 'No target given.');
                    break;
                }
                $apiResult = $this->clean($target);
                break;
            case 'del':
                // removes an article from the build system
                $apiResult = $this->del();
                break;
            case 'queue':
                // queues an article; if the article target already exists
                // it will not be recreated
                // this make sense if xml target has already included pdf generation
                // and now pdf is being queued
                $target = $this->request->getParam('target', '');
                if (empty($target)) {
                    $apiResult = new ApiResult(false, 'No target given.');
                    break;
                }
                $apiResult = $this->queue($target);
                break;
            case 'rerun':
                // clean + rerun
                // will for recreation of target and queues target
                $target = $this->request->getParam('target', '');
                if (empty($target)) {
                    $apiResult = new ApiResult(false, 'No target given.');
                    break;
                }
                $apiResult = $this->rerun($target);
                break;
            case 'snapshot':
                // create history snapshot
                $set = $this->request->getParam('set', '');
                if (!empty($set)) {
                    $apiResult = $this->snapshot($set);
                    break;
                } else {
                    $apiResult = $this->snapshot(null);
                }
                break;
            default:
                $apiResult = new ApiResult(self::NO_ACTION, 'Invalid action ' . htmlspecialchars($this->action) . '.');
        }

        if ($this->resultAsJson) {
            header("Content-Type: application/json");
            echo json_encode($apiResult, JSON_OBJECT_AS_ARRAY);
        } else {
            // create debug output in browser
            header("Content-Type: text/plain");
            echo 'Success: ' . intval($apiResult->getSuccess()) . PHP_EOL;
            echo 'Output: ' . join(PHP_EOL, $apiResult->getOutput()) . PHP_EOL;
            echo 'ShellReturn: ' . $apiResult->getShellReturnVar() . PHP_EOL;
        }
    }

    /**
     *
     * @return ApiResult
     */
    public function add()
    {
        $directory = $this->request->getParam('dir', '');
        $sourcefile = $this->request->getParam('sourcefile', '');
        if (!empty($directory) && !empty($sourcefile)) {
            $minDepth = 1;
            $result = StatEntry::addNew($directory, $sourcefile, $minDepth);
            $this->inotify->trigger();
            return new ApiResult($result);
        } else {
            return new ApiResult(false, 'Incomplete Parameters');
        }
    }

    /**
     *
     * @return ApiResult
     */
    public function del()
    {
        $id = $this->request->getParam('id', '');
        $dir = $this->request->getParam('dir', '');
        if ($id !== '') {
            $result = StatEntry::deleteById($id);
            return new ApiResult($result);
        } elseif ($dir !== '') {
            $result = StatEntry::deleteByFilename($dir);
            return new ApiResult($result);
        } else {
            return new ApiResult(false, 'Incomplete Parameters');
        }
    }

    /**
     *
     * @return ApiResult
     */
    public function clean($stage)
    {
        /** @var $statEntry StatEntry */
        $cfg = Config::getConfig();
        $possibleTargets = array_keys($cfg->stages);
        if (!in_array(preg_replace('/clean$/', '', $stage), $possibleTargets)) {
            return new ApiResult(false, 'Invalid stage.');
        }

        $id = $this->request->getParam('id', '');
        $dir = $this->request->getParam('dir', '');
        if ($id !== '') {
            $statEntry = StatEntry::getById($id);
            if (!($statEntry instanceof StatEntry)) {
                return new ApiResult(false, 'Id ' . $id . ' not found.');
            }
            $directory = $statEntry->filename;
        } elseif ($dir !== '') {
            $statEntry = StatEntry::getByDir($dir);
            if (!($statEntry instanceof StatEntry)) {
                return new ApiResult(false, 'Dir ' . $dir . ' not found.');
            }
            $directory = $statEntry->filename;
        } else {
            return new ApiResult(false, 'Incomplete Parameters');
        }

        // needs to be done via workqueue, so files are deleted in context of dmake
        $result = StatEntry::addToWorkqueue($directory, $stage, 1);
        $this->inotify->trigger(InotifyHandler::wqTrigger);
        sleep(1);
        $returnVar = 0;
        $success = true;
        $statEntry = StatEntry::getById($statEntry->id);
        if ($statEntry->action = StatEntry::WQ_ACTION_NONE) {
            $output = "Done.";
        } else {
            $output = "Cleanup queued.";
        }

        return new ApiResult($success, $output, $returnVar);
    }

    /**
     * Job is queued, if this job has already run before, it will not be recreated.
     * @return ApiResult
     */
    public function queue($stage)
    {
        $cfg = Config::getConfig();
        $possibleTargets = array_keys($cfg->stages);
        if (!in_array($stage, $possibleTargets)) {
            return new ApiResult(false, 'Invalid stage.');
        }
        $id = $this->request->getParam('id', '');
        $dir = $this->request->getParam('dir', '');
        if ($id !== '') {
            $result = StatEntry::addToWorkqueueById($id, $stage, $this->priority);
            $this->inotify->trigger(InotifyHandler::wqTrigger);
            return new ApiResult($result);
        } elseif (!empty($dir)) {
            $result = StatEntry::addToWorkqueue($dir, $stage, $this->priority);
            $this->inotify->trigger(InotifyHandler::wqTrigger);
            return new ApiResult($result);
        } else {
            return new ApiResult(false, 'Incomplete Parameters');
        }
    }

    /**
     * Rerun Job is clean + queue
     * @param string $stage
     * @return ApiResult
     */
    public function rerun($stage)
    {
        $cfg = Config::getConfig();

        $apiResult = $this->clean($stage . 'clean');
        if (!$apiResult->getSuccess()) {
            return $apiResult;
        }

        $apiResult = $this->queue($stage);
        return $apiResult;
    }

    /**
     * create history snapshot
     * @return ApiResult
     */
    public function snapshot($setname)
    {
        if (!empty($set)) {
            $set['set'] = $setname;
            HistoryAction::createHistorySumEntry($set);
        } else {
            HistoryAction::createHistorySumEntries();
        }
        return new ApiResult(true);
    }
}
