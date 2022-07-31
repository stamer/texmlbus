<?php
/**
 * Released under MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 */
require_once "../../include/IncFiles.php";

use Dmake\Dao;
use Dmake\JwToken;
use Server\Config;
use Server\RequestFactory;
use Server\ResponseFactory;

$cfg = Config::getConfig();

if ($cfg->auth->useJwToken) {
    JwToken::authenticate();
}

$dao = Dao::getInstance();
$request = RequestFactory::create();
$response = ResponseFactory::create();

$set = $request->getQueryParam('set', '');

$target_dir = ARTICLEDIR . '/upload/';
$ds          = DIRECTORY_SEPARATOR;

// request upload id for article
// figure out set
// handle zip file
//
if (!empty($_FILES)) {
    $target_file = $target_dir . basename($_FILES['file']['name']);
    // https://www.startutorial.com/articles/view/dropzonejs-php-how-to-display-existing-files-on-server
    if (move_uploaded_file($_FILES['file']['tmp_name'], $target_dir . $_FILES['file']['name'])) {
        header($_SERVER["SERVER_PROTOCOL"] . ' 200 OK', true);
        echo "File has been successfully uploaded.";
    } else {
        header($_SERVER["SERVER_PROTOCOL"] . ' 503 Service unavailable', true);
        echo "Uploading file failed.!";
    }
} else {
    $result = [];

    $files = scandir($target_dir);                 //1
    if (false !== $files) {
        foreach ($files as $file) {
            if ('.' !== $file &&  '..' !== $file) {       //2
                $obj['name'] = $file;
                $obj['size'] = filesize($target_dir.$ds.$file);
                $result[] = $obj;
            }
        }
    }

    header('Content-type: text/json');              //3
    header('Content-type: application/json');
    echo json_encode($result);
}

exit;

