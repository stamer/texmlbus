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

$projectId = $request->getParam('project_id', '');
$projectSrc = 'overleaf';

$username = $request->getParam('username', '');
$password = $request->getParam('password', '');
// param ist either 'true' or 'false'
$cacheParam = $request->getParam('cache', 'false');
$cache = ($cacheParam === 'true');

if (empty($set)) {
    $out['message'] = "No set specified, please select a set where to import to.";
    $out['success'] = false;
    $data['result'] = [
        'message' => "No set specified, please select a set where to import to.",
        'set' => '',
        'name' => $name,
        'success' => false
    ];
    $response->json($data);
    exit;
}

if (in_array($set, $cfg->upload->specialDirs)) {
    $data['result'] = [
        'message' => "The set name may not be named <em>" . htmlspecialchars($set) . "</em>.",
        'set' => $set,
        'name' => $name,
        'success' => false
    ];
    $response->json($data);
    exit;
}

foreach ($cfg->upload->forbiddenSubstrings as $item) {
    if (strpos($set, $item['substring']) !== false) {
        $data['result'] = [
            'message' => $item['message'],
            'set' => $set,
            'name' => $name,
            'success' => false
        ];
        $response->json($data);
        exit;
    }
}

$destDir = ARTICLEDIR . '/' . $set;
$destName = $destDir . '/' . $name;

if (is_dir($destName)) {
    $data['result'] = [
        'message' => "The name " . htmlspecialchars($destName) . " already exists in $set.",
        'set' => $set,
        'name' => $name,
        'success' => false
    ];
    $response->json($data);
    exit;
}

try {
    $git = new GitControl();
    $tmpDir = UtilFile::createTempDir(sys_get_temp_dir());
    $tmpDir .= '/' . $projectId;

    $execResult = $git->cloneOverleaf($projectId, $tmpDir, $username, $password, $cache);
} catch (Throwable $t) {
    $data['result'] = [
        'message' => $t->getMessage(),
        'set' => $set,
        'name' => $name,
        'success' => false
    ];
    $response->json($data);
    exit;
}

if (!is_dir($destDir)) {
    $result = mkdir($destDir);
    if (!$result) {
        $message = "Failed to create " . htmlspecialchars($destDir);
        error_log(__METHOD__ . ": " . $message);
        $data['result'] = [
            'message' => $message,
            'set' => $set,
            'name' => $name,
            'success' => false
        ];
        $response->json($data);
        exit;
    }
}


UtilFile::rename($tmpDir, $destName);
$pf = new PrepareFiles();
$directory = dirname($destName);
$subDirs = [$name];
$documentsImported = 0;
$out = [];
$out['files'] = [];
$out['set'] = $set;
$out['name'] = $name;

$data['success'] = true;

foreach ($subDirs as $subDir) {
    // Only directories relative to ARTICLEDIR are saved in database.
    $relativeDir = $set . '/' . $name;
    if (StatEntry::pathMatches($relativeDir, 2)) {
        error_log("Skipping " . htmlspecialchars($subDir) . ", entry already exists...");
        continue;
    }

    try {
        $result = $pf->importTex(
            $directory . '/' . $subDir,
            $directory,
            '',
            false,
            $projectId,
            $projectSrc);
        if (is_string($result)) {
            $documentsImported++;
        }
        $out['files'][$subDir] = $result;
    } catch (Throwable $t) {
        $out['success'] = false;
        $out['set'] = $set;
        $out['message'] = $t->getMessage();
        break;
    }
}

$out['documentsImported'] = $documentsImported;

$data['result'] = $out;
$response->json($data);
exit;
