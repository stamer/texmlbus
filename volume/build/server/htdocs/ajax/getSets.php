<?php
/**
 * Released under MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 */
require_once "../../include/IncFiles.php";

use Dmake\StatEntry;
use Server\Config;
use Server\RequestFactory;
use Server\ResponseFactory;

$cfg = Config::getConfig();
$request = RequestFactory::create();
$response = ResponseFactory::create();

$query = $request->getQueryParam('q', '');

$sets = StatEntry::getSets($query);

$out['results'] = [];
foreach ($sets as $key => $set) {
    $setName = $set['set'];
    $item = new StdClass();
    $item->id = $setName;
    $item->text = $setName;
    $out['results'][] = $item;
}

$out['pagination'] = new StdClass;
$out['pagination']->more = false;

$response->json($out);

