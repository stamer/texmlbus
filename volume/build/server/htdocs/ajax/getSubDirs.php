<?php
/**
 * Released under MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 */
require_once "../../include/IncFiles.php";

use Dmake\UtilFile;
use Server\Config;
use Server\RequestFactory;
use Server\ResponseFactory;

$cfg = Config::getConfig();
$request = RequestFactory::create();
$response = ResponseFactory::create();

$query = $request->getQueryParam('q','');

$data['results'] = [];

$uploadDirName = basename(UPLOADDIR);

$subDirs = UtilFile::getSubDirs(ARTICLEDIR);

foreach ($subDirs as $key => $subDir) {
    // upload Dir should not be shown
    if ($subDir == $uploadDirName) {
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

