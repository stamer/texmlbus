<?php
/**
 * Released under MIT License
 * (c) 2022 Heinrich Stamerjohanns
 *
 */
require_once "../../include/IncFiles.php";

use Dmake\JwToken;
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
$entry = StatEntry::getById($id);

if ($entry === null) {
    $data['result'] = [
        'success' => false,
        'message' => 'Document for id ' . $id . 'not found.'
    ];
    $response->json($data);
    exit;
}

$data['result'] = [
    'id' => $id,
    'filename' => $entry->getFilename(),
    'comment' => $entry->getComment(),
    'enum_comment_status' => array_keys(StatEntry::ENUM_COMMENT_STATUS),
    'comment_status' => $entry->getCommentStatus(),
    'comment_keyword' => $entry->getCommentKeyword(),
    'comment_date' => $entry->getCommentDate(),
    'success' => true
];

$response->json($data);
exit;
