<?php
/**
 * Released under MIT License
 * (c) 2020 Heinrich Stamerjohanns
 *
 */
require_once "../../include/IncFiles.php";
use Dmake\JwToken;
use Dmake\UtilFile;
use Server\Config;
use Server\RequestFactory;
use Server\ResponseFactory;

$cfg = Config::getConfig();

if ($cfg->auth->useJwToken) {
    JwToken::authenticate();
}

$request = RequestFactory::create();
$response = ResponseFactory::create();

$file = $request->getQueryParam('file', '');

// only used for output
$item = $request->getQueryParam('item', '');
// only
if ($item !== 'directory') {
    $item = 'file';
}
$item = ucfirst($item);

if (!empty($file)) {
    if (str_contains($file, '..')) {
        $out['success'] = false;
        $out['message'] = 'Illegal filename';
    } else {
        // must be prepended
        $fullFilename = ARTICLESTYDIR . '/' . $file;
        $result = UtilFile::deleteDirR($fullFilename);
        $out['success'] = $result;
        $out['filesDeleted'] = (int)$result;
        $out['destFile'] = $file;
        if ($result) {
            $out['message'] = $item . ' ' . htmlspecialchars($file) . ' deleted.';
        } else {
            $out['message'] = 'Unable to delete ' . $item . ' ' . htmlspecialchars($file);
        }
    }
} else {
    $out['success'] = false;
    $out['message'] = 'No file specified. Unable to delete file.';
}

$data['result'] = $out;

$response->json($data);

