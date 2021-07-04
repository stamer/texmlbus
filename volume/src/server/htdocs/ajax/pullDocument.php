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

$id = $request->getParam('id');

// If credentials are cache, password will be just empty.
$password = $request->getParam('password');
// param ist either 'true' or 'false'
$cacheParam = $request->getParam('cache', 'false');
$cache = ($cacheParam === 'true');

$entry = StatEntry::getById($id);

if (empty($entry) ) {
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
} catch (Throwable $t) {
    $data['result'] = [
        'message' => $t->getMessage(),
        'success' => false
    ];
    $response->json($data);
    exit;
}

$data['result'] = $out;
$response->json($data);
exit;
