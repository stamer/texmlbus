<?php
/**
 * Released under MIT License
 * (c) 2019 Heinrich Stamerjohanns
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

$id = $request->getQueryParam('id', '');

if (!empty($id)) {
    $documentsDeleted = UtilManage::deleteDocument($id);
    $out['success'] = true;
    $out['documentsDeleted'] = $documentsDeleted;
    if ($documentsDeleted) {
        $out['message'] = 'Success.';
    } else {
        $out['message'] = 'Failed to delete document.';
    }
} else {
    $out['success'] = false;
    $out['message'] = 'No id specified. Unable to delete document.';
}

$data['result'] = $out;

$response->json($data);

