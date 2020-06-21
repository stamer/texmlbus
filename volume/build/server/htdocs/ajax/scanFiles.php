<?php
/**
 * Released under MIT License
 * (c) 2019 Heinrich Stamerjohanns
 *
 */
require_once "../../include/IncFiles.php";

use Dmake\PrepareFiles;
use Dmake\JwToken;
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

$set = $request->getQueryParam('set', '');

$pf = new PrepareFiles();

$directory = ARTICLEDIR . '/' . $set;
$subDirs = $pf->getSubDirs($directory);
$documentsImported = 0;
$out = [];
$out['destSet'] = $set;
$out['files'] = [];

$data['success'] = true;

foreach ($subDirs as $subDir) {
    // Only directories relative to ARTICLEDIR are saved in database.
    $relativeDir = $set . '/' . $subDir;
    if (StatEntry::pathMatches($relativeDir, 2)) {
        error_log("Skipping $subDir, entry already exists...");
        continue;
    }

    try {
        $result = $pf->import($directory . '/' . $subDir, $directory);
        if (is_string($result)) {
            $documentsImported++;
        }
        $out['files'][$subDir] = $result;
    } catch (Throwable $t) {
        $out['success'] = false;
        $out['message'] = $t->getMessage();
        break;
     }
}

$out['documentsImported'] = $documentsImported;

$data['result'] = $out;

$response->json($data);

