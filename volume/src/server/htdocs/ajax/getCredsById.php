<?php
/**
 * Released under MIT License
 * (c) 2021 Heinrich Stamerjohanns
 *
 */
require_once "../../include/IncFiles.php";

use Dmake\JwToken;
use Dmake\GitControl;
use Dmake\StatEntry;
use Server\Config;
use Server\RequestFactory;
use Server\ResponseFactory;

$cfg = Config::getConfig();

if ($cfg->auth->useJwToken) {
    JwToken::authenticate();
}

$request = RequestFactory::create();
$response = ResponseFactory::create();
$data = [];

$id = $request->getParam('id');
$entry = StatEntry::getById($id);

if ($entry === null) {
    $data['result'] = [
        'success' => false,
        'message' => 'Document for id ' . $id . 'not found.'
    ];
    $response->json($data);
    exit;
}

$dirname = ARTICLEDIR . '/' . $entry->getFilename();

// determine protocol, host and username...
$gc = new GitControl();
$protocol = $gc::OVERLEAF_PROTOCOL;
$host = $gc::OVERLEAF_HOST;
$username = $gc->getUsernameByDir($dirname);

$hasCredentials = $gc->hasCredentials($protocol, $host, $username);

$data['result'] = [
    'id' => $id,
    'username' => $username,
    'success' => $hasCredentials
];

$response->json($data);
exit;
