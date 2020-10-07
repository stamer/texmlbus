<?php
/**
 * Released under MIT License
 * (c) 2019 Heinrich Stamerjohanns
 *
 */
require_once "../../include/IncFiles.php";
use Dmake\StatEntry;
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

if (!empty($id)) {
    $success = StatEntry::addToWorkqueueById($id, '', 'none', 0);
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

