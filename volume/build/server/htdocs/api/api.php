<?php
/**
 * Released under MIT License
 * (c) 2007 - 2018 Heinrich Stamerjohanns
 *
 */
require_once __DIR__ .'/../../../dmake/IncFiles.php';

use Dmake\Api;
use Dmake\Config;
use Dmake\Dao;
use Dmake\JwToken;

use Server\RequestFactory;

$cfg = Config::getConfig();
$dao = Dao::getInstance();
$request = RequestFactory::create();

// this will only decide whether output is written or returned as json
// no security implication
// for ajax requests and format=json return json
$resultAsJson = ('XMLHttpRequest' == ($request->getServerParam('HTTP_X_REQUESTED_WITH', '')))
            || ($request->getParam('format', '') === 'json');

if ($cfg->auth->useJwToken) {
    JwToken::authenticate();
}

$api = new Api($request->getServerParam('REQUEST_URI'), $resultAsJson);

$api->execute();

exit;
