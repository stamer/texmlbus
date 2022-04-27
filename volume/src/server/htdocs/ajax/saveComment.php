<?php
/**
 * Released under MIT License
 * (c) 2022 Heinrich Stamerjohanns
 *
 */
require_once "../../include/IncFiles.php";

use DateTime;
use Dmake\PrepareFiles;
use Dmake\JwToken;
use Dmake\UtilFile;
use Dmake\GitControl;
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
$data = [];

$id = $request->getParam('id');
$comment = $request->getParam('comment');
$comment_status = $request->getParam('comment_status');
$comment_keyword = $request->getParam('comment_keyword');
$comment_date = $request->getParam('comment_date');
$entry = StatEntry::getById($id);

if (empty($entry)) {
    $data['result'] = [
        'success' => false,
        'message' => 'Document for id ' . $id . 'not found.'
    ];
    $response->json($data);
    exit;
}

$entry->setComment($comment);
$entry->setCommentStatus($comment_status);
$entry->setCommentKeyword($comment_keyword);
$entry->setCommentDate((new DateTime())->format('Y-m-d H:i:s'));
$success = $entry->save();

$data['result'] = [
    'id' => $id,
    'success' => $success
];

$response->json($data);
exit;
