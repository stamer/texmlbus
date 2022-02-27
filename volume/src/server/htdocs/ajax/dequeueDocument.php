<?php
/**
 * Released under MIT License
 * (c) 2019 Heinrich Stamerjohanns
 *
 */
require_once "../../include/IncFiles.php";
use Dmake\WorkqueueEntry;
use Dmake\JwToken;
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
$stage = $request->getQueryParam('stage', '');

if (!empty($id)) {
    $wqEntry = WorkqueueEntry::getByStatisticIdAndStage($id, $stage);
    if ($wqEntry && $wqEntry->getPid()) {
        $success = posix_kill($wqEntry->getPid(), SIGHUP);
        if ($success) {
            $out['message'] = 'Success. Please wait to complete...';
        } else {
            $out['message'] = 'Failed to stop conversion.';
        }
    } else {
        $success = WorkqueueEntry::disableEntry($id, $stage);
        if ($success) {
            $out['message'] = 'Success.';
        } else {
            $out['message'] = 'Failed to dequeue document.';
        }
    }
    $out['success'] = $success;
} else {
    $out['success'] = false;
    $out['message'] = 'No id specified. Unable to dequeue document.';
}

$data['result'] = $out;

$response->json($data);

