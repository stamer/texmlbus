<?php
/**
 * MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 *
 */
namespace Dmake;

require_once "IncFiles.php";

use Server\RequestFactory;

class Api
{
    public const NO_ACTION = 128;

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
    public function execute(): void
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
                $stage = $this->request->getParam('stage', '');
                if (empty($stage)) {
                    $apiResult = new ApiResult(false, 'No stage given.');
                    break;
                }
                $target = $this->request->getParam('target', '');
                if (empty($target)) {
                    $apiResult = new ApiResult(false, 'No target given.');
                    break;
                }
                $apiResult = $this->clean($stage, $target);
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
                $stage = $this->request->getParam('stage', '');
                if (empty($stage)) {
                    $apiResult = new ApiResult(false, 'No stage given.');
                    break;
                }
                $target = $this->request->getParam('target', '');
                if (empty($target)) {
                    $apiResult = new ApiResult(false, 'No target given.');
                    break;
                }
                $apiResult = $this->queue($stage, $target);
                break;
            case 'rerun':
                // clean + rerun
                // will for recreation of target and queues target
                $stage = $this->request->getParam('stage', '');
                if (empty($stage)) {
                    $apiResult = new ApiResult(false, 'No stage given.');
                    break;
                }
                $target = $this->request->getParam('target', '');
                if (empty($target)) {
                    $apiResult = new ApiResult(false, 'No target given.');
                    break;
                }
                $apiResult = $this->rerun($stage, $target);
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
            echo 'Success: ' . (int) $apiResult->getSuccess() . PHP_EOL;
            echo 'Output: ' . implode(PHP_EOL, $apiResult->getOutput()) . PHP_EOL;
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
            // ??
            // $this->inotify->trigger();
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
            $result = StatEntry::deleteByDirectory($dir);
            return new ApiResult($result);
        } else {
            return new ApiResult(false, 'Incomplete Parameters');
        }
    }

    /**
     *
     * @return ApiResult
     */
    public function clean($stage, $target)
    {
        /** @var StatEntry $statEntry */
        $possibleTargets = UtilStage::getPossibleTargets();
        $baseTarget = preg_replace('/clean$/', '', $target);
        if (!in_array($baseTarget, $possibleTargets)) {
            return new ApiResult(false, 'Invalid target.');
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
        $hostGroup = UtilStage::getHostGroupByStage($stage);
        $result = StatEntry::addToWorkqueue($directory, $hostGroup, $stage, $target,1);
        $this->inotify->trigger($hostGroup, InotifyHandler::wqTrigger);
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
    public function queue($stage, $target)
    {
        $possibleTargets = UtilStage::getPossibleTargets();
        if (!in_array($target, $possibleTargets)) {
            return new ApiResult(false, 'Invalid target.');
        }
        $id = $this->request->getParam('id', '');
        $dir = $this->request->getParam('dir', '');
        $hostGroup = UtilStage::getHostGroupByStage($stage);
        if ($id !== '') {
            $result = StatEntry::addToWorkqueueById($id, $hostGroup, $stage, $target, $this->priority);
            $this->inotify->trigger($hostGroup, InotifyHandler::wqTrigger);
            return new ApiResult($result);
        } elseif (!empty($dir)) {
            $result = StatEntry::addToWorkqueue($dir, $hostGroup, $stage, $target, $this->priority);
            $this->inotify->trigger($hostGroup, InotifyHandler::wqTrigger);
            return new ApiResult($result);
        } else {
            return new ApiResult(false, 'Incomplete Parameters');
        }
    }

    /**
     * Rerun Job is clean + queue
     * @param string $stage
     * @param string $target
     * @return ApiResult
     */
    public function rerun($stage, $target)
    {
        $apiResult = $this->clean($stage, $target . 'clean');
        if (!$apiResult->getSuccess()) {
            return $apiResult;
        }

        $apiResult = $this->queue($stage, $target);
        return $apiResult;
    }

    /**
     * create history snapshot
     * @return ApiResult
     */
    public function snapshot($setname)
    {
        if (!empty($setname)) {
            $set['set'] = $setname;
            HistoryAction::createHistorySumEntry($set);
        } else {
            HistoryAction::createHistorySumEntries();
        }
        return new ApiResult(true);
    }
}
