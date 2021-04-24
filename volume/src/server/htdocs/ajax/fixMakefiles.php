<?php
/**
 * Released under MIT License
 * (c) 2021 Heinrich Stamerjohanns
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

$pf = new PrepareFiles();

$directory = ARTICLEDIR;
$subDirs = $pf->getAllSubDirs($directory);

$data['success'] = true;

$makefilesFixed = 0;

foreach ($subDirs as $subDir) {
    try {
        $count = $pf->fixMakefile($subDir);
        $makefilesFixed += $count;
    } catch (Throwable $t) {
        $out['success'] = false;
        $out['message'] = $t->getMessage();
        break;
     }
}

$out['makefilesFixed'] = $makefilesFixed;

$data['result'] = $out;

$response->json($data);

