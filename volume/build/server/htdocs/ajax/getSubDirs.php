<?php
/**
 * Released under MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 */
require_once "../../include/IncFiles.php";

use Dmake\UtilFile;
use Dmake\JwToken;
use Server\Config;
use Server\RequestFactory;
use Server\ResponseFactory;

$cfg = Config::getConfig();

if ($cfg->auth->useJwToken) {
    JwToken::authenticateByCookie();
}

$request = RequestFactory::create();
$response = ResponseFactory::create();

$query = $request->getQueryParam('q','');

$data['results'] = [];


$subDirs = UtilFile::getSubDirs(ARTICLEDIR);

foreach ($subDirs as $key => $subDir) {
    // upload or sty dir should not be shown
    if (in_array($subDir, $cfg->upload->specialDirs)) {
        continue;
    }
    $item = new StdClass();
    $item->id = $subDir;
    $item->text = $subDir;
    $data['results'][] = $item;
}

$data['pagination'] = new StdClass;
$data['pagination']->more = false;

$response->json($data);

