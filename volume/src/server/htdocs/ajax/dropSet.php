<?php
/**
 * Released under MIT License
 * (c) 2021 Heinrich Stamerjohanns
 *
 */
require_once "../../include/IncFiles.php";
use Dmake\UtilManage;
use Dmake\JwToken;
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

if (!empty($set)) {
    $documentsDropped = UtilManage::dropSet($set);
    $out['success'] = true;
    $out['documentsDropped'] = $documentsDropped;
    $out['destSet'] = $set;
    $out['message'] = 'Set ' . htmlspecialchars($set) . ' dropped.';
} else {
    $out['success'] = false;
    $out['message'] = 'No set specified. Unable to drop set.';
}

$data['result'] = $out;

$response->json($data);

