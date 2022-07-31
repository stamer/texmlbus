<?php
/**
 * Released under MIT License
 * (c) 2021 Heinrich Stamerjohanns
 *
 */
require_once "../../include/IncFiles.php";

use Dmake\JwToken;
use Dmake\GitAction;
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

// If credentials are cached, password will be just empty.
$password = $request->getParam('password');
// Param ist either 'true' or 'false'.
$cacheParam = $request->getParam('cache', 'false');
$cache = ($cacheParam === 'true');

$entry = StatEntry::getById($id);

if ($entry === null) {
    $out['message'] = "No entry could be found for given id.";
    $out['success'] = false;
    $data['result'] = [
        'message' => "No entry could be found for given id.",
        'success' => false
    ];
    $response->json($data);
}

if (empty($entry->getProjectId()) ) {
    $out['message'] = "Not a project (projectid not found).";
    $out['success'] = false;
    $data['result'] = [
        'message' => "Not a project.",
        'success' => false
    ];
    $response->json($data);
}

$dir = ARTICLEDIR . '/' . $entry->getFilename();

try {
    $git = new GitControl();
    $out = $git->execCommand(GitControl::PULL, $dir, $password, $cache);
    // Not just the current Dir, but also worker directories need to be updated.
    $gitAction = new GitAction($dir);
    $gitAction->updateWorkerDirectories($out['output']);
} catch (Throwable $t) {
    $data['result'] = [
        'message' => $t->getMessage(),
        'success' => false
    ];
    $response->json($data);
    exit;
}

$out['output'] = implode('<br>', $out['output']);
$data['result'] = $out;
$response->json($data);
exit;
