<?php
/**
 * Released under MIT License
 * (c) 2021 Heinrich Stamerjohanns
 *
 */
require_once "../../include/IncFiles.php";

use Dmake\PrepareFiles;
use Dmake\JwToken;
use Dmake\UtilFile;
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

$set = $request->getParam('set');
$name = $request->getParam('name');
$project_id = $request->getParam('project_id');
$username = $request->getParam('username');

// determine protocol, host and username...
$protocol = GitControl::OVERLEAF_PROTOCOL;
$host = GitControl::OVERLEAF_HOST;
$gc = new GitControl();
$hasCredentials = $gc->hasCredentials($protocol, $host, $username);

$data['result'] = [
    'success' => $hasCredentials,
    'set' => $set,
    'name' => $name,
    'project_id' => $project_id,
    'username' => $username
];

$response->json($data);
exit;
