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
    $success = WorkqueueEntry::disableEntry($id, $stage);
    $out['success'] = $success;
    if ($success) {
        $out['message'] = 'Success.';
    } else {
        $out['message'] = 'Failed to dequeue document.';
    }
} else {
    $out['success'] = false;
    $out['message'] = 'No id specified. Unable to dequeue document.';
}

$data['result'] = $out;

$response->json($data);

