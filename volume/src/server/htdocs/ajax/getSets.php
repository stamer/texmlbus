<?php
/**
 * Released under MIT License
 * (c) 2007 - 2019 Heinrich Stamerjohanns
 *
 */
require_once "../../include/IncFiles.php";

use Dmake\Set;
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

$query = $request->getQueryParam('q', '');

$sets = Set::getSets($query);

$out['results'] = [];
foreach ($sets as $key => $set) {
    $item = new stdClass();
    $item->id = $set->getName();
    $item->text = $set->getName();
    $out['results'][] = $item;
}

$out['pagination'] = new stdClass;
$out['pagination']->more = false;

$response->json($out);

