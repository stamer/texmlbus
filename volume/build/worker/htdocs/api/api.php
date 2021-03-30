<?php
/**
 * Released under MIT License
 * (c) 2007 - 2018 Heinrich Stamerjohanns
 *
 */
require_once '../../include/IncFiles.php';

use Dmake\ApiWorkerRequest;
use Worker\ApiWorkerResponse;
use Worker\ApiWorkerHandler;
use Worker\Config;

use Server\RequestFactory;

ini_set('memory_limit', '2G');
ini_set('max_execution_time', 600);

$cfg = Config::getConfig();
$request = RequestFactory::create();

$debug = false;

// This will only decide whether output is written or returned as json.
// No security implication.
// For ajax requests and format=json return json.
$resultAsJson = (
    $request->getServerParam('HTTP_X_REQUESTED_WITH', '') === 'XMLHttpRequest'
    || $request->getParam('format', '') === 'json'
    || $request->getServerParam('HTTP_ACCEPT') === 'application/json'
);

if ($debug) {
    error_log(print_r($request->getServerParam('HTTP_ACCEPT'), 1));
    error_log('resultAsJson: ' . $resultAsJson);
}

try {
    $awr = new ApiWorkerRequest(file_get_contents('php://input'));
} catch (\Exception $e) {
    $apr = new ApiWorkerResponse();
    $apr->badRequest('Invalid JSON: ' . $e->getMessage());
}

$awh = new ApiWorkerHandler($request, $awr, $resultAsJson);

// execute() creates Reponse.
$awh->execute();
