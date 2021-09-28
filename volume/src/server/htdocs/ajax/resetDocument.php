<?php
/**
 * Released under MIT License
 * (c) 2019 Heinrich Stamerjohanns
 *
 */
require_once "../../include/IncFiles.php";
use Dmake\InotifyHandler;
use Dmake\JwToken;
use Dmake\UtilManage;
use Dmake\UtilStage;
use Server\Config;
use Server\RequestFactory;
use Server\ResponseFactory;

$cfg = Config::getConfig();

if ($cfg->auth->useJwToken) {
    JwToken::authenticate();
}

$request = RequestFactory::create();
$response = ResponseFactory::create();

$id = $request->getQueryParam('id', '');

if (!empty($id)) {
    $stagesReset = UtilManage::resetDocument($id);
    $inotify = new InotifyHandler();
    if ($inotify->isActive()) {
        $hostGroups = UtilStage::getHostGroups();
        foreach ($hostGroups as $hostGroupName) {
            $inotify->trigger($hostGroupName, InotifyHandler::doneTrigger);
        }
    }

    $out['success'] = true;
    $out['stagesReset'] = $stagesReset;
    if ($stagesReset) {
        $out['message'] = 'Success.';
    } else {
        $out['message'] = 'No stages have been reset.';
    }
} else {
    $out['success'] = false;
    $out['message'] = 'No id specified. Unable to reset document.';
}

$data['result'] = $out;

$response->json($data);

