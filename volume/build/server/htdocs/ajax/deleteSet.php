<?php
/**
 * Released under MIT License
 * (c) 2019 Heinrich Stamerjohanns
 *
 */
require_once "../../include/IncFiles.php";
use Dmake\UtilManage;
use Server\Config;
use Server\RequestFactory;
use Server\ResponseFactory;

$cfg = Config::getConfig();
$request = RequestFactory::create();
$response = ResponseFactory::create();

$set = $request->getQueryParam('set', '');

if (!empty($set)) {
    $documentsDeleted = UtilManage::deleteSet($set);
    $out['success'] = true;
    $out['documentsDeleted'] = $documentsDeleted;
    $out['destSet'] = $set;
    $out['message'] = 'Set ' . htmlspecialchars($set) . ' deleted.';
} else {
    $out['success'] = false;
    $out['message'] = 'No set specified. Unable to delete set.';
}

$data['result'] = $out;

$response->json($data);

